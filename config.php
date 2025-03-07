<?php
function check_login() {
    if (!isset($_SESSION['user_id'])) {
         if (headers_sent()) {
            die("Cannot redirect, headers already sent.");
        }
        header("Location: login.php");
        echo "not logged in";
        exit();
    }
}

$host = '127.0.0.1';
$dbname = 'simplestock';
$username = 'sqlUsername';
$password = 'sqlPassword';

$conn = new mysqli($host, $username, $password, $dbname);
//echo "Connected successfully"; // Add this line to check if the connection is successful

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}?>
