<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'client') {
    header("Location: ../index.php");
    exit();
}

$client_id = $_SESSION['user']['id'];

// Fetch client's jobs with applications
$sql = "SELECT 
            jobs.title, 
            jobs.id AS job_id,
            applications.*,
            users.name AS freelancer_name
        FROM jobs 
        JOIN applications ON jobs.id = applications.job_id
        JOIN users ON applications.freelancer_id = users.id
        WHERE jobs.client_id = ?
        ORDER BY applications.applied_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications | Freelancer Platform</title>
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

        .application-card {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .application-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .application-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .application-title {
            color: var(--primary);
            margin: 0;
        }

        .application-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }

        .application-meta p {
            margin: 5px 0;
        }

        .application-meta strong {
            color: var(--dark);
        }

        .application-message {
            background: var(--light-gray);
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(253, 203, 110, 0.2);
            color: #e17055;
        }

        .status-accepted {
            background: rgba(0, 184, 148, 0.2);
            color: var(--success);
        }

        .status-rejected {
            background: rgba(214, 48, 49, 0.2);
            color: var(--danger);
        }

        .action-form {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .action-form button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }

        .accept-btn {
            background-color: var(--success);
            color: white;
        }

        .accept-btn:hover {
            background-color: #00a382;
            transform: translateY(-2px);
        }

        .reject-btn {
            background-color: var(--danger);
            color: white;
        }

        .reject-btn:hover {
            background-color: #c02b2b;
            transform: translateY(-2px);
        }

        .rating-form {
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .rating-form input {
            padding: 8px;
            border: 1px solid var(--light-gray);
            border-radius: 5px;
            width: 60px;
        }

        .rating-form button {
            padding: 8px 16px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: var(--transition);
        }

        .rating-form button:hover {
            background-color: #564fd8;
            transform: translateY(-2px);
        }

        .rating-display {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            color: var(--gray);
        }

        .chat-link {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 16px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: var(--transition);
        }

        .chat-link:hover {
            background-color: #564fd8;
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            color: var(--primary);
            text-decoration: none;
            border: 1px solid var(--primary);
            border-radius: 5px;
            transition: var(--transition);
        }

        .back-link:hover {
            background-color: var(--primary-light);
        }

        .no-applications {
            background: var(--white);
            padding: 30px;
            text-align: center;
            border-radius: 8px;
            box-shadow: var(--shadow);
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .application-header {
                flex-direction: column;
            }
            
            .application-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <h2>Applications to Your Jobs</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($app = $result->fetch_assoc()): ?>
            <div class="application-card">
                <div class="application-header">
                    <h3 class="application-title"><?= htmlspecialchars($app['title']) ?> (Job #<?= $app['job_id'] ?>)</h3>
                    <span class="status status-<?= $app['status'] ?>"><?= ucfirst($app['status']) ?></span>
                </div>
                
                <div class="application-meta">
                    <p><strong>Freelancer:</strong> <?= htmlspecialchars($app['freelancer_name']) ?></p>
                    <p><strong>Bid:</strong> ₹<?= number_format($app['bid_amount']) ?></p>
                    <p><strong>Applied at:</strong> <?= date('M d, Y h:i A', strtotime($app['applied_at'])) ?></p>
                </div>
                
                <div class="application-message">
                    <p><strong>Message:</strong></p>
                    <p><?= nl2br(htmlspecialchars($app['message'])) ?></p>
                </div>

                <?php if ($app['status'] === 'pending'): ?>
                    <form method="post" action="update_status.php" class="action-form">
                        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                        <button type="submit" name="action" value="accept" class="accept-btn">✅ Accept Application</button>
                        <button type="submit" name="action" value="reject" class="reject-btn">❌ Reject Application</button>
                    </form>

                <?php else: ?>
                    <?php if (!is_null($app['client_rating'])): ?>
                        <div class="rating-display">
                            <span>⭐</span>
                            <p><strong>Your Rating:</strong> <?= $app['client_rating'] ?>/5</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <a href="../chat/messages.php?job_id=<?= $app['job_id'] ?>" class="chat-link">💬 Open Chat</a>
				<a href="client_deliveries.php?job_id=<?= $app['job_id'] ?>" class="chat-link">💬 Open Deliveries</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-applications">
            <p>No applications yet.</p>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</body>
</html>