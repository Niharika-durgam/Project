<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'freelancer') {
    header("Location: ../index.php");
    exit();
}

$result = $conn->query("SELECT jobs.*, users.name AS client_name FROM jobs JOIN users ON jobs.client_id = users.id ORDER BY jobs.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Jobs</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .job-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background: #fff;
            transition: all 0.3s ease;
        }
        .job-card:hover {
            border-color: #3498db;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.3);
        }
        .job-card h3 {
            color: #3498db;
            margin-top: 0;
        }
        .job-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        .job-description {
            margin: 10px 0;
        }
        .apply-btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .apply-btn:hover {
            background: #2980b9;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Available Jobs</h2>

        <?php while ($job = $result->fetch_assoc()): ?>
            <div class="job-card">
                <h3><?= htmlspecialchars($job['title']) ?></h3>
                <div class="job-meta">
                    <p><strong>Client:</strong> <?= htmlspecialchars($job['client_name']) ?></p>
                    <p><strong>Budget:</strong> ₹<?= $job['budget'] ?></p>
                    <p><strong>Deadline:</strong> <?= $job['deadline'] ?></p>
                </div>
                <div class="job-description">
                    <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                </div>
                <a href="apply.php?job_id=<?= $job['id'] ?>" class="apply-btn">Apply</a>
            </div>
        <?php endwhile; ?>

        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>