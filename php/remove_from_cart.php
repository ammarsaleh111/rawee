<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_POST['product_id']) || !filter_var($_POST['product_id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Product ID.']);
    exit;
}
$productId = (int)$_POST['product_id'];

if (isset($_SESSION['user_id'])) {
    // --- LOGGED-IN USER ---
    $userId = $_SESSION['user_id'];
    $stmt = $mysqli->prepare("
        DELETE ci FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.cart_id
        WHERE c.user_id = ? AND ci.product_id = ?
    ");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
} else {
    // --- GUEST USER ---
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        echo json_encode(['success' => true]);
    }
}
$mysqli->close();
?>
