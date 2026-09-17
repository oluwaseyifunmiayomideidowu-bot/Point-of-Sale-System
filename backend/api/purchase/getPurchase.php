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
        sendResponse(false, 'Valid purchase ID is required', null, 400);
    }

    $purchaseId = (int) $_GET['id'];



    $purchaseSql = "
        SELECT
            pu.id,
            pu.supplier_id,
            pu.user_id,
            pu.purchase_number,
            pu.total_amount,
            pu.created_at,

            s.username AS supplier_name,

            CONCAT(u.first_name, ' ', u.last_name) AS created_by

        FROM purchases pu

        INNER JOIN suppliers s
            ON pu.supplier_id = s.id

        INNER JOIN users u
            ON pu.user_id = u.id

        WHERE pu.id = ?

        LIMIT 1
    ";

    $purchaseStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($purchaseStmt, $purchaseSql)) {
        sendResponse(false, 'Failed to prepare purchase query', null, 500);
    }

    mysqli_stmt_bind_param(
        $purchaseStmt,
        'i',
        $purchaseId
    );

    if (!mysqli_stmt_execute($purchaseStmt)) {
        mysqli_stmt_close($purchaseStmt);
        sendResponse(false, 'Failed to retrieve purchase', null, 500);
    }

    $purchaseResult = mysqli_stmt_get_result($purchaseStmt);

    if (mysqli_num_rows($purchaseResult) === 0) {
        mysqli_stmt_close($purchaseStmt);
        sendResponse(false, 'Purchase not found', null, 404);
    }

    $purchase = mysqli_fetch_assoc($purchaseResult);

    mysqli_stmt_close($purchaseStmt);



    $itemsSql = "
        SELECT
            pi.id,
            pi.product_id,
            p.name AS product_name,
            p.sku,
            pi.quantity,
            pi.cost_price,
            pi.subtotal

        FROM purchase_items pi

        INNER JOIN products p
            ON pi.product_id = p.id

        WHERE pi.purchase_id = ?

        ORDER BY pi.id ASC
    ";

    $itemsStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($itemsStmt, $itemsSql)) {
        sendResponse(false, 'Failed to prepare purchase items query', null, 500);
    }

    mysqli_stmt_bind_param(
        $itemsStmt,
        'i',
        $purchaseId
    );

    if (!mysqli_stmt_execute($itemsStmt)) {
        mysqli_stmt_close($itemsStmt);
        sendResponse(false, 'Failed to retrieve purchase items', null, 500);
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
            'unitCost' => (float) $item['cost_price'],
            'subtotal' => (float) $item['subtotal']
        ];
    }

    mysqli_stmt_close($itemsStmt);



    $purchase['id'] = (int) $purchase['id'];
    $purchase['supplier_id'] = (int) $purchase['supplier_id'];
    $purchase['user_id'] = (int) $purchase['user_id'];
    $purchase['total_amount'] = (float) $purchase['total_amount'];

    $purchase['items'] = $items;



    sendResponse(
        true,
        'Purchase retrieved successfully',
        $purchase
    );