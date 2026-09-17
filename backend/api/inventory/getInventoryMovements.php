<?php
 
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/Product.php';
    require_once __DIR__ . '/../../models/Category.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../models/Inventory.php';
    require_once __DIR__ . '/../../models/Sale.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';

requireAuth();
requireRole(['Administrator', 'Manager']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, 'Method not allowed', null, 405);
}



$productId = $_GET['productId'] ?? null;

if ($productId === null || !ctype_digit((string) $productId)) {
    sendResponse(
        false,
        'Valid productId is required',
        null,
        400
    );
}

$productId = (int) $productId;



$productSql = "
    SELECT id, name, sku, quantity
    FROM products
    WHERE id = ?
    LIMIT 1
";

$productStmt = mysqli_stmt_init($conn);

if (!mysqli_stmt_prepare($productStmt, $productSql)) {
    sendResponse(
        false,
        'Failed to prepare product query',
        null,
        500
    );
}

mysqli_stmt_bind_param(
    $productStmt,
    'i',
    $productId
);

if (!mysqli_stmt_execute($productStmt)) {
    mysqli_stmt_close($productStmt);

    sendResponse(
        false,
        'Failed to retrieve product',
        null,
        500
    );
}

$productResult = mysqli_stmt_get_result($productStmt);
$product = mysqli_fetch_assoc($productResult);

mysqli_stmt_close($productStmt);

if (!$product) {
    sendResponse(
        false,
        'Product not found',
        null,
        404
    );
}



$sql = "
    SELECT
        im.id,
        im.movement_type,
        im.quantity,
        im.reference_id,
        im.note,
        im.created_at,

        u.username

    FROM inventory_movements im

    INNER JOIN users u
        ON im.user_id = u.id

    WHERE im.product_id = ?

    ORDER BY im.created_at DESC, im.id DESC
";

$stmt = mysqli_stmt_init($conn);

if (!mysqli_stmt_prepare($stmt, $sql)) {
    sendResponse(
        false,
        'Failed to prepare inventory movement query',
        null,
        500
    );
}

mysqli_stmt_bind_param(
    $stmt,
    'i',
    $productId
);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    sendResponse(
        false,
        'Failed to retrieve inventory movements',
        null,
        500
    );
}

$result = mysqli_stmt_get_result($stmt);

$movements = [];

while ($row = mysqli_fetch_assoc($result)) {

    $movements[] = [
        'id' => (int) $row['id'],
        'movementType' => $row['movement_type'],
        'quantity' => (int) $row['quantity'],
        'referenceId' => $row['reference_id'] !== null
            ? (int) $row['reference_id']
            : null,
        'note' => $row['note'],
        'performedBy' => $row['username'],
        'createdAt' => $row['created_at']
    ];
}

mysqli_stmt_close($stmt);



sendResponse(
    true,
    'Inventory movements retrieved successfully',
    [
        'product' => [
            'id' => (int) $product['id'],
            'name' => $product['name'],
            'sku' => $product['sku'],
            'currentQuantity' => (int) $product['quantity']
        ],
        'movements' => $movements
    ]
);