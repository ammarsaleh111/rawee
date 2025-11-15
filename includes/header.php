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
    <div class="container header-grid">
        <!-- Left: Logo -->
        <a href="index.php?lang=<?php echo $lang; ?>" class="logo" aria-label="RAWEE Home">RAWEE</a>

        <!-- Center: Navigation (desktop) / Off-canvas content (mobile) -->
        <nav class="primary-nav" id="primaryNav" aria-label="Main navigation">
            <ul class="nav-list" role="menubar">
                <li role="none"><a role="menuitem" href="index.php?lang=<?php echo $lang; ?>"><?php echo $text['nav_home'] ?? 'Home'; ?></a></li>
                <li role="none"><a role="menuitem" href="product.php?lang=<?php echo $lang; ?>"><?php echo $text['nav_product'] ?? 'Product'; ?></a></li>
                <li role="none"><a role="menuitem" href="about.php?lang=<?php echo $lang; ?>"><?php echo $text['nav_about'] ?? 'About Us'; ?></a></li>
                <li role="none"><a role="menuitem" href="contact.php?lang=<?php echo $lang; ?>"><?php echo $text['nav_contact'] ?? 'Contact Us'; ?></a></li>
            </ul>
            <div class="mobile-auth-block" id="mobileAuthMount">
                <?php if ($isLoggedIn): ?>
                    <!-- Logged-in links remain (distinct component) -->
                    <a href="user.php?lang=<?php echo $lang; ?>" class="mobile-auth-link"><i class="fas fa-tachometer-alt"></i> <?php echo $text['header_dashboard'] ?? 'Dashboard'; ?></a>
                    <a href="userProfile.php?lang=<?php echo $lang; ?>" class="mobile-auth-link"><i class="fas fa-user-circle"></i> <?php echo $text['header_myProfile'] ?? 'My Profile'; ?></a>
                    <a href="php/logout_handler.php" class="mobile-auth-link"><i class="fas fa-sign-out-alt"></i> <?php echo $text['header_logout'] ?? 'Logout'; ?></a>
                <?php endif; ?>
            </div>
            <button class="nav-close" type="button" aria-label="Close menu" onclick="toggleMobileMenu(false)">×</button>
        </nav>

        <!-- Right: Auth (desktop) + language + cart + hamburger -->
        <div class="header-right" id="headerRight">
            <?php if ($isLoggedIn): ?>
                <div class="header-user-profile">
                    <div class="profile-greeting">
                        <span><?php echo $text['header_welcome'] ?? 'Welcome,'; ?></span>
                        <strong><?php echo htmlspecialchars($userFullName); ?></strong>
                    </div>
                    <div class="profile-avatar-wrapper" aria-haspopup="true">
                        <img src="https://images.unsplash.com/photo-1557862921-37829c790f19?q=80&w=100&auto=format&fit=crop" alt="User Avatar" class="profile-avatar" />
                        <div class="profile-dropdown" role="menu">
                            <a href="user.php?lang=<?php echo $lang; ?>" role="menuitem"><i class="fas fa-tachometer-alt"></i> <?php echo $text['header_dashboard'] ?? 'Dashboard'; ?></a>
                            <a href="userProfile.php?lang=<?php echo $lang; ?>" role="menuitem"><i class="fas fa-user-circle"></i> <?php echo $text['header_myProfile'] ?? 'My Profile'; ?></a>
                            <hr />
                            <a href="php/logout_handler.php" class="logout-link" role="menuitem"><i class="fas fa-sign-out-alt"></i> <?php echo $text['header_logout'] ?? 'Logout'; ?></a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                                <div class="desktop-auth-buttons" id="authButtons">
                    <a href="#" class="btn btn-secondary" onclick="openLoginModal(); return false;"><?php echo $text['header_login'] ?? 'Login'; ?></a>
                    <a href="#" class="btn btn-primary" onclick="openSignupModal(); return false;"><?php echo $text['header_signup'] ?? 'Sign Up'; ?></a>
                </div>
            <?php endif; ?>
            <div class="utility-icons" aria-label="Utility actions">
                <button class="icon-btn lang-switcher" aria-label="Switch language" onclick="window.location.href='<?php echo $lang_switch_url; ?>'">
                    <i class="fas fa-globe"></i>
                </button>
                <a href="cart.php?lang=<?php echo $lang; ?>" class="header-cart-icon" id="headerCartIcon">
                    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
                    <?php if ($cartItemCount > 0): ?>
                        <span class="cart-item-count" aria-label="<?php echo $cartItemCount; ?> items in cart"><?php echo $cartItemCount; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <button class="hamburger" id="hamburger" aria-label="Open menu" aria-controls="primaryNav" aria-expanded="false" onclick="toggleMobileMenu()">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>
<div class="nav-overlay" id="navOverlay" onclick="toggleMobileMenu(false)"></div>
<script>
  function toggleMobileMenu(forceState){
    const nav = document.getElementById('primaryNav');
    const burger = document.getElementById('hamburger');
    const overlay = document.getElementById('navOverlay');
    const body = document.body;
    const willOpen = (typeof forceState === 'boolean') ? forceState : !nav.classList.contains('open');
    
    if(willOpen){
        nav.classList.add('open');
        overlay.classList.add('active');
        burger.classList.add('active');
        burger.setAttribute('aria-expanded','true');
        body.classList.add('nav-open');
        // focus first link for accessibility
        const first = nav.querySelector('a');
        if(first) first.focus();
    } else {
        nav.classList.remove('open');
        overlay.classList.remove('active');
        burger.classList.remove('active');
        burger.setAttribute('aria-expanded','false');
        body.classList.remove('nav-open');
    }
}

document.addEventListener('keydown', e => { 
    // Close on Escape key press
    if(e.key==='Escape' && document.getElementById('primaryNav').classList.contains('open')){ 
        toggleMobileMenu(false); 
    }
});

// Sidebar auto-close logic: ONLY close when a link that causes a page navigation is clicked.
// This is the fix for the unintended closing.
(function(){
    const nav = document.getElementById('primaryNav');
    if(!nav) return;
    
    nav.addEventListener('click', function(e){
        const link = e.target.closest('a');
        
        // 1. If not clicking a link, or if the sidebar is not open, do nothing.
        if(!link || !nav.classList.contains('open')) return;
        
        const href = link.getAttribute('href');
        
        // 2. Do NOT close for:
        //    - Links with no href (shouldn't happen, but safe check)
        //    - Links with href="#" (often used for modal triggers)
        //    - Links starting with "javascript:" (used for inline JS calls like openLoginModal)
        //    - Links that are children of the mobileAuthMount (which includes the new buttons)
        if(!href || href === '#' || href.startsWith('javascript:')) return;
        
        // Check if the clicked link is inside the mobileAuthMount (the new buttons)
        if (link.closest('#mobileAuthMount')) {
            // If it's an auth link, we assume it triggers a modal or a non-page-navigation action.
            // We only close the sidebar if the link is a full logout link (which navigates away).
            if (href.includes('logout_handler.php')) {
                toggleMobileMenu(false);
            }
            return; // Do not close for other auth links (like modal triggers)
        }
        
        // 3. Close the sidebar ONLY for actual page navigation links (e.g., index.php, product.php)
        // This check is now simplified as the modal/auth links are filtered out above.
        // We also ensure this only happens in mobile view, although the sidebar itself is mobile-only.
        if (window.matchMedia('(max-width: 992px)').matches) {
            toggleMobileMenu(false);
        }
    });
})();

// Modal functions (kept as is)
function openLoginModal() {
    if (typeof ensureAuthModal === 'function') return ensureAuthModal();
    if (typeof openLoginModal === 'function' && window._headerModalFallback !== true) {
        window._headerModalFallback = true;
        setTimeout(() => { if (typeof ensureAuthModal === 'function') ensureAuthModal(); }, 300);
    }
}
function openSignupModal() {
    if (typeof openSignupModal === 'function' && window._headerModalFallback !== true) {
        window._headerModalFallback = true;
        setTimeout(() => { if (typeof switchToSignup === 'function') { ensureAuthModal(); switchToSignup(new Event('click')); } }, 300);
    }
}
</script>
