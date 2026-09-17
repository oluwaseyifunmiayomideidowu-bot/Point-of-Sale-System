<?php 

    $host = "localhost";
    $password = "";
    $database = "point_of_sale_system";

    $conn = new mysqli($host, "root", $password, $database);

    if($conn -> connect_error){
        die("Database connection failed: ". $conn->connect_error);
    }

?>