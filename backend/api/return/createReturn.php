<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/Product.php';
    require_once __DIR__ . '/../../models/Category.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../models/Sale.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';


    requireAuth();
    requireRole(['Administrator', 'Manager']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid JSON data', null, 400);
    }



    if (!isset($data['saleId']) ||!filter_var($data['saleId'], FILTER_VALIDATE_INT) ||$data['saleId'] <= 0){
        sendResponse(false, 'Valid sale ID is required', null, 400);
    }

    $saleId = (int) $data['saleId'];

    $saleNumber = $data['saleNumber'];

    $reason = trim($data['reason'] ?? '');

    if ($reason === '') {
        sendResponse(false, 'Return reason is required', null, 400);
    }

    if (strlen($reason) > 255) {
        sendResponse(false, 'Return reason must not exceed 255 characters', null, 400);
    }

    if ($saleNumber === '') {
        sendResponse(false, 'Sale Number is required', null, 400);
    }

    if (!preg_match("/^[a-zA-Z0-9_.-]+$/", $saleNumber)) {
        sendResponse(false, 'Sale Number contain invaild character ', null, 400);
    }

    if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) === 0){
        sendResponse(false, 'At least one return item is required', null, 400);
    }

    $saleSql = "
        SELECT id, sale_number
        FROM sales
        WHERE sale_number = ?
        LIMIT 1
    ";

    $saleStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($saleStmt, $saleSql)) {
        sendResponse(false, 'Failed to prepare sale query', null, 500);
    }

    mysqli_stmt_bind_param($saleStmt, 's', $saleNumber);

    if (!mysqli_stmt_execute($saleStmt)) {
        mysqli_stmt_close($saleStmt);
        sendResponse(false, 'Failed to check sale', null, 500);
    }

    $saleResult = mysqli_stmt_get_result($saleStmt);

    if (mysqli_num_rows($saleResult) === 0) {
        mysqli_stmt_close($saleStmt);
        sendResponse(false, 'Sale not found', null, 404);
    }

    mysqli_stmt_close($saleStmt);

    mysqli_begin_transaction($conn);

    try {

        $returnItems = [];
        $totalRefund = 0;

        foreach ($data['items'] as $item) {

            if (!is_array($item)) {
                throw new Exception('Invalid return item.');
            }

            if (!isset($item['productId']) || !filter_var($item['productId'], FILTER_VALIDATE_INT) || $item['productId'] <= 0){
                throw new Exception('Valid product ID is required for every return item.');
            }

            if (!isset($item['quantity']) || !filter_var($item['quantity'], FILTER_VALIDATE_INT) || $item['quantity'] <= 0){
                throw new Exception('Return quantity must be a positive integer.');
            }

            $productId = (int) $item['productId'];
            $returnQuantity = (int) $item['quantity'];

            $saleItemSql = "
                SELECT
                    si.product_id,
                    si.quantity,
                    si.unit_price,
                    p.name AS product_name,
                    p.sku
                FROM sale_items si
                INNER JOIN products p ON si.product_id = p.id
                WHERE si.sale_number = ?
                AND si.product_id = ?
                LIMIT 1
                FOR UPDATE
            ";

            $saleItemStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($saleItemStmt, $saleItemSql)) {
                throw new Exception('Failed to prepare sale item query.');
            }

            mysqli_stmt_bind_param( $saleItemStmt, 'si', $saleNumber, $productId);

            if (!mysqli_stmt_execute($saleItemStmt)) {
                mysqli_stmt_close($saleItemStmt);
                throw new Exception('Failed to check sold product.');
            }

            $saleItemResult = mysqli_stmt_get_result($saleItemStmt);

            if (mysqli_num_rows($saleItemResult) === 0) {
                mysqli_stmt_close($saleItemStmt);

                throw new Exception(
                    "Product ID {$productId} was not part of this sale."
                );
            }

            $saleItem = mysqli_fetch_assoc($saleItemResult);

            mysqli_stmt_close($saleItemStmt);

            $originalQuantity = (int) $saleItem['quantity'];
            $unitPrice = (float) $saleItem['unit_price'];

            $returnedSql = "
                SELECT COALESCE(SUM(ri.quantity), 0) AS returned_quantity
                FROM return_items ri
                INNER JOIN returns r ON ri.return_id = r.id
                WHERE r.sale_id = ?
                AND ri.product_id = ?
            ";

            $returnedStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($returnedStmt, $returnedSql)) {
                throw new Exception('Failed to prepare returned quantity query.');
            }

            mysqli_stmt_bind_param($returnedStmt, 'ii', $saleId, $productId);

            if (!mysqli_stmt_execute($returnedStmt)) {
                mysqli_stmt_close($returnedStmt);
                throw new Exception('Failed to check previous returns.');
            }

            $returnedResult = mysqli_stmt_get_result($returnedStmt);
            $returnedRow = mysqli_fetch_assoc($returnedResult);

            mysqli_stmt_close($returnedStmt);

            $alreadyReturned = (int) $returnedRow['returned_quantity'];

            $remainingQuantity = $originalQuantity - $alreadyReturned;

            if ($returnQuantity > $remainingQuantity) {
                throw new Exception(
                    "Cannot return {$returnQuantity} unit(s) of {$saleItem['product_name']}. " .
                    "Only {$remainingQuantity} unit(s) remain returnable."
                );
            }

            $subtotal = $unitPrice * $returnQuantity;

            $totalRefund += $subtotal;

            $returnItems[] = [
                'productId' => $productId,
                'productName' => $saleItem['product_name'],
                'sku' => $saleItem['sku'],
                'quantity' => $returnQuantity,
                'unitPrice' => $unitPrice,
                'subtotal' => $subtotal
            ];
        }
        
        $returnNumber = 'RET-' . strtoupper(
            date('YmdHis') . '-' . bin2hex(random_bytes(3))
        );
        
        $userId = (int) $_SESSION['user_id'];

        $returnSql = "
            INSERT INTO returns (
                sale_id,
                user_id,
                return_number,
                reason,
                total_refund
            )
            VALUES (?, ?, ?, ?, ?)
        ";

        $returnStmt = mysqli_stmt_init($conn);

        if(!mysqli_stmt_prepare($returnStmt, $returnSql)){
            throw new Exception('Failed to prepare return insert query.');
        }

        mysqli_stmt_bind_param($returnStmt, 'iissd', $saleId, $userId, $returnNumber, $reason, $totalRefund);

        if(!mysqli_stmt_execute($returnStmt)){
            mysqli_stmt_close($returnStmt);
            throw new Exception('Failed to create return.');
        }

        $returnId = mysqli_insert_id($conn);

        mysqli_stmt_close($returnStmt);

        foreach ($returnItems as $item) {

            $insertItemSql = "
                INSERT INTO return_items (
                    return_id,
                    product_id,
                    quantity,
                    unit_price,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?)
            ";

            $insertItemStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($insertItemStmt, $insertItemSql)) {
                throw new Exception('Failed to prepare return item query.');
            }

            mysqli_stmt_bind_param($insertItemStmt, 'iiidd', $returnId, $item['productId'], $item['quantity'], $item['unitPrice'], $item['subtotal']);

            if (!mysqli_stmt_execute($insertItemStmt)) {
                mysqli_stmt_close($insertItemStmt);
                throw new Exception('Failed to create return item.');
            }

            mysqli_stmt_close($insertItemStmt);
            
            $updateStockSql = "
                UPDATE products
                SET quantity = quantity + ?
                WHERE id = ?
            ";

            $stockStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($stockStmt, $updateStockSql)) {
                throw new Exception('Failed to prepare stock update query.');
            }

            mysqli_stmt_bind_param($stockStmt, 'ii', $item['quantity'], $item['productId']);

            if (!mysqli_stmt_execute($stockStmt)) {
                mysqli_stmt_close($stockStmt);
                throw new Exception('Failed to restore product stock.');
            }

            mysqli_stmt_close($stockStmt);
        }

        mysqli_commit($conn);
        sendResponse(true, 'Return created successfully',
            [
                'returnId' => $returnId,
                'returnNumber' => $returnNumber,
                'saleId' => $saleId,
                'reason' => $reason,
                'totalRefund' => $totalRefund,
                'items' => $returnItems
            ],
            201
        );

    } catch (Throwable $e) {

        mysqli_rollback($conn);
        sendResponse(false, $e->getMessage(), null, 400);
    }
?>