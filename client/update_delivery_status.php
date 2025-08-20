<?php
session_start();
include '../includes/db.php';

if ($_SESSION['user']['user_type'] !== 'client') {
    die("Unauthorized");
}

$delivery_id = $_POST['delivery_id'];
$new_status = $_POST['status'];

$stmt = $conn->prepare("UPDATE deliveries SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $delivery_id);
$stmt->execute();

header("Location: client_deliveries.php");
exit();
?>