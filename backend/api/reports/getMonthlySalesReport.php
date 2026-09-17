
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


    $month = $_GET['month'] ?? date('Y-m');

    $monthObject = DateTime::createFromFormat('!Y-m', $month);

    if (
        !$monthObject ||
        $monthObject->format('Y-m') !== $month
    ) {
        sendResponse(
            false,
            'Month must be in YYYY-MM format',
            null,
            400
        );
    }

    $startDate = $month . '-01';

    $endDateObject = new DateTime($startDate);
    $endDateObject->modify('+1 month');

    $endDate = $endDateObject->format('Y-m-d');


    $sql = "
        SELECT
            DATE(created_at) AS sale_date,
            COUNT(*) AS total_sales,
            COALESCE(SUM(subtotal), 0) AS subtotal,
            COALESCE(SUM(discount), 0) AS discount,
            COALESCE(SUM(tax), 0) AS tax,
            COALESCE(SUM(total_amount), 0) AS total_revenue
        FROM sales
        WHERE created_at >= ?
        AND created_at < ?
        GROUP BY DATE(created_at)
        ORDER BY sale_date ASC
    ";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        sendResponse(
            false,
            'Failed to prepare monthly sales query',
            null,
            500
        );
    }

    mysqli_stmt_bind_param(
        $stmt,
        'ss',
        $startDate,
        $endDate
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        sendResponse(
            false,
            'Failed to retrieve monthly sales report',
            null,
            500
        );
    }

    $result = mysqli_stmt_get_result($stmt);

    $dailySales = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $dailySales[] = [
            'date' => $row['sale_date'],
            'totalSales' => (int) $row['total_sales'],
            'subtotal' => (float) $row['subtotal'],
            'discount' => (float) $row['discount'],
            'tax' => (float) $row['tax'],
            'totalRevenue' => (float) $row['total_revenue']
        ];
    }

    mysqli_stmt_close($stmt);


    $totalSql = "
        SELECT
            COUNT(*) AS total_sales,
            COALESCE(SUM(subtotal), 0) AS subtotal,
            COALESCE(SUM(discount), 0) AS discount,
            COALESCE(SUM(tax), 0) AS tax,
            COALESCE(SUM(total_amount), 0) AS total_revenue
        FROM sales
        WHERE created_at >= ?
        AND created_at < ?
    ";

    $totalStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($totalStmt, $totalSql)) {
        sendResponse(
            false,
            'Failed to prepare monthly totals query',
            null,
            500
        );
    }

    mysqli_stmt_bind_param(
        $totalStmt,
        'ss',
        $startDate,
        $endDate
    );

    if (!mysqli_stmt_execute($totalStmt)) {
        mysqli_stmt_close($totalStmt);

        sendResponse(
            false,
            'Failed to retrieve monthly totals',
            null,
            500
        );
    }

    $totalResult = mysqli_stmt_get_result($totalStmt);

    $totals = mysqli_fetch_assoc($totalResult);

    mysqli_stmt_close($totalStmt);


    sendResponse(
        true,
        'Monthly sales report retrieved successfully',
        [
            'month' => $month,

            'totals' => [
                'totalSales' => (int) $totals['total_sales'],
                'subtotal' => (float) $totals['subtotal'],
                'discount' => (float) $totals['discount'],
                'tax' => (float) $totals['tax'],
                'totalRevenue' => (float) $totals['total_revenue']
            ],

            'dailySales' => $dailySales
        ]
    );
?>