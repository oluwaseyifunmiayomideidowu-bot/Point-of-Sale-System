<?php

    require_once __DIR__ . '/../../config/db.php';
    require_once __DIR__ . '/../../config/cors.php';
    require_once __DIR__ . '/../../config/setting.php';
    require_once __DIR__ . '/../../models/Product.php';
    require_once __DIR__ . '/../../models/Category.php';
    require_once __DIR__ . '/../../models/Supplier.php';
    require_once __DIR__ . '/../../models/Sale.php';
    require_once __DIR__ . '/../../middleware/auth.php';
    require_once __DIR__ . '/../../middleware/role.php';
    require_once __DIR__ . '/../../helpers/functions.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed.', null, 405);
    }

    requireAuth();
    requireRole(['Administrator', 'Manager', 'Cashier']);

    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        sendResponse(false, 'Invalid request data.', null, 400);
    }

    $items = $data['items'] ?? [];
    $discount = $data['discount'] ?? 0;
    $paymentMethod = trim($data['paymentMethod'] ?? '');
    $amountPaid = $data['amountPaid'] ?? null;


    // Basic validation
    $inputErrors = [];

    if (!is_array($items) || count($items) === 0) {
        $inputErrors['items'] = 'At least one product is required.';
    }

    if (!is_numeric($discount) || $discount < 0) {
        $inputErrors['discount'] = 'Invalid discount.';
    }

    if (!is_numeric($amountPaid) || $amountPaid < 0) {
        $inputErrors['amountPaid'] = 'Invalid amount paid.';
    }

    $allowedPaymentMethods = [
        'Cash',
        'Card',
        'Transfer'
    ];

    if (!in_array($paymentMethod, $allowedPaymentMethods, true)) {
        $inputErrors['paymentMethod'] = 'Invalid payment method.';
    }

    if (!empty($inputErrors)) {
        sendResponse(false, 'Validation failed.', removeNullValues($inputErrors), 422);
    }

    $discount = (float)$discount;
    $amountPaid = (float)$amountPaid;

    $userId = (int)$_SESSION['user_id'];


    // Start transaction
    mysqli_begin_transaction($conn);

    try {

        $subtotal = 0;
        $saleItems = [];

        foreach ($items as $item) {

            $productId = $item['productId'] ?? null;
            $quantity = $item['quantity'] ?? null;

            if (
                !filter_var($productId, FILTER_VALIDATE_INT) ||
                (int)$productId <= 0
            ) {
                throw new Exception('Invalid product ID.');
            }

            if (
                !filter_var($quantity, FILTER_VALIDATE_INT) ||
                (int)$quantity <= 0
            ) {
                throw new Exception('Product quantity must be a positive whole number.');
            }

            $productId = (int)$productId;
            $quantity = (int)$quantity;


            // Lock the product row while this sale is being processed
            $productSql = "
                SELECT
                    id,
                    name,
                    selling_price,
                    quantity,
                    status
                FROM products
                WHERE id = ?
                FOR UPDATE
            ";

            $productStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($productStmt, $productSql)) {
                throw new Exception('Failed to prepare product query.');
            }

            mysqli_stmt_bind_param(
                $productStmt,
                'i',
                $productId
            );

            if (!mysqli_stmt_execute($productStmt)) {
                mysqli_stmt_close($productStmt);
                throw new Exception('Failed to retrieve product.');
            }

            $result = mysqli_stmt_get_result($productStmt);

            if (mysqli_num_rows($result) === 0) {
                mysqli_stmt_close($productStmt);
                throw new Exception('Product not found.');
            }

            $product = mysqli_fetch_assoc($result);

            mysqli_stmt_close($productStmt);


            // Product must be active
            if ($product['status'] !== 'active') {
                throw new Exception(
                    $product['name'] . ' is inactive.'
                );
            }


            // Check stock
            if ((int)$product['quantity'] < $quantity) {
                throw new Exception(
                    'Insufficient stock for ' . $product['name'] . '.'
                );
            }


            $unitPrice = (float)$product['selling_price'];
            $itemTotal = $unitPrice * $quantity;

            $subtotal += $itemTotal;


            $saleItems[] = [
                'productId' => $productId,
                'quantity' => $quantity,
                'unitPrice' => $unitPrice,
                'total' => $itemTotal
            ];
        }


        // Prevent discount from exceeding subtotal
        if ($discount > $subtotal) {
            throw new Exception(
                'Discount cannot be greater than the subtotal.'
            );
        }


        /*
        * Tax will be added here once we connect the
        * business tax setting.
        */
        $tax = 0;

        $total = $subtotal - $discount + $tax;


        // Check payment
        if ($amountPaid < $total) {
            throw new Exception(
                "Amount paid cannot be less than the total. $total"
            );
        }

        $changeAmount = $amountPaid - $total;
        $saleNumber = generateSaleNumber($conn);
        $nextSaleNumber = previewSaleNumber($conn);


        // Create sale
        $saleSql = "
            INSERT INTO sales (
                user_id,
                sale_number,
                subtotal,
                discount,
                tax,
                total_amount,
                payment_method,
                amount_received,
                change_amount
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $saleStmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($saleStmt, $saleSql)) {
            throw new Exception('Failed to prepare sale.');
        }

        mysqli_stmt_bind_param(
            $saleStmt,
            'isddddssd',
            $userId,
            $saleNumber,
            $subtotal,
            $discount,
            $tax,
            $total,
            $paymentMethod,
            $amountPaid,
            $changeAmount
        );

        if (!mysqli_stmt_execute($saleStmt)) {
            mysqli_stmt_close($saleStmt);
            throw new Exception('Failed to create sale.');
        }

        $saleId = mysqli_insert_id($conn);
        mysqli_stmt_close($saleStmt);


        // Create sale items and reduce stock
        foreach ($saleItems as $saleItem) {

            $itemSql = "
                INSERT INTO sale_items (
                    sale_id,
                    sale_number,
                    product_id,
                    quantity,
                    unit_price,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ";

            $itemStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($itemStmt, $itemSql)) {
                throw new Exception('Failed to prepare sale item.');
            }

            mysqli_stmt_bind_param(
                $itemStmt,
                'isiidd',
                $saleId,
                $saleNumber,
                $saleItem['productId'],
                $saleItem['quantity'],
                $saleItem['unitPrice'],
                $saleItem['total']
            );

            if (!mysqli_stmt_execute($itemStmt)) {
                mysqli_stmt_close($itemStmt);
                throw new Exception('Failed to create sale item.');
            }

            mysqli_stmt_close($itemStmt);


            // Reduce stock
            $stockSql = "
                UPDATE products
                SET quantity = quantity - ?
                WHERE id = ?
            ";

            $stockStmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($stockStmt, $stockSql)) {
                throw new Exception('Failed to prepare stock update.');
            }

            mysqli_stmt_bind_param(
                $stockStmt,
                'ii',
                $saleItem['quantity'],
                $saleItem['productId']
            );

            if (!mysqli_stmt_execute($stockStmt)) {
                mysqli_stmt_close($stockStmt);
                throw new Exception('Failed to update product stock.');
            }

            mysqli_stmt_close($stockStmt);
        }


        // Everything succeeded
        mysqli_commit($conn);


        sendResponse(
            true,
            'Sale completed successfully.',
            [
                'saleId' => $saleId,
                'saleNumber' => $saleNumber,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'amountPaid' => $amountPaid,
                'changeAmount' => $changeAmount,
                'nextSaleNumber' => $nextSaleNumber
            ],
            201
        );

    } catch (Throwable $e) {

        // Undo EVERYTHING if anything failed
        mysqli_rollback($conn);

        sendResponse(
            false,
            $e->getMessage(),
            null,
            422
        );
    }