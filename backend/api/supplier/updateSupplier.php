
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../helpers/functions.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';

    // Only allow PUT requests
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // User must be logged in
    requireAuth();

    // Only Administrator can update Supplier
    requireRole(['Administrator']);

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data', null, 400);
    }

    $supplierId = $data['supplierId'] ?? null;

    if (!filter_var($supplierId, FILTER_VALIDATE_INT) || $supplierId <= 0) {
        sendResponse( false, 'A valid supplier ID is required.', null, 422);
    }

    $supplierData = [
        "id" => $supplierId
    ];

    $supplier = findSupplier($conn, $supplierData);

    if($supplier){
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $userName = trim($data['userName'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $status = $data['status'] ?? '';

        $inputErrors = [
            'firstName' => validateName($firstName, 'First Name'),
            'lastName' => validateName($lastName, 'Last Name'),
            'userName' => validateName($userName, 'Username', true),
            'email' => validateEmail($email),
            'phone' => validatePhone($phone),
            'status' => validateSelect($status, $allowedStatuses, 'Invalid status selected.')
        ];
        
        if(!isAllNull($inputErrors)){
            sendResponse(false, 'Validation failed', removeNullValues($inputErrors), 422);
        };

        $existingData = [
            'email' => $email,
            'phone' => $phone,
            'username' => $userName
        ];

        $existingSupplier = findSupplier($conn, $existingData, $supplierId);

        if($existingSupplier === false){
            sendResponse(false, 'Unable to check existing supplier.',null, 500);
        }

        if ($existingSupplier !== null) {

            $duplicateFields = [];

            if ($existingSupplier['username'] === $userName) {
                $duplicateFields[] = 'username';
            }

            if ($existingSupplier['email'] === $email) {
                $duplicateFields[] = 'email';
            }

            if ($existingSupplier['phone'] === $phone) {
                $duplicateFields[] = 'phone number';
            }

            sendResponse( false, 'The following already exist: ' . implode(', ', $duplicateFields) . '.', null, 409);
        }
        
        $updated = updateSupplier($conn, $supplierId, $firstName, $lastName, $userName, $email, $phone, $status);

        if (!$updated) {
            sendResponse(false, 'Unable to update supplier.', null, 500);
        }

        $updatedSupplier = findSupplier($conn, [
        'id' => $supplierId
    ]);

    if ($updatedSupplier === false) {
        sendResponse(false, 'Supplier was updated, but the updated information could not be retrieved.', null, 500);
    }

    sendResponse(true, 'Supplier updated successfully.',
        [
            'supplier' => [
                'id' => $updatedSupplier['id'],
                'first_name' => $updatedSupplier['first_name'],
                'last_name' => $updatedSupplier['last_name'],
                'username' => $updatedSupplier['username'],
                'email' => $updatedSupplier['email'],
                'phone' => $updatedSupplier['phone'],
                'status' => $updatedSupplier['status']
            ]
        ],
        200
    );

    }elseif($supplier === false){
        sendResponse(false, 'Unable to check existing supplier',null, 500);
    }else{
        sendResponse(false, 'Supplier not found.', null, 404);
    }
?>