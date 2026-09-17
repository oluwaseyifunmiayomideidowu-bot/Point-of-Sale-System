<?php

require_once __DIR__ . '/../middleware/auth.php';

requireAuth();

sendResponse(
    true,
    'Authenticated user',
    [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name'],
        'role' => $_SESSION['role']
    ]
);