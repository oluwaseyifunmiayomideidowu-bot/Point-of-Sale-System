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


// Stock health by category: current units compared with the category's
// combined reorder levels. This keeps the dashboard bars tied to inventory.
$categorySql = "
    SELECT
        COALESCE(c.name, 'Uncategorised') AS category_name,
        COALESCE(SUM(p.quantity), 0) AS current_quantity,
        COALESCE(SUM(p.reorder_level), 0) AS reorder_quantity
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.status = 'Active'
    GROUP BY c.id, c.name
    ORDER BY category_name ASC
";

$categoryResult = mysqli_query($conn, $categorySql);

if (!$categoryResult) {
    sendResponse(false, 'Failed to retrieve category inventory summary', null, 500);
}

requireAuth();
requireRole(['Administrator', 'Manager']);

$categories = [];

while ($category = mysqli_fetch_assoc($categoryResult)) {
    $currentQuantity = (int) $category['current_quantity'];
    $reorderQuantity = (int) $category['reorder_quantity'];
    $healthPercent = $reorderQuantity > 0
        ? min(100, (int) round(($currentQuantity / $reorderQuantity) * 100))
        : 100;

    $categories[] = [
        'name' => $category['category_name'],
        'currentQuantity' => $currentQuantity,
        'reorderQuantity' => $reorderQuantity,
        'healthPercent' => $healthPercent
    ];
}

mysqli_free_result($categoryResult);

$alertsSql = "
    SELECT name, sku, quantity, reorder_level
    FROM products
    WHERE status = 'Active' AND quantity <= reorder_level
    ORDER BY quantity ASC, name ASC
    LIMIT 3
";

$alertsResult = mysqli_query($conn, $alertsSql);

if (!$alertsResult) {
    sendResponse(false, 'Failed to retrieve stock alerts', null, 500);
}

$stockAlerts = [];

while ($alert = mysqli_fetch_assoc($alertsResult)) {
    $stockAlerts[] = [
        'name' => $alert['name'],
        'sku' => $alert['sku'],
        'quantity' => (int) $alert['quantity'],
        'reorderLevel' => (int) $alert['reorder_level']
    ];
}

mysqli_free_result($alertsResult);

$recentSalesSql = "
    SELECT
            s.id,
            s.sale_number,
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
    LIMIT 3
";

$recentSalesResult = mysqli_query($conn, $recentSalesSql);

if (!$recentSalesResult) {
    sendResponse(false, 'Failed to retrieve recent sales', null, 500);
}

$recentSales = [];

while ($sale = mysqli_fetch_assoc($recentSalesResult)) {
    $recentSales[] = [
        'saleNumber' => $sale['sale_number'],
        'totalAmount' => (float) $sale['total_amount'],
        'paymentMethod' => $sale['payment_method'],
        'createdAt' => $sale['created_at'],
        'firstName'=> $sale['first_name'],
        'lastName'=> $sale['last_name'],
    ];
}

mysqli_free_result($recentSalesResult);



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
        ],
        'categories' => $categories,
        'stockAlerts' => $stockAlerts,
        'recentSales' => $recentSales
    ]
);
