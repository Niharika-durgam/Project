<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$message = '';
$user = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $profileImage = $_FILES['profile_image'] ?? null;

    $imagePath = null;
    if ($profileImage && $profileImage['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/profile/';
        $imageName = uniqid() . '_' . basename($profileImage['name']);
        $targetFile = $uploadDir . $imageName;

        if (move_uploaded_file($profileImage['tmp_name'], $targetFile)) {
            $imagePath = $imageName;

            // Update session image
            $_SESSION['user']['profile_image'] = $imagePath;
        }
    }

    $sql = "UPDATE users SET name = ?, bio = ?, skills = ?" . ($imagePath ? ", profile_image = ?" : "") . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($imagePath) {
        $stmt->bind_param("ssssi", $name, $bio, $skills, $imagePath, $userId);
    } else {
        $stmt->bind_param("sssi", $name, $bio, $skills, $userId);
    }

    if ($stmt->execute()) {
        $_SESSION['user']['name'] = $name;
        $message = "Profile updated successfully.";
    } else {
        $message = "Failed to update profile.";
    }
}

// Fetch latest data
$stmt = $conn->prepare("SELECT name, email, bio, skills, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// REMOVE THIS LINE:
// session_start();

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
    <title>Edit Profile | Freelancer Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .profile-container {
            max-width: 700px;
            margin: 50px auto;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 40px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h2 {
            color: var(--primary);
            font-size: 28px;
            margin-bottom: 10px;
        }

        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-light);
            transition: var(--transition);
        }

        .profile-image:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .image-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary);
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .image-upload:hover {
            background: var(--secondary);
            transform: scale(1.1);
        }

        .image-upload input {
            display: none;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }

        .btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 206, 201, 0.4);
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .message-success {
            background-color: rgba(0, 184, 148, 0.2);
            color: var(--success);
        }

        .message-error {
            background-color: rgba(214, 48, 49, 0.2);
            color: var(--danger);
        }

        .email-field {
            background-color: var(--light-gray);
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 25px;
                margin: 20px;
            }
            
            .profile-header h2 {
                font-size: 24px;
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
        <div class="profile-header">
            <h2>Edit Your Profile</h2>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message message-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="profile-image-container">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="uploads/profile/<?= htmlspecialchars($user['profile_image']) ?>" class="profile-image" alt="Profile">
                <?php else: ?>
                    <img src="assets/default-avatar.png" class="profile-image" alt="Default">
                <?php endif; ?>
                
                <label class="image-upload">
                    <i class="fas fa-camera"></i>
                    <input type="file" name="profile_image" accept="image/*">
                </label>
            </div>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" class="form-control email-field" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" class="form-control"><?= htmlspecialchars($user['bio']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="skills">Skills (comma separated)</label>
                <input type="text" id="skills" name="skills" class="form-control" value="<?= htmlspecialchars($user['skills']) ?>">
            </div>

            <button type="submit" class="btn">Save Changes</button>
        </form>
		<a href="<?= $dashboardPath ?>" class="back-link">← Back to Dashboard</a>
    </div>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	
</body>
</html>