<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sender_id = $_SESSION['user']['id'];
    $receiver_id = $_POST['receiver_id'];
    $job_id = $_POST['job_id'];
    $message = trim($_POST['message']);
    $attachment_path = null;

    // Check if both message and file are empty
    if ($message === '' && (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] === UPLOAD_ERR_NO_FILE)) {
        $_SESSION['error'] = "Message cannot be empty and no file attached.";
        header("Location: messages.php?job_id=" . $job_id);
        exit();
    }

    // Handle file upload if present
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../uploads/chat_attachments/";
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Validate file
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf', 
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain'
        ];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        $fileType = $_FILES['attachment']['type'];
        $fileSize = $_FILES['attachment']['size'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['error'] = "File type not allowed. Allowed types: JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, TXT";
            header("Location: messages.php?job_id=" . $job_id);
            exit();
        }
        
        if ($fileSize > $maxFileSize) {
            $_SESSION['error'] = "File too large. Maximum size is 5MB.";
            header("Location: messages.php?job_id=" . $job_id);
            exit();
        }
        
        // Generate unique filename
        $extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
            $attachment_path = $targetPath;
        } else {
            $_SESSION['error'] = "Failed to upload file.";
            header("Location: messages.php?job_id=" . $job_id);
            exit();
        }
    }

    // Insert message into database
    $stmt = $conn->prepare("INSERT INTO messages (job_id, sender_id, receiver_id, message, attachment_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $job_id, $sender_id, $receiver_id, $message, $attachment_path);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Message sent successfully!";
    } else {
        $_SESSION['error'] = "Failed to send message.";
    }

    header("Location: messages.php?job_id=" . $job_id);
    exit();
}
?>