
<?php
include __DIR__ . '/php/db_connect.php';
// ======================= LANGUAGE LOGIC (MUST BE AT THE TOP) =======================
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) ? $_GET['lang'] : 'en';
$lang_file = "translations/{$lang}.json";
$text = file_exists($lang_file) ? json_decode(file_get_contents($lang_file), true) : [];

// The page title uses the specific 'product_pageTitle' key.
$pageTitle = $text['product_pageTitle'] ?? 'Our Products - RAWEE Smart Farm IoT Solutions';

// Shared form handler path for contact form
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
  $basePath = '';
}
$sendMessageAction = $basePath . '/php/send_message.php';
// ========================================================================================
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo ($lang == 'ar') ? 'rtl' : 'ltr'; ?>">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <?php
      $styleCssVer = @filemtime(__DIR__ . '/css/style.css') ?: time();
      $productCssVer = @filemtime(__DIR__ . '/css/product.css') ?: time();
    ?>
    <link rel="stylesheet" href="css/style.css?v=<?php echo $styleCssVer; ?>" />
    <link rel="stylesheet" href="css/product.css?v=<?php echo $productCssVer; ?>" />
    <?php if ($lang == 'ar' ): ?>
      <link rel="stylesheet" href="css/rtl.css" />
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
 
  </head>
  <body>
    <?php
        // The header is included here. It will use the $lang and $text variables.
        include __DIR__ . '/includes/header.php';
    ?>
    
    <!-- Hero Section -->
    <section class="hero_section text-center p-5 mb-5 d-flex justify-content-center align-items-center" style="min-height: 600px; background-color: #077a7d;color:white">
      <div class="hero-content">
        <h1 class="hero-title"><?php echo $text['product_heroTitle'] ?? 'RAWEE Products'; ?></h1>
        <p class="hero-subtitle lead">
          <?php echo $text['product_heroSubtitle'] ?? 'Automated 24/7 monitoring...'; ?>
        </p>
        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-number">100+</div>
            <div class="stat-label"><?php echo $text['product_stat1_label'] ?? 'Sensors Deployed'; ?></div>
          </div>
          <div class="stat-item">
            <div class="stat-number">20+</div>
            <div class="stat-label"><?php echo $text['product_stat2_label'] ?? 'Farms Automated'; ?></div>
          </div>
          <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div class="stat-label"><?php echo $text['product_stat3_label'] ?? 'Monitoring & Support'; ?></div>
          </div>
        </div>
      </div>
      <div class="floating-elements">
        <div class="floating-iot iot-1">📡</div>
        <div class="floating-iot iot-2">💧</div>
        <div class="floating-iot iot-3">☀️</div>
      </div>
    </section>

    <main class="container-fluid">
    
      <!-- Products Section -->
      <section class="container my-5" id="productsSection">
        <div class="row">
          <!-- Sidebar Filters -->
          <aside class="col-lg-3 col-md-4 my-4" id="filtersSidebar">
            <div class="filter-card shadow-lg">
              <div class="filter-header">
                <h5><i class="fas fa-filter me-2"></i><?php echo $text['product_filter_title'] ?? 'Filter'; ?></h5>
                <div class="filter-header-actions">
                  <button class="btn btn-sm btn-outline-secondary" id="clearFilters">
                    <i class="fas fa-times"></i> <?php echo $text['product_filter_clear'] ?? 'Clear'; ?>
                  </button>
                  <button class="btn btn-sm btn-light mobile-close-filters" id="closeFiltersBtn" aria-label="Close filters">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>

              <div class="search-section mb-4">
                <div class="search-input-wrapper">
                  <i class="fas fa-search search-icon"></i>
                  <input type="text" class="form-control search-input" placeholder="<?php echo $text['product_filter_searchPlaceholder'] ?? 'Search solutions...'; ?>" id="searchInput" />
                </div>
              </div>

              <div class="filter-section mb-4">
                <h6 class="filter-title"><i class="fas fa-tags me-2"></i><?php echo $text['product_filter_typeTitle'] ?? 'Solution Type'; ?></h6>
                <div class="filter-options" id="categoryFilters">
                  <?php
                    // Load categories dynamically from DB so filters match admin-configured categories
                    $cats_stmt = $mysqli->query("SELECT category_id, category_name, slug FROM categories ORDER BY category_id ASC");
                    if ($cats_stmt && $cats_stmt->num_rows > 0) {
                      $idx = 1;
                      while ($cat = $cats_stmt->fetch_assoc()) {
                        $chkId = 'cat_filter_' . intval($cat['category_id']);
                        $label = htmlspecialchars($cat['category_name']);
                        $value = htmlspecialchars($cat['slug']);
                        echo '<div class="form-check custom-checkbox">';
                        echo "<input class=\"form-check-input\" type=\"checkbox\" id=\"$chkId\" value=\"$value\" />";
                        echo "<label class=\"form-check-label\" for=\"$chkId\"><i class=\"fas fa-tag me-2\"></i>$label</label>";
                        echo '</div>';
                        $idx++;
                      }
                    } else {
                      echo '<p class="text-muted">No categories configured.</p>';
                    }
                  ?>
                </div>
              </div>

              <div class="filter-section mb-4">
                <h6 class="filter-title"><i class="fas fa-dollar-sign me-2"></i><?php echo $text['product_filter_rangeTitle'] ?? 'Investment Range'; ?></h6>
                <div class="price-range-wrapper">
                  <input type="range" class="form-range" id="priceRange" min="0" max="1000" value="500" />
                  <div class="price-display">
                    <span>$0</span>
                    <span id="priceValue">$500</span>
                    <span>$1000+</span>
                  </div>
                </div>
              </div>

              <div class="filter-section mb-4">
                <h6 class="filter-title"><i class="fas fa-check-circle me-2"></i><?php echo $text['product_filter_availabilityTitle'] ?? 'Availability'; ?></h6>
                <div class="filter-options">
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="stock" id="allStock" value="all" checked />
                    <label class="form-check-label" for="allStock"><?php echo $text['product_filter_availability1'] ?? 'All Solutions'; ?></label>
                  </div>
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="stock" id="inStock" value="available" />
                    <label class="form-check-label" for="inStock"><span class="badge bg-success me-2">●</span><?php echo $text['product_filter_availability2'] ?? 'Available'; ?></label>
                  </div>
                  <div class="form-check custom-radio">
                    <input class="form-check-input" type="radio" name="stock" id="outStock" value="custom_order" />
                    <label class="form-check-label" for="outStock"><span class="badge bg-info me-2">●</span><?php echo $text['product_filter_availability3'] ?? 'UnAvailable'; ?></label>
                  </div>
                </div>
              </div>

              <div class="filter-section mb-4">
                <h6 class="filter-title"><i class="fas fa-sort me-2"></i><?php echo $text['product_filter_sortTitle'] ?? 'Sort by'; ?></h6>
                <select class="form-select custom-select" id="sortSelect">
                  <option value="featured"><?php echo $text['product_filter_sort1'] ?? 'Featured'; ?></option>
                  <option value="price-low"><?php echo $text['product_filter_sort2'] ?? 'Investment: Low to High'; ?></option>
                  <option value="price-high"><?php echo $text['product_filter_sort3'] ?? 'Investment: High to Low'; ?></option>
                  <option value="name"><?php echo $text['product_filter_sort4'] ?? 'Name A-Z'; ?></option>
                  <option value="rating"><?php echo $text['product_filter_sort5'] ?? 'Highest Rated'; ?></option>
                  <option value="newest"><?php echo $text['product_filter_sort6'] ?? 'Newest First'; ?></option>
                </select>
              </div>

              <button class="btn btn-primary w-100 btn-apply-filters" id="applyFilters">
                <i class="fas fa-check me-2"></i><?php echo $text['product_filter_applyButton'] ?? 'Apply Filters'; ?>
              </button>
            </div>
          </aside>

          <!-- Product Grid -->
          <div class="col-lg-9 col-md-8">
            <div class="results-header mb-4">
              <div class="d-flex justify-content-between align-items-center">
                <div class="results-info">
                  <h4 class="mb-1"><?php echo $text['product_grid_title'] ?? 'IoT Farm Solutions'; ?></h4>
                  <p class="text-muted mb-0">
                    <?php echo $text['product_grid_showing'] ?? 'Showing'; ?> <span id="resultsCount"><?php echo isset($totalResults ) ? $totalResults : '0'; ?></span> <?php echo $text['product_grid_of'] ?? 'of'; ?>
                    <span id="totalResults"><?php echo isset($totalResults) ? $totalResults : '0'; ?></span> <?php echo $text['product_grid_solutions'] ?? 'solutions'; ?>
                  </p>
                </div>
                <div class="view-toggle">
                  <button class="btn btn-outline-secondary btn-sm active" id="gridView"><i class="fas fa-th"></i></button>
                  <button class="btn btn-outline-secondary btn-sm" id="listView"><i class="fas fa-list"></i></button>
                </div>
              </div>
            </div>

            <div class="products-container" id="productsContainer">
  <div class="row g-4" id="productGrid">
    <?php
    if (isset($mysqli)) {
        $stmt = $mysqli->query("SELECT p.*, c.category_name 
                                FROM products p 
                                LEFT JOIN categories c ON p.category_id = c.category_id 
                                ORDER BY p.created_at DESC LIMIT 48");
        if ($stmt && $stmt->num_rows > 0) {
            while ($product = $stmt->fetch_assoc()) {
        // Normalize image source: product.image_url may be a filename, an uploads/ path, or a full URL
        $imgSrc = 'images/default_product.png';
        if (!empty($product['image_url']) && $product['image_url'] != '0') {
          $raw = $product['image_url'];
          // If full URL
          if (preg_match('#^https?://#i', $raw)) {
            $imgSrc = $raw;
          } elseif (strpos($raw, 'uploads/') === 0 || strpos($raw, '/uploads/') === 0) {
            $imgSrc = $raw;
          } else {
            // treat as filename and prepend uploads/
            $imgSrc = 'uploads/' . htmlspecialchars($raw);
          }
        }

                echo '<div class="col-md-6 col-lg-4">';
                echo '  <div class="product-card-themed shadow-sm rounded-4 p-3 h-100">';

                // Image + Stock badge
                echo '    <div class="product-image-container">';
                echo '      <img src="' . $imgSrc . '" class="img-fluid rounded-3 mb-3" alt="' . htmlspecialchars($product['name']) . '">';
                echo '      <div class="product-stock-badge ' . ($product['in_stock'] ? 'in-stock' : 'out-of-stock') . '">';
                echo            ($product['in_stock'] ? 'In Stock' : 'Out of Stock');
                echo '      </div>';
                echo '    </div>';

                // Product content
                echo '    <div class="product-content">';
                echo '      <span class="product-category">' . htmlspecialchars($product['category_name'] ?? 'Uncategorized') . '</span>';
                echo '      <h5 class="product-title-themed">' . htmlspecialchars($product['name']) . '</h5>';
                echo '      <p class="product-price-themed">$' . number_format($product['price'], 2) . '</p>';
                echo '    </div>';

                // Actions (Details + Add to Cart)
echo '<div class="product-actions">';
echo '  <a href="product-detail.php?id=' . intval($product['product_id']) . '&lang=' . $lang . '" class="btn-details">'
        . ($text['product_grid_viewDetails'] ?? 'View Details') . '</a>';
echo '  <button class="btn-add-to-cart" onclick="addToCart(' . intval($product['product_id']) . ', this)">';
echo '    <i class="fas fa-shopping-cart"></i> ' . ($text['product_grid_addToCart'] ?? 'Add to Cart');
echo '  </button>';
echo '</div>';


                echo '  </div>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-muted">' . ($text['product_grid_noProducts'] ?? 'No products found.') . '</p>';
        }
    } else {
        echo '<p class="text-danger">' . ($text['product_grid_dbError'] ?? 'Database connection not established.') . '</p>';
    }
    ?>
  </div>
</div>


            <nav aria-label="Solution pagination" class="mt-5">
              <div class="pagination-wrapper">
                <div class="pagination-info">
                  <span><?php echo $text['product_pagination_page'] ?? 'Page'; ?> <span id="currentPage">1</span> <?php echo $text['product_grid_of'] ?? 'of'; ?> <span id="totalPages">4</span></span>
                </div>
                <ul class="pagination pagination-custom justify-content-center" id="pagination"></ul>
                <div class="items-per-page">
                  <label for="itemsPerPage" class="form-label"><?php echo $text['product_pagination_perPage'] ?? 'Solutions per page:'; ?></label>
                  <select class="form-select form-select-sm" id="itemsPerPage">
                    <option value="6">6</option>
                    <option value="12" selected>12</option>
                    <option value="24">24</option>
                    <option value="48">48</option>
                  </select>
                </div>
              </div>
            </nav>
          </div>
        </div>
      </section>

      <!-- Customize Section -->
      <section class="customize-section">
        <div class="customize-bg">
          <div class="container text-center">
            <div class="customize-content">
              <h2 class="customize-title">
                <span class="text-gradient"><?php echo $text['product_customize_title_gradient'] ?? 'Tailor'; ?></span> <?php echo $text['product_customize_title'] ?? 'Your Smart Farm Solution'; ?>
              </h2>
              <p class="customize-subtitle">
                <?php echo $text['product_customize_subtitle'] ?? 'Looking for something unique?...'; ?>
              </p>
              <div class="customize-features">
                <div class="feature-item"><i class="fas fa-cogs"></i><span><?php echo $text['product_customize_feature1'] ?? 'Custom Configuration'; ?></span></div>
                <div class="feature-item"><i class="fas fa-chart-line"></i><span><?php echo $text['product_customize_feature2'] ?? 'Real-time Analytics'; ?></span></div>
                <div class="feature-item"><i class="fas fa-headset"></i><span><?php echo $text['product_customize_feature3'] ?? '24/7 Expert Support'; ?></span></div>
              </div>
              <a href="#custom-form" class="btn btn-custom-primary btn-lg">
                <i class="fas fa-hand-sparkles me-2"></i><?php echo $text['product_customize_button'] ?? 'Start Customizing'; ?>
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- Contact Form -->
      <section id="custom-form" class="contact-section">
        <div class="container">
          <div class="row align-items-center g-5">
            <div class="col-lg-7">
              <div class="form-container">
                <div class="form-header">
                  <h3 class="form-title"><?php echo $text['product_form_title'] ?? 'Contact Our IoT Farm Specialists'; ?></h3>
                  <p class="form-subtitle"><?php echo $text['product_form_subtitle'] ?? 'Tell us about your farm needs...'; ?></p>
                </div>
                <?php if (isset($_GET['status'])):
                  $isSuccess = $_GET['status'] === 'success';
                  $statusMessage = $isSuccess
                    ? ($text['product_form_success'] ?? 'Thanks! Our specialists will contact you soon.')
                    : ($text['product_form_error'] ?? 'We could not submit your request. Please try again.');
                ?>
                  <div class="form-status" style="padding:12px 16px;margin-bottom:1.5rem;border-radius:10px;background:<?php echo $isSuccess ? '#e6f4ea' : '#fdeaea'; ?>;color:<?php echo $isSuccess ? '#0f5132' : '#842029'; ?>;font-weight:500;">
                    <?php echo $statusMessage; ?>
                  </div>
                <?php endif; ?>
                <form class="custom-form" id="contactForm" action="<?php echo $sendMessageAction; ?>" method="POST">
                  <div class="row g-3">
                    <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" id="fullName" name="name" placeholder="Full Name" required /><label for="fullName"><i class="fas fa-user me-2"></i><?php echo $text['product_form_nameLabel'] ?? 'Full Name'; ?></label></div></div>
                    <div class="col-md-6"><div class="form-floating"><input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone / WhatsApp" required /><label for="phone"><i class="fas fa-phone me-2"></i><?php echo $text['product_form_phoneLabel'] ?? 'Phone / WhatsApp'; ?></label></div></div>
                    <div class="col-12"><div class="form-floating"><input type="email" class="form-control" id="email" name="email" placeholder="Email" /><label for="email"><i class="fas fa-envelope me-2"></i><?php echo $text['product_form_emailLabel'] ?? 'Email (optional)'; ?></label></div></div>
                    <div class="col-md-6"><div class="form-floating"><select class="form-select" id="farmSize" name="farm_size" required><option value=""><?php echo $text['product_form_farmSizeOption0'] ?? 'Select farm size'; ?></option><option value="small"><?php echo $text['product_form_farmSizeOption1'] ?? 'Small (1-10 acres)'; ?></option><option value="medium"><?php echo $text['product_form_farmSizeOption2'] ?? 'Medium (10-50 acres)'; ?></option><option value="large"><?php echo $text['product_form_farmSizeOption3'] ?? 'Large (50-200 acres)'; ?></option><option value="enterprise"><?php echo $text['product_form_farmSizeOption4'] ?? 'Enterprise (200+ acres)'; ?></option></select><label for="farmSize"><i class="fas fa-seedling me-2"></i><?php echo $text['product_form_farmSizeLabel'] ?? 'Farm Size'; ?></label></div></div>
                    <div class="col-md-6"><div class="form-floating"><select class="form-select" id="systemType" name="solution_type" required><option value=""><?php echo $text['product_form_solutionTypeOption0'] ?? 'Select solution type'; ?></option><option value="aquaculture"><?php echo $text['product_form_solutionTypeOption1'] ?? 'Aquaculture Monitoring'; ?></option><option value="hydroponics"><?php echo $text['product_form_solutionTypeOption2'] ?? 'Hydroponics Automation'; ?></option><option value="greenhouse"><?php echo $text['product_form_solutionTypeOption3'] ?? 'Greenhouse Climate Control'; ?></option><option value="field_crops"><?php echo $text['product_form_solutionTypeOption4'] ?? 'Field Crop Optimization'; ?></option><option value="other"><?php echo $text['product_form_solutionTypeOption5'] ?? 'Other IoT Solution'; ?></option></select><label for="systemType"><i class="fas fa-microchip me-2"></i><?php echo $text['product_form_solutionTypeLabel'] ?? 'Solution Type'; ?></label></div></div>
                    <div class="col-12"><div class="form-floating"><textarea class="form-control" id="description" name="message" placeholder="Description" style="height: 120px"></textarea><label for="description"><i class="fas fa-edit me-2"></i><?php echo $text['product_form_needsLabel'] ?? 'Describe your specific needs'; ?></label></div></div>
                    <!-- Identify form + redirect back to this section with language -->
                    <input type="hidden" name="form_source" value="product" />
                    <input type="hidden" name="subject" value="Product page custom solution request" />
                    <input type="hidden" name="redirect" value="product.php?lang=<?php echo $lang; ?>#custom-form" />
                    <div class="col-12"><button type="submit" class="btn btn-primary btn-lg w-100 btn-submit"><span class="btn-text"><i class="fas fa-paper-plane me-2"></i><?php echo $text['product_form_submitButton'] ?? 'Submit Request'; ?></span><span class="btn-loading d-none"><i class="fas fa-spinner fa-spin me-2"></i><?php echo $text['product_form_sendingButton'] ?? 'Sending...'; ?></span></button></div>
                  </div>
                </form>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="contact-visual">
                <div class="image-container">
                  <img
                    src="images/hero.avif"
                    class="img-fluid rounded-4 shadow-lg"
                    alt="Smart farm with IoT sensors"
                  />
                  <div class="image-overlay">
                    <div class="overlay-content">
                      <h5><?php echo $text['product_visual_title'] ?? 'Precision Agriculture'; ?></h5>
                      <p><?php echo $text['product_visual_subtitle'] ?? 'Leveraging IoT for optimal farm productivity'; ?></p>
                    </div>
                  </div>
                </div>
                <div class="contact-info mt-4">
                  <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                      <strong><?php echo $text['product_visual_info1_title'] ?? 'Response Time'; ?></strong>
                      <span><?php echo $text['product_visual_info1_desc'] ?? 'Within 2 hours'; ?></span>
                    </div>
                  </div>
                  <div class="info-item">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                      <strong><?php echo $text['product_visual_info2_title'] ?? 'Warranty'; ?></strong>
                      <span><?php echo $text['product_visual_info2_desc'] ?? '2 years full coverage'; ?></span>
                    </div>
                  </div>
                  <div class="info-item">
                    <i class="fas fa-users"></i>
                    <div>
                      <strong><?php echo $text['product_visual_info3_title'] ?? 'Expert Team'; ?></strong>
                      <span><?php echo $text['product_visual_info3_desc'] ?? 'Certified IoT farm specialists'; ?></span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Sticky Filter Button (visible on small screens) -->
    <button type="button" id="stickyFilterBtn" class="sticky-filter-btn" aria-label="Open filters">
      <i class="fas fa-filter"></i>
    </button>

    <!-- Filters Overlay -->
    <div class="filters-overlay" id="filtersOverlay" aria-hidden="true"></div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body text-center p-4">
            <div class="success-icon mb-3">
              <i class="fas fa-check-circle"></i>
            </div>
            <h5><?php echo $text['product_modal_successTitle'] ?? 'Request Submitted Successfully!'; ?></h5>
            <p class="text-muted">
              <?php echo $text['product_modal_successSubtitle'] ?? 'Our team will contact you within 2 hours...'; ?>
            </p>
            <button
              type="button"
              class="btn btn-primary"
              data-bs-dismiss="modal"
            >
              <?php echo $text['product_modal_successButton'] ?? 'Great!'; ?>
            </button>
          </div>
        </div>
      </div>
    </div>
  <script src="js/cart_ajax.js"></script>
  <script src="js/product.js"></script>
    <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
</html>

