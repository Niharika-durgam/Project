<?php
session_start();
include '../includes/db.php';

function generateToken() {
    return bin2hex(random_bytes(16));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $verification_token = generateToken();

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, user_type, is_verified) VALUES (?, ?, ?, 'admin', 0)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        // Simulate email by displaying the verification link
        $verification_link = "http://localhost/FreelancerPlatform/admin/verify.php?id=$user_id";
        $success_message = "<div class='verification-message'>
            <h3>Registration successful!</h3>
            <p>To activate your admin account, click the verification link:</p>
            <div class='verification-link'>
                <a href='$verification_link'>$verification_link</a>
            </div>
            <p>This link will expire in 24 hours.</p>
        </div>";
    } else {
        $error = "Email already exists.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration | Wonder Connect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6c5ce7;
            --primary-dark: #5649c0;
            --secondary: #00cec9;
            --dark: #2d3436;
            --light: #f5f6fa;
            --gray: #636e72;
            --light-gray: #dfe6e9;
            --white: #ffffff;
            --danger: #d63031;
            --success: #00b894;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, var(--light) 0%, #f0f2f5 100%);
        }
        
        .auth-container {
            width: 100%;
            max-width: 500px;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .auth-container:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .auth-header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .auth-header::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -30px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .auth-header h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .auth-header p {
            opacity: 0.9;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }
        
        .auth-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--light);
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
            background-color: var(--white);
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(108, 92, 231, 0.4);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
        }
        
        .auth-footer a {
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .auth-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .error-message {
            color: var(--danger);
            background: rgba(214, 48, 49, 0.1);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-right: 10px;
        }
        
        .verification-message {
            background: rgba(0, 184, 148, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(0, 184, 148, 0.3);
        }
        
        .verification-message h3 {
            color: var(--success);
            margin-bottom: 15px;
        }
        
        .verification-link {
            background: var(--white);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            word-break: break-all;
            border: 1px dashed var(--success);
        }
        
        .verification-link a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .verification-link a:hover {
            text-decoration: underline;
        }
        
        /* Password strength indicator */
        .password-strength {
            height: 5px;
            background: var(--light-gray);
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0;
            background: var(--danger);
            transition: var(--transition);
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-header {
                padding: 20px;
            }
            
            .auth-body {
                padding: 20px;
            }
            
            .auth-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="fas fa-user-shield"></i> Admin Registration</h2>
            <p>Create your admin account to manage the platform</p>
        </div>
        
        <div class="auth-body">
            <?php if (isset($success_message)): ?>
                <?= $success_message ?>
                <div class="auth-footer">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            <?php else: ?>
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" id="registerForm">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" required placeholder="Enter your full name">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" required placeholder="Enter your email address">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" required placeholder="Create a password">
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-user-plus"></i> Register Admin Account
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="index.php"><i class="fas fa-sign-in-alt"></i> Login here</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Check password length
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Check for mixed case
            if (password.match(/[a-z]/) strength += 0.5;
            if (password.match(/[A-Z]/)) strength += 0.5;
            
            // Check for numbers
            if (password.match(/\d/)) strength += 1;
            
            // Check for special chars
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            // Update strength bar
            const width = (strength / 4) * 100;
            strengthBar.style.width = width + '%';
            
            // Change color based on strength
            if (strength < 2) {
                strengthBar.style.backgroundColor = 'var(--danger)';
            } else if (strength < 3) {
                strengthBar.style.backgroundColor = '#f39c12';
            } else {
                strengthBar.style.backgroundColor = 'var(--success)';
            }
        });
        
        // Form submission animation
        const form = document.getElementById('registerForm');
        if (form) {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                btn.disabled = true;
            });
        }
    </script>
</body>
</html>