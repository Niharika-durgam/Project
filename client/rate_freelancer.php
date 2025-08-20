<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['rating'])) {
    $app_id = $_POST['app_id'];
    $rating = (int)$_POST['rating'];

    if ($rating < 1 || $rating > 5) {
        die("Invalid rating.");
    }

    $stmt = $conn->prepare("UPDATE applications SET client_rating = ? WHERE id = ?");
    $stmt->bind_param("ii", $rating, $app_id);
    if ($stmt->execute()) {
        header("Location: view_applications.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>