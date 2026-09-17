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

    $selectSql = "
        SELECT
            s.id,
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
        ORDER BY s.created_at DESC
    ";

    $result = mysqli_query($conn, $selectSql);

    if (!$result) {
        sendResponse(
            false,
            'Failed to retrieve sales.',
            null,
            500
        );
    }

    $sales = [];

    while ($sale = mysqli_fetch_assoc($result)) {
        $sales[] = $sale;
    }

    mysqli_free_result($result);

    sendResponse(
        true,
        'Sales retrieved successfully.',
        $sales
    );