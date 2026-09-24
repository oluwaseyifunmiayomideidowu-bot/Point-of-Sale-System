
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

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed.', null, 405);
    }

    requireAuth();
    requireRole(['Administrator', 'Manager']);

    $productId = $_POST['productId'] ?? null;

    if (!filter_var($productId, FILTER_VALIDATE_INT) || (int)$productId <= 0) {
        sendResponse(false, 'Invalid product ID.', null, 422);
    }

    $productId = (int)$productId;


    // Find existing product
    $selectSql = "SELECT id, category_id, supplier_id, name, sku, cost_price, selling_price, quantity, reorder_level, image, status FROM products WHERE id = ? LIMIT 1";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $selectSql)) {
        sendResponse(false, 'Failed to prepare product query.', null, 500);
    }

    mysqli_stmt_bind_param($stmt, 'i', $productId);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        sendResponse(false, 'Failed to retrieve product.', null, 500);
    }

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        sendResponse(false, 'Product not found.', null, 404);
    }

    $existingProduct = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    // Get submitted values
    $productName = trim($_POST['productName'] ?? '');
    $categoryName = trim($_POST['categoryName'] ?? '');
    $supplierName = trim($_POST['supplierName'] ?? '');
    $costPrice = $_POST['costPrice'] ?? null;
    $sellingPrice = $_POST['sellingPrice'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $reorderLevel = $_POST['reorderLevel'] ?? null;
    $status = $_POST['status'] ?? '';


    // Validate
    $inputErrors = [
        'productName' => validateName($productName, 'Product name'),
        'categoryName' => validateName($categoryName, 'Category Name'),
        'supplierName' => validateName($supplierName, 'Supplier Name'),
        'costPrice' => validateNumeric($costPrice, 'Cost Price'),
        'sellingPrice' => validateNumeric($sellingPrice, 'Selling Price'),
        'quantity' => validateQuantity($quantity, 'Quantity'),
        'reorderLevel' => validateQuantity($reorderLevel, 'Reorder Level'),
        'status' => validateSelect( $status, $allowedStatuses,'Invalid product status.')
    ];

    if (!isAllNull($inputErrors)) {
        sendResponse(false, 'Validation failed.', removeNullValues($inputErrors), 422);
    }


    // Convert values
    $costPrice = (float)$costPrice;
    $sellingPrice = (float)$sellingPrice;
    $reorderLevel = (int)$reorderLevel;

    $categoryData = [
        "categoryName" => $categoryName
    ];


    // Find category
    $category = findCategory($conn, $categoryName);

    if ($category === false) {
        sendResponse(false, 'Unable to check category.', null, 500);
    }

    if ($category === null) {
        sendResponse(false, 'Category not found.', null, 404);
    }

    $categoryId = (int)$category['id'];

    $supplierData = [
        "username" => $supplierName
    ];

    // Find supplier
    $supplier = findSupplier($conn, $supplierData);

    if ($supplier === false) {
        sendResponse(false, 'Unable to check supplier.', null, 500);
    }

    if ($supplier === null) {
        sendResponse(false, 'Supplier not found.', null, 404);
    }

    $supplierId = (int)$supplier['id'];


    // Keep existing image unless a new one is uploaded
    $image = $existingProduct['image'];
    $newUploadedFilePath = null;


    // Upload new image if supplied
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

        $uploadResult = uploadProductImage($_FILES['image']);

        if ($uploadResult['status'] === 'error') {
            sendResponse(false, $uploadResult['message'], null, 422);
        }

        $image = $uploadResult['path'];
        $newUploadedFilePath = $uploadResult['fullPath'];
    }


    // Update product
    $updateSql = "UPDATE products SET category_id = ?, supplier_id = ?, name = ?, cost_price = ?, selling_price = ?, reorder_level = ?, image = ?, status = ? WHERE id = ?";

    $updateStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {

        if (
            $newUploadedFilePath !== null &&
            file_exists($newUploadedFilePath)
        ) {
            unlink($newUploadedFilePath);
        }

        sendResponse(false, 'Failed to prepare product update.', null, 500);
    }

    mysqli_stmt_bind_param($updateStmt, 'iisddiisi', $categoryId, $supplierId, $name, $costPrice, $sellingPrice, $reorderLevel, $image, $status, $productId);

    if (!mysqli_stmt_execute($updateStmt)) {

        mysqli_stmt_close($updateStmt);

        if (
            $newUploadedFilePath !== null &&
            file_exists($newUploadedFilePath)
        ) {
            unlink($newUploadedFilePath);
        }

        sendResponse(false, 'Failed to update product.', null, 500);
    }

    mysqli_stmt_close($updateStmt);


    // Delete old image only after successful update
    if (
        $newUploadedFilePath !== null &&
        !empty($existingProduct['image'])
    ) {
        $oldImagePath = __DIR__ . '/../' . $existingProduct['image'];

        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }


    sendResponse(
        true,
        'Product updated successfully.',
        [
            'productId' => $productId
        ]
    );
?>