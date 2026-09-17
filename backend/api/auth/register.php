
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . '/../../helpers/response.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // ONLY ALLOW THE FIRST ADMINISTRATOR TO REGISTER

    if(userExist($conn) > 0){
        sendResponse(false, 'Initail registration has already been completed.',null, 403);
    }

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Make sure valid JSON was received
    if (!is_array($data)) {
        sendResponse( false, 'Invalid request data', null, 400);
    }

    // --- RETRIVING DATA FROM THE FORM ---
    
    $firstName = trim($data['firstName'] ?? '');
    $lastName = trim($data['lastName'] ?? '');
    $userName = trim($data['userName'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirmPassword'] ?? '';
    
    // --- VALIDATE THE DATA ---

    $inputErrors = [
        'firstName' => validateName($firstName,'First Name'),
        'lastName' => validateName($lastName,'Last Name'),
        'userName' => validateName($userName,'Username', true),
        'email' => validateEmail($email),
        'phone' => validatePhone($phone),
        'passwords' => validatePasswords($password,$confirmPassword),
    ];

    if(isAllNull($inputErrors)){

        $role = 'Administrator';
        $status = 'Active';

        $result = createUser($conn, $firstName, $lastName, $userName, $email, $phone, $password, $role, $status);

        if($result === 'success'){
            sendResponse(true, "Registration Successful", null, 201);
            exit();
        }else{
            sendResponse(false, "Unable to create user", $result["message"], 500);
        }

    }else{
        sendResponse(false, "Validation failed", removeNullValues($inputErrors), 422);
    };
?>