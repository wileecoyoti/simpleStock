<?php
session_start();
require 'config.php';

// Check if a user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_user'])) {
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

// Handle user deletion (only if user_id = 1)
if (isset($_GET['delete_user']) && $_SESSION['user_id'] == 1) {
    $delete_id = intval($_GET['delete_user']);

    if ($delete_id !== 1) { // Prevent deleting admin user ID 1
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        header("Location: register.php");
        exit();
    } else {
        $message = "Admin user cannot be deleted.";
    }
}

// Fetch all users (if user_id = 1)
$users = [];
if ($_SESSION['user_id'] == 1) {
    $result = $conn->query("SELECT id, username FROM users ORDER BY id ASC");
    $users = $result->fetch_all(MYSQLI_ASSOC);
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

        <button type="submit" name="register_user">Register</button>
    </form>

    <?php if ($_SESSION['user_id'] == 1): ?>
    <div class="widget">
        <h3>Registered Users</h3>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <?php if ($user['id'] != 1): ?>
                                <a href="register.php?delete_user=<?php echo $user['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this user?');">
                                    Delete
                                </a>
                            <?php else: ?>
                                (Admin)
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>

