<?php
require_once __DIR__ . '/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../userProfile.php"); exit; }
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

$current = $_POST['currentPassword'] ?? '';
$new = $_POST['newPassword'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';

if ($new === '' || strlen($new) < 8) { header("Location: ../userProfile.php?error=weak"); exit; }
if ($new !== $confirm) { header("Location: ../userProfile.php?error=nomatch"); exit; }

// Fetch hash
$stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($current, $hash)) {
    header("Location: ../userProfile.php?error=wrongcurrent");
    exit;
}

$new_hash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
$stmt->bind_param("si", $new_hash, $_SESSION['user_id']);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    header("Location: ../userProfile.php?changed=1");
    exit;
} else {
    header("Location: ../userProfile.php?error=failed");
    exit;
}
?>
