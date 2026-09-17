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


    $sql = "
        SELECT
            p.id AS product_id,
            p.name AS product_name,
            p.sku,
            p.quantity,
            p.reorder_level,

            c.name AS category_name,
            s.username AS supplier_name

        FROM products p

        LEFT JOIN categories c
            ON p.category_id = c.id

        LEFT JOIN suppliers s
            ON p.supplier_id = s.id

        WHERE p.status = 'Active'
        AND p.quantity <= p.reorder_level

        ORDER BY p.quantity ASC, p.name ASC
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        sendResponse(
            false,
            'Failed to retrieve low-stock products',
            null,
            500
        );
    }

    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $quantity = (int) $row['quantity'];
        $reorderLevel = (int) $row['reorder_level'];

        $products[] = [
            'productId' => (int) $row['product_id'],
            'productName' => $row['product_name'],
            'sku' => $row['sku'],
            'quantity' => $quantity,
            'reorderLevel' => $reorderLevel,
            'categoryName' => $row['category_name'],
            'supplierName' => $row['supplier_name'],
            'outOfStock' => $quantity === 0
        ];
    }

    mysqli_free_result($result);


    sendResponse(
        true,
        'Low-stock products retrieved successfully',
        [
            'totalProducts' => count($products),
            'products' => $products
        ]
    );
?>