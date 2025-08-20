<?php
$host = "localhost";
$user = "root";      // Change if not using XAMPP
$pass = "";
$db = "freelancer_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>