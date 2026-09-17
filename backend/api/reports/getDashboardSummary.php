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
    sendResponse(false, 'Method not allowed', null, 405);
}



$salesSql = "
    SELECT
        COUNT(*) AS total_sales,
        COALESCE(SUM(total_amount), 0) AS total_revenue
    FROM sales
    WHERE created_at >= CURDATE()
    AND created_at < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
";

$salesResult = mysqli_query($conn, $salesSql);

if (!$salesResult) {
    sendResponse(
        false,
        'Failed to retrieve sales summary',
        null,
        500
    );
}

$sales = mysqli_fetch_assoc($salesResult);

mysqli_free_result($salesResult);



$lowStockSql = "
    SELECT COUNT(*) AS low_stock_count
    FROM products
    WHERE status = 'Active'
    AND quantity <= reorder_level
";

$lowStockResult = mysqli_query($conn, $lowStockSql);

if (!$lowStockResult) {
    sendResponse(
        false,
        'Failed to retrieve low-stock summary',
        null,
        500
    );
}

$lowStock = mysqli_fetch_assoc($lowStockResult);

mysqli_free_result($lowStockResult);



$outOfStockSql = "
    SELECT COUNT(*) AS out_of_stock_count
    FROM products
    WHERE status = 'Active'
    AND quantity = 0
";

$outOfStockResult = mysqli_query($conn, $outOfStockSql);

if (!$outOfStockResult) {
    sendResponse(
        false,
        'Failed to retrieve out-of-stock summary',
        null,
        500
    );
}

$outOfStock = mysqli_fetch_assoc($outOfStockResult);

mysqli_free_result($outOfStockResult);



$productsSql = "
    SELECT COUNT(*) AS total_products
    FROM products
    WHERE status = 'Active'
";

$productsResult = mysqli_query($conn, $productsSql);

if (!$productsResult) {
    sendResponse(
        false,
        'Failed to retrieve product summary',
        null,
        500
    );
}

$products = mysqli_fetch_assoc($productsResult);

mysqli_free_result($productsResult);



sendResponse(
    true,
    'Dashboard summary retrieved successfully',
    [
        'today' => [
            'totalSales' => (int) $sales['total_sales'],
            'totalRevenue' => (float) $sales['total_revenue']
        ],

        'inventory' => [
            'totalProducts' => (int) $products['total_products'],
            'lowStockProducts' => (int) $lowStock['low_stock_count'],
            'outOfStockProducts' => (int) $outOfStock['out_of_stock_count']
        ]
    ]
);