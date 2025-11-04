<?php
require_once __DIR__ . '/db_connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../userProfile.php"); exit; }
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

$full_name = trim($_POST['full_name'] ?? $_POST['fullName'] ?? '');
if ($full_name === '') { header("Location: ../userProfile.php?error=empty"); exit; }

$stmt = $mysqli->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
$stmt->bind_param("si", $full_name, $_SESSION['user_id']);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    $_SESSION['full_name'] = $full_name;
    header("Location: ../userProfile.php?updated=1");
    exit;
} else {
    header("Location: ../userProfile.php?error=failed");
    exit;
}
?>
