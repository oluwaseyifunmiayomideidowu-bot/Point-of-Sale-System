
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

    $sql = "
        SELECT
            id,
            business_name,
            address,
            phone,
            email,
            logo,
            tax_rate
        FROM settings
        ORDER BY id ASC
        LIMIT 1
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        sendResponse(false, 'Failed to retrieve settings', null, 500);
    }

    if (mysqli_num_rows($result) === 0) {
        mysqli_free_result($result);

        sendResponse(
            false,
            'Business settings have not been configured',
            null,
            404
        );
    }

    $settings = mysqli_fetch_assoc($result);

    mysqli_free_result($result);

    $settings['id'] = (int) $settings['id'];
    $settings['tax_rate'] = (float) $settings['tax_rate'];

    sendResponse(
        true,
        'Settings retrieved successfully',
        $settings
    );
?>