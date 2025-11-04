<?php
require_once __DIR__ . '/db_connect.php'; // provides $mysqli and session

header('Content-Type: application/json; charset=utf-8');

// --- Read GET params (safe defaults) ---
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$availability = isset($_GET['availability']) ? $_GET['availability'] : 'all'; // all, available, custom_order
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured'; // price-low, price-high, name, rating, newest, featured
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 12;

$offset = ($page - 1) * $per_page;

// Build base query with joins (categories)
$sql = "SELECT p.product_id, p.name, p.description, p.price, p.image_url, p.in_stock, p.rating, p.is_new, c.slug AS category_slug, c.category_name AS category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id";

$where = [];
$params = [];
$types = "";

// Filters
if ($category !== '') {
    // Support comma-separated category slugs (e.g., aquaculture,hydroponics)
    $slugs = array_filter(array_map('trim', explode(',', $category)));
    if (count($slugs) === 1) {
        $where[] = "c.slug = ?";
        $params[] = $slugs[0];
        $types .= "s";
    } elseif (count($slugs) > 1) {
        // Build placeholders for IN (?)
        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $where[] = "c.slug IN ($placeholders)";
        foreach ($slugs as $s) { $params[] = $s; $types .= 's'; }
    }
}
if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like;
    $types .= "ss";
}
if ($max_price !== null) {
    $where[] = "p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}
if ($availability === 'available') {
    $where[] = "p.in_stock = 1";
} elseif ($availability === 'custom_order') {
    $where[] = "p.in_stock = 0";
}

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Sorting
switch ($sort) {
    case "price-low": $sql .= " ORDER BY p.price ASC"; break;
    case "price-high": $sql .= " ORDER BY p.price DESC"; break;
    case "name": $sql .= " ORDER BY p.name ASC"; break;
    case "rating": $sql .= " ORDER BY p.rating DESC"; break;
    case "newest": $sql .= " ORDER BY p.is_new DESC, p.created_at DESC"; break;
    default: $sql .= " ORDER BY p.rating DESC, p.created_at DESC"; break;
}

// Count total (for pagination)
$count_sql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
if (count($where) > 0) {
    $count_sql .= " WHERE " . implode(" AND ", $where);
}

// Prepare count statement
if ($stmt = $mysqli->prepare($count_sql)) {
    if ($types !== "") {
        // bind types - create refs
        $bind_names = [];
        $bind_names[] = $types;
        foreach ($params as $k => $param) {
            $bind_names[] = &$params[$k];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    }
    $stmt->execute();
    $stmt->bind_result($total_count);
    $stmt->fetch();
    $stmt->close();
} else {
    echo json_encode(["error" => "count_prepare_failed"]);
    exit;
}

// Add limit/offset
$sql .= " LIMIT ?, ?";
$params_with_limit = $params;
$types_with_limit = $types . "ii";
$params_with_limit[] = $offset;
$params_with_limit[] = $per_page;

// Prepare main query
if ($stmt = $mysqli->prepare($sql)) {
    $bind_names = [];
    $bind_names[] = $types_with_limit;
    foreach ($params_with_limit as $k => $param) {
        // need references
        $bind_names[] = &$params_with_limit[$k];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();

    echo json_encode([
        "page" => $page,
        "per_page" => $per_page,
        "total" => intval($total_count),
        "total_pages" => intval(ceil($total_count / $per_page)),
        "products" => $items
    ]);
    exit;
} else {
    echo json_encode(["error" => "prepare_failed", "sql" => $sql]);
    exit;
}
?>
