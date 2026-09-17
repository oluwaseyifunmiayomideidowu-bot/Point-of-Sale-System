
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../helpers/functions.php';
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    requireAuth();
    requireRole(['Administrator']);

    $userId = $_GET['id'] ?? null;

    if (!filter_var($userId, FILTER_VALIDATE_INT) || $userId <= 0) {
        sendResponse(false, 'A valid user ID is required.', null, 422);
    }

    $user = findUser($conn, [
        'id' => $userId
    ]);

    if ($user === false) {
        sendResponse(false, 'Unable to retrieve user.', null, 500);
    }

    if ($user === null) {
        sendResponse(false, 'User not found.', null, 404);
    }

    sendResponse(
        true,
        'User retrieved successfully.',
        [
            'user' => [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role'],
                'status' => $user['status']
            ]
        ],
        200
    );
?>