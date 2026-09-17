
<?php

    function sendResponse( bool $success, string $message, mixed $data = null, int $statusCode = 200): void {

        http_response_code($statusCode);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => $success, 
            'message' => $message, 
            'data' => $data
        ]);

        exit;
    }

?>