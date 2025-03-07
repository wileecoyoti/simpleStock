<?php
require 'config.php';
session_start();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && password_verify($password, $result['password'])) {
            $_SESSION['user_id'] = $result['id'];
            header("Location: index.php");
            exit();
        } else {
            $message = "Invalid username or password.";
        }
    } else {
        $message = "Please enter a username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Center the login form vertically and horizontally */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #2C3E50; /* Same dark background as your other pages */
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .login-container {
            background: #ECF0F1; /* Light background for contrast */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 300px;
        }

        .login-container h1 {
            color: #2C3E50; /* Matching title color */
            margin-bottom: 20px;
        }

        .login-container input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #BDC3C7;
            border-radius: 5px;
        }

        .login-container button {
            background-color: #3498DB; /* Same blue as buttons on other pages */
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .login-container button:hover {
            background-color: #2980B9; /* Darker blue on hover */
        }

        .error-message {
            color: #E74C3C; /* Red for errors */
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if (isset($message)): ?>
            <p class="error-message"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <form method="POST" style="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>

