// Enhanced RAWEE Products JavaScript

// products now come from the server via php/get_products.php
// Removed hard-coded sample products so admin-inserted products are used instead.
let productsData = [];

// Application state
let currentPage = 1;
let itemsPerPage = 12;
let filteredProducts = [];
let totalPagesGlobal = 1;
let totalResultsGlobal = 0;
let currentFilters = {
  search: "",
  categories: [],
  priceRange: 1000,
  availability: "all",
  sortBy: "featured"
};

// DOM elements
const productGrid = document.getElementById("productGrid");
const pagination = document.getElementById("pagination");
const searchInput = document.getElementById("searchInput");
// category checkboxes are rendered dynamically inside #categoryFilters
const categoryCheckboxes = () => document.querySelectorAll('#categoryFilters input[type="checkbox"]');
const priceRange = document.getElementById("priceRange");
const priceValue = document.getElementById("priceValue");
const availabilityRadios = document.querySelectorAll("input[name=\"stock\"]");
const sortSelect = document.getElementById("sortSelect");
const itemsPerPageSelect = document.getElementById("itemsPerPage");
const resultsCount = document.getElementById("resultsCount");
const totalResults = document.getElementById("totalResults");
const currentPageSpan = document.getElementById("currentPage");
const totalPagesSpan = document.getElementById("totalPages");
const applyFiltersBtn = document.getElementById("applyFilters");
const clearFiltersBtn = document.getElementById("clearFilters");
const loadingScreen = document.getElementById("loading-screen");

// Initialize the application
document.addEventListener("DOMContentLoaded", function() {
  // Hide loading screen
  setTimeout(() => {
    if (loadingScreen) loadingScreen.classList.add("hidden");
  }, 1500);

  // Initialize components
  initializeEventListeners();
  updatePriceDisplay();
  // Fetch first page of products from server
  fetchProducts(1);
  
  // Add scroll animations
  addScrollAnimations();
  
  // Initialize header scroll effect
  initializeHeaderScroll();
});

// Event listeners
function initializeEventListeners() {
  // Search input
  searchInput.addEventListener("input", debounce(handleSearch, 300));
  
  // Category checkboxes (dynamic NodeList getter)
  Array.from(categoryCheckboxes()).forEach(checkbox => {
    checkbox.addEventListener("change", handleCategoryChange);
  });
  
  // Price range
  priceRange.addEventListener("input", handlePriceChange);
  
  // Availability radios
  availabilityRadios.forEach(radio => {
    radio.addEventListener("change", handleAvailabilityChange);
  });
  
  // Sort select
  sortSelect.addEventListener("change", handleSortChange);
  
  // Items per page
  itemsPerPageSelect.addEventListener("change", handleItemsPerPageChange);
  
  // Filter buttons
  applyFiltersBtn.addEventListener("click", applyFilters);
  clearFiltersBtn.addEventListener("click", () => { clearFilters(); fetchProducts(1); });
  
  // Contact form
  const contactForm = document.getElementById("contactForm");
  if (contactForm) {
    contactForm.addEventListener("submit", handleContactSubmit);
  }
  
  // Mobile menu toggle
  const toggle = document.querySelector(".toggle");
  const mainNav = document.querySelector(".main-nav");
  if (toggle && mainNav) {
    toggle.addEventListener("click", () => {
      toggle.classList.toggle("active");
      mainNav.classList.toggle("active");
    });
  }
  
  // View toggle
  const gridView = document.getElementById("gridView");
  const listView = document.getElementById("listView");
  if (gridView && listView) {
    gridView.addEventListener("click", () => setView("grid"));
    listView.addEventListener("click", () => setView("list"));
  }

  // ======================= RESPONSIVE FILTERS PANEL =======================
  const filtersSidebar = document.getElementById("filtersSidebar");
  const filtersOverlay = document.getElementById("filtersOverlay");
  const stickyFilterBtn = document.getElementById("stickyFilterBtn");
  const closeFiltersBtn = document.getElementById("closeFiltersBtn");

  // Open filters panel
  const openFilters = () => {
    if (!filtersSidebar || !filtersOverlay) return;
    filtersSidebar.classList.add("open");
    filtersOverlay.classList.add("active");
    document.body.style.overflow = "hidden";
    // Hide sticky button while panel is open
    if (stickyFilterBtn) stickyFilterBtn.classList.remove("visible");
  };

  // Close filters panel
  const closeFilters = () => {
    if (!filtersSidebar || !filtersOverlay) return;
    filtersSidebar.classList.remove("open");
    filtersOverlay.classList.remove("active");
    document.body.style.overflow = "";
    // Re-show sticky button immediately if on small screen and in products section
    const isSmallScreen = window.matchMedia("(max-width: 1079px)").matches;
    const productsSection = document.getElementById("productsSection") || 
                            document.querySelector(".products-container") || 
                            document.querySelector("section.container.my-5");
    if (stickyFilterBtn && isSmallScreen && productsSection) {
      // Check if products section is visible in viewport
      const rect = productsSection.getBoundingClientRect();
      const isInViewport = rect.top < window.innerHeight && rect.bottom > 0;
      if (isInViewport) {
        stickyFilterBtn.classList.add("visible");
      }
    }
  };

  // Bind sticky filter button
  if (stickyFilterBtn) {
    stickyFilterBtn.addEventListener("click", openFilters);
  }

  // Bind close button inside sidebar
  if (closeFiltersBtn) {
    closeFiltersBtn.addEventListener("click", closeFilters);
  }

  // Bind overlay click to close
  if (filtersOverlay) {
    filtersOverlay.addEventListener("click", closeFilters);
  }

  // Close on ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && filtersSidebar && filtersSidebar.classList.contains("open")) {
      closeFilters();
    }
  });

  // Show/hide sticky button based on products section visibility and screen size
  if (stickyFilterBtn && "IntersectionObserver" in window) {
    const productsSection = document.getElementById("productsSection") || 
                            document.querySelector(".products-container") || 
                            document.querySelector("section.container.my-5");
    
    if (productsSection) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          const isSmallScreen = window.matchMedia("(max-width: 1079px)").matches;
          const panelOpen = filtersSidebar && filtersSidebar.classList.contains("open");
          
          if (entry.isIntersecting && isSmallScreen && !panelOpen) {
            stickyFilterBtn.classList.add("visible");
          } else {
            stickyFilterBtn.classList.remove("visible");
          }
        });
      }, { threshold: 0.1, rootMargin: "-10% 0px -30% 0px" });
      
      observer.observe(productsSection);

      // Hide button on resize to desktop width
      window.addEventListener("resize", debounce(() => {
        const isSmallScreen = window.matchMedia("(max-width: 1079px)").matches;
        if (!isSmallScreen) {
          stickyFilterBtn.classList.remove("visible");
          closeFilters(); // Auto-close panel when resizing to desktop
        }
      }, 150));
    }
  }
}

// Search functionality
function handleSearch(event) {
  currentFilters.search = event.target.value.toLowerCase();
  fetchProducts(1, { scroll: true });
}

// Category filter
function handleCategoryChange() {
  const selectedCategories = Array.from(categoryCheckboxes())
    .filter(cb => cb.checked)
    .map(cb => cb.value);
  currentFilters.categories = selectedCategories;
  fetchProducts(1, { scroll: true });
}

// Price range filter
function handlePriceChange(event) {
  currentFilters.priceRange = parseInt(event.target.value);
  updatePriceDisplay();
  // update server-side filter
  fetchProducts(1, { scroll: true });
}

function updatePriceDisplay() {
  if (priceValue) {
    priceValue.textContent = `$${currentFilters.priceRange}`;
  }
}

// Availability filter
function handleAvailabilityChange(event) {
  currentFilters.availability = event.target.value;
  fetchProducts(1, { scroll: true });
}

// Sort functionality
function handleSortChange(event) {
  currentFilters.sortBy = event.target.value;
  fetchProducts(1, { scroll: true });
}

// Items per page
function handleItemsPerPageChange(event) {
  itemsPerPage = parseInt(event.target.value);
  currentPage = 1;
  fetchProducts(1, { scroll: true });
}

// Apply filters
function applyFilters() {
  // Server-driven filters: fetch products from backend
  fetchProducts(1, { scroll: true });
}

// Sort products
function sortProducts() {
  switch (currentFilters.sortBy) {
    case "price-low":
      filteredProducts.sort((a, b) => a.price - b.price);
      break;
    case "price-high":
      filteredProducts.sort((a, b) => b.price - a.price);
      break;
    case "name":
      filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
      break;
    case "rating":
      filteredProducts.sort((a, b) => b.rating - a.rating);
      break;
    case "newest":
      filteredProducts.sort((a, b) => b.isNew - a.isNew);
      break;
    default: // featured
      filteredProducts.sort((a, b) => b.rating - a.rating);
  }
}

// Clear filters
function clearFilters() {
  // Reset form elements
  searchInput.value = "";
  Array.from(categoryCheckboxes()).forEach(cb => cb.checked = false);
  priceRange.value = 1000;
  document.getElementById("allStock").checked = true;
  sortSelect.value = "featured";
  
  // Reset filters object
  currentFilters = {
    search: "",
    categories: [],
    priceRange: 1000,
    availability: "all",
    sortBy: "featured"
  };
  
  updatePriceDisplay();
  applyFilters();
}

// Render products
function renderProducts() {
  const productsToShow = filteredProducts;
  
  if (!productsToShow || productsToShow.length === 0) {
    productGrid.innerHTML = `
      <div class="col-12 text-center py-5">
        <div class="no-results">
          <i class="fas fa-search fa-3x text-muted mb-3"></i>
          <h4>No solutions found</h4>
          <p class="text-muted">Try adjusting your filters or search terms</p>
          <button class="btn btn-primary" onclick="clearFilters()">Clear Filters</button>
        </div>
      </div>
    `;
    return;
  }
  
  productGrid.innerHTML = productsToShow.map(product => createProductCard(product)).join("");
  
  // Add staggered animation to cards
  const cards = productGrid.querySelectorAll(".product-card");
  cards.forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`;
    card.classList.add("slide-up");
  });
}

// Create product card HTML
function createProductCard(product) {
  // Backend uses product_id, image_url, in_stock (0/1), is_new
  const inStock = product.in_stock === 1 || product.in_stock === '1' || product.in_stock === true;
  const stockBadge = inStock
    ? "<span class=\"badge bg-success\">Available</span>"
    : "<span class=\"badge bg-info\">Custom Order</span>";

  const newBadge = (product.is_new === 1 || product.is_new === '1' || product.is_new === true)
    ? "<span class=\"badge bg-warning text-dark ms-2\">New</span>"
    : "";

  const features = (product.features || []).map(feature => 
    `<span class=\"feature-tag\">${feature}</span>`
  ).join("");

  const rating = parseFloat(product.rating) || 0;
  const stars = "★".repeat(Math.floor(rating)) + 
                (rating % 1 ? "☆" : "") + 
                "☆".repeat(5 - Math.ceil(rating));

  // Normalize image source from API: could be filename, 'uploads/...' path or full URL
  // Use a relative fallback so it resolves under the current site root (e.g., /rawee/)
  let imageSrc = 'images/hero.avif';
  if (product.image_url) {
    const raw = product.image_url;
    if (/^https?:\/\//i.test(raw)) {
      imageSrc = raw;
    } else {
      // Make relative to current app root by stripping any leading '/'
      const normalized = String(raw).replace(/^\/+/, '');
      if (normalized.indexOf('uploads/') === 0) {
        imageSrc = normalized;
      } else if (normalized.trim() !== '') {
        imageSrc = 'uploads/' + normalized;
      }
    }
  } else if (product.image) {
    // Also normalize any leading slash here to keep paths relative to app root
    imageSrc = String(product.image).replace(/^\/+/, '');
  }

  // Match server-side 'product-card-themed' structure so styles and cart logic are consistent
  const productCategory = product.category_name || product.category_slug || 'Uncategorized';
  const priceFormatted = product.price ? Number(product.price).toFixed(2) : '0.00';
  return `
    <div class="col-md-6 col-lg-4">
      <div class="product-card-themed shadow-sm rounded-4 p-3 h-100">
        <div class="product-image-container">
          <img src="${imageSrc}" class="img-fluid rounded-3 mb-3" alt="${escapeHtml(product.name)}">
          <div class="product-stock-badge ${inStock ? 'in-stock' : 'out-of-stock'}">
            ${inStock ? 'In Stock' : 'Out of Stock'}
          </div>
        </div>
        <div class="product-content">
          <span class="product-category">${escapeHtml(productCategory)}</span>
          <h5 class="product-title-themed">${escapeHtml(product.name)}</h5>
          <p class="product-price-themed">$${priceFormatted}</p>
        </div>
        <div class="product-actions">
          <a href="product-detail.php?id=${product.product_id}&lang=${document.documentElement.lang || 'en'}" class="btn-details">View Details</a>
          <button class="btn-add-to-cart" onclick="addToCart(${product.product_id}, this)">
            <i class="fas fa-shopping-cart"></i> Add to Cart
          </button>
        </div>
      </div>
    </div>
  `;
}

// Simple HTML escape for small interpolations
function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

// Render pagination
function renderPagination() {
  const totalPages = totalPagesGlobal || 1;

  if (totalPages <= 1) {
    pagination.innerHTML = "";
    if (totalPagesSpan) totalPagesSpan.textContent = totalPages;
    if (currentPageSpan) currentPageSpan.textContent = currentPage;
    return;
  }

  let paginationHTML = "";

  // Previous button
  paginationHTML += `
    <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
      <a class="page-link" href="#" onclick="changePage(${currentPage - 1})" aria-label="Previous">
        <i class="fas fa-chevron-left"></i>
      </a>
    </li>
  `;

  // Page numbers
  const startPage = Math.max(1, currentPage - 2);
  const endPage = Math.min(totalPages, currentPage + 2);

  if (startPage > 1) {
    paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1)">1</a></li>`;
    if (startPage > 2) {
      paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    paginationHTML += `
      <li class="page-item ${i === currentPage ? "active" : ""}">
        <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
      </li>
    `;
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }
    paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a></li>`;
  }

  // Next button
  paginationHTML += `
    <li class="page-item ${currentPage === totalPages ? "disabled" : ""}">
      <a class="page-link" href="#" onclick="changePage(${currentPage + 1})" aria-label="Next">
        <i class="fas fa-chevron-right"></i>
      </a>
    </li>
  `;

  pagination.innerHTML = paginationHTML;

  // Update page info
  if (totalPagesSpan) totalPagesSpan.textContent = totalPages;
  if (currentPageSpan) currentPageSpan.textContent = currentPage;
}

// Change page
function changePage(page) {
  const totalPages = totalPagesGlobal || 1;

  if (page < 1 || page > totalPages || page === currentPage) {
    return;
  }
  // Fetch the requested page from server
  fetchProducts(page, { scroll: true });
  // Scroll to top of products after fetch completes (fetchProducts will render and scroll)
}

// Update results info
function updateResultsInfo() {
  const startIndex = (currentPage - 1) * itemsPerPage + 1;
  const endIndex = Math.min(currentPage * itemsPerPage, totalResultsGlobal || 0);

  if (resultsCount) resultsCount.textContent = `${startIndex}-${endIndex}`;
  if (totalResults) totalResults.textContent = totalResultsGlobal || 0;
}

// View toggle
function setView(viewType) {
  const gridBtn = document.getElementById("gridView");
  const listBtn = document.getElementById("listView");
  
  if (viewType === "grid") {
    gridBtn.classList.add("active");
    listBtn.classList.remove("active");
    productGrid.className = "row g-4";
  } else {
    listBtn.classList.add("active");
    gridBtn.classList.remove("active");
    productGrid.className = "row g-2";
    // Add list view styling if needed
  }
}

// Contact form submission
function handleContactSubmit(event) {
  event.preventDefault();
  
  const submitBtn = event.target.querySelector(".btn-submit");
  const btnText = submitBtn.querySelector(".btn-text");
  const btnLoading = submitBtn.querySelector(".btn-loading");
  
  // Show loading state
  btnText.classList.add("d-none");
  btnLoading.classList.remove("d-none");
  submitBtn.disabled = true;
  
  // Simulate form submission
  setTimeout(() => {
    // Hide loading state
    btnText.classList.remove("d-none");
    btnLoading.classList.add("d-none");
    submitBtn.disabled = false;
    
    // Show success modal
    const successModal = new bootstrap.Modal(document.getElementById("successModal"));
    successModal.show();
    
    // Reset form
    event.target.reset();
  }, 2000);
}

// Header scroll effect
function initializeHeaderScroll() {
  const header = document.querySelector(".header");
  let lastScrollY = window.scrollY;
  
  window.addEventListener("scroll", function () {
    const header = document.getElementById("header") || document.querySelector(".main-header") || document.getElementById("mainHeader");
    if (!header) return;
    if (window.scrollY > 50) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });
}

// Scroll animations
function addScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px"
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("fade-in");
      }
    });
  }, observerOptions);
  
  // Observe elements for animation
  document.querySelectorAll(".filter-card, .results-header, .customize-section, .contact-section").forEach(el => {
    observer.observe(el);
  });
}

// Utility function for debouncing
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Mobile menu toggle function (global for onclick)
function toggleMenu() {
  const toggle = document.querySelector(".toggle");
  const mainNav = document.querySelector(".main-nav");
  
  if (toggle && mainNav) {
    toggle.classList.toggle("active");
    mainNav.classList.toggle("active");
  }
}

// Smooth scrolling for anchor links
document.addEventListener("click", function(e) {
  if (e.target.matches("a[href^=\"#\"]")) {
    e.preventDefault();
    const targetId = e.target.getAttribute("href").substring(1);
    const targetElement = document.getElementById(targetId);
    
    if (targetElement) {
      targetElement.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    }
  }
});

// Keyboard navigation for pagination
document.addEventListener("keydown", function(e) {
  if (e.target.matches("input, textarea, select")) return;
  
  const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
  
  if (e.key === "ArrowLeft" && currentPage > 1) {
    changePage(currentPage - 1);
  } else if (e.key === "ArrowRight" && currentPage < totalPages) {
    changePage(currentPage + 1);
  }
});

// Performance optimization: Lazy loading for images
if ("IntersectionObserver" in window) {
  const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src || img.src;
        img.classList.remove("lazy");
        imageObserver.unobserve(img);
      }
    });
  });
  
  // Observe images when they're added to the DOM
  const observeImages = () => {
    document.querySelectorAll("img[loading=\"lazy\"]").forEach(img => {
      imageObserver.observe(img);
    });
  };
  
  // Initial observation
  setTimeout(observeImages, 100);
}

 window.addEventListener("scroll", function () {
    const header = document.getElementById("header");
    if (header) {
      if (window.scrollY > 50) {
        header.classList.add("scrolled");
      } else {
        header.classList.remove("scrolled");
      }
    }
  });


// Fetch products from backend API with current filters and pagination
function buildApiUrl(page = 1) {
  const params = new URLSearchParams();
  params.set('page', page);
  params.set('per_page', itemsPerPage);
  // category - if multiple categories selected use first or send empty
  if (currentFilters.categories && currentFilters.categories.length > 0) {
    // send as comma-separated list of slugs (get_products.php supports this)
    params.set('category', currentFilters.categories.join(','));
  }
  if (currentFilters.search) params.set('search', currentFilters.search);
  if (currentFilters.priceRange) params.set('max_price', currentFilters.priceRange);
  if (currentFilters.availability && currentFilters.availability !== 'all') params.set('availability', currentFilters.availability);
  if (currentFilters.sortBy) params.set('sort', currentFilters.sortBy);
  return `php/get_products.php?${params.toString()}`;
}

function fetchProducts(page = 1, options = { scroll: false }) {
  const url = buildApiUrl(page);
  // show loading
  if (loadingScreen) loadingScreen.classList.remove('hidden');

  fetch(url)
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        console.error('API error', data);
        filteredProducts = [];
        totalResultsGlobal = 0;
        totalPagesGlobal = 1;
      } else {
        filteredProducts = data.products || [];
        totalResultsGlobal = data.total || filteredProducts.length;
        totalPagesGlobal = data.total_pages || Math.ceil(totalResultsGlobal / itemsPerPage);
        currentPage = data.page || page;
      }

      renderProducts();
      renderPagination();
      updateResultsInfo();

      // smooth scroll to products only when explicitly requested (user interaction)
      if (options && options.scroll) {
        const container = document.querySelector('.products-container');
        if (container) container.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    })
    .catch(err => {
      console.error('Fetch failed', err);
      filteredProducts = [];
      totalResultsGlobal = 0;
      totalPagesGlobal = 1;
      renderProducts();
      renderPagination();
      updateResultsInfo();
    })
    .finally(() => {
      if (loadingScreen) loadingScreen.classList.add('hidden');
    });
}

