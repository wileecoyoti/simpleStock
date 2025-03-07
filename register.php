<?php
session_start();
require 'config.php';

// Check if an admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $message = "User registered successfully.";
        } else {
            $message = "Error: Username may already exist.";
        }
    } else {
        $message = "Please enter a valid username and password.";
    }
}

require 'menu.php';
?>

<div class="main-content">
    <h1>Register New User</h1>
    <?php if (isset($message)) echo "<p>$message</p>"; ?>
    
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>
