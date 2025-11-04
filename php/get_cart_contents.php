<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

$cartItems = [];
$total = 0.00;
$itemCount = 0;

if (isset($_SESSION['user_id'])) {
    // LOGGED-IN USER
    $userId = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("
        SELECT p.product_id, p.name, p.price, p.image_url, ci.quantity 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        JOIN carts c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ? ORDER BY ci.cart_item_id DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
    $itemCount = $result->num_rows;
    $stmt->close();
} else {
    // GUEST USER
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // We need to fetch product details from DB for guests
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $types = str_repeat('i', count($product_ids));
        
        $stmt = $mysqli->prepare("SELECT product_id, name, price, image_url FROM products WHERE product_id IN ($placeholders)");
        $stmt->bind_param($types, ...$product_ids);
        $stmt->execute();
        $products_from_db = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Create a lookup map for products
        $product_map = [];
        foreach ($products_from_db as $p) {
            $product_map[$p['product_id']] = $p;
        }

        foreach ($_SESSION['cart'] as $productId => $item) {
            if (isset($product_map[$productId])) {
                $product_data = $product_map[$productId];
                $cartItems[] = [
                    'product_id' => $productId,
                    'name' => $product_data['name'],
                    'price' => $product_data['price'],
                    'image_url' => $product_data['image_url'],
                    'quantity' => $item['quantity']
                ];
                $total += $product_data['price'] * $item['quantity'];
            }
        }
        $itemCount = count($cartItems);
    }
}

echo json_encode([
    'success' => true,
    'itemCount' => $itemCount,
    'subtotal' => number_format($total, 2),
    'items' => $cartItems
]);

$mysqli->close();
?>
