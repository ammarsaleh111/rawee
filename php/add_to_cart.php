<?php
session_start();
include 'db_connect.php'; // Connects to your database
header('Content-Type: application/json');

if (!isset($_POST['product_id']) || !filter_var($_POST['product_id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Product ID.']);
    exit;
}
$productId = (int)$_POST['product_id'];

if (isset($_SESSION['user_id'])) {
    // --- LOGGED-IN USER ---
    $userId = $_SESSION['user_id'];

    // 1. Find the user's active cart
    $cartStmt = $mysqli->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
    $cartStmt->bind_param("i", $userId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();
    
    if ($cartResult->num_rows > 0) {
        $cart = $cartResult->fetch_assoc();
        $cartId = $cart['cart_id'];
    } else {
        // 2. If no cart exists, create one
        $insertCartStmt = $mysqli->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $insertCartStmt->bind_param("i", $userId);
        $insertCartStmt->execute();
        $cartId = $mysqli->insert_id;
        $insertCartStmt->close();
    }
    $cartStmt->close();

    // 3. Add item to cart_items or update quantity
    // We need a UNIQUE key on (cart_id, product_id) in cart_items for this to work perfectly
    $itemStmt = $mysqli->prepare("
        INSERT INTO cart_items (cart_id, product_id, quantity) 
        VALUES (?, ?, 1) 
        ON DUPLICATE KEY UPDATE quantity = quantity + 1
    ");
    $itemStmt->bind_param("ii", $cartId, $productId);
    
    if ($itemStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Product added to your cart!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product.']);
    }
    $itemStmt->close();

} else {
    // --- GUEST USER (Uses Session) ---
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity']++;
    } else {
        $stmt = $mysqli->prepare("SELECT name, price, image_url FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if ($product) {
            $_SESSION['cart'][$productId] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'price' => $product['price'],
                'image_url' => $product['image_url'],
                'quantity' => 1
            ];
        }
    }
    echo json_encode(['success' => true, 'message' => 'Product added to cart!']);
}
$mysqli->close();
?>
