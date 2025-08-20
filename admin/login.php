<?php
session_start();
include '../includes/db.php';

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
}
}
?>

<h2>Admin Login</h2>
<form method="post">
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<a href="register.php">New Admin? Register here</a>