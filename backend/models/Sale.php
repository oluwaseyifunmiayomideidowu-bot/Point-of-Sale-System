
<?php

    function generateSaleNumber(mysqli $conn): string{
        $saleDate = date('Y-m-d');

        // Lock today's sequence row
        $sql = "
            SELECT last_number
            FROM sale_sequences
            WHERE sale_date = ?
            FOR UPDATE
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            throw new Exception(
                "Failed to prepare sale number query."
            );
        }

        mysqli_stmt_bind_param($stmt, "s", $saleDate);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            throw new Exception(
                "Failed to retrieve sale number sequence."
            );
        }

        $result = mysqli_stmt_get_result($stmt);
        $sequence = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        // No sequence exists for today
        if (!$sequence) {

            $lastNumber = 1;

            $sql = "
                INSERT INTO sale_sequences
                    (sale_date, last_number)
                VALUES
                    (?, ?)
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                throw new Exception(
                    "Failed to prepare sale sequence."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $saleDate,
                $lastNumber
            );

            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                throw new Exception(
                    "Failed to create sale sequence."
                );
            }

            mysqli_stmt_close($stmt);

        } else {

            // Existing sequence for today
            $lastNumber =
                (int) $sequence['last_number'] + 1;

            $sql = "
                UPDATE sale_sequences
                SET last_number = ?
                WHERE sale_date = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            if (!$stmt) {
                throw new Exception(
                    "Failed to prepare sale sequence update."
                );
            }

            mysqli_stmt_bind_param(
                $stmt,
                "is",
                $lastNumber,
                $saleDate
            );

            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                throw new Exception(
                    "Failed to update sale sequence."
                );
            }

            mysqli_stmt_close($stmt);
        }


        // Build sale number
        return "SALE-" .
            date('Ymd') . "-" .
            str_pad(
                $lastNumber,
                4,
                "0",
                STR_PAD_LEFT
            );
    }

    function previewSaleNumber(mysqli $conn): string{
        $saleDate = date('Y-m-d');

        $sql = "
            SELECT last_number
            FROM sale_sequences
            WHERE sale_date = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            throw new Exception(
                "Failed to prepare sale number preview query."
            );
        }

        mysqli_stmt_bind_param($stmt, "s", $saleDate);

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            throw new Exception(
                "Failed to retrieve sale number sequence."
            );
        }

        $result = mysqli_stmt_get_result($stmt);
        $sequence = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        $nextNumber = $sequence
            ? (int) $sequence['last_number'] + 1
            : 1;


        return "SALE-" .
            date('Ymd') . "-" .
            str_pad(
                $nextNumber,
                4,
                "0",
                STR_PAD_LEFT
            );
    }

?>