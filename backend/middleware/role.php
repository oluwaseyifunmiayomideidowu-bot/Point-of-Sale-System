
<?php

require_once __DIR__ . '/../helpers/response.php';

function requireRole(array $allowedRoles): void
{
    if (!isset($_SESSION['role'])) {
        sendResponse(
            false,
            'Authentication required',
            null,
            401
        );
    }

    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        sendResponse(
            false,
            'You do not have permission to perform this action',
            null,
            403
        );
    }
}