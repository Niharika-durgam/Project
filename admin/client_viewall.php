<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get client count
$clientCount = 0;
$result = $conn->query("SELECT COUNT(*) AS count FROM users WHERE user_type = 'client'");
if ($result) {
    $row = $result->fetch_assoc();
    $clientCount = $row['count'];
}

// Get all clients
$clients = $conn->query("SELECT * FROM users WHERE user_type = 'client' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Clients | Wonder Connect</title>
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
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
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

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: var(--primary);
            color: var(--white);
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .back-btn i {
            margin-right: 8px;
        }

        .back-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        /* Table styles */
        .table-container {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-top: 20px;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table th {
            background: var(--primary);
            color: var(--white);
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        .user-table td {
            padding: 15px;
            border-bottom: 1px solid var(--lighter-gray);
            vertical-align: middle;
        }

        .user-table tr:last-child td {
            border-bottom: none;
        }

        .user-table tr:hover {
            background: var(--primary-light);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        .user-name {
            display: flex;
            align-items: center;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-client {
            background: rgba(108, 99, 255, 0.2);
            color: var(--primary);
        }

        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            padding: 5px;
            margin: 0 3px;
            font-size: 16px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            transition: all 0.3s;
        }

        .view-btn {
            color: var(--primary);
        }

        .edit-btn {
            color: var(--success);
        }

        .delete-btn {
            color: var(--danger);
        }

        .action-btn:hover {
            background: var(--lighter-gray);
            transform: scale(1.1);
        }

        .no-users {
            text-align: center;
            padding: 40px;
            color: var(--gray);
        }

        .no-users i {
            font-size: 50px;
            margin-bottom: 20px;
            color: var(--primary);
        }

        .no-users p {
            font-size: 18px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .user-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .back-btn {
                margin-top: 15px;
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
                    <li><a href="applications.php"><i class="fas fa-file-alt"></i> <span>Applications</span></a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Clients</h1>
                <div class="user-menu">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['name'] ?? 'Admin') ?>&background=6c63ff&color=fff" alt="User">
                    <span><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></span>
                </div>
            </div>
            
            <a href="users.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <div class="table-container">
                <div class="user-tabs-wrapper">
                    <div class="user-tabs">
                        <div class="user-tab active">Clients (<?= $clientCount ?>)</div>
                    </div>
                    
                    <!-- Clients Tab -->
                    <div id="clients-tab" class="user-tab-content active">
                        <?php if ($clients->num_rows > 0): ?>
                            <table class="user-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <!--<th>Company</th>-->
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($client = $clients->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="user-name">
                                                <img src="<?= !empty($client['profile_pic']) ? '../uploads/profile_pics/' . htmlspecialchars($client['profile_pic']) : 'https://ui-avatars.com/api/?name=' . urlencode($client['name']) . '&background=6c63ff&color=fff' ?>" 
                                                     alt="<?= htmlspecialchars($client['name']) ?>" class="user-avatar">
                                                <?= htmlspecialchars($client['name']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($client['email']) ?></td>
                                        <td><?= date('M d, Y', strtotime($client['created_at'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="no-users">
                                <i class="fas fa-briefcase"></i>
                                <p>No clients found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>