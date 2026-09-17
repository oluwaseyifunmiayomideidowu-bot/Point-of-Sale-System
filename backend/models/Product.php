
<?php

    function findProductBySku(mysqli $conn, string $sku){
        $selectSql = "SELECT id, name, sku FROM products WHERE sku = ? LIMIT 1";

        $selectStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($selectStmt, $selectSql)) {
            return false;
        }

        mysqli_stmt_bind_param($selectStmt, 's', $sku);

        if (!mysqli_stmt_execute($selectStmt)) {
            mysqli_stmt_close($selectStmt);
            return false;
        }

        $result = mysqli_stmt_get_result($selectStmt);

        if (mysqli_num_rows($result) === 0) {
            mysqli_stmt_close($selectStmt);
            return null;
        }

        $product = mysqli_fetch_assoc($result);

        mysqli_stmt_close($selectStmt);

        return $product;
    }

    function createProduct( mysqli $conn, int $categoryId, int $supplierId, string $name, string $sku, float $costPrice, float $sellingPrice, int $quantity, int $reorderLevel, ?string $image, string $status){
        $insertSql = "INSERT INTO products (category_id, supplier_id, name, sku, cost_price, selling_price, quantity, reorder_level, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $insertStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($insertStmt, $insertSql)) {
            return false;
        }

        mysqli_stmt_bind_param($insertStmt, 'iissddiiss', $categoryId, $supplierId, $name, $sku, $costPrice, $sellingPrice, $quantity, $reorderLevel, $image, $status);

        if (!mysqli_stmt_execute($insertStmt)) {
            mysqli_stmt_close($insertStmt);
            return false;
        }

        $productId = mysqli_insert_id($conn);

        mysqli_stmt_close($insertStmt);

        return $productId;
    }

    function createSku(mysqli $conn): string|false{
        do {
            $sku = 'VND-' . strtoupper(bin2hex(random_bytes(4)));

            $existingSku = findProductBySku($conn, $sku);

            if ($existingSku === false) {
                return false;
            }

        } while ($existingSku !== null);

        return $sku;
    }

    function uploadProductImage(array $file){
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'status' => 'error',
                'message' => 'Product image upload failed.'
            ];
        }

        // Maximum size: 2MB
        if ($file['size'] > 2 * 1024 * 1024) {
            return [
                'status' => 'error',
                'message' => 'Product image must not exceed 2MB.'
            ];
        }

        // Check the actual file type
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedTypes[$mimeType])) {
            return [
                'status' => 'error',
                'message' => 'Only JPG, PNG and WebP images are allowed.'
            ];
        }

        $extension = $allowedTypes[$mimeType];

        // Generate a random filename
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;

        $uploadDirectory = __DIR__ . '/../uploads/products/';

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }

        $filePath = $uploadDirectory . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return [
                'status' => 'error',
                'message' => 'Failed to save product image.'
            ];
        }

        return [
            'status' => 'success',
            'path' => 'uploads/products/' . $fileName,
            'fullPath' => $filePath
        ];
    }

?>