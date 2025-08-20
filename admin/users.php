<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get user counts by type
$freelancerCount = 0;
$clientCount = 0;
$totalUsers = 0;

$result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE user_type = 'freelancer'");
if ($result) {
    $row = $result->fetch_assoc();
    $freelancerCount = $row['count'];
}

$result1 = $conn->query("SELECT COUNT(*) AS count FROM users WHERE user_type = 'client'");
if ($result1) {
    $row = $result1->fetch_assoc();
    $clientCount = $row['count'];
}

$totalUsers = $freelancerCount + $clientCount;

// Get job count
$jobCount = 0;
$result2 = $conn->query("SELECT COUNT(*) AS count FROM jobs");
if ($result2) {
    $row = $result2->fetch_assoc();
    $jobCount = $row['count'];
}

// Get application count
$appCount = 0;
$result3 = $conn->query("SELECT COUNT(*) AS count FROM applications");
if ($result3) {
    $row = $result3->fetch_assoc();
    $appCount = $row['count'];
}

// Get user counts by type
$freelancerCount = 0;
$clientCount = 0;
$totalUsers = 0;

$result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE user_type = 'freelancer'");
if ($result) {
    $row = $result->fetch_assoc();
    $freelancerCount = $row['count'];
}

$result1 = $conn->query("SELECT COUNT(*) AS count FROM users WHERE user_type = 'client'");
if ($result1) {
    $row = $result1->fetch_assoc();
    $clientCount = $row['count'];
}

$totalUsers = $freelancerCount + $clientCount;

// Get all freelancers
$freelancers = $conn->query("SELECT * FROM users WHERE user_type = 'freelancer' ORDER BY created_at DESC");

// Get all clients
$clients = $conn->query("SELECT * FROM users WHERE user_type = 'client' ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Users | Wonder Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6c63ff;
            --primary-dark: #564fd8;
            --primary-light: rgba(108, 99, 255, 0.1);
            --secondary: #ff6584;
            --dark: #2d3748;
            --darker: #1a202c;
            --gray: #718096;
            --light-gray: #f7fafc;
            --lighter-gray: #edf2f7;
            --white: #ffffff;
            --success: #48bb78;
            --warning: #ed8936;
            --info: #4299e1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background: var(--darker);
            color: var(--white);
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            background: var(--primary-dark);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--light-gray);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover, 
        .sidebar-menu a.active {
            background: var(--primary);
            color: var(--white);
        }

        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .user-menu {
            display: flex;
            align-items: center;
        }

        .user-menu img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        /* User Type Cards */
        .user-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .user-card {
            background: var(--white);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            border-top: 4px solid var(--info);
        }

        .user-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .user-card.clients {
            border-top-color: var(--success);
        }

        .user-card .card-bg {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 120px;
            opacity: 0.05;
            transform: rotate(15deg);
            transition: all 0.3s ease;
        }

        .user-card:hover .card-bg {
            opacity: 0.1;
            transform: rotate(5deg) scale(1.1);
        }

        .user-card .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .user-card .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: white;
        }

        .user-card.freelancers .card-icon {
            background: var(--info);
            box-shadow: 0 10px 20px rgba(66, 153, 225, 0.3);
        }

        .user-card.clients .card-icon {
            background: var(--success);
            box-shadow: 0 10px 20px rgba(72, 187, 120, 0.3);
        }

        .user-card .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--gray);
        }

        .user-card .card-value {
            font-size: 36px;
            font-weight: 700;
            margin: 15px 0;
            transition: all 0.3s ease;
        }

        .user-card.freelancers .card-value {
            color: var(--info);
        }

        .user-card.clients .card-value {
            color: var(--success);
        }

        .user-card:hover .card-value {
            transform: scale(1.05);
        }

        .user-card .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed var(--lighter-gray);
        }

        .user-card .percentage {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray);
        }

        .user-card .view-btn {
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .user-card.freelancers .view-btn {
            background: rgba(66, 153, 225, 0.1);
            color: var(--info);
        }

        .user-card.clients .view-btn {
            background: rgba(72, 187, 120, 0.1);
            color: var(--success);
        }

        .user-card .view-btn i {
            margin-left: 5px;
            transition: all 0.3s ease;
        }

        .user-card:hover .view-btn {
            transform: translateX(5px);
        }

        .user-card:hover .view-btn i {
            transform: translateX(3px);
        }

        /* Wave Animation */
        .wave-container {
            position: relative;
            height: 100px;
            overflow: hidden;
            margin-top: 20px;
            border-radius: 10px;
        }

        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200%;
            height: 100%;
            background-repeat: repeat no-repeat;
            background-position: 0 bottom;
            transform-origin: center bottom;
            animation: wave 8s linear infinite;
        }

        .wave.freelancer-wave {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%234299e1' fill-opacity='0.2' d='M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
        }

        .wave.client-wave {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%2348bb78' fill-opacity='0.2' d='M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
        }

        .wave2 {
            opacity: 0.5;
            animation: wave 12s linear infinite reverse;
        }

        @keyframes wave {
            0% {
                transform: translateX(0) translateZ(0) scaleY(1);
            }
            50% {
                transform: translateX(-25%) translateZ(0) scaleY(0.8);
            }
            100% {
                transform: translateX(-50%) translateZ(0) scaleY(1);
            }
        }

        /* Animated Progress */
        .progress-container {
            margin-top: 20px;
            height: 8px;
            background: var(--lighter-gray);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            width: 0;
            transition: width 1.5s ease-out;
        }

        .freelancers .progress-bar {
            background: var(--info);
        }

        .clients .progress-bar {
            background: var(--success);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .user-cards {
                grid-template-columns: 1fr;
            }
            
            .user-card {
                padding: 20px;
            }
            
            .user-card .card-value {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-user-cog"></i> <span>Admin Panel</span></h3>
            </div>
            
            <div class="sidebar-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="users.php" class="active"><i class="fas fa-users"></i> <span>All Users</span></a></li>
                    <li><a href="jobs.php"><i class="fas fa-briefcase"></i> <span>All Jobs</span></a></li>
                    <li><a href="applications.php"><i class="fas fa-file-alt"></i> <span>Applications</span></a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>User Statistics</h1>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['name'] ?? 'Admin') ?>&background=6c63ff&color=fff" alt="User">
                    <span><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></span>
                </div>
            </div>
            
            <div class="user-cards">
                <div class="user-card freelancers">
                    <i class="fas fa-laptop-code card-bg"></i>
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div class="card-title">Freelancers</div>
                    </div>
                    <div class="card-value"><?= $freelancerCount ?></div>
                    
                    <div class="wave-container">
                        <div class="wave freelancer-wave"></div>
                        <div class="wave freelancer-wave wave2"></div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= ($totalUsers > 0) ? ($freelancerCount/$totalUsers)*100 : 0 ?>%"></div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="percentage"><?= ($totalUsers > 0) ? round(($freelancerCount/$totalUsers)*100) : 0 ?>% of total users</div>
                        <a href="freelancer_viewall.php" class="view-btn">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <div class="user-card clients">
                    <i class="fas fa-briefcase card-bg"></i>
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="card-title">Clients</div>
                    </div>
                    <div class="card-value"><?= $clientCount ?></div>
                    
                    <div class="wave-container">
                        <div class="wave client-wave"></div>
                        <div class="wave client-wave wave2"></div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= ($totalUsers > 0) ? ($clientCount/$totalUsers)*100 : 0 ?>%"></div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="percentage"><?= ($totalUsers > 0) ? round(($clientCount/$totalUsers)*100) : 0 ?>% of total users</div>
                        <a href="client_viewall.php" class="view-btn">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
				</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bars on page load
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const width = bar.style.width.match(/\d+/);
                if (width) {
                    bar.style.width = width[0] + '%';
                }
            });
        });
    </script>
</body>
</html>