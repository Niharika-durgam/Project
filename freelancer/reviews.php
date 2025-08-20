<?php
session_start();
include '../includes/db.php';

if ($_SESSION['user']['user_type'] !== 'freelancer') {
    header("Location: ../index.php");
    exit();
}

$freelancer_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT r.*, j.title, u.name AS client_name 
    FROM reviews r 
    JOIN deliveries d ON r.delivery_id = d.id 
    JOIN jobs j ON d.job_id = j.id 
    JOIN users u ON r.client_id = u.id
    WHERE r.freelancer_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Client Feedback</h2>

<?php while ($row = $result->fetch_assoc()): ?>
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <h3><?= htmlspecialchars($row['title']) ?> - From <?= htmlspecialchars($row['client_name']) ?></h3>
        <p><strong>⭐ Rating:</strong> <?= $row['rating'] ?>/5</p>
        <p><strong>Review:</strong> <?= nl2br(htmlspecialchars($row['review'])) ?></p>
        <p><em>Posted on <?= $row['created_at'] ?></em></p>
    </div>
<?php endwhile; ?>