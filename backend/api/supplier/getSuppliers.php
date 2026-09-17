
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../helpers/response.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';

    // Only allow GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // User must be logged in
    requireAuth();

    // Only Administrator can view all supplier
    requireRole(['Administrator']);

    // Get all suppliers
    $selectSql = "SELECT id, first_name, last_name, username, email, phone, status FROM suppliers ORDER BY created_at DESC";

    $result = mysqli_query($conn, $selectSql);

    if (!$result) {
        sendResponse( false, 'Unable to retrieve suppliers.', null, 500);
    }

    $suppliers = [];

    while ($supplier = mysqli_fetch_assoc($result)) {
        $suppliers[] = $supplier;
    }

    mysqli_free_result($result);

    sendResponse(true, 'Suppliers retrieved successfully.', $suppliers, 200);
?>