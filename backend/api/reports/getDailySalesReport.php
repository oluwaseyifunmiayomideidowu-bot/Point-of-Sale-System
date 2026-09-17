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


    $date = $_GET['date'] ?? date('Y-m-d');

    $dateObject = DateTime::createFromFormat('Y-m-d', $date);

    if (
        !$dateObject ||
        $dateObject->format('Y-m-d') !== $date
    ) {
        sendResponse(
            false,
            'Date must be in YYYY-MM-DD format',
            null,
            400
        );
    }


    $summarySql = "
        SELECT
            COUNT(*) AS total_sales,
            COALESCE(SUM(subtotal), 0) AS subtotal,
            COALESCE(SUM(discount), 0) AS discount,
            COALESCE(SUM(tax), 0) AS tax,
            COALESCE(SUM(total_amount), 0) AS total_revenue
        FROM sales
        WHERE created_at >= ?
        AND created_at < DATE_ADD(?, INTERVAL 1 DAY)
    ";

    $summaryStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($summaryStmt, $summarySql)) {
        sendResponse(false, 'Failed to prepare sales report query', null, 500);
    }

    mysqli_stmt_bind_param(
        $summaryStmt,
        'ss',
        $date,
        $date
    );

    if (!mysqli_stmt_execute($summaryStmt)) {
        mysqli_stmt_close($summaryStmt);

        sendResponse(
            false,
            'Failed to retrieve daily sales report',
            null,
            500
        );
    }

    $summaryResult = mysqli_stmt_get_result($summaryStmt);

    $summary = mysqli_fetch_assoc($summaryResult);

    mysqli_stmt_close($summaryStmt);


    $paymentSql = "
        SELECT
            payment_method,
            COUNT(*) AS transaction_count,
            COALESCE(SUM(total_amount), 0) AS total
        FROM sales
        WHERE created_at >= ?
        AND created_at < DATE_ADD(?, INTERVAL 1 DAY)
        GROUP BY payment_method
        ORDER BY payment_method ASC
    ";

    $paymentStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($paymentStmt, $paymentSql)) {
        sendResponse(false, 'Failed to prepare payment report query', null, 500);
    }

    mysqli_stmt_bind_param(
        $paymentStmt,
        'ss',
        $date,
        $date
    );

    if (!mysqli_stmt_execute($paymentStmt)) {
        mysqli_stmt_close($paymentStmt);

        sendResponse(
            false,
            'Failed to retrieve payment report',
            null,
            500
        );
    }

    $paymentResult = mysqli_stmt_get_result($paymentStmt);

    $payments = [];

    while ($row = mysqli_fetch_assoc($paymentResult)) {

        $payments[] = [
            'paymentMethod' => $row['payment_method'],
            'transactionCount' => (int) $row['transaction_count'],
            'total' => (float) $row['total'],
            'isworkin' => "working"
        ];
    }

    mysqli_stmt_close($paymentStmt);


    sendResponse(
        true,
        'Daily sales report retrieved successfully',
        [
            'date' => $date,
            'totalSales' => (int) $summary['total_sales'],
            'subtotal' => (float) $summary['subtotal'],
            'discount' => (float) $summary['discount'],
            'tax' => (float) $summary['tax'],
            'totalRevenue' => (float) $summary['total_revenue'],
            'payments' => $payments
        ]
    );