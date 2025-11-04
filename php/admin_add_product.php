<?php
// This is your existing database connection file.
include 'db_connect.php';

// Get all the form data
$name = $_POST['name'];
$description = $_POST['description'];
// Support per-language detailed descriptions (EN/AR). Fall back to legacy field name if used.
$detailed_description_en = $_POST['detailed_description_en'] ?? ($_POST['detailed_description'] ?? '');
$detailed_description_ar = $_POST['detailed_description_ar'] ?? '';
// For backward compatibility, also write the legacy column with the English text
$detailed_description = $detailed_description_en;
$price = $_POST['price'];
$category_id = $_POST['category_id'];
$in_stock = isset($_POST['in_stock']) ? 1 : 0;
$is_new = isset($_POST['is_new']) ? 1 : 0;

// Handle image upload: store only the filename in DB; renderers will prepend uploads/
$image_filename = ""; // empty means no custom image
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
    $targetDir = "../uploads/";
    // Create a unique filename to prevent overwriting
    $filename = uniqid() . "_" . basename($_FILES['image_file']['name']);
    $targetFile = $targetDir . $filename;

    // Basic validation for image type
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            $image_filename = $filename; // store filename only
        } else {
            error_log("Failed to move uploaded file to " . $targetFile);
        }
    }
}

// Prepare the SQL statement including per-language columns (ensure DB has these columns)
// Insert product; image_url column will contain filename or empty string
$stmt = $mysqli->prepare("INSERT INTO products (name, description, detailed_description, detailed_description_en, detailed_description_ar, price, category_id, image_url, in_stock, is_new) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Bind the parameters. Types: s=string, d=double, i=integer
$stmt->bind_param("sssssdisii", $name, $description, $detailed_description, $detailed_description_en, $detailed_description_ar, $price, $category_id, $image_filename, $in_stock, $is_new);

// Execute the statement and close it
$stmt->execute();
$stmt->close();

// Redirect back to the admin page
header("Location: ../admin.php?status=added#products");
exit();
?>
