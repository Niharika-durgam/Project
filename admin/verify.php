<?php
include '../includes/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ? AND user_type = 'admin'");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<h2>✅ Admin email verified successfully!</h2>";
        echo "<a href='login.php'>Click here to login</a>";
    } else {
        echo "Error verifying.";
    }
} else {
    echo "Invalid verification link.";
}