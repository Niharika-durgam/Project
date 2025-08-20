<?php
session_start();
include '../includes/db.php';

if ($_SESSION['user']['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT applications.*, jobs.title, users.name AS freelancer_name
        FROM applications
        JOIN jobs ON applications.job_id = jobs.id
        JOIN users ON applications.freelancer_id = users.id
        ORDER BY applications.applied_at DESC";

$applications = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Applications | Wonder Connect</title>
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
            --danger: #f56565;
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

        /* Table styles */
        .table-container {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--lighter-gray);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--primary);
            color: var(--white);
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--lighter-gray);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: var(--primary-light);
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background: rgba(237, 137, 54, 0.2);
            color: var(--warning);
        }

        .status-accepted {
            background: rgba(72, 187, 120, 0.2);
            color: var(--success);
        }

        .status-rejected {
            background: rgba(245, 101, 101, 0.2);
            color: var(--danger);
        }

        .status-completed {
            background: rgba(66, 153, 225, 0.2);
            color: var(--info);
        }

        .rating {
            display: inline-flex;
            align-items: center;
        }

        .rating-stars {
            color: #fbbf24;
            margin-right: 5px;
        }

        .rating-text {
            color: var(--gray);
            font-size: 14px;
        }

        .bid-amount {
            font-weight: 600;
            color: var(--dark);
        }

        /* Button styles */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.4);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
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
                    <li><a href="users.php"><i class="fas fa-users"></i> <span>All Users</span></a></li>
                    <li><a href="jobs.php"><i class="fas fa-briefcase"></i> <span>All Jobs</span></a></li>
                    <li><a href="applications.php" class="active"><i class="fas fa-file-alt"></i> <span>Applications</span></a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Applications</h1>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['name'] ?? 'Admin') ?>&background=6c63ff&color=fff" alt="User">
                    <span><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></span>
                </div>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h2>Job Applications</h2>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Application ID</th>
                            <th>Job Title</th>
                            <th>Freelancer</th>
                            <th>Bid Amount</th>
                            <th>Status</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $applications->fetch_assoc()): 
                            // Determine status class
                            $statusClass = 'status-' . strtolower($app['status']);
                        ?>
                        <tr>
                            <td><?= $app['id'] ?></td>
                            <td><?= htmlspecialchars($app['title']) ?></td>
                            <td><?= htmlspecialchars($app['freelancer_name']) ?></td>
                            <td class="bid-amount">₹<?= number_format($app['bid_amount']) ?></td>
                            <td>
                                <span class="status <?= $statusClass ?>"><?= ucfirst($app['status']) ?></span>
                            </td>
                            <td class="rating">
                                <?php if (isset($app['client_rating']) && $app['client_rating'] > 0): ?>
                                    <div class="rating-stars">
                                        <?php 
                                        $rating = $app['client_rating'];
                                        $fullStars = floor($rating);
                                        $hasHalfStar = $rating - $fullStars >= 0.5;
                                        
                                        for ($i = 1; $i <= 5; $i++): 
                                            if ($i <= $fullStars): ?>
                                                <i class="fas fa-star"></i>
                                            <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif;
                                        endfor; 
                                        ?>
                                    </div>
                                    <span class="rating-text"><?= number_format($app['client_rating'], 1) ?></span>
                                <?php else: ?>
                                    <span class="rating-text">Not Rated</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>