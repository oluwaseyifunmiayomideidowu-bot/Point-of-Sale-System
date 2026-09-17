
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

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
        sendResponse(false, 'Valid expense ID is required', null, 400);
    }

    $expenseId = (int) $_GET['id'];


    $expenseSql = "
        SELECT
            e.id,
            e.user_id,
            e.expense_number,
            e.category,
            e.description,
            e.amount,
            e.expense_date,

            CONCAT(u.first_name, ' ', u.last_name) AS created_by

        FROM expenses e

        INNER JOIN users u
            ON e.user_id = u.id

        WHERE e.id = ?

        LIMIT 1
    ";

    $expenseStmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($expenseStmt, $expenseSql)) {
        sendResponse(false, 'Failed to prepare expense query', null, 500);
    }

    mysqli_stmt_bind_param(
        $expenseStmt,
        'i',
        $expenseId
    );

    if (!mysqli_stmt_execute($expenseStmt)) {
        mysqli_stmt_close($expenseStmt);
        sendResponse(false, 'Failed to retrieve expense', null, 500);
    }

    $result = mysqli_stmt_get_result($expenseStmt);

    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($expenseStmt);
        sendResponse(false, 'Expense not found', null, 404);
    }

    $expense = mysqli_fetch_assoc($result);

    mysqli_stmt_close($expenseStmt);


    $expense['id'] = (int) $expense['id'];
    $expense['user_id'] = (int) $expense['user_id'];
    $expense['amount'] = (float) $expense['amount'];


    sendResponse(
        true,
        'Expense retrieved successfully',
        $expense
    );
?>