<?php

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../config/cors.php';

$method = $_SERVER['REQUEST_METHOD'];

$uri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// API TEST
if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api') {
    sendResponse(true, 'Vendly API is working');
    exit;
}
// REGISTER USER
if ($method === 'POST' && $uri === '/point_of_sale_system/backend/api/register') {
    require_once __DIR__ . '/auth/register.php';
    exit;
}

// TEST
if($method === 'GET' && $uri === '/point_of_sale_system/backend/api/test') {
    require_once __DIR__ . '/test.php';
    exit;
}

// LOGIN USER
if($method === 'POST' && $uri === '/point_of_sale_system/backend/api/login') {
    require_once __DIR__ . '/auth/login.php';
    exit;
}

// LOGOUT USER
if($method === 'POST' && $uri === '/point_of_sale_system/backend/api/logout') {
    require_once __DIR__ . '/auth/logout.php';
    exit;
}

// CREATE USER
if($method === 'POST' && $uri === '/point_of_sale_system/backend/api/createUser') {
    require_once __DIR__ . '/user/createUser.php';
    exit;
}

// GET USERS
if($method === 'GET' && $uri === '/point_of_sale_system/backend/api/users') {
    require_once __DIR__ . '/user/getUsers.php';
    exit;
}

// GET USER
if($method === 'GET' && preg_match('#^/point_of_sale_system/backend/api/user/([0-9]+)$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];

    require_once __DIR__ . '/user/getUser.php';
    exit;
}

// UPDATE USER
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/updateUser') {
    require_once __DIR__ . '/user/updateUser.php';
    exit;
}

// DEACTIVATE USER
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/deactivateUser') {
    require_once __DIR__ . '/user/deactivateUser.php';
    exit;
}

// ACTIVATE USER
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/activateUser') {
    require_once __DIR__ . '/user/activateUser.php';
    exit;
}

// CREATE SUPPLIER
if($method === 'POST' && $uri === '/point_of_sale_system/backend/api/createSupplier') {
    require_once __DIR__ . '/supplier/createSupplier.php';
    exit;
}

// GET SUPPLIERS
if($method === 'GET' && $uri === '/point_of_sale_system/backend/api/suppliers') {
    require_once __DIR__ . '/supplier/getSuppliers.php';
    exit;
}

// GET SUPPLIER
if($method === 'GET' && preg_match('#^/point_of_sale_system/backend/api/supplier/([0-9]+)$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];

    require_once __DIR__ . '/supplier/getSupplier.php';
    exit;
}

// UPDATE SUPPLIER
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/updateSupplier') {
    require_once __DIR__ . '/supplier/updateSupplier.php';
    exit;
}

// DEACTIVATE SUPPLIER
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/deactivateSupplier') {
    require_once __DIR__ . '/supplier/deactivateSupplier.php';
    exit;
}

// ACTIVATE SUPPLIER
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/activateSupplier') {
    require_once __DIR__ . '/supplier/activateSupplier.php';
    exit;
}

// CREATE CATEGORY
if($method === 'POST' && $uri === '/point_of_sale_system/backend/api/createCategory') {
    require_once __DIR__ . '/category/createCategory.php';
    exit;
}

// GET CATEGORIES
if($method === 'GET' && $uri === '/point_of_sale_system/backend/api/categories') {
    require_once __DIR__ . '/category/getCategories.php';
    exit;
}

// GET CATEGORY
if($method === 'GET' && preg_match('#^/point_of_sale_system/backend/api/category/([0-9]+)$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];

    require_once __DIR__ . '/category/getCategory.php';
    exit;
}

// UPDATE CATEGORY
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/updateCategory') {
    require_once __DIR__ . '/category/updateCategory.php';
    exit;
}

// DELETE CATEGORY
if($method === 'DELETE' && $uri === '/point_of_sale_system/backend/api/categories/delete') {
    require_once __DIR__ . '/category/deleteCategory.php';
    exit;
}

// CREATE PRODUCT
if($method === 'POST' && $uri === '/point_of_sale_system/backend/api/createProduct') {
    require_once __DIR__ . '/product/createProduct.php';
    exit;
}

// GET PRODUCTS
if($method === 'GET' && $uri === '/point_of_sale_system/backend/api/products') {
    require_once __DIR__ . '/product/getProducts.php';
    exit;
}

// GET PRODUCT
if($method === 'GET' &&preg_match('#^/point_of_sale_system/backend/api/products/([0-9]+)$#', $uri, $matches)){
    $_GET['id'] = $matches[1];

    require_once __DIR__ . '/product/getProduct.php';
    exit;
}

// UPDATE PRODUCT
if($method === 'POST' && $uri === '/point_of_sale_system/backend/api/products/updateProduct'){
    require_once __DIR__ . '/product/updateProduct.php';
    exit;
}

// DEACTIVATE PRODUCT
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/products/deactivateProduct'){
    require_once __DIR__ . '/product/deactivateProduct.php';
    exit;
}

// ACTIVATE PRODUCT
if($method === 'PUT' && $uri === '/point_of_sale_system/backend/api/products/activateProduct'){
    require_once __DIR__ . '/product/activateProduct.php';
    exit;
}

// CREATE SALE
if($method === 'POST' && $uri === '/point_of_sale_system/backend/api/createSale'){
    require_once __DIR__ . '/sales/createSale.php';
    exit;
}

// GET SALE
if($method === 'GET' && preg_match('#^/point_of_sale_system/backend/api/sales/([0-9]+)$#', $uri, $matches)){
    $_GET['id'] = $matches[1];

    require_once __DIR__ . '/sales/getSale.php';
    exit;
} 

// GET SALES
if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/sales/getSales'){
    require_once __DIR__ . '/sales/getSales.php';
    exit;
}

// CREATE RETURNS
if ($method === 'POST' && $uri === '/point_of_sale_system/backend/api/returns'){
    require_once __DIR__ . '/return/createReturn.php';
    exit;
}

// GET RETURN
if ($method === 'GET' && preg_match('#^/point_of_sale_system/backend/api/returns/([0-9]+)$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];

    require_once __DIR__ . '/return/getReturn.php';
    exit;
}

// GET RETURNS
if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/returns') {

    require_once __DIR__ . '/return/getReturns.php';
    exit;
}

// CREATE PURCHASE
if ( $method === 'POST' && $uri === '/point_of_sale_system/backend/api/purchases') {
    require_once __DIR__ . '/purchase/createPurchase.php';
    exit;
}

// GET PURCHASE
if ($method === 'GET' && preg_match('#^/point_of_sale_system/backend/api/purchases/([0-9]+)$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];

    require_once __DIR__ . '/purchase/getPurchase.php';

    exit;
}

// GET PURCHASES
if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/purchases'){
    require_once __DIR__ . '/purchase/getPurchases.php';
    exit;
}

// CREATE EXPENSE
if ($method === 'POST' && $uri === '/point_of_sale_system/backend/api/createExpense') {
    require_once __DIR__ . '/expense/createExpense.php';
    exit;
}

// GET EXPENSE
if ($method === 'GET' && preg_match( '#^/point_of_sale_system/backend/api/expenses/([0-9]+)$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];

    require_once __DIR__ . '/expense/getExpense.php';
    exit;
}

// GET EXPENSES
if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/getExpenses') {
    require_once __DIR__ . '/expense/getExpenses.php';
    exit;
}

if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/settings') {
    require_once __DIR__ . '/getSettings.php';
    exit;
}

if (
    $method === 'PUT' &&
    $uri === '/point_of_sale_system/backend/api/settings'
) {
    require_once __DIR__ . '/updateSettings.php';
    exit;
}

// GET DAILY SALES
if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/reports/daily-sales') {
    require_once __DIR__ . '/reports/getDailySalesReport.php';
    exit;
}

// GET MONTHLY SALES
if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/reports/monthly-sales') {
    require_once __DIR__ . '/reports/getMonthlySalesReport.php';
    exit;
}

// GET BEST SELLING PRODUCT
if ($method === 'GET' &&$uri === '/point_of_sale_system/backend/api/reports/best-selling-products') {
    require_once __DIR__ . '/reports/getBestSellingProductsReport.php';
    exit;
}

if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/reports/low-stock') {
    require_once __DIR__ . '/reports/getLowStockProductsReport.php';
    exit;
}

if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/reports/dashboard-summary') {
    require_once __DIR__ . '/reports/getDashboardSummary.php';
    exit;
}

if ($method === 'GET' && $uri === '/point_of_sale_system/backend/api/inventory') {
    require_once __DIR__ . '/inventory/getInventory.php';
    exit;
}

if (
    $method === 'GET' &&
    preg_match(
        '#^/point_of_sale_system/backend/api/inventory/movements$#',
        $uri
    )
) {
    require_once __DIR__ . '/getInventoryMovements.php';
    exit;
}

if (
    $method === 'GET' &&
    $uri === '/point_of_sale_system/backend/api/inventory'
) {
    require_once __DIR__ . '/getInventory.php';
    exit;
}




sendResponse(false, 'Route not found', null, 404);

?>
