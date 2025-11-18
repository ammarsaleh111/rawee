<?php
// This footer file assumes $lang and $text have been defined by the parent page (e.g., index.php)
?>

<!-- ======================= NEW: CART SIDEBAR ======================= -->
<div class="cart-sidebar-overlay" id="cartSidebarOverlay" onclick="toggleCartSidebar()"></div>
<div class="cart-sidebar" id="cartSidebar">
    <div class="sidebar-header">
        <h3><?php echo $text['cart_title'] ?? 'Shopping Cart'; ?></h3>
        <button class="close-sidebar-btn" onclick="toggleCartSidebar()">&times;</button>
    </div>

    <div class="sidebar-items-list" id="sidebarItemsList">
        <!-- Cart items will be loaded here by JavaScript -->
        <div class="sidebar-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading Cart...</span>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-subtotal">
            <span><?php echo $text['cart_subtotal'] ?? 'Subtotal'; ?></span>
            <span id="sidebarSubtotal">$0.00</span>
        </div>
        <!-- Hide direct View Cart to ensure only Proceed to Checkout navigates -->
        <!-- <a href="cart.php?lang=<?php echo $lang; ?>" class="btn-view-cart"><?php echo $text['user_tabCart'] ?? 'View Cart'; ?></a> -->
        <button class="btn-checkout-sidebar" onclick="proceedToCheckout()">
            <?php echo $text['cart_checkoutButton'] ?? 'Proceed to Checkout'; ?>
        </button>
    </div>
</div>
<!-- =============================================================== -->
<footer class="main-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-column brand-column">
        <h3 class="footer-logo">RAWEE</h3>
        <p><?php echo $text['footer_tagline'] ?? 'Engineering a Greener World...'; ?></p>
        <div class="social-list">
          <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-icon" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <a href="#mainHeader" class="back-to-top"><i class="fas fa-arrow-up"></i> <?php echo $text['footer_backToTop'] ?? 'Back to Top'; ?></a>
      </div>

      <div class="footer-column">
        <h4><?php echo $text['footer_homeTitle'] ?? 'Home'; ?></h4>
        <ul class="footer-links">
          <li><a href="index.php?lang=<?php echo $lang; ?>#about-short"><?php echo $text['footer_homeLink1'] ?? 'Know About Us'; ?></a></li>
          <li><a href="index.php?lang=<?php echo $lang; ?>#technology"><?php echo $text['footer_homeLink2'] ?? 'Our Technology'; ?></a></li>
          <li><a href="index.php?lang=<?php echo $lang; ?>#solutions"><?php echo $text['footer_homeLink3'] ?? 'Our Solutions'; ?></a></li>
          <li><a href="contact.php?lang=<?php echo $lang; ?>"><?php echo $text['footer_homeLink4'] ?? 'Get In Touch'; ?></a></li>
        </ul>
      </div>

      <div class="footer-column">
        <h4><?php echo $text['footer_aboutTitle'] ?? 'About Us'; ?></h4>
        <ul class="footer-links">
          <li><a href="about.php?lang=<?php echo $lang; ?>"><?php echo $text['footer_aboutLink1'] ?? 'Our Vision'; ?></a></li>
          <li><a href="about.php?lang=<?php echo $lang; ?>#team"><?php echo $text['footer_aboutLink2'] ?? 'Who We Are'; ?></a></li>
          <li><a href="about.php?lang=<?php echo $lang; ?>#why-us"><?php echo $text['footer_aboutLink3'] ?? 'Why Us'; ?></a></li>
        </ul>
      </div>

      <div class="footer-column">
        <h4><?php echo $text['footer_solutionsTitle'] ?? 'Solutions'; ?></h4>
        <ul class="footer-links">
          <li><a href="product.php?lang=<?php echo $lang; ?>"><?php echo $text['footer_solutionsLink1'] ?? 'Aquaculture'; ?></a></li>
          <li><a href="product.php?lang=<?php echo $lang; ?>"><?php echo $text['footer_solutionsLink2'] ?? 'Hydroponics'; ?></a></li>
          <li><a href="product.php?lang=<?php echo $lang; ?>"><?php echo $text['footer_solutionsLink3'] ?? 'Greenhouse'; ?></a></li>
          <li><a href="product.php?lang=<?php echo $lang; ?>"><?php echo $text['footer_solutionsLink4'] ?? 'Field Crops'; ?></a></li>
        </ul>
      </div>

      <div class="footer-column">
        <h4><?php echo $text['footer_legalTitle'] ?? 'Legal'; ?></h4>
        <ul class="footer-links">
          <li><a href="#"><?php echo $text['footer_legalLink1'] ?? 'Privacy Policy'; ?></a></li>
          <li><a href="#"><?php echo $text['footer_legalLink2'] ?? 'Terms of Service'; ?></a></li>
          <li><a href="#"><?php echo $text['footer_legalLink3'] ?? 'Cookie Policy'; ?></a></li>
          <li><a href="#"><?php echo $text['footer_legalLink4'] ?? 'Data Protection'; ?></a></li>
        </ul>
      </div>
    </div>

    <div class="copyright">
      <p>&copy; <?php echo date("Y"); ?> RAWEE. <?php echo $text['footer_copyright'] ?? 'All Rights Reserved.'; ?></p>
    </div>
  </div>
</footer>

<!-- SINGLE AUTH MODAL FOR ALL PAGES -->
<div class="auth-modal" id="authModal">
  <div class="auth-modal-overlay" onclick="closeAuthModal()"></div>
  <div class="auth-modal-content">
    <button class="close-button" onclick="closeAuthModal()" aria-label="Close modal">&times;</button>

    <!-- Login Form -->
    <div class="auth-form" id="loginForm">
      <h3 class="form-title"><?php echo $text['modal_loginTitle'] ?? 'Welcome Back'; ?></h3>
      <p class="form-subtitle"><?php echo $text['modal_loginSubtitle'] ?? 'Login to access...'; ?></p>
      <form action="php/login_handler.php" method="POST">
        <div class="input-group">
          <label for="loginEmailModal"><i class="fas fa-envelope"></i> <?php echo $text['modal_emailLabel'] ?? 'Email Address'; ?></label>
          <input type="email" id="loginEmailModal" name="email" placeholder="you@example.com" required />
        </div>
        <div class="input-group">
          <label for="loginPasswordModal"><i class="fas fa-lock"></i> <?php echo $text['modal_passwordLabel'] ?? 'Password'; ?></label>
          <input type="password" id="loginPasswordModal" name="password" placeholder="••••••••" required />
        </div>
        <div class="form-options">
          <a href="#" class="forgot-password"><?php echo $text['modal_forgotPassword'] ?? 'Forgot Password?'; ?></a>
        </div>
        <button type="submit" class="btn-submit"><i class="fas fa-sign-in-alt"></i> <?php echo $text['modal_loginButton'] ?? 'Login'; ?></button>
      </form>
      <p class="switch-form-text"><?php echo $text['modal_switchToSignup'] ?? "Don't have an account?"; ?> <a href="#" onclick="switchToSignup(event)"><?php echo $text['modal_signupLink'] ?? 'Sign Up'; ?></a></p>
    </div>

    <!-- Sign-Up Form -->
    <div class="auth-form" id="signupForm" style="display: none">
      <h3 class="form-title"><?php echo $text['modal_signupTitle'] ?? 'Create Your Account'; ?></h3>
      <p class="form-subtitle"><?php echo $text['modal_signupSubtitle'] ?? 'Join RAWEE...'; ?></p>
<form action="php/signup_handler.php" method="POST">
        <div class="input-group">
          <label for="signupNameModal"><i class="fas fa-user"></i> <?php echo $text['modal_nameLabel'] ?? 'Full Name'; ?></label>
          <input type="text" id="signupNameModal" name="full_name" placeholder="Alex Johnson" required />
        </div>
        <div class="input-group">
          <label for="signupEmailModal"><i class="fas fa-envelope"></i> <?php echo $text['modal_emailLabel'] ?? 'Email Address'; ?></label>
          <input type="email" id="signupEmailModal" name="email" placeholder="you@example.com" required />
        </div>
        <div class="input-group">
          <label for="signupPasswordModal"><i class="fas fa-lock"></i> <?php echo $text['modal_createPasswordLabel'] ?? 'Create Password'; ?></label>
          <input type="password" id="signupPasswordModal" name="password" placeholder="<?php echo $text['modal_createPasswordPlaceholder'] ?? 'Minimum 8 characters'; ?>" required />
        </div>
        <button type="submit" class="btn-submit"><i class="fas fa-user-plus"></i> <?php echo $text['modal_createAccountButton'] ?? 'Create Account'; ?></button>
      </form>
      <p class="switch-form-text"><?php echo $text['modal_switchToLogin'] ?? 'Already have an account?'; ?> <a href="#" onclick="switchToLogin(event)"><?php echo $text['modal_loginLink'] ?? 'Login'; ?></a></p>
    </div>
  </div>
</div>

<!-- SCRIPTS -->
<?php $cartJsVer = @filemtime(__DIR__ . '/../js/cart_ajax.js') ?: time(); ?>
<script src="js/cart_ajax.js?v=<?php echo $cartJsVer; ?>"></script>
<script src="js/edits.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
