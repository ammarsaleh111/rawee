<?php
// Start session and protect page
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php?error=notloggedin");
    exit();
}

// ======================= LANGUAGE LOGIC (MUST BE AT THE TOP) =======================
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';
$lang_file = "translations/{$lang}.json";
$text = file_exists($lang_file) ? json_decode(file_get_contents($lang_file), true) : [];

// The page title uses the specific 'user_pageTitle' key.
$pageTitle = $text['user_pageTitle'] ?? 'My Dashboard - RAWEE Smart Farming';
// ========================================================================================

// Include header AFTER language logic is complete
include 'includes/header.php';
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
</head>
<body>
    <main class="dashboard-page-main">
      <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-welcome-banner">
          <div class="banner-content">
            <h2><?php echo $text['user_welcome'] ?? 'Welcome Back,'; ?> <?php echo htmlspecialchars($_SESSION['full_name'] ); ?>!</h2>
            <p>
              <?php 
                // Using sprintf to safely insert the number into the translated string
                $cart_items_count = 3; 
                $subtitle_template = $text['user_banner_subtitle'] ?? 'Your farm is looking healthy. You have <strong>%s items</strong> in your cart.';
                echo sprintf($subtitle_template, $cart_items_count);
              ?>
            </p>
            <div class="banner-actions">
              <a href="#" class="btn-get-help">
                <i class="fas fa-life-ring"></i> <?php echo $text['user_getHelp'] ?? 'Get Help'; ?>
              </a>
            </div>
          </div>
          <div class="banner-illustration">
            <img
              src="https://raw.githubusercontent.com/manus-labs/assets/main/dashboard-hero.svg"
              alt="Smart farm illustration"
            />
          </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
          <div class="dashboard-main-content">
            <div class="tabs-container">
              <div class="tab-nav">
                <button class="tab-link active" onclick="openTab(event, 'orders' )">
                  <i class="fas fa-receipt"></i> <?php echo $text['user_tabOrders'] ?? 'My Orders'; ?>
                </button>
                <button class="tab-link" onclick="openTab(event, 'cart')">
                  <i class="fas fa-shopping-cart"></i> <?php echo $text['user_tabCart'] ?? 'Shopping Cart'; ?>
                </button>
              </div>

              <!-- Orders Tab -->
              <div id="orders" class="tab-content active">
                <div class="order-item">
                  <div class="order-details">
                    <h5>Order #RWF-84321</h5>
                    <p><?php echo $text['user_order_date'] ?? 'Date:'; ?> 05 Sep 2025</p>
                    <span class="status-badge shipped"><?php echo $text['user_order_statusShipped'] ?? 'Shipped'; ?></span>
                  </div>
                  <div class="order-total">
                    <p>$1,250.00</p>
                    <a href="#" class="btn-view-order"><?php echo $text['user_order_viewDetails'] ?? 'View Details'; ?></a>
                  </div>
                </div>
                <div class="order-item">
                  <div class="order-details">
                    <h5>Order #RWF-84199</h5>
                    <p><?php echo $text['user_order_date'] ?? 'Date:'; ?> 15 Aug 2025</p>
                    <span class="status-badge delivered"><?php echo $text['user_order_statusDelivered'] ?? 'Delivered'; ?></span>
                  </div>
                  <div class="order-total">
                    <p>$800.00</p>
                    <a href="#" class="btn-view-order"><?php echo $text['user_order_viewDetails'] ?? 'View Details'; ?></a>
                  </div>
                </div>
              </div>

              <!-- Cart Tab -->
              <div id="cart" class="tab-content">
                <div class="cart-item">
                  <img
                    src="https://placehold.co/100x100/077A7D/EBF4F6?text=Sensor"
                    alt="Product Image"
                  />
                  <div class="cart-item-details">
                    <h5><?php echo $text['user_cart_product1'] ?? 'Soil Moisture Sensor (x5 )'; ?></h5>
                    <p>SKU: SMS-002</p>
                  </div>
                  <div class="cart-item-price">$250.00</div>
                  <button class="btn-remove-item">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="cart-item">
                  <img
                    src="https://placehold.co/100x100/37B7C3/06202B?text=Hub"
                    alt="Product Image"
                  />
                  <div class="cart-item-details">
                    <h5><?php echo $text['user_cart_product2'] ?? 'Central IoT Hub'; ?></h5>
                    <p>SKU: HUB-001</p>
                  </div>
                  <div class="cart-item-price">$400.00</div>
                  <button class="btn-remove-item">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="cart-summary">
                  <div class="summary-line">
                    <span><?php echo $text['user_cart_subtotal'] ?? 'Subtotal'; ?></span>
                    <span>$650.00</span>
                  </div>
                  <div class="summary-line">
                    <span><?php echo $text['user_cart_shipping'] ?? 'Shipping'; ?></span>
                    <span>$25.00</span>
                  </div>
                  <div class="summary-total">
                    <span><?php echo $text['user_cart_total'] ?? 'Total'; ?></span>
                    <span>$675.00</span>
                  </div>
                  <button class="btn-checkout">
                    <i class="fas fa-credit-card"></i> <?php echo $text['user_cart_checkout'] ?? 'Proceed to Checkout'; ?>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/edits.js"></script>
    <script>
      // Dashboard tab switcher
      function openTab(evt, tabName ) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
          tabcontent[i].style.display = "none";
          tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("tab-link");
        for (i = 0; i < tablinks.length; i++) {
          tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.className += " active";
      }

      document.addEventListener("DOMContentLoaded", function () {
        // Ensure a tab is active on page load
        if(document.querySelector(".tab-link.active")) {
            document.querySelector(".tab-link.active").click();
        } else if (document.querySelector(".tab-link")) {
            document.querySelector(".tab-link").click();
        }
      });
    </script>
</body>
</html>
