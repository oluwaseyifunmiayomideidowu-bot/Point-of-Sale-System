
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/category.php';
    require_once __DIR__ . '/../../helpers/functions.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';

    // Only allow PUT requests
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // User must be logged in
    requireAuth();

    // Only Administrator can update category
    requireRole(['Administrator']);

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data', null, 400);
    }

    $categoryId = $data['categoryId'] ?? null;

    if (!filter_var($categoryId, FILTER_VALIDATE_INT) || $categoryId <= 0) {
        sendResponse( false, 'A valid category ID is required.', null, 422);
    }

    $categoryData = [
        "id" => $categoryId
    ];

    $category = findcategory($conn, $categoryData);

    if($category){
        $categoryName = trim($data['categoryName'] ?? '');
        $description = trim($data['description'] ?? '');

        $inputErrors = [
            'categoryName' => validateName($categoryName, 'category Name'),
            'description' => validateName($description, 'Description')
        ];
        
        if(!isAllNull($inputErrors)){
            sendResponse(false, 'Validation failed', removeNullValues($inputErrors), 422);
        };

        $existingData = [
            "categoryName" => $categoryName
        ];

        $existingcategory = findCategory($conn, $existingData, $categoryId);

        if($existingcategory === false){
            sendResponse(false, 'Unable to check existing category.', null, 500);
        }

        if ($existingcategory !== null) {

           $duplicateFields = [];

            if ($existingcategory['name'] === $categoryName) {
                $duplicateFields[] = 'category Name';
            }

            sendResponse( false, 'The following already exist: ' . implode(', ', $duplicateFields) . '.', null, 409);
        }
        
        $updated = updatecategory($conn, $categoryId, $categoryName, $description);

        if (!$updated) {
            sendResponse(false, 'Unable to update category.', null, 500);
        }

        $updatedcategory = findcategory($conn, [
            'id' => $categoryId
        ]);

    if ($updatedcategory === false) {
        sendResponse(false, 'category was updated, but the updated information could not be retrieved.', null, 500);
    }

    sendResponse(true, 'category updated successfully.',
        [
            'category' => [
                'id' => $updatedcategory['id'],
                'category Name' => $updatedcategory['name'],
                'Description' => $updatedcategory['description'],
            ]
        ],
        200
    );

    }elseif($category === false){
        sendResponse(false, 'Unable to check existing category',null, 500);
    }else{
        sendResponse(false, 'category not found.', null, 404);
    }
?>