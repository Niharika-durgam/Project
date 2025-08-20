<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['action'])) {
    $app_id = $_POST['app_id'];
    $action = $_POST['action'];

    if (!in_array($action, ['accept', 'reject'])) {
        die("Invalid action.");
    }

    $status = $action === 'accept' ? 'accepted' : 'rejected';

    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $app_id);
    if ($stmt->execute()) {
        header("Location: view_applications.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?> 