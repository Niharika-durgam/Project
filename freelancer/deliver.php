<?php
session_start();
include '../includes/db.php';

if ($_SESSION['user']['user_type'] !== 'freelancer') {
    exit("Unauthorized");
}

$job_id = $_GET['job_id'] ?? null;

if (!$job_id) {
    die("❌ Missing job ID. Please access this page from a valid job application.");
}

$freelancer_id = $_SESSION['user']['id'];

// Fetch job title
$job_stmt = $conn->prepare("SELECT title FROM jobs WHERE id = ?");
$job_stmt->bind_param("i", $job_id);
$job_stmt->execute();
$job_result = $job_stmt->get_result();

if ($job_result->num_rows === 0) {
    die("❌ Job not found.");
}

$job = $job_result->fetch_assoc();
$job_title = htmlspecialchars($job['title']);

$client_stmt = $conn->prepare("
    SELECT u.name 
    FROM jobs j
    JOIN users u ON j.client_id = u.id
    WHERE j.id = ?
");
$client_stmt->bind_param("i", $job_id);
$client_stmt->execute();
$client_result = $client_stmt->get_result();

if ($client_result->num_rows === 0) {
    die("❌ Job or client not found.");
}

$client = $client_result->fetch_assoc();
$client_name = htmlspecialchars($client['name']);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['file'])) {
    $uploadDir = "../uploads/";

    // ✅ Ensure uploads folder exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['file']['name']);
    $targetFile = $uploadDir . time() . "_" . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        $message = $_POST['message'];

        $stmt = $conn->prepare("INSERT INTO deliveries (job_id, freelancer_id, file_path, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $job_id, $freelancer_id, $targetFile, $message);
        $stmt->execute();

        echo "<div class='success-message'>✅ Project delivered!</div>";
    } else {
        echo "<div class='error-message'>❌ Failed to upload file.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deliver Project</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .job-title {
            font-size: 1.2em;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #eaf2f8;
            border-left: 4px solid #3498db;
        }
        
        .delivery-form {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 15px;
            font-family: inherit;
        }
        
        .file-input {
            margin: 15px 0;
        }
        
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="delivery-form">
        <h2>Deliver Project <?= $client_name ?></h2>
        <div class="job-title">Job: <?= $job_title ?></div>
        
        <form method="post" enctype="multipart/form-data">
            <label for="message">Message (optional):</label><br>
            <textarea name="message" id="message" rows="4" cols="50" placeholder="Add any additional notes about your delivery..."></textarea><br>
            
            <div class="file-input">
                <label for="file">Select file:</label><br>
                <input type="file" name="file" id="file" required>
            </div>
            
            <button type="submit">Upload & Deliver</button>
        </form>
    </div>
</body>
</html>