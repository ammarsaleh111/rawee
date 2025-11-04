<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($full_name) || empty($email) || empty($password)) {
        header("location: /rawee/rawee/index.php?error=empty_fields#authModal");
        exit();
    }

    // Check if email already exists
    $check_sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($check_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("location: /rawee/rawee/index.php?error=email_taken#authModal");
        exit();
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $role_id = 2; // Default "Customer"
    $sql = "INSERT INTO users (full_name, email, password_hash, role_id) VALUES (?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssi", $full_name, $email, $password_hash, $role_id);

    if ($stmt->execute()) {
        // Auto-login after signup
        session_start();
        $_SESSION["loggedin"] = true;
        $_SESSION["user_id"] = $stmt->insert_id;
        $_SESSION["full_name"] = $full_name;
        $_SESSION["role_id"] = $role_id;

        header("location: /rawee/rawee/index.php#authModal"); // Close modal after signup
        exit();
    } else {
        header("location: /rawee/rawee/index.php?error=signup_failed#authModal");
        exit();
    }

    $stmt->close();
    $mysqli->close();
}
?>
