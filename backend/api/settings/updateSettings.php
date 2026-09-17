
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
    requireRole(['Administrator']);

    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid JSON data', null, 400);
    }



    $businessName = trim($data['businessName'] ?? '');

    if ($businessName === '') {
        sendResponse(false, 'Business name is required', null, 400);
    }

    if (strlen($businessName) > 150) {
        sendResponse(false, 'Business name must not exceed 150 characters', null, 400);
    }



    $address = trim($data['address'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $email = trim($data['email'] ?? '');
    $logo = trim($data['logo'] ?? '');


    if (strlen($address) > 255) {
        sendResponse(false, 'Address must not exceed 255 characters', null, 400);
    }

    if (strlen($phone) > 50) {
        sendResponse(false, 'Phone must not exceed 50 characters', null, 400);
    }

    if (strlen($email) > 150) {
        sendResponse(false, 'Email must not exceed 150 characters', null, 400);
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Invalid email address', null, 400);
    }

    if (strlen($logo) > 255) {
        sendResponse(false, 'Logo path must not exceed 255 characters', null, 400);
    }



    if (
        !isset($data['taxRate']) ||
        !is_numeric($data['taxRate'])
    ) {
        sendResponse(false, 'Valid tax rate is required', null, 400);
    }

    $taxRate = (float) $data['taxRate'];

    if ($taxRate < 0 || $taxRate > 100) {
        sendResponse(
            false,
            'Tax rate must be between 0 and 100',
            null,
            400
        );
    }



    $checkSql = "
        SELECT id
        FROM settings
        ORDER BY id ASC
        LIMIT 1
    ";

    $checkResult = mysqli_query($conn, $checkSql);

    if (!$checkResult) {
        sendResponse(false, 'Failed to check settings', null, 500);
    }



    if (mysqli_num_rows($checkResult) > 0) {

        $row = mysqli_fetch_assoc($checkResult);
        $settingsId = (int) $row['id'];

        mysqli_free_result($checkResult);

        $updateSql = "
            UPDATE settings
            SET
                business_name = ?,
                address = ?,
                phone = ?,
                email = ?,
                logo = ?,
                tax_rate = ?
            WHERE id = ?
        ";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $updateSql)) {
            sendResponse(false, 'Failed to prepare settings update', null, 500);
        }

        mysqli_stmt_bind_param(
            $stmt,
            'sssssdi',
            $businessName,
            $address,
            $phone,
            $email,
            $logo,
            $taxRate,
            $settingsId
        );

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            sendResponse(
                false,
                'Failed to update settings',
                null,
                500
            );
        }

        mysqli_stmt_close($stmt);

    } else {


        mysqli_free_result($checkResult);

        $insertSql = "
            INSERT INTO settings (
                business_name,
                address,
                phone,
                email,
                logo,
                tax_rate
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $insertSql)) {
            sendResponse(false, 'Failed to prepare settings insert', null, 500);
        }

        mysqli_stmt_bind_param(
            $stmt,
            'sssssd',
            $businessName,
            $address,
            $phone,
            $email,
            $logo,
            $taxRate
        );

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            sendResponse(
                false,
                'Failed to create settings',
                null,
                500
            );
        }

        mysqli_stmt_close($stmt);
    }



    sendResponse(
        true,
        'Settings updated successfully',
        [
            'businessName' => $businessName,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'logo' => $logo,
            'taxRate' => $taxRate
        ]
    );
?>