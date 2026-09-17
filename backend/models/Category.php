
<?php

    // categories = "categories";
    // category = "category";

    // Insert the data given into the  Database Table
    // Returns success if the insertion was complete and errors and the error message if it was not
    function createCategory(mysqli $conn, string $categoryName, string $description){
        mysqli_begin_transaction($conn);

        try {
            $insertSql = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertSql);

            if(!$insertStmt){
                throw new Exception("Failed to prepare category insert query.");
            }

            mysqli_stmt_bind_param($insertStmt,'ss', $categoryName, $description);

            if(!mysqli_stmt_execute($insertStmt)){
                mysqli_stmt_close($insertStmt);

                throw new Exception("Failed to insert New category.");
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
    function findCategory(mysqli $conn, $data, $exceptId = null){
        $conditions = [];
        $values = [];
        $types = "";

        if (isset($data["id"])) {
            $conditions[] = "id = ?";
            $values[] = $data["id"];
            $types .= "i";
        }

        if (isset($data["categoryName"])) {
            $conditions[] = "name = ?";
            $values[] = $data["categoryName"];
            $types .= "s";
        }

        // Nothing was supplied
        if (empty($conditions)) {
            return false;
        }

        $selectSql = "SELECT  id, name, description FROM categories WHERE (" . implode(" OR ", $conditions) . ")";

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
    function updateCategory(mysqli $conn, int $categoryId, string $categoryName, string $description) {
        $updateSql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param($updateStmt, 'ssi', $categoryName, $description, $categoryId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }
    
    // Delete a supplier
    // Returns true if success and false if an error was encounrtered
    function countProductReferences(mysqli $conn, int $productId){
        $sql = "SELECT ( SELECT COUNT(*) FROM sale_items WHERE product_id = ? ) + ( SELECT COUNT(*) FROM purchase_items WHERE product_id = ? ) + ( SELECT COUNT(*) FROM return_items WHERE product_id = ? ) + ( SELECT COUNT(*) FROM inventory_movements WHERE product_id = ? ) AS reference_count";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'iiii', $productId, $productId, $productId, $productId);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $row = mysqli_fetch_assoc($result);

        mysqli_free_result($result);
        mysqli_stmt_close($stmt);

        return (int) $row['reference_count'];
    }

    function countProductsByCategory(mysqli $conn, int $categoryId){
        $sql = "SELECT COUNT(*) AS product_count FROM products WHERE category_id = ?";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            return false;
        }

        mysqli_stmt_bind_param( $stmt, 'i', $categoryId);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $row = mysqli_fetch_assoc($result);

        mysqli_free_result($result);
        mysqli_stmt_close($stmt);

        return (int) $row['product_count'];
    }

    function deleteProduct(mysqli $conn, int $productId){
        $deleteSql = "DELETE FROM products WHERE id = ?";

        $deleteStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($deleteStmt, $deleteSql)) {
            return false;
        }

        mysqli_stmt_bind_param($deleteStmt,'i',$productId);

        $success = mysqli_stmt_execute($deleteStmt);

        mysqli_stmt_close($deleteStmt);

        return $success;
    }

    function deleteCategory(mysqli $conn, int $categoryId): bool{
        $sql = "DELETE FROM categories WHERE id = ?";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 'i', $categoryId);

        $success = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $success;
    }

    // Activate a supplier
    // Returns true if success and false if an errror was encounrtered
    function activateCategory(mysqli $conn, int $categoryId){

        $updateSql = "UPDATE categories SET status = 'Active' WHERE id = ?";

        $updateStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($updateStmt, $updateSql)) {
            return false;
        }

        mysqli_stmt_bind_param( $updateStmt, 'i', $categoryId);

        if (!mysqli_stmt_execute($updateStmt)) {
            mysqli_stmt_close($updateStmt);
            return false;
        }

        mysqli_stmt_close($updateStmt);

        return true;
    }

?>