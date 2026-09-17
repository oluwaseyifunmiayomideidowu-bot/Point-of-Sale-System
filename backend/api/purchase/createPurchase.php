
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



    $supplierName = trim($data['supplierName'] ?? '');

    if ($supplierName === '') {
        sendResponse(false, 'Supplier name is required', null, 400);
    }

    $supplier = findSupplier($conn, ["username" =>$supplierName]);

    if ($supplier === false) {
        sendResponse(false, 'Failed to find supplier', null, 500);
    }

    if ($supplier === null) {
        sendResponse(false, 'Supplier not found', null, 404);
    }

    $supplierId = (int) $supplier['id'];



    if (
        !isset($data['items']) ||
        !is_array($data['items']) ||
        count($data['items']) === 0
    ) {
        sendResponse(false, 'At least one purchase item is required', null, 400);
    }



    mysqli_begin_transaction($conn);

    try {

        $purchaseItems = [];
        $totalAmount = 0;



        foreach ($data['items'] as $item) {

            if (!is_array($item)) {
                throw new Exception('Invalid purchase item.');
            }

            if (
                !isset($item['productId']) ||
                !filter_var($item['productId'], FILTER_VALIDATE_INT) ||
                $item['productId'] <= 0
            ) {
                throw new Exception('Valid product ID is required.');
            }

            if (
                !isset($item['quantity']) ||
                !filter_var($item['quantity'], FILTER_VALIDATE_INT) ||
                $item['quantity'] <= 0
            ) {
                throw new Exception('Purchase quantity must be a positive integer.');
            }

            if (
                !isset($item['unitCost']) ||
                !is_numeric($item['unitCost']) ||
                (float) $item['unitCost'] < 0
            ) {
                throw new Exception('Valid unit cost is required.');
            }

            $productId = (int) $item['productId'];
            $quantity = (int) $item['quantity'];
            $unitCost = (float) $item['unitCost'];



            $productSql = "
                SELECT id, name, sku, status
                FROM products
                WHERE id = ?
                LIMIT 1
                FOR UPDATE
            ";

            $productStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($productStmt, $productSql)) {
                throw new Exception('Failed to prepare product query.');
            }

            mysqli_stmt_bind_param(
                $productStmt,
                'i',
                $productId
            );

            if (!mysqli_stmt_execute($productStmt)) {
                mysqli_stmt_close($productStmt);
                throw new Exception('Failed to check product.');
            }

            $productResult = mysqli_stmt_get_result($productStmt);

            if (mysqli_num_rows($productResult) === 0) {
                mysqli_stmt_close($productStmt);
                throw new Exception("Product ID {$productId} not found.");
            }

            $product = mysqli_fetch_assoc($productResult);

            mysqli_stmt_close($productStmt);

            if ($product['status'] !== 'active') {
                throw new Exception(
                    "Product '{$product['name']}' is inactive."
                );
            }



            $subtotal = $quantity * $unitCost;

            $totalAmount += $subtotal;


            $purchaseItems[] = [
                'productId' => $productId,
                'productName' => $product['name'],
                'sku' => $product['sku'],
                'quantity' => $quantity,
                'unitCost' => $unitCost,
                'subtotal' => $subtotal
            ];
        }



        $purchaseNumber = 'PUR-' . strtoupper(
            date('YmdHis') . '-' . bin2hex(random_bytes(3))
        );



        $userId = (int) $_SESSION['user_id'];

        $purchaseSql = "
            INSERT INTO purchases (
                supplier_id,
                user_id,
                purchase_number,
                total_amount
            )
            VALUES (?, ?, ?, ?)
        ";

        $purchaseStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($purchaseStmt, $purchaseSql)) {
            throw new Exception('Failed to prepare purchase query.');
        }

        mysqli_stmt_bind_param(
            $purchaseStmt,
            'iisd',
            $supplierId,
            $userId,
            $purchaseNumber,
            $totalAmount
        );

        if (!mysqli_stmt_execute($purchaseStmt)) {
            mysqli_stmt_close($purchaseStmt);
            throw new Exception('Failed to create purchase.');
        }

        $purchaseId = mysqli_insert_id($conn);

        mysqli_stmt_close($purchaseStmt);



        foreach ($purchaseItems as $item) {

            $itemSql = "
                INSERT INTO purchase_items (
                    purchase_id,
                    product_id,
                    quantity,
                    cost_price,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?)
            ";

            $itemStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($itemStmt, $itemSql)) {
                throw new Exception('Failed to prepare purchase item query.');
            }

            mysqli_stmt_bind_param(
                $itemStmt,
                'iiidd',
                $purchaseId,
                $item['productId'],
                $item['quantity'],
                $item['unitCost'],
                $item['subtotal']
            );

            if (!mysqli_stmt_execute($itemStmt)) {
                mysqli_stmt_close($itemStmt);
                throw new Exception('Failed to create purchase item.');
            }

            mysqli_stmt_close($itemStmt);



            $stockSql = "
                UPDATE products
                SET quantity = quantity + ?
                WHERE id = ?
            ";

            $stockStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($stockStmt, $stockSql)) {
                throw new Exception('Failed to prepare stock update query.');
            }

            mysqli_stmt_bind_param(
                $stockStmt,
                'ii',
                $item['quantity'],
                $item['productId']
            );

            if (!mysqli_stmt_execute($stockStmt)) {
                mysqli_stmt_close($stockStmt);
                throw new Exception('Failed to update product stock.');
            }

            mysqli_stmt_close($stockStmt);
        }



        mysqli_commit($conn);

        sendResponse(
            true,
            'Purchase created successfully',
            [
                'purchaseId' => $purchaseId,
                'purchaseNumber' => $purchaseNumber,
                'supplierId' => $supplierId,
                'supplierName' => $supplierName,
                'totalAmount' => $totalAmount,
                'items' => $purchaseItems
            ],
            201
        );

    } catch (Throwable $e) {

        mysqli_rollback($conn);

        sendResponse(
            false,
            $e->getMessage(),
            null,
            400
        );
    }