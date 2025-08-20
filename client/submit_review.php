<?php
session_start();
include '../includes/db.php';

if ($_SESSION['user']['user_type'] !== 'client') {
    die("Unauthorized");
}

$delivery_id = $_POST['delivery_id'];
$freelancer_id = $_POST['freelancer_id'];
$client_id = $_SESSION['user']['id'];
$rating = $_POST['rating'];
$review = $_POST['review'];

$stmt = $conn->prepare("INSERT INTO reviews (delivery_id, client_id, freelancer_id, rating, review) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiis", $delivery_id, $client_id, $freelancer_id, $rating, $review);
$stmt->execute();

header("Location: client_deliveries.php");
exit();
?>