
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



    $startDate = $_GET['startDate'] ?? date('Y-m-01');
    $endDate = $_GET['endDate'] ?? date('Y-m-d');

    $startObject = DateTime::createFromFormat('Y-m-d', $startDate);
    $endObject = DateTime::createFromFormat('Y-m-d', $endDate);

    if (
        !$startObject ||
        $startObject->format('Y-m-d') !== $startDate
    ) {
        sendResponse(
            false,
            'startDate must be in YYYY-MM-DD format',
            null,
            400
        );
    }

    if (
        !$endObject ||
        $endObject->format('Y-m-d') !== $endDate
    ) {
        sendResponse(
            false,
            'endDate must be in YYYY-MM-DD format',
            null,
            400
        );
    }

    if ($startDate > $endDate) {
        sendResponse(
            false,
            'startDate cannot be later than endDate',
            null,
            400
        );
    }



    $sql = "
        SELECT
            p.id AS product_id,
            p.name AS product_name,
            p.sku,

            SUM(si.quantity) AS quantity_sold,

            COALESCE(
                SUM(si.subtotal),
                0
            ) AS sales_amount

        FROM sale_items si

        INNER JOIN sales s
            ON si.sale_id = s.id

        INNER JOIN products p
            ON si.product_id = p.id

        WHERE s.created_at >= ?
        AND s.created_at < DATE_ADD(?, INTERVAL 1 DAY)

        GROUP BY
            p.id,
            p.name,
            p.sku

        ORDER BY quantity_sold DESC
    ";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        sendResponse(
            false,
            'Failed to prepare best-selling products query',
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
            'Failed to retrieve best-selling products',
            null,
            500
        );
    }

    $result = mysqli_stmt_get_result($stmt);

    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $products[] = [
            'productId' => (int) $row['product_id'],
            'productName' => $row['product_name'],
            'sku' => $row['sku'],
            'quantitySold' => (int) $row['quantity_sold'],
            'salesAmount' => (float) $row['sales_amount']
        ];
    }

    mysqli_stmt_close($stmt);



    sendResponse(
        true,
        'Best-selling products report retrieved successfully',
        [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'products' => $products
        ]
    );
?>