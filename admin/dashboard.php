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

// Get recent activities
$activities = [];
$result4 = $conn->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 5");
if ($result4) {
    while ($row = $result4->fetch_assoc()) {
        $activities[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Wonder Connect</title>
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
            --purple: #9f7aea;
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

        .sidebar-menu a:hover, .sidebar-menu a.active {
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

        /* Enhanced Dashboard cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--white);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            border-top: 4px solid var(--primary);
            z-index: 1;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.1) 0%, rgba(108, 99, 255, 0) 100%);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .card.card-users {
            border-top-color: var(--info);
        }

        .card.card-jobs {
            border-top-color: var(--success);
        }

        .card.card-applications {
            border-top-color: var(--purple);
        }

        .card .card-bg-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 80px;
            opacity: 0.1;
            z-index: -1;
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .card.card-users .card-bg-icon {
            color: var(--info);
        }

        .card.card-jobs .card-bg-icon {
            color: var(--success);
        }

        .card.card-applications .card-bg-icon {
            color: var(--purple);
        }

        .card:hover .card-bg-icon {
            transform: scale(1.1) rotate(5deg);
            opacity: 0.15;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-header h3 {
            font-size: 18px;
            color: var(--gray);
            font-weight: 600;
        }

        .card-header i {
            font-size: 24px;
            color: var(--primary);
        }

        .card.card-users .card-header i {
            color: var(--info);
        }

        .card.card-jobs .card-header i {
            color: var(--success);
        }

        .card.card-applications .card-header i {
            color: var(--purple);
        }

        .card p {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 15px;
        }

        .card-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            position: relative;
            display: inline-block;
        }

        .card.card-users .card-value {
            color: var(--info);
        }

        .card.card-jobs .card-value {
            color: var(--success);
        }

        .card.card-applications .card-value {
            color: var(--purple);
        }

        .card-value::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 3px;
            background: currentColor;
            transition: width 0.5s ease;
        }

        .card:hover .card-value::after {
            width: 100%;
        }

        .card-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed var(--lighter-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-link {
            font-size: 14px;
            color: var(--gray);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .card.card-users .card-link {
            color: var(--info);
        }

        .card.card-jobs .card-link {
            color: var(--success);
        }

        .card.card-applications .card-link {
            color: var(--purple);
        }

        .card-link i {
            margin-left: 5px;
            transition: transform 0.3s ease;
        }

        .card:hover .card-link {
            transform: translateX(5px);
        }

        .card:hover .card-link i {
            transform: translateX(3px);
        }

        /* Animated counter */
        .counter {
            display: inline-block;
            transition: all 0.5s ease;
        }

        /* Pulse animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .card:hover {
            animation: pulse 2s infinite;
        }

        /* Floating particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(108, 99, 255, 0.3);
            border-radius: 50%;
            opacity: 0;
            animation: float 15s infinite linear;
        }

        .card.card-users .particle {
            background: rgba(66, 153, 225, 0.3);
        }

        .card.card-jobs .particle {
            background: rgba(72, 187, 120, 0.3);
        }

        .card.card-applications .particle {
            background: rgba(159, 122, 234, 0.3);
        }

        @keyframes float {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 0.5;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100px) translateX(50px);
                opacity: 0;
            }
        }

        /* Content sections */
        .content-section {
            display: none;
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .content-section.active {
            display: block;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--lighter-gray);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
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
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> <span>All Users</span></a></li>
                    <li><a href="jobs.php"><i class="fas fa-briefcase"></i> <span>All Jobs</span></a></li>
                    <li><a href="applications.php"><i class="fas fa-file-alt"></i> <span>Applications</span></a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['name'] ?? 'Admin') ?>&background=6c63ff&color=fff" alt="User">
                    <span><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></span>
                </div>
            </div>
			
			<h2>Welcome Admin!</h2><br>
			<p>Welcome to your admin dashboard. Here's a quick overview of your platform statistics.</p><br>
            
            <!-- Enhanced Dashboard Cards with Animations -->
            <div class="dashboard-cards">
                <div class="card card-users" onclick="window.location.href='users.php'">
                    <div class="particles" id="particles-users"></div>
                    <i class="fas fa-users card-bg-icon"></i>
                    <div class="card-header">
                        <h3>Users</h3>
                        <i class="fas fa-users"></i>
                    </div>
                    <p>Manage all registered users</p>
                    <div class="card-value counter" data-target="<?= $totalUsers ?>">0</div>
                    <div class="card-footer">
                        <span><?= $freelancerCount ?> freelancers, <?= $clientCount ?> clients</span>
                        <a href="users.php" class="card-link">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <div class="card card-jobs" onclick="window.location.href='jobs.php'">
                    <div class="particles" id="particles-jobs"></div>
                    <i class="fas fa-briefcase card-bg-icon"></i>
                    <div class="card-header">
                        <h3>Jobs</h3>
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <p>View and manage all jobs</p>
                    <div class="card-value counter" data-target="<?= $jobCount ?>">0</div>
                    <div class="card-footer">
                        <span><?= $appCount ?> applications</span>
                        <a href="jobs.php" class="card-link">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <div class="card card-applications" onclick="window.location.href='applications.php'">
                    <div class="particles" id="particles-applications"></div>
                    <i class="fas fa-file-alt card-bg-icon"></i>
                    <div class="card-header">
                        <h3>Applications</h3>
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <p>Manage job applications</p>
                    <div class="card-value counter" data-target="<?= $appCount ?>">0</div>
                    <div class="card-footer">
                        <span>Latest activities</span>
                        <a href="applications.php" class="card-link">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
           
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create floating particles for each card
            function createParticles(containerId, count) {
                const container = document.getElementById(containerId);
                if (!container) return;
                
                for (let i = 0; i < count; i++) {
                    const particle = document.createElement('div');
                    particle.classList.add('particle');
                    
                    // Random size between 3px and 8px
                    const size = Math.random() * 5 + 3;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    
                    // Random position
                    particle.style.left = `${Math.random() * 100}%`;
                    particle.style.top = `${Math.random() * 100}%`;
                    
                    // Random animation duration and delay
                    const duration = Math.random() * 10 + 10;
                    const delay = Math.random() * 5;
                    particle.style.animation = `float ${duration}s ${delay}s infinite linear`;
                    
                    container.appendChild(particle);
                }
            }
            
            // Create particles for each card
            createParticles('particles-users', 10);
            createParticles('particles-jobs', 10);
            createParticles('particles-applications', 10);
            
            // Animated counter for stats
            const counters = document.querySelectorAll('.counter');
            const speed = 200; // The lower the faster
            
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = target / speed;
                
                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(updateCounter, 1);
                } else {
                    counter.innerText = target;
                }
                
                function updateCounter() {
                    const current = +counter.innerText;
                    const increment = target / speed;
                    
                    if (current < target) {
                        counter.innerText = Math.ceil(current + increment);
                        setTimeout(updateCounter, 1);
                    } else {
                        counter.innerText = target;
                    }
                }
            });
            
            // Animation for cards on page load
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.animation = `fadeInUp 0.5s ease forwards ${index * 0.1}s`;
            });
            
            // Add keyframes dynamically
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeInUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>