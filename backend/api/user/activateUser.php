
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    // Only allow PUT requests
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // User must be logged in
    requireAuth();

    // Only Administrator can activate users
    requireRole(['Administrator']);

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data', null, 400);
    }

    // Get user ID
    $userId = $data['userId'] ?? null;

    // Validate user ID
    if (!filter_var($userId, FILTER_VALIDATE_INT) || $userId <= 0) {
        sendResponse(false, 'A valid user ID is required.', null, 422);
    }

    // Check if user exists
    $user = findUser($conn, [
        'id' => $userId
    ]);

    if ($user === false) {
        sendResponse(false, 'Unable to check user.', null, 500);
    }

    if ($user === null) {
        sendResponse(false, 'User not found.', null, 404);
    }

    // Check if already active
    if ($user['status'] === 'Active') {
        sendResponse(false, 'User account is already active.', null, 409);
    }

    // Activate user
    $result = activateUser($conn, $userId);

    if (!$result) {
        sendResponse(false, 'Unable to activate user.', null, 500);
    }

    sendResponse(true, 'User activated successfully.', null, 200);
?>