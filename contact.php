<?php
// ======================= LANGUAGE LOGIC (MUST BE AT THE TOP) =======================
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';
$lang_file = "translations/{$lang}.json";
$text = file_exists($lang_file) ? json_decode(file_get_contents($lang_file), true) : [];

// The page title uses the specific 'contact_pageTitle' key.
$pageTitle = $text['contact_pageTitle'] ?? 'Contact Us - RAWEE Smart Farming Solutions';

// Shared form handler path (works whether site lives under /rawee or root)
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
  $basePath = '';
}
$sendMessageAction = $basePath . '/php/send_message.php';
// ========================================================================================
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    
    <link rel="stylesheet" href="css/style.css" />
    <?php if ($lang == 'ar'): ?>
      <link rel="stylesheet" href="css/rtl.css" />
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="contact-page-main">
      <div class="contact-hero">
        <div class="container">
          <h1><?php echo $text['contact_mainTitle'] ?? 'Get in Touch'; ?></h1>
          <p>
            <?php echo $text['contact_mainSubtitle'] ?? "We're here to help..."; ?>
          </p>
        </div>
      </div>

      <section class="contact-details-section">
        <div class="container">
          <div class="contact-grid">
            <div class="contact-info-wrapper">
              <h3><?php echo $text['contact_infoTitle'] ?? 'Contact Information'; ?></h3>
              <p><?php echo $text['contact_infoSubtitle'] ?? 'Reach out to us...'; ?></p>

              <div class="info-item">
                <i class="fas fa-phone-alt"></i>
                <div>
                  <h4><?php echo $text['contact_phoneLabel'] ?? 'Phone'; ?></h4>
                  <a href="tel:+1234567890">+1 (234 ) 567-890</a>
                </div>
              </div>

              <div class="info-item">
                <i class="fas fa-envelope"></i>
                <div>
                  <h4><?php echo $text['contact_emailLabel'] ?? 'Email'; ?></h4>
                  <a href="mailto:hello@rawee.tech">hello@rawee.tech</a>
                </div>
              </div>

              <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                  <h4><?php echo $text['contact_locationLabel'] ?? 'Office Location'; ?></h4>
                  <p><?php echo $text['contact_locationAddress'] ?? '123 Innovation Drive...'; ?></p>
                </div>
              </div>

              <div class="map-container">
                <iframe
                  src="https://www.openstreetmap.org/export/embed.html?bbox=-118.25%2C34.05%2C-118.24%2C34.06&layer=mapnik&marker=34.0522,-118.2437" 
                  width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade" title="RAWEE Office Location">
                </iframe>
              </div>
            </div>

            <div class="contact-form-wrapper">
              <h3><?php echo $text['contact_formTitle'] ?? 'Send Us a Message'; ?></h3>
              <?php if (isset($_GET['status'])): 
                $isSuccess = $_GET['status'] === 'success';
                $statusMessage = $isSuccess
                  ? ($text['contact_formSuccess'] ?? 'Your message was sent successfully.')
                  : ($text['contact_formError'] ?? 'Something went wrong. Please try again.');
              ?>
                <div class="form-status" style="padding:12px 16px;margin-bottom:1rem;border-radius:10px;background:<?php echo $isSuccess ? '#e6f4ea' : '#fdeaea'; ?>;color:<?php echo $isSuccess ? '#0f5132' : '#842029'; ?>;font-weight:500;">
                  <?php echo $statusMessage; ?>
                </div>
              <?php endif; ?>
              <form class="contact-form" action="<?php echo $sendMessageAction; ?>" method="POST">
                <div class="input-group">
                  <label for="contactName"><?php echo $text['contact_formNameLabel'] ?? 'Full Name'; ?></label>
                  <input type="text" id="contactName" name="name" placeholder="<?php echo $text['contact_formNamePlaceholder'] ?? 'e.g., Alex Johnson'; ?>" required />
                </div>
                <div class="input-group">
                  <label for="contactEmail"><?php echo $text['contact_formEmailLabel'] ?? 'Email Address'; ?></label>
                  <input type="email" id="contactEmail" name="email" placeholder="<?php echo $text['contact_formEmailPlaceholder'] ?? 'you@company.com'; ?>" required />
                </div>
                <div class="input-group">
                  <label for="contactSubject"><?php echo $text['contact_formSubjectLabel'] ?? 'Subject'; ?></label>
                  <input type="text" id="contactSubject" name="subject" placeholder="<?php echo $text['contact_formSubjectPlaceholder'] ?? 'What is your message about?'; ?>" required />
                </div>
                <div class="input-group">
                  <label for="contactMessage"><?php echo $text['contact_formMessageLabel'] ?? 'Your Message'; ?></label>
                  <textarea id="contactMessage" name="message" rows="6" placeholder="<?php echo $text['contact_formMessagePlaceholder'] ?? 'Please describe your inquiry...'; ?>" required></textarea>
                </div>
                <!-- Identify form + redirect back to this page with language -->
                <input type="hidden" name="form_source" value="contact" />
                <input type="hidden" name="redirect" value="contact.php?lang=<?php echo $lang; ?>" />
                <button type="submit" class="btn-submit-contact">
                  <i class="fas fa-paper-plane"></i> <?php echo $text['contact_formSendButton'] ?? 'Send Message'; ?>
                </button>
              </form>
            </div>

          </div>
        </div>
      </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- ======================= LOGIN/SIGN-UP MODAL ======================= -->
    <div class="auth-modal" id="authModalContact">
      <div class="auth-modal-overlay" onclick="closeAuthModal('authModalContact' )"></div>
      <div class="auth-modal-content">
        <button class="close-button" onclick="closeAuthModal('authModalContact')" aria-label="Close modal">&times;</button>

        <!-- Login Form -->
        <div class="auth-form" id="loginFormContact">
          <h3 class="form-title"><?php echo $text['contact_modalLoginTitle'] ?? 'Welcome Back'; ?></h3>
          <p class="form-subtitle"><?php echo $text['contact_modalLoginSubtitle'] ?? 'Login to access...'; ?></p>
          <form>
            <div class="input-group">
              <label for="loginEmailContact"><i class="fas fa-envelope"></i> <?php echo $text['contact_modalEmailLabel'] ?? 'Email Address'; ?></label>
              <input type="email" id="loginEmailContact" placeholder="you@example.com" required />
            </div>
            <div class="input-group">
              <label for="loginPasswordContact"><i class="fas fa-lock"></i> <?php echo $text['contact_modalPasswordLabel'] ?? 'Password'; ?></label>
              <input type="password" id="loginPasswordContact" placeholder="••••••••" required />
            </div>
            <div class="form-options">
              <a href="#" class="forgot-password"><?php echo $text['contact_modalForgotPassword'] ?? 'Forgot Password?'; ?></a>
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-sign-in-alt"></i> <?php echo $text['contact_modalLoginButton'] ?? 'Login'; ?></button>
          </form>
          <p class="switch-form-text">
            <?php echo $text['contact_modalSwitchToSignup'] ?? "Don't have an account?"; ?> <a href="#" onclick="switchToSignup('loginFormContact','signupFormContact',event)"><?php echo $text['contact_modalSignupLink'] ?? 'Sign Up'; ?></a>
          </p>
        </div>

        <!-- Sign-Up Form -->
        <div class="auth-form" id="signupFormContact" style="display: none">
          <h3 class="form-title"><?php echo $text['contact_modalSignupTitle'] ?? 'Create Your Account'; ?></h3>
          <p class="form-subtitle"><?php echo $text['contact_modalSignupSubtitle'] ?? 'Join RAWEE...'; ?></p>
          <form>
            <div class="input-group">
              <label for="signupNameContact"><i class="fas fa-user"></i> <?php echo $text['contact_modalNameLabel'] ?? 'Full Name'; ?></label>
              <input type="text" id="signupNameContact" placeholder="Alex Johnson" required />
            </div>
            <div class="input-group">
              <label for="signupEmailContact"><i class="fas fa-envelope"></i> <?php echo $text['contact_modalEmailLabel'] ?? 'Email Address'; ?></label>
              <input type="email" id="signupEmailContact" placeholder="you@example.com" required />
            </div>
            <div class="input-group">
              <label for="signupPasswordContact"><i class="fas fa-lock"></i> <?php echo $text['contact_modalCreatePasswordLabel'] ?? 'Create Password'; ?></label>
              <input type="password" id="signupPasswordContact" placeholder="<?php echo $text['contact_modalCreatePasswordPlaceholder'] ?? 'Minimum 8 characters'; ?>" required />
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-user-plus"></i> <?php echo $text['contact_modalCreateAccountButton'] ?? 'Create Account'; ?></button>
          </form>
          <p class="switch-form-text">
            <?php echo $text['contact_modalSwitchToLogin'] ?? 'Already have an account?'; ?> <a href="#" onclick="switchToLogin('signupFormContact','loginFormContact',event)"><?php echo $text['contact_modalLoginLink'] ?? 'Login'; ?></a>
          </p>
        </div>

      </div>
    </div>
</body>
</html>
