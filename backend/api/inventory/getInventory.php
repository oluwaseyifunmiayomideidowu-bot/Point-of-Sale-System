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


$sql = "
    SELECT
        p.id,
        p.name,
        p.sku,
        p.quantity,
        p.reorder_level,
        p.cost_price,
        p.selling_price,
        p.status,
        p.image,
        p.updated_at,

        c.name AS category_name,
        s.username AS supplier_name

    FROM products p

    LEFT JOIN categories c
        ON p.category_id = c.id

    LEFT JOIN suppliers s
        ON p.supplier_id = s.id

    ORDER BY p.name ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    sendResponse(
        false,
        'Failed to retrieve inventory',
        null,
        500
    );
}

$inventory = [];

while ($row = mysqli_fetch_assoc($result)) {

    $quantity = (int) $row['quantity'];
    $reorderLevel = (int) $row['reorder_level'];

    $stockStatus = 'In Stock';

    if ($quantity === 0) {
        $stockStatus = 'Out of Stock';
    } elseif ($quantity <= $reorderLevel) {
        $stockStatus = "$quantity Remain";
    } elseif ($quantity <= $reorderLevel + 10) {
        $stockStatus = "Low Stock";
    }


    $inventory[] = [
        'productId' => (int) $row['id'],
        'productName' => $row['name'],
        'sku' => $row['sku'],
        'quantity' => $quantity,
        'reorderLevel' => $reorderLevel,
        'costPrice' => (float) $row['cost_price'],
        'sellingPrice' => (float) $row['selling_price'],
        'categoryName' => $row['category_name'],
        'supplierName' => $row['supplier_name'],
        'status' => $row['status'],
        'updated_at' => $row['updated_at'],
        'image'=> $row['image'],
        'stockStatus' => $stockStatus
    ];
}

mysqli_free_result($result);

sendResponse(
    true,
    'Inventory retrieved successfully',
    [
        'totalProducts' => count($inventory),
        'products' => $inventory
    ]
);