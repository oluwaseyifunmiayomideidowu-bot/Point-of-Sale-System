
<?php

    // Insert the data given into the  Database Table
    // Returns success if the insertion was complete and errors and the error message if it was not
    function createSupplier(mysqli $conn, string $firstName, string $lastName, string $userName, string $email, string $phone, string $status){
        mysqli_begin_transaction($conn);

        try {
            $insertSql = "INSERT INTO suppliers (first_name, last_name, username, email, phone, status) VALUES (?, ?, ?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertSql);

            if(!$insertStmt){
                throw new Exception("Failed to prepare supplier insert query.");
            }

            mysqli_stmt_bind_param($insertStmt,'ssssss', $firstName, $lastName, $userName, $email, $phone, $status);

            if(!mysqli_stmt_execute($insertStmt)){
                mysqli_stmt_close($insertStmt);

                throw new Exception("Failed to insert New Supplier.");
            }
            
            mysqli_commit($conn);
            mysqli_stmt_close($insertStmt);

            return 'success';
        }catch(Throwable $e){
            mysqli_rollback($conn);
            return[
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    };
    
    // Checks the database table if one of the supplied values already exists.
    // Returns the supplier's information if a match is found,
    // null if the supplier doesn't exist,
    // and false if the query could not be prepared/executed
    // or no search value was supplied.
    // It can also find find a supplier info except for a specific id
    // Return value remains the same
    function findSupplier(mysqli $conn, $data, $exceptId = null){
        $conditions = [];
        $values = [];
        $types = "";

        if (isset($data["id"])) {
            $conditions[] = "id = ?";
            $values[] = $data["id"];
            $types .= "i";
        }

        if (isset($data["username"])) {
            $conditions[] = "username = ?";
            $values[] = $data["username"];
            $types .= "s";
        }

        if (isset($data["email"])) {
            $conditions[] = "email = ?";
            $values[] = $data["email"];
            $types .= "s";
        }

        if (isset($data["phone"])) {
            $conditions[] = "phone = ?";
            $values[] = $data["phone"];
            $types .= "s";
        }

        // Nothing was supplied
        if (empty($conditions)) {
            return false;
        }

        $selectSql = "SELECT  id, first_name, last_name, username, email, phone, status FROM suppliers WHERE (" . implode(" OR ", $conditions) . ")";

        // Exclude a specific user when requested
        if ($exceptId !== null) {
            $selectSql .= " AND id != ?";
            $values[] = $exceptId;
            $types .= "i";
        }

        $selectSql .= " LIMIT 1";

        $selectStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($selectStmt, $selectSql)) {
            return false;
        }

        mysqli_stmt_bind_param($selectStmt, $types, ...$values
        );

        if (!mysqli_stmt_execute($selectStmt)) {
            mysqli_stmt_close($selectStmt);
            return false;
        }

        $result = mysqli_stmt_get_result($selectStmt);

        if (mysqli_num_rows($result) === 0) {
            mysqli_stmt_close($selectStmt);
            return null;
        }

        $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($selectStmt);

        return $user;
    }

    // Update a supplier info
    // Returns true if the update was successful and false if an error was encoutered
    function updateSupplier(mysqli $conn, int $supplierId, string $firstName, string $lastName, string $userName, string $email, string $phone, string $status) {
        $updateSql = "UPDATE suppliers SET first_name = ?, last_name = ?, username = ?, email = ?, phone = ?, status = ? WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param($updateStmt, 'ssssssi', $firstName, $lastName, $userName, $email, $phone, $status, $supplierId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }
    
    // Deactive a supplier
    // Returns true if success and false if an error was encounrtered
    function deactivateSupplier(mysqli $conn, int $supplierId){
        $updateSql = "UPDATE suppliers SET status = 'Inactive' WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param($updateStmt, 'i', $supplierId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }

    // Activate a supplier
    // Returns true if success and false if an errror was encounrtered
    function activateSupplier(mysqli $conn, int $supplierId){

        $updateSql = "UPDATE suppliers SET status = 'Active' WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param( $updateStmt, 'i', $supplierId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }

?>