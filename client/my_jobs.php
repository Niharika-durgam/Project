<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'client') {
    header("Location: ../index.php");
    exit();
}

$client_id = $_SESSION['user']['id'];

$result = $conn->query("SELECT * FROM jobs WHERE client_id = $client_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posted Jobs | Freelancer Platform</title>
    <style>
        :root {
            --primary: #6c5ce7;
            --primary-light: rgba(108, 92, 231, 0.1);
            --secondary: #00cec9;
            --dark: #2d3436;
            --light: #f5f6fa;
            --gray: #636e72;
            --light-gray: #dfe6e9;
            --white: #ffffff;
            --success: #00b894;
            --warning: #fdcb6e;
            --danger: #d63031;
            --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            color: var(--primary);
            margin-bottom: 25px;
            font-size: 28px;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
        }

        h3 {
            color: var(--primary);
            margin-bottom: 10px;
        }

        .job-card {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .job-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
            color: var(--gray);
        }

        .job-meta p {
            margin: 5px 0;
        }

        .job-meta strong {
            color: var(--dark);
        }

        .action-links {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }

        .action-links a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: var(--transition);
        }

        .action-links a:first-child {
            background-color: var(--primary);
            color: white;
        }

        .action-links a:first-child:hover {
            background-color: #564fd8;
            transform: translateY(-2px);
        }

        .action-links a:last-child {
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .action-links a:last-child:hover {
            background-color: var(--primary-light);
        }

        .no-jobs {
            background: var(--white);
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            box-shadow: var(--shadow);
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .job-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-links {
                flex-direction: column;
            }
            
            .action-links a {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <h2>My Posted Jobs</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($job = $result->fetch_assoc()): ?>
            <div class="job-card">
                <h3><?= htmlspecialchars($job['title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                
                <div class="job-meta">
                    <p><strong>Budget:</strong> ₹<?= number_format($job['budget']) ?></p>
                    <p><strong>Deadline:</strong> <?= date('M d, Y', strtotime($job['deadline'])) ?></p>
                    <p><strong>Posted on:</strong> <?= date('M d, Y', strtotime($job['created_at'])) ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-jobs">
            <p>You haven't posted any jobs yet.</p>
        </div>
    <?php endif; ?>

    <div class="action-links">
        <a href="post_job.php">+ Post New Job</a>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</body>
</html>