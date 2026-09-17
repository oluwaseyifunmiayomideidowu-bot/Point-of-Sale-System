
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    // Only allow PUT requests
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // User must be logged in
    requireAuth();

    // Only Administrator can deactivate supplier
    requireRole(['Administrator']);

    // Read JSON request body
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data', null, 400);
    }

    // Get supplier ID
    $supplierId = $data['supplierId'] ?? null;

    // Validate supplier ID
    if (!filter_var($supplierId, FILTER_VALIDATE_INT) || $supplierId <= 0) {
        sendResponse(false, 'A valid supplier ID is required.', null, 422);
    }

    // Check if supplier exists
    $supplier = findSupplier($conn, [
        'id' => $supplierId
    ]);

    if ($supplier === false) {
        sendResponse(false, 'Unable to check suppliers.', null, 500);
    }

    if ($supplier === null) {
        sendResponse(false, 'Supplier not found.', null, 404);
    }

    // Check if already active
    if ($supplier['status'] === 'Active') {
        sendResponse(false, 'Supplier account is already active.', null, 409);
    }

    // Deactivate supplier
    $result = activateSupplier($conn, $supplierId);

    if (!$result) {
        sendResponse(false, 'Unable to activate supplier.', null, 500);
    }

    sendResponse(true, 'Supplier activated successfully.', null, 200);

?>