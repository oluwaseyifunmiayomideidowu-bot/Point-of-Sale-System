
<?php

    // Insert the data given into the  Database Table
    // Returns success if the insertion was complete and errors and the error message if it was not
    function createUser(mysqli $conn, string $firstName, string $lastName, string $userName, string $email, string $phone, string $password, string $role, string $status){
        mysqli_begin_transaction($conn);

        try {
            $insertSql = "INSERT INTO users (first_name, last_name, username, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertSql);

            if(!$insertStmt){
                throw new Exception("Failed to prepare user insert query.");
            }

            $options = [
                "cost" => 12
            ];

            $hasedPassword = password_hash($password, PASSWORD_BCRYPT, $options);

            mysqli_stmt_bind_param($insertStmt,'ssssssss', $firstName, $lastName, $userName, $email, $phone, $hasedPassword, $role, $status);

            if(!mysqli_stmt_execute($insertStmt)){
                mysqli_stmt_close($insertStmt);

                throw new Exception("Failed to insert New User.");
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
    // Returns the user's information if a match is found,
    // null if the user doesn't exist,
    // and false if the query could not be prepared/executed
    // or no search value was supplied.
    // It can also find find a user info except for a specific id
    // Return value remains the same
    function findUser(mysqli $conn, $data, $exceptId = null){
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

        $selectSql = "SELECT  id, first_name, last_name, username, email, phone, password, role, status FROM users WHERE (" . implode(" OR ", $conditions) . ")";

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

    // Update the last login 
    // Return true if it could and flase if could not 
    function updateUserLastLogin(mysqli $conn, int $userId){
        $updateSql = "UPDATE users SET last_login = NOW() WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param($updateStmt, 'i', $userId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }

    // Update a user info
    // Returns true if the update was successful and false if an error was encoutered
    function updateUser(mysqli $conn, int $userId, string $firstName, string $lastName, string $userName, string $email, string $phone, string $role, string $status) {
        $updateSql = "UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param($updateStmt, 'sssssssi', $firstName, $lastName, $userName, $email, $phone, $role, $status, $userId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }

    // Deactive a user
    // Returns true if success and false if an error was encounrtered
    function deactivateUser(mysqli $conn, int $userId){
        $updateSql = "UPDATE users SET status = 'Inactive' WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param($updateStmt, 'i', $userId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }

    // Activate a user
    // Returns true if success and false if an errror was encounrtered
    function activateUser(mysqli $conn, int $userId){

        $updateSql = "UPDATE users SET status = 'Active' WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param( $updateStmt, 'i', $userId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }

    // Count Active Administrators
    // retuns the count and false if there was an error
    function countActiveAdministrators(mysqli $conn){
        $selectSql = "SELECT COUNT(*) AS admin_count FROM users WHERE role = 'Administrator' AND status = 'Active'";

        $result = mysqli_query($conn, $selectSql);

        if (!$result) {
            return false;
        }

        $row = mysqli_fetch_assoc($result);

        mysqli_free_result($result);

        return (int) $row['admin_count'];
    }

?>