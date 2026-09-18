
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

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Make sure valid JSON was received
    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data', null, 400);
    }

    $login = trim($data['login'] ?? '');
    $password = $data['password'] ?? '';

    $inputErrors = [
        "login" => validateLoginInput($login, "Email, username or phone number"),
        "password" => validateLoginPassword($password)
    ];

    if(isAllNull($inputErrors)) {

        $data = [
            "username" => $login,
            "email" => $login,
            "phone" => $login
        ];

        $result = findUser($conn, $data);

        if($result){

            if($result['status'] !== 'Active'){
                sendResponse(false, 'Your account is inactive', null, 403);
            }else{

                $hashedPassword = $result['password'];
    
                if(password_verify($password, $hashedPassword)){
    
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $result['id'];
                    $_SESSION['username'] = $result['username'];
                    $_SESSION['first_name'] = $result['first_name'];
                    $_SESSION['last_name'] = $result['last_name'];
                    $_SESSION['role'] = $result['role'];
    
                    // Update Last Login
                    $update = updateUserLastLogin($conn, $result['id']);

                    if(!$update){
                        error_log("Failed to Update last login for user Id: ". $result["id"]);
                    }

                    sendResponse( true, 'Login successful',[
                            'user' => [
                                'id' => $result['id'],
                                'first_name' => $result['first_name'],
                                'last_name' => $result['last_name'],
                                'username' => $result['username'],
                                'role' => $result['role']
                            ]
                        ],
                        200
                    );

                }else{
                    sendResponse(false, 'Invalid Login credentials.', null, 401);
                }

            }

        }elseif($result === null){
            sendResponse(false, 'User Not Found',null, 404);
        }else{
            sendResponse(false, 'Unable to check existing user', null, 500);
        }

    }else{
        sendResponse(false, "Validation failed", removeNullValues($inputErrors), 422);
    }

?>

