
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../helpers/functions.php';
    require_once __DIR__ . '/../../models/category.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    requireAuth();
    requireRole(['Administrator']);

    $categoryId = $_GET['id'] ?? null;

    if (!filter_var($categoryId, FILTER_VALIDATE_INT) || $categoryId <= 0) {
        sendResponse(false, 'A valid category ID is required.', null, 422);
    }

    $category = findcategory($conn, [
        'id' => $categoryId
    ]);

    if ($category === false) {
        sendResponse(false, 'Unable to retrieve category.', null, 500);
    }

    if ($category === null) {
        sendResponse(false, 'Category not found.', null, 404);
    }

    sendResponse(
        true,
        'Category retrieved successfully.',
        [
            'category' => [
                'id' => $category['id'],
                'category Name' => $category['name'],
                'Description' => $category['description']
            ]
        ],
        200
    );
?>