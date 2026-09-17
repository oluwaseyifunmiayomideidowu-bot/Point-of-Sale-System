
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

        ORDER BY e.expense_date DESC
    ";

    $result = mysqli_query($conn, $expenseSql);

    if (!$result) {
        sendResponse(false, 'Failed to retrieve expenses', null, 500);
    }

    $expenses = [];

    while ($row = mysqli_fetch_assoc($result)) {

        $expenses[] = [
            'id' => (int) $row['id'],
            'userId' => (int) $row['user_id'],
            'expenseNumber' => $row['expense_number'],
            'category' => $row['category'],
            'description' => $row['description'],
            'amount' => (float) $row['amount'],
            'createdBy' => $row['created_by'],
            'expenseDate' => $row['expense_date']
        ];
    }

    mysqli_free_result($result);



    sendResponse(
        true,
        'Expenses retrieved successfully',
        $expenses
    );
?>