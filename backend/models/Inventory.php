<?php

function createInventoryMovement(
    mysqli $conn,
    int $productId,
    int $userId,
    string $movementType,
    int $quantity,
    ?int $referenceId = null,
    ?string $note = null
): bool {

    $sql = "
        INSERT INTO inventory_movements (
            product_id,
            user_id,
            movement_type,
            quantity,
            reference_id,
            note
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iisiss',
        $productId,
        $userId,
        $movementType,
        $quantity,
        $referenceId,
        $note
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}

?>