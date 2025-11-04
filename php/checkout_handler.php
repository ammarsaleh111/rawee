<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    // Redirect back to cart page and instruct frontend to open the login modal
    $lang = isset($_GET['lang']) ? htmlspecialchars($_GET['lang']) : 'en';
    header("Location: ../cart.php?lang={$lang}&login=1");
    exit;
}

$user_id = $_SESSION["user_id"];
$address = $_POST["address"] ?? "";
$payment_method = $_POST["payment_method"] ?? "Cash";

// find cart
$stmt = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($cart_id);
if (!$stmt->fetch()) {
    header("Location: cart.php");
    exit;
}
$stmt->close();

// fetch items
$stmt = $conn->prepare("SELECT ci.product_id, ci.quantity, p.price 
                        FROM cart_items ci 
                        JOIN products p ON ci.product_id = p.product_id 
                        WHERE ci.cart_id = ?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
    $total += $row["price"] * $row["quantity"];
}
$stmt->close();

if (empty($items)) {
    header("Location: cart.php");
    exit;
}

// create order
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'Pending')");
$stmt->bind_param("id", $user_id, $total);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// insert order items
$stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($items as $item) {
    $stmt->bind_param("iiid", $order_id, $item["product_id"], $item["quantity"], $item["price"]);
    $stmt->execute();
}
$stmt->close();

// clear cart
$stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$stmt->close();

header("Location: order_success.php?order_id=" . $order_id);
exit;
