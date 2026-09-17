
<?php
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../helpers/response.php';

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed', null, 405);
    }

    // Start the session if it is not already active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Clear all session data
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    sendResponse( true, 'Logout successful', null, 200);

?>