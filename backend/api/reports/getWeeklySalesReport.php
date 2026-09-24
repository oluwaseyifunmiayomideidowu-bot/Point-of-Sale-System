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


requireAuth();
requireRole(['Administrator', 'Manager']);

$sql = "
    SELECT
        DATE(created_at) AS sale_date,
        DAYNAME(created_at) AS day_name,
        COALESCE(SUM(subtotal), 0) AS total_sales
    FROM sales
    WHERE created_at >= CURDATE() - INTERVAL 6 DAY
      AND created_at < CURDATE() + INTERVAL 1 DAY
    GROUP BY DATE(created_at), DAYNAME(created_at)
    ORDER BY sale_date ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    sendResponse(
        false,
        'Failed to retrieve weekly sales',
        null,
        500
    );
}

$salesByDate = [];

while ($row = mysqli_fetch_assoc($result)) {
    $salesByDate[$row['sale_date']] = [
        'date' => $row['sale_date'],
        'day' => date('D', strtotime($row['sale_date'])),
        'totalSales' => (float) $row['total_sales']
    ];
}

mysqli_free_result($result);

$sales = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));

    $sales[] = $salesByDate[$date] ?? [
        'date' => $date,
        'day' => date('D', strtotime($date)),
        'totalSales' => 0
    ];
}

sendResponse(
    true,
    'Weekly sales retrieved successfully',
    [
        'sales' => $sales
    ]
);