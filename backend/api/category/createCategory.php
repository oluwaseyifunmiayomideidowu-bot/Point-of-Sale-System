
<?php

    session_start();

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/category.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // User must be logged in
    requireAuth();

    // Only Administrator can create users
    requireRole(['Administrator','Manager']);

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data', null, 400);
    }

    // Retrieve input
    $categoryName = trim($data['categoryName'] ?? '');
    $description = trim($data['description'] ?? '');
    
    // Validate input
    $inputErrors = [
        'categoryName' => validateName($categoryName, 'category Name'),
        'description' => validateText($description, 'Description')
    ];

    if (!isAllNull($inputErrors)) {
        sendResponse( false, 'Validation failed', removeNullValues($inputErrors), 422);
    }

    $data = [
            "categoryName" => $categoryName
        ];

    $exist = findcategory($conn, $data);

    if($exist === null){
        $result = createcategory($conn, $categoryName, $description);

        if($result ===  'success'){
            sendResponse(true, "category Created successfully.", null, 201);
        }

        sendResponse(false, "Unable to create category.", $result['message'], 500);

    }elseif($exist === false){
        sendResponse(false, 'Unable to check existing category',null, 500);
    }else{

        $duplicateFields = [];

        if ($exist['name'] === $categoryName) {
            $duplicateFields[] = 'category Name';
        }

        sendResponse( false, 'The following already exist: ' . implode(', ', $duplicateFields) . '.', null, 409);
    }
?>