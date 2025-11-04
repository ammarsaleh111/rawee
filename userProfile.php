<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=notloggedin");
    exit();
}

// ======================= LANGUAGE LOGIC (MUST BE AT THE TOP) =======================
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';
$lang_file = "translations/{$lang}.json";
$text = file_exists($lang_file) ? json_decode(file_get_contents($lang_file), true) : [];

// The page title uses the specific 'profile_pageTitle' key.
$pageTitle = $text['profile_pageTitle'] ?? 'My Profile - RAWEE Smart Farming';
// ========================================================================================

// Include database connection
include __DIR__ . '/php/db_connect.php';

$mysqli = $mysqli ?? null; // ensure $mysqli is defined
$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    if (!empty($fullName) && !empty($email)) {
        $stmt = $mysqli->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $fullName, $email, $userId);
        if ($stmt->execute()) {
            $successMsg = $text['profile_successUpdate'] ?? "Profile updated successfully!";
            $_SESSION['full_name'] = $fullName;
        } else {
            $errorMsg = $text['profile_errorUpdateFailed'] ?? "Failed to update profile. Try again.";
        }
        $stmt->close();
    } else {
        $errorMsg = $text['profile_errorEmptyFields'] ?? "Full name and email cannot be empty.";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $errorMsg = $text['profile_errorPasswordMismatch'] ?? "New password and confirm password do not match.";
    } else {
        // Fetch current password hash
        $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($currentPassword, $user['password_hash'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            if ($stmt->execute()) {
                $successMsg = $text['profile_successPasswordUpdate'] ?? "Password updated successfully!";
            } else {
                $errorMsg = $text['profile_errorPasswordUpdateFailed'] ?? "Failed to update password. Try again.";
            }
            $stmt->close();
        } else {
            $errorMsg = $text['profile_errorIncorrectPassword'] ?? "Current password is incorrect.";
        }
    }
}

// Fetch current user info
$stmt = $mysqli->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/user.css" />
    <?php if ($lang == 'ar'): ?>
      <link rel="stylesheet" href="css/rtl.css" />
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="profile-page-main">
  <div class="container">
    <div class="profile-header">
      <h1><?php echo $text['profile_mainTitle'] ?? 'My Profile'; ?></h1>
      <p><?php echo $text['profile_mainSubtitle'] ?? 'Manage your personal information and account security.'; ?></p>

      <?php if ($successMsg ) echo "<div class='success-msg'>$successMsg</div>"; ?>
      <?php if ($errorMsg) echo "<div class='error-msg'>$errorMsg</div>"; ?>
    </div>

    <div class="profile-grid">
      <!-- Profile Update Form -->
      <div class="profile-card">
        <h3><i class="fas fa-user-edit"></i> <?php echo $text['profile_form1_title'] ?? 'Profile Information'; ?></h3>
        <form method="POST">
          <div class="input-group">
            <label for="full_name"><?php echo $text['profile_form1_nameLabel'] ?? 'Full Name'; ?></label>
            <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required />
          </div>
          <div class="input-group">
            <label for="email"><?php echo $text['profile_form1_emailLabel'] ?? 'Email Address'; ?></label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />
          </div>
          <button type="submit" name="update_profile" class="btn-profile-save">
            <i class="fas fa-save"></i> <?php echo $text['profile_form1_saveButton'] ?? 'Save Changes'; ?>
          </button>
        </form>
      </div>

      <!-- Change Password Form -->
      <div class="profile-card">
        <h3><i class="fas fa-key"></i> <?php echo $text['profile_form2_title'] ?? 'Change Password'; ?></h3>
        <form method="POST">
          <div class="input-group">
            <label for="current_password"><?php echo $text['profile_form2_currentPassLabel'] ?? 'Current Password'; ?></label>
            <input type="password" name="current_password" id="current_password" required />
          </div>
          <div class="input-group">
            <label for="new_password"><?php echo $text['profile_form2_newPassLabel'] ?? 'New Password'; ?></label>
            <input type="password" name="new_password" id="new_password" required />
          </div>
          <div class="input-group">
            <label for="confirm_password"><?php echo $text['profile_form2_confirmPassLabel'] ?? 'Confirm New Password'; ?></label>
            <input type="password" name="confirm_password" id="confirm_password" required />
          </div>
          <button type="submit" name="change_password" class="btn-profile-save">
            <i class="fas fa-shield-alt"></i> <?php echo $text['profile_form2_updateButton'] ?? 'Update Password'; ?>
          </button>
        </form>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
