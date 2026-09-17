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

    $returnSql = "
        SELECT
            r.id,
            r.sale_id,
            r.user_id,
            r.return_number,
            r.reason,
            r.total_refund,
            r.created_at,
            CONCAT(u.first_name, ' ', u.last_name) AS processed_by
        FROM returns r
        INNER JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
    ";

    $result = mysqli_query($conn, $returnSql);

    if (!$result) {
        sendResponse(false, 'Failed to retrieve returns', null, 500);
    }

    $returns = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $returns[] = [
            'id' => (int) $row['id'],
            'saleId' => (int) $row['sale_id'],
            'userId' => (int) $row['user_id'],
            'returnNumber' => $row['return_number'],
            'reason' => $row['reason'],
            'totalRefund' => (float) $row['total_refund'],
            'createdAt' => $row['created_at'],
            'processedBy' => $row['processed_by']
        ];
    }

    mysqli_free_result($result);

    sendResponse(
        true,
        'Returns retrieved successfully',
        $returns
    ); 
?>