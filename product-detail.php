<?php
// ======================= 1. LANGUAGE & DATABASE LOGIC =======================

// --- LANGUAGE SETUP ---
session_start();

// --- DATABASE CONNECTION & DATA FETCHING ---

$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';
$lang_file = "translations/{$lang}.json";
$text = file_exists($lang_file) ? json_decode(file_get_contents($lang_file), true) : [];

require_once 'php/db_connect.php';



// We still get the ID from the URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Use empty() which is safer for checking URL parameters
if (empty($product_id) || !is_numeric($product_id)) {
    http_response_code(400 );
    $errorTitle = $text['pdp_error_invalidRequest'] ?? '400 - Invalid Request';
    $errorMsg = $text['pdp_error_invalidRequest_msg'] ?? 'No product ID was provided.';
    die("<h1>{$errorTitle}</h1><p>{$errorMsg}</p>");
}



// This SQL assumes 'detailed_description' is a column in your 'products' table.
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $mysqli->prepare($sql);

if ($stmt === false) {
    http_response_code(500 );
    $errorTitle = $text['pdp_error_serverError'] ?? '500 - Server Error';
    $errorMsg = $text['pdp_error_serverError_msg'] ?? 'Failed to prepare the database statement.';
    die("<h1>{$errorTitle}</h1><p>{$errorMsg}</p>");
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404 );
    $errorTitle = $text['pdp_error_notFound'] ?? '404 - Product Not Found';
    $errorMsg = $text['pdp_error_notFound_msg'] ?? 'Sorry, the product you are looking for does not exist.';
    $backLink = $text['pdp_error_backLink'] ?? 'Back to Products';
    die("<h1>{$errorTitle}</h1><p>{$errorMsg}</p><a href='product.php?lang={$lang}'>{$backLink}</a>");
}

// --- Sensible defaults ---
// Normalize category and images
$product['category_name'] = $product['category_name'] ?? 'General Solutions';
$product_images = [];
if (!empty($product['image_url']) && $product['image_url'] != '0') {
    $raw = $product['image_url'];
    if (preg_match('#^https?://#i', $raw)) {
        $product_images[] = $raw;
    } elseif (strpos($raw, 'uploads/') === 0 || strpos($raw, '/uploads/') === 0) {
        $product_images[] = $raw;
    } else {
        $product_images[] = 'uploads/' . $raw;
    }
    // add placeholders for additional angles
    $product_images[] = 'https://placehold.co/1200x1200/EBF4F6/06202B?text=Angle+2';
    $product_images[] = 'https://placehold.co/1200x1200/EBF4F6/06202B?text=Angle+3';
} else {
    $product_images[] = 'https://placehold.co/1200x1200/EBF4F6/06202B?text=No+Image';
}

// Set page title for the header
$pageTitle = ($text['pdp_pageTitle'] ?? 'Product Details' ) . ' - ' . htmlspecialchars($product['name']);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?> - RAWEE</title>

    <!-- Your CSS links -->
    <link rel="stylesheet" href="css/style.css" />
    <?php if ($lang == 'ar'): ?>
      <link rel="stylesheet" href="css/rtl.css" />
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main class="product-detail-main">
        <div class="container">
            <section class="product-showcase-grid">
                <!-- Image Gallery -->
                <div class="product-gallery">
                    <div class="main-image-wrapper">
                        <img id="mainProductImage" src="<?php echo htmlspecialchars($product_images[0] ); ?>" alt="<?php echo htmlspecialchars($product['name']); ?> Main View">
                    </div>
                    <div class="thumbnail-gallery">
                        <?php foreach ($product_images as $index => $image_src): ?>
                            <img class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 src="<?php echo htmlspecialchars($image_src); ?>" 
                                 alt="Thumbnail <?php echo $index + 1; ?>" 
                                 onclick="changeImage(this, '<?php echo htmlspecialchars($image_src); ?>')">
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="product-info">
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="product-short-description"><?php echo htmlspecialchars($product['description']); ?></p>
                    
                    <div class="product-price-detail">
                        <span>$<?php echo number_format($product['price'], 2); ?></span>
                        <?php if ($product['in_stock']): ?>
                            <span class="stock-status in-stock"><i class="fas fa-check-circle"></i> <?php echo $text['pdp_stockStatus_in'] ?? 'In Stock'; ?></span>
                        <?php else: ?>
                            <span class="stock-status out-of-stock"><i class="fas fa-times-circle"></i> <?php echo $text['pdp_stockStatus_out'] ?? 'Out of Stock'; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="product-actions">
                        <div class="quantity-selector">
                            <button id="decrease-qty">-</button>
                            <input type="text" id="quantity-input" value="1" readonly>
                            <button id="increase-qty">+</button>
                        </div>
                        <button class="btn-add-to-cart-detail" onclick="addToCart(<?php echo $product['product_id']; ?>, this)" <?php echo !$product['in_stock'] ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> <?php echo $text['pdp_addToCartButton'] ?? 'Add to Cart'; ?>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Detailed Information Tabs -->
            <section class="product-details-tabs">
                <div class="tab-nav-detail">
                    <button class="tab-link-detail active" onclick="openDetailTab(event, 'description')"><?php echo $text['pdp_tab_description'] ?? 'Description'; ?></button>
                </div>

                <div id="description" class="tab-content-detail active">
                    
                                        <!-- Detailed description selection (prefer language-specific fields when available) -->
                                        <?php
                                            // Prefer language-specific fields if present; fall back to legacy fields
                                            $detailed = '';
                                            if ($lang === 'ar' && !empty($product['detailed_description_ar'])) {
                                                $detailed = $product['detailed_description_ar'];
                                            } elseif (!empty($product['detailed_description_en'])) {
                                                $detailed = $product['detailed_description_en'];
                                            } elseif (!empty($product['detailed_description'])) {
                                                $detailed = $product['detailed_description'];
                                            } else {
                                                $detailed = $product['description'] ?? '';
                                            }
                                        ?>
                                        <p><?php echo nl2br(htmlspecialchars($detailed)); ?></p>

                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/cart_ajax.js"></script>
    <script>
        // --- Image Gallery Script ---
        function changeImage(thumbElement, newSrc) {
            const mainImage = document.getElementById('mainProductImage');
            if (!mainImage) return;
            mainImage.style.opacity = 0;
            setTimeout(() => {
                mainImage.src = newSrc;
                mainImage.style.opacity = 1;
            }, 300);

            document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
            thumbElement.classList.add('active');
        }

        // --- Quantity Selector & Detail Tabs Script ---
        document.addEventListener('DOMContentLoaded', () => {
            const decreaseBtn = document.getElementById('decrease-qty');
            const increaseBtn = document.getElementById('increase-qty');
            const qtyInput = document.getElementById('quantity-input');

            if (decreaseBtn && increaseBtn && qtyInput) {
                decreaseBtn.addEventListener('click', () => {
                    let currentQty = parseInt(qtyInput.value);
                    if (currentQty > 1) {
                        qtyInput.value = currentQty - 1;
                    }
                });
                increaseBtn.addEventListener('click', () => {
                    let currentQty = parseInt(qtyInput.value);
                    qtyInput.value = currentQty + 1;
                });
            }
        });
        
        function openDetailTab(evt, tabName) {
            document.querySelectorAll('.tab-content-detail').forEach(tab => tab.style.display = 'none');
            document.querySelectorAll('.tab-link-detail').forEach(link => link.classList.remove('active'));
            
            const tabToShow = document.getElementById(tabName);
            if(tabToShow) {
                tabToShow.style.display = 'block';
            }
            
            evt.currentTarget.classList.add('active');
        }

        // Ensure the first tab is visible on load
        document.addEventListener('DOMContentLoaded', () => {
            const firstTab = document.querySelector('.tab-link-detail');
            if(firstTab) {
                firstTab.click();
            }
        });
    </script>
</body>
</html>
