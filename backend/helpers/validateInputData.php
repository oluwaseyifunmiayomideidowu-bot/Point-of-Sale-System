
<?php

    function validateName(string $name, string $typeOfName, bool $isOptional = false): ?string {
        if ($name === "" && !$isOptional) {
            return "$typeOfName is required.";
        }
        
        if ($name !== "" &&  !preg_match("/^[\p{L}\s'-]+$/u", $name)) {
            return "$typeOfName contains invalid characters.";
        }

        return null;
    }

    function validateUsername(string $username, bool $isOptional = false): ?string{
        if ($username === "" && !$isOptional) {
            return "Username is required.";
        }

        if ($username !== "" && !preg_match("/^[a-zA-Z0-9_.-]+$/", $username)) {
            return "Username contains invalid characters.";
        }

        if ($username !== "" && (strlen($username) < 3 || strlen($username) > 30)) {
            return "Username must be between 3 and 30 characters.";
        }

        return null;
    }

    function validateLoginInput($input, string $typeOfName, bool $isOptional = false){
        if ($input === "" && !$isOptional) {
            return "$typeOfName is required.";
        }

        if ($input !== "") {
            $isUsername = preg_match("/^[a-zA-Z0-9_.-]+$/", $input);
            $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);
            $isPhone = preg_match("/^(?:\+234|0)[789][01]\d{8}$/", $input);

            if (!$isUsername && !$isEmail && !$isPhone) {
                return "Enter a valid $typeOfName.";
            }
        }

        return null;
    }

    function validateEmail( string $email , bool $isOptional = false): ?string {

        if ($email === "" && !$isOptional) {
            return "Email is required.";
        }
        
        if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Please enter a valid email";
        }

        return null;
    }

    function validatePhone( string $phone, bool $isOptional = false): ?string {
        if(!$isOptional){
            if ($phone === "") {
                return "Phone number is required.";
            } elseif (!preg_match('/^\+?\d+$/', $phone)) {
                return "Please enter a valid phone number.";
            }
        }else {
            if ($phone !== "" && !preg_match('/^\+?\d+$/', $phone)) {
                $errors["phone"] = "Phone number is invaild.";
            } 
        }
        return null;
    }

    function validatePasswords( string $password , string $confirmPassword): ?string {
        if($password === "") {
            return "Password is required.";
        }elseif(strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }elseif(!preg_match('/[A-Z]/', $password)) {
            return "Password must contain at least one uppercase letter.";
        }elseif (!preg_match('/[a-z]/', $password)) {
            return "Password must contain at least one lowercase letter.";
        }elseif (!preg_match('/[0-9]/', $password)) {
            return "Password must contain at least one number.";
        }elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return "Password must contain at least one special character.";
        }

        if($confirmPassword === "") {
            return "Confirm Password is required.";
        }elseif ($password !== $confirmPassword) {
            return "Confirm Password must match Password.";
        }

        return null;
    }

    function validateLoginPassword(string $password){

        if($password === "") {
            return "Password is required.";
        }

        return null;
    }

    function validateCheck($checkValue, $valueExpected, string $errorText): ?string{
        if($checkValue !== $valueExpected) {
            return $errorText;
        }
        return null;
    }
    
    function validateSelect($selectValue, array $allowedValues, string $errorText): ?string{

        if(!in_array($selectValue, $allowedValues, true)){
            return $errorText;
        }

        return null;
    }
    
    function validateDate($dateValue, $errorText){
            if($dateValue === ""){
                return $errorText;
            }else{
                try{
                    $date = new DateTime($dateValue);
                    $currentDate = new DateTime();
                    
                    if($date >= $currentDate){
                        return $errorText;
                    }
                }catch (Exception $e){
                    return $errorText;
                }
            }

        return null;
    }

    function validateNumeric($value, string $nameOfValue){

        if($value === ""){
            return "$nameOfValue is required.";
        }

        if (!is_numeric($value) || $value < 0) {
            return "$nameOfValue must be a valid positive number.";
        }

        return null;
    }

    function validateQuantity($quantity, string $nameOfQuantity){

        if($quantity === ""){
            return "$nameOfQuantity is required.";
        }

        if (!filter_var($quantity, FILTER_VALIDATE_INT) && $quantity !== '0' && $quantity !== 0) {
            return "$nameOfQuantity must be a valid whole number.";
        } elseif ((int)$quantity < 0) {
            return "$nameOfQuantity cannot be negative.";
        }

        return null;
    }

    function validateText($text, $fieldName = "Text", $isOptional = false){
        $text = trim($text);

        if ($text === "" && !$isOptional) {
            return "$fieldName is required.";
        }

        if ($text !== "" && mb_strlen($text) > 1000) {
            return "$fieldName must not exceed 1000 characters.";
        }

        return null;
    }

    function isAllNull($arr){
        return array_filter($arr, fn($value) => $value !== null) === [];
    }

    function removeNullValues($arr){
        return array_filter($arr, fn($value) => $value !== null);
    }  # Returns true if all error are null and false is there are not 
    
?>