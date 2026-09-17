
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
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

    // Only Administrator can update users
    requireRole(['Administrator']);

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data', null, 400);
    }

    $userId = $data['userId'] ?? null;

    if (!filter_var($userId, FILTER_VALIDATE_INT) || $userId <= 0) {
        sendResponse( false, 'A valid user ID is required.', null, 422);
    }

    $userData = [
        "id" => $userId
    ];

    $user = findUser($conn, $userData);

    if($user){
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $userName = trim($data['userName'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $role = $data['role'] ?? '';
        $status = $data['status'] ?? '';

        $inputErrors = [
            'firstName' => validateName($firstName, 'First Name'),
            'lastName' => validateName($lastName, 'Last Name'),
            'userName' => validateName($userName, 'Username', true),
            'email' => validateEmail($email),
            'phone' => validatePhone($phone),
            'role' => validateSelect($role, $allowedRoles, 'Invalid role selected.'),
            'status' => validateSelect($status, $allowedStatuses, 'Invalid status selected.')
        ];
        
        if(!isAllNull($inputErrors)){
            sendResponse(false, 'Validation failed', removeNullValues($inputErrors), 422);
        };

        if ($user['role'] === 'Administrator' && $role !== 'Administrator' && $user['status'] === 'Active'){
            $adminCount = countActiveAdministrators($conn);

            if ($adminCount === false) {
                sendResponse(false, 'Unable to check active administrators.', null, 500);
            }

            if ($adminCount <= 1) {
                sendResponse(false, 'The last active Administrator cannot have their role changed.', null, 403);
            }
        }

        if ($user['role'] === 'Administrator' && $user['status'] === 'Active' && $status === 'Inactive'){
            $adminCount = countActiveAdministrators($conn);

            if ($adminCount === false) {
                sendResponse(false, 'Unable to check active administrators.', null, 500);
            }

            if ($adminCount <= 1) {
                sendResponse(false, 'The last active Administrator cannot be deactivated.', null, 403);
            }
        }

        $existingData = [
            'email' => $email,
            'phone' => $phone,
            'username' => $userName
        ];

        $existingUser = findUser($conn, $existingData, $userId);

        if($existingUser === false){
            sendResponse(false, 'Unable to check existing user',null, 500);
        }

        if ($existingUser !== null) {

            $duplicateFields = [];

            if ($existingUser['username'] === $userName) {
                $duplicateFields[] = 'username';
            }

            if ($existingUser['email'] === $email) {
                $duplicateFields[] = 'email';
            }

            if ($existingUser['phone'] === $phone) {
                $duplicateFields[] = 'phone number';
            }

            sendResponse( false, 'The following already exist: ' . implode(', ', $duplicateFields) . '.', null, 409);
        }
        
        $updated = updateUser($conn, $userId, $firstName, $lastName, $userName, $email, $phone, $role, $status);

        if (!$updated) {
            sendResponse(false, 'Unable to update user.', null, 500);
        }

        $updatedUser = findUser($conn, [
            'id' => $userId
        ]);

        if ($updatedUser === false) {
            sendResponse(false, 'User was updated, but the updated information could not be retrieved.', null, 500);
        }

        sendResponse(true, 'User updated successfully.',
            [
                'user' => [
                    'id' => $updatedUser['id'],
                    'first_name' => $updatedUser['first_name'],
                    'last_name' => $updatedUser['last_name'],
                    'username' => $updatedUser['username'],
                    'email' => $updatedUser['email'],
                    'phone' => $updatedUser['phone'],
                    'role' => $updatedUser['role'],
                    'status' => $updatedUser['status']
                ]
            ],
            200
        );

    }elseif($user === false){
        sendResponse(false, 'Unable to check existing user',null, 500);
    }else{
        sendResponse(false, 'User not found.', null, 404);
    }
?>