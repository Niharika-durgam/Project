<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'client') {
    header("Location: ../index.php");
    exit();
}

$client_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT d.*, j.title, u.name AS freelancer_name 
    FROM deliveries d 
    JOIN jobs j ON d.job_id = j.id 
    JOIN users u ON d.freelancer_id = u.id
    WHERE j.client_id = ?
    ORDER BY d.delivered_at DESC
");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivered Projects | Freelancer Platform</title>
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

        .delivery-card {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .delivery-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .delivery-title {
            color: var(--primary);
            margin-bottom: 15px;
        }

        .delivery-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }

        .delivery-meta p {
            margin: 5px 0;
        }

        .delivery-meta strong {
            color: var(--dark);
        }

        .delivery-message {
            background: var(--light-gray);
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .download-link {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            transition: var(--transition);
        }

        .download-link:hover {
            background-color: #564fd8;
            transform: translateY(-2px);
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin: 10px 0;
        }

        .status-delivered {
            background: rgba(253, 203, 110, 0.2);
            color: #e17055;
        }

        .status-approved {
            background: rgba(0, 184, 148, 0.2);
            color: var(--success);
        }

        .action-form {
            margin: 15px 0;
        }

        .action-form button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            margin-right: 10px;
        }

        .approve-btn {
            background-color: var(--success);
            color: white;
        }

        .approve-btn:hover {
            background-color: #00a382;
            transform: translateY(-2px);
        }

        .review-form {
            margin-top: 20px;
            padding: 15px;
            background: var(--light-gray);
            border-radius: 5px;
        }

        .review-form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 500;
        }

        .review-form input[type="number"] {
            padding: 8px;
            border: 1px solid var(--light-gray);
            border-radius: 5px;
            width: 60px;
        }

        .review-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--light-gray);
            border-radius: 5px;
            resize: vertical;
            min-height: 80px;
        }

        .review-form button {
            padding: 8px 16px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: var(--transition);
        }

        .review-form button:hover {
            background-color: #564fd8;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .delivery-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .action-form button {
                margin-bottom: 10px;
            }
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
    </style>
</head>
<body>
    <h2>Delivered Projects</h2>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="delivery-card">
            <h3 class="delivery-title"><?= htmlspecialchars($row['title']) ?></h3>
            
            <div class="delivery-meta">
                <p><strong>Freelancer:</strong> <?= htmlspecialchars($row['freelancer_name']) ?></p>
                <p><strong>Delivered At:</strong> <?= date('M d, Y h:i A', strtotime($row['delivered_at'])) ?></p>
            </div>
            
            <div class="delivery-message">
                <p><strong>Message:</strong></p>
                <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
            </div>
            
            <a href="<?= $row['file_path'] ?>" download class="download-link">📥 Download File</a>
            
            <div class="status status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></div>

            <?php if ($row['status'] == 'delivered'): ?>
                <form method="post" action="update_delivery_status.php" class="action-form">
                    <input type="hidden" name="delivery_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="status" value="approved" class="approve-btn">✅ Approve</button>
                </form>
            <?php endif; ?>

            <?php
            $reviewCheck = $conn->prepare("SELECT * FROM reviews WHERE delivery_id = ?");
            $reviewCheck->bind_param("i", $row['id']);
            $reviewCheck->execute();
            $reviewResult = $reviewCheck->get_result();

            if ($reviewResult->num_rows == 0 && $row['status'] == 'approved'): ?>
                <form method="post" action="submit_review.php" class="review-form">
                    <input type="hidden" name="delivery_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="freelancer_id" value="<?= $row['freelancer_id'] ?>">
                    <label>⭐ Rating (1–5):</label>
                    <input type="number" name="rating" min="1" max="5" required>
                    <label>💬 Review:</label>
                    <textarea name="review" required></textarea>
                    <button type="submit">Submit Review</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
	<a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</body>
</html>