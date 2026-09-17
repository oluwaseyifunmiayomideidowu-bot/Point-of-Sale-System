
<?php

    function userExist($conn){
        
        $checkSql = "SELECT id FROM users LIMIT 1";

        $checkStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($checkStmt, $checkSql)) {
            die("Prepare failed: " . mysqli_error($conn));
        }

        mysqli_stmt_execute($checkStmt);

        $result = mysqli_stmt_get_result($checkStmt);

        mysqli_stmt_close($checkStmt);

        return mysqli_num_rows($result);

    }

?>