
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../helpers/functions.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    requireAuth();
    requireRole(['Administrator']);

    $supplierId = $_GET['id'] ?? null;

    if (!filter_var($supplierId, FILTER_VALIDATE_INT) || $supplierId <= 0) {
        sendResponse(false, 'A valid supplier ID is required.', null, 422);
    }

    $supplier = findSupplier($conn, [
        'id' => $supplierId
    ]);

    if ($supplier === false) {
        sendResponse(false, 'Unable to retrieve supplier.', null, 500);
    }

    if ($supplier === null) {
        sendResponse(false, 'supplier not found.', null, 404);
    }

    sendResponse(
        true,
        'Supplier retrieved successfully.',
        [
            'supplier' => [
                'id' => $supplier['id'],
                'first_name' => $supplier['first_name'],
                'last_name' => $supplier['last_name'],
                'username' => $supplier['username'],
                'email' => $supplier['email'],
                'phone' => $supplier['phone'],
                'status' => $supplier['status']
            ]
        ],
        200
    );
?>