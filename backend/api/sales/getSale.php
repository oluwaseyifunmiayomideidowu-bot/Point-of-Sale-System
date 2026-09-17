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

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed.', null, 405);
    }

    requireAuth();
    requireRole(['Administrator', 'Manager', 'Cashier']);

    $saleId = $_GET['id'] ?? null;

    if (!filter_var($saleId, FILTER_VALIDATE_INT) || (int)$saleId <= 0) {
        sendResponse(false, 'Invalid sale ID.', null, 422);
    }

    $saleId = (int)$saleId;


    // Get sale
    $saleSql = "
        SELECT
            s.id,
            s.user_id,
            s.subtotal,
            s.discount,
            s.tax,
            s.total_amount,
            s.payment_method,
            s.amount_received,
            s.change_amount,
            s.created_at,
            u.first_name,
            u.last_name
        FROM sales s
        INNER JOIN users u
            ON s.user_id = u.id
        WHERE s.id = ?
        LIMIT 1
    ";

    $saleStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($saleStmt, $saleSql)) {
        sendResponse(false, 'Failed to prepare sale query.', null, 500);
    }

    mysqli_stmt_bind_param($saleStmt, 'i', $saleId);

    if (!mysqli_stmt_execute($saleStmt)) {
        mysqli_stmt_close($saleStmt);
        sendResponse(false, 'Failed to retrieve sale.', null, 500);
    }

    $saleResult = mysqli_stmt_get_result($saleStmt);

    if (mysqli_num_rows($saleResult) === 0) {
        mysqli_stmt_close($saleStmt);
        sendResponse(false, 'Sale not found.', null, 404);
    }

    $sale = mysqli_fetch_assoc($saleResult);

    mysqli_stmt_close($saleStmt);


    // Get sale items
    $itemSql = "
        SELECT
            si.id,
            si.product_id,
            p.name AS product_name,
            p.sku,
            si.quantity,
            si.unit_price,
            si.subtotal
        FROM sale_items si
        INNER JOIN products p
            ON si.product_id = p.id
        WHERE si.sale_id = ?
        ORDER BY si.id ASC
    ";

    $itemStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($itemStmt, $itemSql)) {
        sendResponse(false, 'Failed to prepare sale items query.', null, 500);
    }

    mysqli_stmt_bind_param($itemStmt, 'i', $saleId);

    if (!mysqli_stmt_execute($itemStmt)) {
        mysqli_stmt_close($itemStmt);
        sendResponse(false, 'Failed to retrieve sale items.', null, 500);
    }

    $itemResult = mysqli_stmt_get_result($itemStmt);

    $items = [];

    while ($item = mysqli_fetch_assoc($itemResult)) {
        $items[] = $item;
    }

    mysqli_stmt_close($itemStmt);


    // Combine sale and items
    $sale['items'] = $items;

    sendResponse(
        true,
        'Sale retrieved successfully.',
        $sale
    );
?>