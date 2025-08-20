<?php
session_start();
include '../includes/db.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (!$user['is_verified']) {
            $error = "Please verify your email before logging in.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No admin account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Wonder Connect</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #2575fc;
      --secondary-color: #6a11cb;
      --accent-color: #ff7e5f;
      --light-color: #f8f9fa;
      --dark-color: #333;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --transition: all 0.3s ease;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0;
      padding: 20px;
      color: var(--dark-color);
    }
    
    .login-container {
      display: flex;
      max-width: 1000px;
      width: 100%;
      background: white;
      border-radius: 16px;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      animation: fadeInUp 0.8s ease;
      position: relative;
    }
    
    .login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(to bottom, var(--secondary-color), var(--primary-color));
    }
    
    .login-image {
      flex: 1;
      background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 60px 40px;
      color: white;
      position: relative;
      overflow: hidden;
    }
    
    .login-image::before {
      content: '';
      position: absolute;
      top: 20px;
      right: 20px;
      width: 80px;
      height: 80px;
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M30,50 Q50,30 70,50 T90,50" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="2"/></svg>');
      opacity: 0.6;
    }
    
    .login-image h2 {
      font-size: 2.2rem;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
      text-align: center;
    }
    
    .login-image p {
      text-align: center;
      margin-bottom: 30px;
      opacity: 0.9;
      position: relative;
      z-index: 1;
      max-width: 80%;
    }
    
    .login-image img {
      max-width: 80%;
      height: auto;
      animation: float 6s ease-in-out infinite;
      position: relative;
      z-index: 1;
      filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
    }
    
    .login-form {
      flex: 1;
      padding: 60px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: #fff;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 40px;
    }
    
    .login-header h1 {
      color: var(--primary-color);
      font-size: 2.5rem;
      margin-bottom: 10px;
      position: relative;
      display: inline-block;
    }
    
    .login-header h1::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 4px;
      background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
      border-radius: 2px;
    }
    
    .login-header p {
      color: #64748b;
      font-size: 1.1rem;
    }
    
    .form-group {
      margin-bottom: 25px;
      position: relative;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #555;
    }
    
    .form-control {
      width: 100%;
      padding: 15px 20px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 16px;
      transition: var(--transition);
      background-color: #f9f9f9;
      font-family: 'Poppins', sans-serif;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(37, 117, 252, 0.2);
      outline: none;
      background-color: white;
    }
    
    .password-toggle {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #999;
      transition: var(--transition);
    }
    
    .password-toggle:hover {
      color: var(--primary-color);
    }
    
    .btn-login {
      background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
      color: white;
      border: none;
      padding: 15px;
      border-radius: 30px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
      margin-top: 10px;
      font-family: 'Poppins', sans-serif;
      width: 100%;
    }
    
    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(106, 17, 203, 0.4);
    }
    
    .btn-login:active {
      transform: translateY(0);
    }
    
    .remember-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }
    
    .remember-me {
      display: flex;
      align-items: center;
    }
    
    .remember-me input {
      margin-right: 8px;
      accent-color: var(--primary-color);
    }
    
    .forgot-password {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      transition: var(--transition);
    }
    
    .forgot-password:hover {
      text-decoration: underline;
    }
    
    .divider {
      display: flex;
      align-items: center;
      margin: 25px 0;
      color: #999;
    }
    
    .divider::before, .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e2e8f0;
    }
    
    .divider::before {
      margin-right: 15px;
    }
    
    .divider::after {
      margin-left: 15px;
    }
    
    .social-login {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-bottom: 25px;
    }
    
    .social-btn {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      font-size: 20px;
      transition: var(--transition);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .social-btn.google {
      background: #db4437;
    }
    
    .social-btn.facebook {
      background: #4267B2;
    }
    
    .social-btn.twitter {
      background: #1DA1F2;
    }
    
    .social-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }
    
    .register-link {
      text-align: center;
      margin-top: 20px;
      color: #666;
    }
    
    .register-link a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      transition: var(--transition);
    }
    
    .register-link a:hover {
      text-decoration: underline;
    }
    
    /* Error message styling */
    .error-message {
      color: var(--danger-color);
      font-size: 0.9rem;
      margin-top: 5px;
      text-align: center;
      padding: 10px;
      background-color: rgba(220, 53, 69, 0.1);
      border-radius: 5px;
      display: <?php echo $error ? 'block' : 'none'; ?>;
    }
    
    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes float {
      0% {
        transform: translateY(0px);
      }
      50% {
        transform: translateY(-15px);
      }
      100% {
        transform: translateY(0px);
      }
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
      }
      
      .login-image {
        padding: 40px 20px;
      }
      
      .login-image img {
        max-width: 60%;
      }
      
      .login-form {
        padding: 40px 30px;
      }
      
      .login-header h1 {
        font-size: 2rem;
      }
    }
    
    /* Form validation styles */
    .form-group.error .form-control {
      border-color: var(--danger-color);
    }
    
    .form-group.success .form-control {
      border-color: var(--success-color);
    }
    
    .form-group .validation-message {
      color: var(--danger-color);
      font-size: 0.85rem;
      margin-top: 5px;
      display: none;
    }
    
    .form-group.error .validation-message {
      display: block;
    }
    
    /* Pulse animation for admin icon */
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(37, 117, 252, 0.4); }
      70% { box-shadow: 0 0 0 10px rgba(37, 117, 252, 0); }
      100% { box-shadow: 0 0 0 0 rgba(37, 117, 252, 0); }
    }
    
    .admin-icon {
      animation: pulse 2.5s infinite;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-image">
      <h2>Welcome Back, Admin!</h2>
      <p>Access your dashboard to manage the platform and help our community thrive.</p>
      <img src="https://cdn-icons-png.flaticon.com/512/2997/2997259.png" alt="Admin Dashboard">
    </div>
    <div class="login-form">
      <div class="login-header">
        <h1>Admin Login</h1>
        <p>Please enter your credentials to access the dashboard</p>
      </div>
      
      <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>
      
      <form method="post" id="adminLoginForm">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="Enter your admin email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          <div class="validation-message">Please enter a valid email address</div>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
          <i class="fas fa-eye password-toggle" id="togglePassword"></i>
          <div class="validation-message">Password must be at least 6 characters</div>
        </div>
        
        <div class="remember-forgot">
          <div class="remember-me">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
          </div>
          <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
        </div>
        
        <button type="submit" class="btn-login">Login to Dashboard</button>
        
        <div class="divider">or</div>
        
        <div class="social-login">
          <a href="#" class="social-btn google" aria-label="Login with Google">
            <i class="fab fa-google"></i>
          </a>
          <a href="#" class="social-btn facebook" aria-label="Login with Facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="social-btn twitter" aria-label="Login with Twitter">
            <i class="fab fa-twitter"></i>
          </a>
        </div>
      </form>
      
      <div class="register-link">
        Not an admin? <a href="../startingpage.html">Return to main site</a> | 
        <a href="register.php">New Admin? Register here</a>
      </div>
    </div>
  </div>

  <script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('fa-eye-slash');
    });
    
    // Form validation
    const form = document.getElementById('adminLoginForm');
    
    form.addEventListener('submit', function(e) {
      let isValid = true;
      
      // Validate email
      const email = document.getElementById('email');
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      
      if (!emailRegex.test(email.value)) {
        email.parentElement.classList.add('error');
        isValid = false;
      } else {
        email.parentElement.classList.remove('error');
      }
      
      // Validate password
      const password = document.getElementById('password');
      
      if (password.value.length < 6) {
        password.parentElement.classList.add('error');
        isValid = false;
      } else {
        password.parentElement.classList.remove('error');
      }
      
      // If form is invalid, prevent submission
      if (!isValid) {
        e.preventDefault();
      }
    });
    
    // Input validation on blur
    document.getElementById('email').addEventListener('blur', function() {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.value)) {
        this.parentElement.classList.add('error');
      } else {
        this.parentElement.classList.remove('error');
      }
    });
    
    document.getElementById('password').addEventListener('blur', function() {
      if (this.value.length < 8) {
        this.parentElement.classList.add('error');
      } else {
        this.parentElement.classList.remove('error');
      }
    });
  </script>
</body>
</html>