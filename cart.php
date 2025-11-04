<?php
session_start();
include 'php/db_connect.php';

$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';
$lang_file = "translations/{$lang}.json";
$text = file_exists($lang_file) ? json_decode(file_get_contents($lang_file), true) : [];
$pageTitle = $text['cart_pageTitle'] ?? 'Your Shopping Cart';

$cartItems = [];
$total = 0;
$isLoggedIn = isset($_SESSION['user_id']);

if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("
        SELECT p.product_id, p.name, p.price, p.image_url, ci.quantity 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        JOIN carts c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
    $stmt->close();
} else {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cartItems[] = $item;
            $total += $item['price'] * $item['quantity'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/cart.css" /> <!-- We will create this file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">
    <?php include 'includes/header.php'; ?>

    <main class="cart-page-main">
        <div class="container">
            <div class="cart-header">
                <h1><?php echo $text['cart_title'] ?? 'Shopping Cart'; ?></h1>
                <a href="product.php?lang=<?php echo $lang; ?>" class="continue-shopping"><?php echo $text['cart_continueShopping'] ?? 'Continue Shopping'; ?></a>
            </div>

            <div class="cart-container">
                <div class="cart-items-list">
                    <?php if (empty($cartItems )): ?>
                        <div class="cart-empty">
                            <i class="fas fa-shopping-basket"></i>
                            <p><?php echo $text['cart_empty'] ?? 'Your cart is currently empty.'; ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                                $cartImg = '/images/default_product.png';
                                if (!empty($item['image_url'])) {
                                    $raw = $item['image_url'];
                                    if (preg_match('#^https?://#i', $raw)) {
                                        $cartImg = $raw;
                                    } elseif (strpos($raw, 'uploads/') === 0 || strpos($raw, '/uploads/') === 0) {
                                        $cartImg = $raw;
                                    } else {
                                        $cartImg = 'uploads/' . $raw;
                                    }
                                }
                            ?>
                            <div class="cart-item" id="item-<?php echo $item['product_id']; ?>">
                                <img src="<?php echo htmlspecialchars($cartImg); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="item-details">
                                    <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="item-price">$<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="item-quantity">
                                    <button class="quantity-btn minus" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                    <input type="number" value="<?php echo $item['quantity']; ?>" min="1" onchange="setQuantity(<?php echo $item['product_id']; ?>, this.value)">
                                    <button class="quantity-btn plus" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                                </div>
                                <div class="item-total-price" data-price="<?php echo $item['price']; ?>">
                                    $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                                <button class="item-remove" onclick="removeItem(<?php echo $item['product_id']; ?>)"><i class="fas fa-trash"></i></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($cartItems)): ?>
                <div class="cart-summary">
                    <h4><?php echo $text['cart_summaryTitle'] ?? 'Order Summary'; ?></h4>
                    <div class="summary-line">
                        <span><?php echo $text['cart_subtotal'] ?? 'Subtotal'; ?></span>
                        <span id="summary-subtotal">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-line">
                        <span><?php echo $text['cart_shipping'] ?? 'Shipping'; ?></span>
                        <span><?php echo $text['cart_shipping_cost'] ?? 'Calculated at checkout'; ?></span>
                    </div>
                    <hr>
                    <div class="summary-total">
                        <span><?php echo $text['cart_total'] ?? 'Total'; ?></span>
                        <span id="summary-total">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <button class="btn-checkout" onclick="proceedToCheckout()">
                        <?php echo $text['cart_checkoutButton'] ?? 'Proceed to Checkout'; ?>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/cart_ajax.js"></script>
        <script>
            // If the server redirected with ?login=1, open the auth modal after load
            (function() {
                const params = new URLSearchParams(window.location.search);
                if (params.get('login') === '1') {
                    window.addEventListener('DOMContentLoaded', function() {
                        if (typeof openLoginModal === 'function') {
                            openLoginModal();
                        }
                    });
                }
            })();
        </script>
</body>
</html>
