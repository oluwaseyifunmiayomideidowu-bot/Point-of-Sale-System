
<?php

    require_once __DIR__ . '/../helpers/response.php';

    function requireAuth(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            sendResponse( false, 'Authentication required', null, 401);
        }
    }
?>