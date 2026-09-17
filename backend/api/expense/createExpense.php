
<?php
    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/Product.php';
    require_once __DIR__ . '/../../models/Category.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../models/Sale.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    requireAuth();
    requireRole(['Administrator', 'Manager']);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid JSON data', null, 400);
    }


    $category = trim($data['category'] ?? '');

    if ($category === '') {
        sendResponse(false, 'Expense category is required', null, 400);
    }

    if (strlen($category) > 100) {
        sendResponse(false, 'Expense category must not exceed 100 characters', null, 400);
    }


    $description = trim($data['description'] ?? '');

    if (strlen($description) > 255) {
        sendResponse(false, 'Description must not exceed 255 characters', null, 400);
    }


    if (
        !isset($data['amount']) ||
        !is_numeric($data['amount']) ||
        (float) $data['amount'] <= 0
    ) {
        sendResponse(false, 'Expense amount must be greater than zero', null, 400);
    }

    $amount = (float) $data['amount'];


    $userId = (int) $_SESSION['user_id'];


    $expenseNumber = 'EXP-' . strtoupper(
        date('YmdHis') . '-' . bin2hex(random_bytes(3))
    );


    $expenseSql = "
        INSERT INTO expenses (
            user_id,
            expense_number,
            category,
            description,
            amount
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $expenseStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($expenseStmt, $expenseSql)) {
        sendResponse(false, 'Failed to prepare expense query', null, 500);
    }

    mysqli_stmt_bind_param(
        $expenseStmt,
        'isssd',
        $userId,
        $expenseNumber,
        $category,
        $description,
        $amount
    );

    if (!mysqli_stmt_execute($expenseStmt)) {
        mysqli_stmt_close($expenseStmt);

        sendResponse(
            false,
            'Failed to create expense',
            null,
            500
        );
    }

    $expenseId = mysqli_insert_id($conn);

    mysqli_stmt_close($expenseStmt);


    sendResponse(
        true,
        'Expense created successfully',
        [
            'expenseId' => $expenseId,
            'expenseNumber' => $expenseNumber,
            'category' => $category,
            'description' => $description,
            'amount' => $amount
        ],
        201
    );
?>