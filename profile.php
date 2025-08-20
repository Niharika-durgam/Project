<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$user = [];

try {
    $sql = "SELECT name, email, bio, skills, profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc() ?? [];
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

$userType = $_SESSION['user']['user_type'] ?? 'guest'; // Access user_type from session 'user' array

// Set dashboard path based on user type
switch ($userType) {
    case 'client':
        $dashboardPath = '/FreelancerPlatform/client/dashboard.php';
        break;
    case 'freelancer':
        $dashboardPath = '/FreelancerPlatform/freelancer/dashboard.php';
        break;
    case 'admin':
        $dashboardPath = '/FreelancerPlatform/admin/dashboard.php';
        break;
    default:
        $dashboardPath = '/FreelancerPlatform/index.php'; // fallback
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Wonder Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: #f5f7ff;
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
        }

        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        h2 {
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .profile-image:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .profile-info {
            width: 100%;
        }

        .info-item {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }

        .info-item strong {
            display: block;
            margin-bottom: 8px;
            color: var(--primary);
            font-size: 16px;
        }

        .info-item p {
            color: var(--dark);
            line-height: 1.7;
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .skill-tag {
            background-color: var(--light-gray);
            color: var(--dark);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }

        .edit-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            margin-top: 20px;
        }

        .edit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .edit-btn i {
            margin-right: 8px;
        }

        .error-message {
            color: var(--secondary);
            text-align: center;
            padding: 20px;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 20px;
            }
            
            .profile-image {
                width: 120px;
                height: 120px;
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
    <div class="profile-container">
        <h2>My Profile</h2>

        <?php if (empty($user)): ?>
            <p class="error-message">Error loading profile.</p>
        <?php else: ?>
            <div class="profile-header">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="uploads/profile/<?= htmlspecialchars($user['profile_image']) ?>" class="profile-image" alt="Profile Picture">
                <?php else: ?>
                    <img src="assets/default-avatar.png" class="profile-image" alt="Default Profile">
                <?php endif; ?>
            </div>

            <div class="profile-info">
                <div class="info-item">
                    <strong>Name</strong>
                    <p><?= htmlspecialchars($user['name']) ?></p>
                </div>

                <div class="info-item">
                    <strong>Email</strong>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>

                <div class="info-item">
                    <strong>Bio</strong>
                    <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                </div>

                <div class="info-item">
                    <strong>Skills</strong>
                    <div class="skills-list">
                        <?php 
                        $skills = explode(',', $user['skills']);
                        foreach ($skills as $skill): 
                            if (!empty(trim($skill))):
                        ?>
                            <span class="skill-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>

                <a href="edit_profile.php" class="edit-btn">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        <?php endif; ?>
				<a href="<?= $dashboardPath ?>" class="back-link">← Back to Dashboard</a>

    </div>
</body>
</html>