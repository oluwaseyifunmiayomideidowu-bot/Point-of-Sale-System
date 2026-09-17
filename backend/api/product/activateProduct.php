
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

    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        sendResponse(false, 'Method not allowed.', null, 405);
    }

    requireAuth();
    requireRole(['Administrator', 'Manager']);

    $data = json_decode(file_get_contents('php://input'), true);

    $productId = $data['productId'] ?? null;

    if (!filter_var($productId, FILTER_VALIDATE_INT) || (int)$productId <= 0) {
        sendResponse(false, 'Invalid product ID.', null, 422);
    }

    $productId = (int)$productId;


    // Find product
    $selectSql = "
        SELECT id, name, status
        FROM products
        WHERE id = ?
        LIMIT 1
    ";

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

    $product = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($product['status'] === 'Active') {
        sendResponse(
            false,
            'Product is already active.',
            null,
            409
        );
    }


    // Activate product
    $updateSql = "
        UPDATE products
        SET status = 'Active'
        WHERE id = ?
    ";

    $updateStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
        sendResponse(false, 'Failed to prepare product update.', null, 500);
    }

    mysqli_stmt_bind_param($updateStmt, 'i', $productId);

    if (!mysqli_stmt_execute($updateStmt)) {
        mysqli_stmt_close($updateStmt);
        sendResponse(false, 'Failed to activate product.', null, 500);
    }

    mysqli_stmt_close($updateStmt);

    sendResponse(
        true,
        'Product activated successfully.',
        [
            'productId' => $productId
        ]
    );
?>