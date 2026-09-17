
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

    $productId = $_GET['id'] ?? null;

    if (!filter_var($productId, FILTER_VALIDATE_INT) || (int)$productId <= 0) {
        sendResponse(false, 'Invalid product ID.', null, 422);
    }

    $productId = (int)$productId;

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
        c.id AS category_id,
        c.name AS category_name,
        s.id AS supplier_id,
        s.username AS username,
        p.created_at,
        p.updated_at
    FROM products p
    INNER JOIN categories c ON p.category_id = c.id
    INNER JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.id = ?
    LIMIT 1";
    
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $selectSql)) {
        sendResponse(false, 'Failed to prepare product query.', null, 500);
    }

    mysqli_stmt_bind_param($stmt, 'i', $productId);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        sendResponse(false, 'Failed to retrieve product.', null, 500);
    }

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        sendResponse(false, 'Product not found.', null, 404);
    }

    $product = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    sendResponse(true, 'Product retrieved successfully.', $product);
?>