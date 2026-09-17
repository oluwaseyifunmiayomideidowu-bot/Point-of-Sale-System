<?php
    
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/category.php';
    require_once __DIR__ . '/../../helpers/functions.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';


    requireAuth();
    requireRole(['Administrator', 'Manager']);

    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    $categoryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$categoryId || $categoryId <= 0) {
        sendResponse(false, 'Valid category ID is required', null, 400);
    }

    $categoryData = [
        "id" => $categoryId
    ];

    $category = findCategory($conn, $categoryData);

    if ($category === null) {
        sendResponse(false, 'Category not found', null, 404);
    }

    $productCount = countProductsByCategory($conn, $categoryId);

    if ($productCount === false) {
        sendResponse(false, 'Failed to check category products', null, 500);
    }

    if ($productCount > 0) {
        sendResponse(
            false,
            'Category cannot be deleted because it is being used by products.Deactivate Instead',
            [
                'categoryId' => $categoryId,
                'productCount' => $productCount
            ],
            409
        );
    }

    if (!deleteCategory($conn, $categoryId)) {
        sendResponse(false, 'Failed to delete category', null, 500);
    }

    sendResponse(
        true,
        'Category deleted successfully',
        [
            'categoryId' => $categoryId
        ]
    );
?>