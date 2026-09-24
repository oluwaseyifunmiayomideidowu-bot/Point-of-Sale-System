
<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/Product.php';
    require_once __DIR__ . '/../../models/Category.php';
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

    // Only Administrator can create users
    requireRole(['Administrator', 'Manager']);

    // Read JSON request body
    // $data = json_decode(file_get_contents("php://input"), true);;

    // if (!is_array($data)) {
    //     sendResponse(false, 'Invalid request data', null, 400);
    // }

    // Retrieve input
    $productName = trim($_POST['productName'] ?? '');
    $categoryName = trim($_POST['categoryName'] ?? '');
     $supplierName = trim($_POST['supplierName'] ?? '');
    $costPrice = $_POST['costPrice'] ?? null;
    $sellingPrice = $_POST['sellingPrice'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $reorderLevel = $_POST['reorderLevel'] ?? null;
    $status = trim($_POST['status'] ?? '');

    // Validate input
    $inputErrors = [
       'supplierName' => validateUsername($supplierName, 'Supplier Name'),
       'costPrice' => validateNumeric($costPrice, 'Cost Price'),
       'sellingPrice' => validateNumeric($sellingPrice, 'Selling Price'),
       'quantity' => validateQuantity($quantity, 'Quantity'),
       'reorderLevel' => validateQuantity($reorderLevel, 'Reorder Level'),
       'status' => validateSelect($status, $allowedStatuses, 'Invalid Status Selected')
    ];

    if (!isAllNull($inputErrors)) {
        sendResponse( false, 'Validation failed', removeNullValues($inputErrors), 422);
    }

    $category = findCategory($conn,[
        "categoryName" => $categoryName
    ]);

    if ($category === false) {
        sendResponse(false, 'Unable to check category.', null, 500);
    }

    if ($category === null) {
        sendResponse(false, 'Category not found.', null, 404);
    }

    $supplier = findSupplier($conn, [
        "username" => $supplierName
    ]);

    if ($supplier === false) {
        sendResponse(false, 'Unable to check supplier.', null, 500);
    }

    if ($supplier === null) {
        sendResponse(false, 'Supplier not found.', null, 404);
    }

    $sku = createSku($conn);

    if ($sku === false) {
        sendResponse( false, 'Unable to generate product SKU.', null, 500);
    }

    $costPrice = (float) $costPrice;
    $sellingPrice = (float) $sellingPrice;
    $quantity = (int) $quantity;
    $reorderLevel = (int) $reorderLevel;
    $categoryId = $category['id'];
    $supplierId = $supplier['id'];

    $image = null;

    if (isset($_FILES['image'])) {
        $uploadResult = uploadProductImage($_FILES['image']);

        if ($uploadResult['status'] === 'error') {
            sendResponse(false, $uploadResult['message'], null, 422);
        }

        $image = $uploadResult['path'];
    }

    $productId = createProduct($conn, $categoryId, $supplierId, $productName, $sku, $costPrice, $sellingPrice, $quantity, $reorderLevel, $image, $status);

    if ($productId === false) {
        // Remove uploaded image if database insertion failed
        if ($uploadedFilePath !== null && file_exists($uploadedFilePath)) {
            unlink($uploadedFilePath);
        }

        sendResponse( false, 'Unable to create product.', null, 500);
    }

    sendResponse(true, 'Product created successfully.', [ 'productId' => $productId ], 201);

?>