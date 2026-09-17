
<?php

    session_start();

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // User must be logged in
    requireAuth();

    // Only Administrator can create supplier
    requireRole(['Administrator']);

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data', null, 400);
    }

    // Retrieve input
    $firstName = trim($data['firstName'] ?? '');
    $lastName = trim($data['lastName'] ?? '');
    $userName = trim($data['userName'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $status = trim($data['status'] ?? '');

    // Validate input
    $inputErrors = [
        'firstName' => validateName($firstName, 'First Name'),
        'lastName' => validateName($lastName, 'Last Name'),
        'userName' => validateName($userName, 'Username', true),
        'email' => validateEmail($email),
        'phone' => validatePhone($phone),
        'status' => validateSelect($status, $allowedStatuses, 'Invalid status selected.')
    ];

    if (!isAllNull($inputErrors)) {
        sendResponse( false, 'Validation failed', removeNullValues($inputErrors), 422);
    }

    $createdUsername = createUsername($userName, "supplier");

    $data = [
            "username" => $createdUsername,
            "email" => $email,
            "phone" => $phone
        ];

    $exist = findSupplier($conn, $data);

    if($exist === null){
        $result = createSupplier($conn, $firstName, $lastName, $createdUsername, $email, $phone, $status);

        if($result ===  'success'){
            sendResponse(true, "Supplier Created successfully.", null, 201);
        }

        sendResponse(false, "Unable to create supplier.", $result['message'], 500);

    }elseif($exist === false){
        sendResponse(false, 'Unable to check existing supplier',null, 500);
    }else{

        $duplicateFields = [];

        if ($exist['username'] === $createdUsername) {
            $duplicateFields[] = 'username';
        }

        if ($exist['email'] === $email) {
            $duplicateFields[] = 'email';
        }

        if ($exist['phone'] === $phone) {
            $duplicateFields[] = 'phone number';
        }

        sendResponse( false, 'The following already exist: ' . implode(', ', $duplicateFields) . '.', null, 409);
    }
?>