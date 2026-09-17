
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

    // Only Administrator can deactivate users
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

    // Prevent administrator from deactivating themselves
    if ($userId === $_SESSION['user_id']) {
        sendResponse(false, 'You cannot deactivate your own account.', null, 403);
    }

    if ($user['role'] === 'Administrator' && $user['status'] === 'Active'){
        $adminCount = countActiveAdministrators($conn);

        if ($adminCount === false) {
            sendResponse(false, 'Unable to check active administrators.', null, 500);
        }

        if ($adminCount <= 1) {
            sendResponse(false, 'The last active Administrator cannot be deactivated.', null, 403);
        }
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

    // Check if already inactive
    if ($user['status'] === 'Inactive') {
        sendResponse(false, 'User account is already inactive.', null, 409);
    }

    // Deactivate user
    $result = deactivateUser($conn, $userId);

    if (!$result) {
        sendResponse(false, 'Unable to deactivate user.', null, 500);
    }

    sendResponse(true, 'User deactivated successfully.', null, 200);

?>