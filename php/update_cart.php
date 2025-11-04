<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_POST['product_id']) || !filter_var($_POST['product_id'], FILTER_VALIDATE_INT) ||
    !isset($_POST['quantity']) || !filter_var($_POST['quantity'], FILTER_VALIDATE_INT) || $_POST['quantity'] < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}
$productId = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

if (isset($_SESSION['user_id'])) {
    // --- LOGGED-IN USER ---
    $userId = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("
        UPDATE cart_items ci
        JOIN carts c ON ci.cart_id = c.cart_id
        SET ci.quantity = ?
        WHERE c.user_id = ? AND ci.product_id = ?
    ");
    $stmt->bind_param("iii", $quantity, $userId, $productId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
} else {
    // --- GUEST USER ---
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        echo json_encode(['success' => true]);
    }
}
$mysqli->close();
?>
