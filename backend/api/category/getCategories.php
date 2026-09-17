
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../helpers/response.php';
    require_once __DIR__ . '/../../models/category.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';

    // Only allow GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // User must be logged in
    requireAuth();

    // Only Administrator can view all users
    requireRole(['Administrator', "Manager", 'Cashier']);

    // Get all users
    $selectSql = "SELECT id, name, description FROM categories ORDER BY created_at DESC";

    $result = mysqli_query($conn, $selectSql);

    if (!$result) {
        sendResponse( false, 'Unable to retrieve categories.', null, 500);
    }

    $users = [];

    while ($user = mysqli_fetch_assoc($result)) {
        $users[] = $user;
    }

    mysqli_free_result($result);

    sendResponse(true, 'Categories retrieved successfully.', $users, 200);
?>