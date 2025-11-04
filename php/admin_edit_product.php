<?php
// This is your existing database connection file.
include 'db_connect.php';

// Check that the request is valid
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    die("Invalid request.");
}

// Get all the form data
$product_id = $_POST['product_id'];
$name = $_POST['name'];
$description = $_POST['description'] ?? '';
// Support per-language detailed descriptions
$detailed_description_en = $_POST['detailed_description_en'] ?? ($_POST['detailed_description'] ?? '');
$detailed_description_ar = $_POST['detailed_description_ar'] ?? '';
// legacy field
$detailed_description = $detailed_description_en;
$price = $_POST['price'];
$category_id = $_POST['category_id'];
$in_stock = isset($_POST['in_stock']) ? 1 : 0;
$is_new = isset($_POST['is_new']) ? 1 : 0;

$stmt = $mysqli->prepare("UPDATE products SET name=?, description=?, detailed_description=?, detailed_description_en=?, detailed_description_ar=?, price=?, category_id=?, in_stock=?, is_new=? WHERE product_id=?");

// Bind the parameters. Types: s=string, d=double, i=integer
$stmt->bind_param("sssssdiiii", $name, $description, $detailed_description, $detailed_description_en, $detailed_description_ar, $price, $category_id, $in_stock, $is_new, $product_id);
$stmt->execute();
$stmt->close();

// Handle image upload ONLY if a new file is provided
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
    $targetDir = "../uploads/";
    $filename = uniqid() . "_" . basename($_FILES['image_file']['name']);
    $targetFile = $targetDir . $filename;

    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            $image_filename = $filename; // store only filename
            // Update image filename in DB
            $img_stmt = $mysqli->prepare("UPDATE products SET image_url = ? WHERE product_id = ?");
            $img_stmt->bind_param("si", $image_filename, $product_id);
            $img_stmt->execute();
            $img_stmt->close();
        }
    }
}

// Redirect back to the admin page
header("Location: ../admin.php?status=edited#products");
exit();
?>
