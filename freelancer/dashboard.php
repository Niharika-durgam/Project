<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "freelancer_db");

$sql = "SELECT COUNT(*) AS total_applications FROM jobs";
$result = mysqli_query($conn, $sql);

$sql1 = "SELECT COUNT(*) AS active_applications FROM applications WHERE status = 'accepted'";
$result1 = mysqli_query($conn, $sql1);

$totalApplications = 0;
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalApplications = $row['total_applications'];
}

$activeApplications = 0;
if ($result1 && mysqli_num_rows($result1) > 0) {
    $row1 = mysqli_fetch_assoc($result1);
    $activeApplications = $row1['active_applications'];
}

$sql2 = "SELECT AVG(client_rating) AS avg_rating FROM apllications WHERE client_rating IS NOT NULL";
$result2 = mysqli_query($conn, $sql2);

$avgRating = 0;
if ($result2) {
    $row = mysqli_fetch_assoc($result2);
    $avgRating = $row['avg_rating'];
}

$freelancer_name = $_SESSION['user']['name'];

$userId = $_SESSION['user']['id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancer Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --secondary: #f72585;
            --success: #4cc9f0;
            --warning: #f8961e;
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
            overflow-x: hidden;
        }

        /* Top Navigation Bar */
        .top-nav {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 20px 0;
        }

        .logo i {
            color: var(--success);
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-item {
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            color: rgba(255, 255, 255, 0.8);
        }

        .nav-item:hover, .nav-item.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
        }

        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25%;
            width: 50%;
            height: 3px;
            background: var(--success);
            border-radius: 3px 3px 0 0;
        }

        .nav-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px 0;
        }

        .user-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            cursor: pointer;
        }

        .user-img:hover {
            transform: scale(1.1);
            border-color: white;
        }

        /* Main Content */
        .main-content {
            padding: 30px;
            position: relative;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            margin-bottom: 30px;
        }

        .welcome {
            display: flex;
            flex-direction: column;
        }

        .welcome h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
            animation: fadeIn 0.8s ease;
        }

        .welcome p {
            color: var(--gray);
            font-size: 14px;
        }

        /* Hero Slider */
        .hero-slider {
            height: 500px;
            width: 100%;
            border-radius: var(--border-radius);
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            position: relative;
        }

        .swiper {
            width: 100%;
            height: 100%;
        }

        .swiper-slide {
            position: relative;
        }

        .slide-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slide-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
        }

        .slide-content h3 {
            font-size: 24px;
            margin-bottom: 10px;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.5s 0.3s forwards;
        }

        .slide-content p {
            font-size: 14px;
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.5s 0.5s forwards;
        }

        .swiper-pagination-bullet {
            background: white;
            opacity: 0.5;
            width: 10px;
            height: 10px;
            transition: var(--transition);
        }

        .swiper-pagination-bullet-active {
            background: var(--primary);
            opacity: 1;
            width: 30px;
            border-radius: 10px;
        }

        /* Quick Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary);
        }

        .stat-card h3 {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .stat-card p {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-card i {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 40px;
            color: rgba(67, 97, 238, 0.1);
        }

        /* Main Actions */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .action-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 30px;
            color: white;
            transition: var(--transition);
        }

        .action-card:hover .action-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .action-card:nth-child(1) .action-icon {
            background: linear-gradient(135deg, var(--primary), var(--success));
        }

        .action-card:nth-child(2) .action-icon {
            background: linear-gradient(135deg, var(--secondary), var(--warning));
        }

        .action-card:nth-child(3) .action-icon {
            background: linear-gradient(135deg, var(--success), var(--primary));
        }

        .action-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .action-card p {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 25px;
            border-radius: 50px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { 
                transform: translateY(20px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .nav-container {
                flex-direction: column;
                align-items: stretch;
            }

            .user-profile {
                padding: 15px 0;
            }

            .nav-menu {
                overflow-x: auto;
                padding: 10px 0;
                -webkit-overflow-scrolling: touch;
            }

            .nav-item {
                padding: 15px 20px;
                white-space: nowrap;
            }

            .hero-slider {
                height: 350px;
            }
        }

        @media (max-width: 768px) {
            .hero-slider {
                height: 300px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 20px;
            }

            .hero-slider {
                height: 250px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .slide-content h3 {
                font-size: 20px;
            }
        }
		
		.profile-dropdown {
        position: relative;
        display: inline-block;
    }
    
    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background-color: white;
        min-width: 200px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-radius: 8px;
        z-index: 1;
        overflow: hidden;
        animation: fadeIn 0.3s ease;
    }
	
	.dropdown-content a {
        color: var(--dark);
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .dropdown-content a:hover {
        background-color: var(--light-gray);
        color: var(--primary);
    }
    
    .dropdown-content a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    .profile-dropdown:hover .dropdown-content {
        display: block;
    }
	
	.dropdown-divider {
        height: 1px;
        background-color: var(--light-gray);
        margin: 5px 0;
    }
	
	.user-img {
    width: 50px;    
    height: 50px;
    object-fit: cover;
    border-radius: 50%;     
    border: 2px solid #ccc;   
    }
	
	
    .logo {
      display: flex;
      align-items: center;
      font-size: 24px;
      font-weight: 700;
      color: var(--primary);
      transition: transform 0.3s ease;
    }

    .logo:hover {
      transform: scale(1.05);
    }

    .logo-icon {
      margin-right: 10px;
      font-size: 28px;
    }
	
	.logo-text {
		color: white;
	}

	
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="top-nav">
        <div class="nav-container">
            <div class="logo">
					<span class="logo-icon">✨</span>
					<span class="logo-text">Wonder Connect</span>
            </div>
            
            <!--<div class="nav-menu">
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-search"></i>
                    <span>Find Jobs</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-briefcase"></i>
                    <span>My Projects</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Earnings</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </div>
            </div>-->
            
    <div class="profile-dropdown">
			
	<?php
$profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'default.png';
?>
<img src="/FreelancerPlatform/uploads/profile/<?= htmlspecialchars($profileImage) ?>" alt="User" class="user-img">

    <div class="dropdown-content">
        <div style="padding: 12px 16px; font-weight: 600; color: var(--primary);">
           <a href="/FreelancerPlatform/profile.php">
		   <i class="fas fa-user-circle"></i>
            <?= htmlspecialchars($freelancer_name) ?>
		   </a>
        </div>
        <div class="dropdown-divider"></div>
        <a href="/FreelancerPlatform/edit_profile.php">
            <i class="fas fa-user-edit"></i> Edit Profile
        </a>
        <div class="dropdown-divider"></div>
        <a href="/FreelancerPlatform/logout.php" style="color: var(--secondary);">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="welcome">
                <h1>Welcome back, <?= htmlspecialchars($freelancer_name) ?>!</h1>
                <p>Here's what's happening with your freelance business today</p>
            </div>
        </div>

        <!-- Hero Slider -->
        <div class="hero-slider">
            <div class="swiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="../images/free1.jpg" class="slide-img" alt="Freelance Work">
                        <div class="slide-content">
                            <h3>Find Your Next Big Project</h3>
                            <p>Thousands of clients are looking for your skills right now</p>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="../images/free2.jpg" class="slide-img" alt="Work Together">
                        <div class="slide-content">
                            <h3>Collaborate Seamlessly</h3>
                            <p>Our platform makes client communication effortless</p>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="../images/free3.jpg" class="slide-img" alt="Analytics">
                        <div class="slide-content">
                            <h3>Track Your Success</h3>
                            <p>Powerful analytics to help grow your freelance business</p>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-briefcase"></i>
                <h3>All Projects</h3>
                <p><?php echo $totalApplications; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h3>Active Projects</h3>
                <p><?php echo $activeApplications; ?></p>
            </div>
            <!--<div class="stat-card">
                <i class="fas fa-star"></i>
                <h3>Client Rating</h3>
                <p><?php echo $avgRating; ?></p>
            </div>-->
            <!--<div class="stat-card">
                <i class="fas fa-wallet"></i>
                <h3>Earnings</h3>
                <p>$8,450</p>
            </div>-->
        </div>

        <!-- Main Actions -->
        <div class="actions-grid">
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Find Jobs</h3>
                <p>Browse thousands of projects matching your skills and expertise</p>
                <a href="jobs.php" class="btn">Explore Jobs</a>
            </div>
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>My Projects</h3>
                <p>Manage your current projects and deliver outstanding work</p>
                <a href="my_applications.php" class="btn">View Projects</a>
            </div>
            <div class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h3>My Profile</h3>
                <p>Update your profile to attract more clients and better projects</p>
                <a href="../edit_profile.php" class="btn">Edit Profile</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        // Initialize Swiper
        const swiper = new Swiper('.swiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
        });

        // Add animation to action cards on hover
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.querySelector('.action-icon').style.animation = 'pulse 0.6s ease';
            });
            
            card.addEventListener('mouseleave', () => {
                card.querySelector('.action-icon').style.animation = '';
            });
        });
    </script>
</body>
</html>