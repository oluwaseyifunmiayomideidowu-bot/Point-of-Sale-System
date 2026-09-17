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

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
        sendResponse(false, 'Valid return ID is required', null, 400);
    }

    $returnId = (int) $_GET['id'];


    $returnSql = "
        SELECT
            r.id,
            r.sale_id,
            r.user_id,
            r.return_number,
            r.reason,
            r.total_refund,
            r.created_at,
            CONCAT(u.first_name, ' ', u.last_name) AS processed_by
        FROM returns r
        INNER JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
        LIMIT 1
    ";

    $returnStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($returnStmt, $returnSql)) {
        sendResponse(false, 'Failed to prepare return query', null, 500);
    }

    mysqli_stmt_bind_param($returnStmt, 'i', $returnId);

    if (!mysqli_stmt_execute($returnStmt)) {
        mysqli_stmt_close($returnStmt);
        sendResponse(false, 'Failed to get return', null, 500);
    }

    $returnResult = mysqli_stmt_get_result($returnStmt);

    if (mysqli_num_rows($returnResult) === 0) {
        mysqli_stmt_close($returnStmt);
        sendResponse(false, 'Return not found', null, 404);
    }

    $return = mysqli_fetch_assoc($returnResult);

    mysqli_stmt_close($returnStmt);



    $itemsSql = "
        SELECT
            ri.id,
            ri.product_id,
            p.name AS product_name,
            p.sku,
            ri.quantity,
            ri.unit_price,
            ri.subtotal
        FROM return_items ri
        INNER JOIN products p ON ri.product_id = p.id
        WHERE ri.return_id = ?
        ORDER BY ri.id ASC
    ";

    $itemsStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($itemsStmt, $itemsSql)) {
        sendResponse(false, 'Failed to prepare return items query', null, 500);
    }

    mysqli_stmt_bind_param($itemsStmt, 'i', $returnId);

    if (!mysqli_stmt_execute($itemsStmt)) {
        mysqli_stmt_close($itemsStmt);
        sendResponse(false, 'Failed to get return items', null, 500);
    }

    $itemsResult = mysqli_stmt_get_result($itemsStmt);

    $items = [];

    while ($item = mysqli_fetch_assoc($itemsResult)) {
        $items[] = [
            'id' => (int) $item['id'],
            'productId' => (int) $item['product_id'],
            'productName' => $item['product_name'],
            'sku' => $item['sku'],
            'quantity' => (int) $item['quantity'],
            'unitPrice' => (float) $item['unit_price'],
            'subtotal' => (float) $item['subtotal']
        ];
    }

    mysqli_stmt_close($itemsStmt);



    $return['id'] = (int) $return['id'];
    $return['sale_id'] = (int) $return['sale_id'];
    $return['user_id'] = (int) $return['user_id'];
    $return['total_refund'] = (float) $return['total_refund'];

    $return['items'] = $items;

    sendResponse(
        true,
        'Return retrieved successfully',
        $return
    );