<?php
// This header file assumes $lang and $text have been defined by the parent page (e.g., index.php)

// Session and Login Logic
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userFullName = $isLoggedIn ? $_SESSION['full_name'] : '';

// ======================= NEW: CART COUNT LOGIC =======================
$cartItemCount = 0;
if ($isLoggedIn) {
    // For logged-in users, count items in the database
    // This requires a database connection, so we include it here.
    // Make sure the path is correct for your structure.
    include __DIR__ . '/../php/db_connect.php'; 
    $userId = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("SELECT COUNT(ci.cart_item_id) as count FROM cart_items ci JOIN carts c ON ci.cart_id = c.cart_id WHERE c.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $cartItemCount = $result['count'] ?? 0;
    $stmt->close();
} else {
    // For guests, count items in the session
    if (isset($_SESSION['cart'])) {
        $cartItemCount = count($_SESSION['cart']);
    }
}
// =====================================================================

// Language Switcher Logic
$other_lang = ($lang == 'en') ? 'ar' : 'en';
$other_lang_text = ($lang == 'en') ? 'العربية' : 'English';
$current_script = basename($_SERVER['PHP_SELF']);
// Preserve existing query params except 'lang', then set the new lang
$current_query = $_GET ?? [];
if (isset($current_query['lang'])) {
    unset($current_query['lang']);
}
// Build new query with preserved params + new lang
$new_query = array_merge($current_query, ['lang' => $other_lang]);
$lang_switch_url = $current_script . '?' . http_build_query($new_query);
?>
<header class="main-header" id="mainHeader">
    <div class="container">
        <a href="index.php?lang=<?php echo $lang; ?>" class="logo">RAWEE</a>
        
        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="index.php?lang=<?php echo $lang; ?>"><?php echo $text['nav_home'] ?? 'Home'; ?></a></li>
                <li><a href="product.php?lang=<?php echo $lang; ?>"><?php echo $text['nav_product'] ?? 'Product'; ?></a></li>
                <li><a href="about.php?lang=<?php echo $lang; ?>"><?php echo $text['nav_about'] ?? 'About Us'; ?></a></li>
                <li><a href="contact.php?lang=<?php echo $lang; ?>"><?php echo $text['nav_contact'] ?? 'Contact Us'; ?></a></li>
            </ul>
        </nav>

        <div class="header-actions-container">
            <?php if ($isLoggedIn): ?>
                <div class="header-user-profile">
                    <div class="profile-greeting">
                        <span><?php echo $text['header_welcome'] ?? 'Welcome,'; ?></span>
                        <strong><?php echo htmlspecialchars($userFullName); ?></strong>
                    </div>
                    <div class="profile-avatar-wrapper">
                        <img src="https://images.unsplash.com/photo-1557862921-37829c790f19?q=80&w=100&auto=format&fit=crop" alt="User Avatar" class="profile-avatar" />
                        <div class="profile-dropdown">
                            <a href="user.php?lang=<?php echo $lang; ?>"><i class="fas fa-tachometer-alt"></i> <?php echo $text['header_dashboard'] ?? 'Dashboard'; ?></a>
                            <a href="userProfile.php?lang=<?php echo $lang; ?>"><i class="fas fa-user-circle"></i> <?php echo $text['header_myProfile'] ?? 'My Profile'; ?></a>
                            <hr />
                            <a href="php/logout_handler.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> <?php echo $text['header_logout'] ?? 'Logout'; ?></a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="header-actions">
                    <a href="#" class="btn btn-secondary" onclick="openLoginModal( )"><?php echo $text['header_login'] ?? 'Login'; ?></a>
                    <a href="#" class="btn btn-primary" onclick="openSignupModal()"><?php echo $text['header_signup'] ?? 'Sign Up'; ?></a>
                </div>
            <?php endif; ?>

            <!-- language & cart icons moved outside container for edge positioning -->
        </div>

        <div class="mobile-toggle" id="mobileToggle" onclick="toggleMobileMenu()">
            <span></span><span></span><span></span>
        </div>
    </div>
</header>
    <div class="header-icon-group" aria-hidden="false" style="top: 21px; right: 15px;">
        <button class="icon-btn lang-switcher"  aria-label="Switch language" onclick="window.location.href='<?php echo $lang_switch_url; ?>'">
            <i style="right: -50px;" class="fas fa-globe"></i>
        </button>

        <a href="cart.php?lang=<?php echo $lang; ?>" class="header-cart-icon" id="headerCartIcon" onclick="toggleCartSidebar(event)">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($cartItemCount > 0): ?>
                <span class="cart-item-count"><?php echo $cartItemCount; ?></span>
            <?php endif; ?>
        </a>
    </div>
<script>
  // Mobile menu toggle
  function toggleMobileMenu() {
    document.getElementById('mainNav').classList.toggle('active');
    document.getElementById('mobileToggle').classList.toggle('active');
  }

    // Use modal functions defined in js/edits.js when available; otherwise defer until loaded
    function openLoginModal() {
        if (typeof ensureAuthModal === 'function') return ensureAuthModal();
        if (typeof openLoginModal === 'function' && window._headerModalFallback !== true) {
            // avoid infinite recursion if overwritten
            window._headerModalFallback = true;
            setTimeout(() => { if (typeof ensureAuthModal === 'function') ensureAuthModal(); }, 300);
        }
    }
    function openSignupModal() {
        if (typeof openSignupModal === 'function' && window._headerModalFallback !== true) {
            // ensure edits.js will handle showing signup via switchToSignup
            window._headerModalFallback = true;
            setTimeout(() => { if (typeof switchToSignup === 'function') { ensureAuthModal(); switchToSignup(new Event('click')); } }, 300);
        }
    }
</script>
