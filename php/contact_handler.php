<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Collect and sanitize form data
    $full_name     = trim($_POST['full_name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $message       = trim($_POST['message'] ?? '');
    $farm_size     = trim($_POST['farm_size'] ?? '');
    $solution_type = trim($_POST['solution_type'] ?? '');

    // Validate required fields
    if (empty($full_name) || empty($phone) || empty($farm_size) || empty($solution_type) || empty($message)) {
        header("Location: ../product.php?error=1");
        exit;
    }

    // Prepare SQL statement
    $sql = "INSERT INTO contact_messages (full_name, email, phone, message, farm_size, solution_type) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        // Debugging: check SQL prepare error
        die("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("ssssss", $full_name, $email, $phone, $message, $farm_size, $solution_type);

    // Execute and check success
    if ($stmt->execute()) {
        header("Location: ../product.php?success=1");
    } else {
        // Debugging: check execute error
        header("Location: ../product.php?error=1");
    }

    $stmt->close();
    $mysqli->close();
} else {
    // If accessed directly without POST
    header("Location: ../product.php");
    exit;
}
?>
