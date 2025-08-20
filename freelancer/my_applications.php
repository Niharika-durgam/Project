<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'freelancer') {
    header("Location: ../index.php");
    exit();
}

$freelancer_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT applications.*, jobs.title FROM applications JOIN jobs ON applications.job_id = jobs.id WHERE freelancer_id = ? ORDER BY applied_at DESC");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Applications</h2>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications | Freelance Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .application-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 20px;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .application-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .application-card h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 20px;
        }

        .job-id {
            color: #6c757d;
            font-size: 14px;
            font-weight: normal;
        }

        .application-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 15px 0;
        }

        .detail-item {
            flex: 1;
            min-width: 150px;
        }

        .detail-item strong {
            display: block;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status.accepted {
            background-color: #d4edda;
            color: #155724;
        }

        .status.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 14px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #38b6db;
        }

        .btn i {
            margin-right: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-state i {
            font-size: 50px;
            color: #adb5bd;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #6c757d;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .application-details {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
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

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="application-card">
                    <h3><?= htmlspecialchars($row['title']) ?> <span class="job-id">(Job #<?= $row['job_id'] ?>)</span></h3>
                    
                    <div class="application-details">
                        <div class="detail-item">
                            <strong>Bid Amount</strong>
                            <span>₹<?= number_format($row['bid_amount'], 2) ?></span>
                        </div>
                        
                        <div class="detail-item">
                            <strong>Status</strong>
                            <span class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                        </div>
                        
						<?php
                        // Get latest delivery status for this job
                        $delStatus = $conn->prepare("SELECT status FROM deliveries WHERE job_id = ? AND freelancer_id = ? ORDER BY delivered_at DESC LIMIT 1");
                        $delStatus->bind_param("ii", $row['job_id'], $freelancer_id);
                        $delStatus->execute();
                        $statusRes = $delStatus->get_result();
                        if ($sRow = $statusRes->fetch_assoc()): ?>
                            <div class="detail-item">
                                <strong>Delivery Status</strong>
                                <span class="status <?= $sRow['status'] ?>"><?= ucfirst($sRow['status']) ?></span>
                            </div>
                        <?php endif; ?>
						
                        <?php if ($row['status'] === 'accepted'): ?>
                            <div class="detail-item">
                                <strong>Status Message</strong>
                                <span style="color: var(--success-color);"><i class="fas fa-check-circle"></i> You've been accepted!</span>
                            </div>
                        <?php elseif ($row['status'] === 'rejected'): ?>
                            <div class="detail-item">
                                <strong>Status Message</strong>
                                <span style="color: var(--danger-color);"><i class="fas fa-times-circle"></i> Application rejected</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="../chat/messages.php?job_id=<?= $row['job_id'] ?>" class="btn btn-primary">
                            <i class="fas fa-comments"></i> Open Chat
                        </a>
                        
                        <?php if ($row['status'] === 'accepted'): ?>
                            <a href="../freelancer/deliver.php?job_id=<?= $row['job_id'] ?>" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Deliver Project
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <p>You haven't applied to any jobs yet.</p>
            </div>
        <?php endif; ?>
		<a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>