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

    $purchaseSql = "
        SELECT
            pu.id,
            pu.supplier_id,
            pu.user_id,
            pu.purchase_number,
            pu.total_amount,
            pu.status,
            pu.created_at,

            s.username AS supplier_name,

            CONCAT(u.first_name, ' ', u.last_name) AS created_by

        FROM purchases pu

        INNER JOIN suppliers s
            ON pu.supplier_id = s.id

        INNER JOIN users u
            ON pu.user_id = u.id

        ORDER BY pu.created_at DESC
    ";

    $result = mysqli_query($conn, $purchaseSql);

    if (!$result) {
        sendResponse(false, 'Failed to retrieve purchases', null, 500);
    }

    $purchases = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $purchases[] = [
            'id' => (int) $row['id'],
            'supplierId' => (int) $row['supplier_id'],
            'userId' => (int) $row['user_id'],
            'purchaseNumber' => $row['purchase_number'],
            'supplierName' => $row['supplier_name'],
            'status' => $row['status'],
            'totalAmount' => (float) $row['total_amount'],
            'createdBy' => $row['created_by'],
            'createdAt' => $row['created_at']
        ];
    }

    mysqli_free_result($result);

    sendResponse(
        true,
        'Purchases retrieved successfully',
        $purchases
    );
?>