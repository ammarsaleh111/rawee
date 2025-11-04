<?php
session_start();

// Include the database connection
include __DIR__ . '/db_connect.php'; // This defines $mysqli

// Initialize error message
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Check if admin login
        if ($email === 'admin@rawee.com' && $password === 'admin@RAWEE') {
            $_SESSION['user_email'] = $email;
            $_SESSION['full_name'] = 'Admin User';
            $_SESSION['role'] = 'Admin';
            header("Location: ../admin.php");
            exit();
        }

        // Regular user login
        $stmt = $mysqli->prepare("SELECT user_id, full_name, password_hash, role_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role_id'] == 1 ? 'Admin' : 'Customer';
            header("Location: ../index.php");
            exit();
        } else {
            $errorMsg = "Invalid email or password.";
        }
    } else {
        $errorMsg = "Please enter both email and password.";
    }
}

// Redirect back to login page with error
if ($errorMsg) {
    $_SESSION['login_error'] = $errorMsg;
    header("Location: ../index.php");
    exit();
}
