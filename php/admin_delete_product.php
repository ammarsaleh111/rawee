<?php
// admin_delete_product.php
include 'db_connect.php';

if(isset($_POST['product_id'])){
    $product_id = intval($_POST['product_id']);

    $stmt = $mysqli->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if($stmt->execute()){
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
} else {
    echo "error";
}
$mysqli->close();
