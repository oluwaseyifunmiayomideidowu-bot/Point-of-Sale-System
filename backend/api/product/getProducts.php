
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/Product.php';
    require_once __DIR__ . '/../../models/Category.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed.', null, 405);
    }

    requireAuth();
    requireRole(['Administrator', 'Manager']);

    $selectSql = "SELECT 
        p.id,
        p.name,
        p.sku,
        p.cost_price,
        p.selling_price,
        p.quantity,
        p.reorder_level,
        p.image,
        p.status,
        c.name AS category_name,
        s.username AS username,
        p.created_at,
        p.updated_at
    FROM products p
    INNER JOIN categories c ON p.category_id = c.id
    INNER JOIN suppliers s ON p.supplier_id = s.id
    ORDER BY p.created_at DESC";

    $result = mysqli_query($conn, $selectSql);

    if (!$result) {
        sendResponse(false, 'Failed to retrieve products.', null, 500);
    }

    $products = [];

    while ($product = mysqli_fetch_assoc($result)) {
        $products[] = $product;
    }

    mysqli_free_result($result);

    sendResponse(true, 'Products retrieved successfully.', $products);
?>