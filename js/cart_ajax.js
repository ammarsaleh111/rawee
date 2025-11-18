// js/cart_ajax.js

/**
 * Adds a product to the cart via AJAX and refreshes the sidebar.
 * @param {number} productId - The ID of the product to add.
 * @param {HTMLElement} button - The button element that was clicked.
 */
function addToCart(productId, button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('php/add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i> Added!';
            // --- THIS IS THE NEW PART ---
            // Refresh the sidebar to show the new item and count.
            loadSidebarContents(); 
            // --- END OF NEW PART ---
        } else {
            alert(data.message || 'An error occurred.');
            button.innerHTML = originalText;
        }
        // Reset the button text after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Could not connect to the server.');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

/**
 * Updates the quantity of a product on the main cart.php page.
 * @param {number} productId - The product ID.
 * @param {number} change - The change in quantity (+1 or -1).
 */
function updateQuantity(productId, change) {
    const itemRow = document.getElementById(`item-${productId}`);
    const quantityInput = itemRow.querySelector('input[type="number"]');
    let newQuantity = parseInt(quantityInput.value) + change;

    if (newQuantity < 1) {
        newQuantity = 1; // Prevent quantity from going below 1
    }
    
    quantityInput.value = newQuantity;
    setQuantity(productId, newQuantity);
}

/**
 * Sets a specific quantity for a product in the cart via AJAX (used by updateQuantity).
 * @param {number} productId - The product ID.
 * @param {number} quantity - The new quantity.
 */
function setQuantity(productId, quantity) {
    fetch('php/update_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateRowAndTotal(productId, quantity);
        } else {
            alert(data.message || 'Failed to update quantity.');
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Removes an item from the cart on the main cart.php page.
 * @param {number} productId - The product ID to remove.
 */
function removeItem(productId) {
    if (!confirm('Are you sure you want to remove this item?')) {
        return;
    }

    fetch('php/remove_from_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const itemRow = document.getElementById(`item-${productId}`);
            itemRow.style.transition = 'opacity 0.5s';
            itemRow.style.opacity = '0';
            setTimeout(() => {
                itemRow.remove();
                updateTotal(); // Recalculate total after removing
            }, 500);
        } else {
            alert(data.message || 'Failed to remove item.');
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * Updates the UI for a specific row on cart.php and recalculates the grand total.
 * @param {number} productId - The product ID of the row to update.
 * @param {number} quantity - The new quantity.
 */
function updateRowAndTotal(productId, quantity) {
    const itemRow = document.getElementById(`item-${productId}`);
    const priceElement = itemRow.querySelector('.item-total-price');
    const basePrice = parseFloat(priceElement.getAttribute('data-price'));
    
    const newRowTotal = basePrice * quantity;
    priceElement.textContent = `$${newRowTotal.toFixed(2)}`;
    
    updateTotal();
}

/**
 * Recalculates and updates the grand total in the summary on cart.php.
 */
function updateTotal() {
    let subtotal = 0;
    document.querySelectorAll('.cart-item').forEach(row => {
        const priceText = row.querySelector('.item-total-price').textContent;
        subtotal += parseFloat(priceText.replace('$', ''));
    });

    document.getElementById('summary-subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('summary-total').textContent = `$${subtotal.toFixed(2)}`;

    if (document.querySelectorAll('.cart-item').length === 0) {
        location.reload(); // Reload to show the "empty cart" message
    }
}

// (Removed old proceedToCheckout that navigated to checkout.php to follow the requirement
//  that only the sidebar's Proceed to Checkout navigates to the shopping cart page.)


// ===================================================================
// == NEW FUNCTIONS FOR THE CART SIDEBAR (MINI-CART)
// ===================================================================

/**
 * Toggles the visibility of the cart sidebar.
 * @param {Event} event - The click event, to prevent default link behavior.
 */
function toggleCartSidebar(event) {
    if (event) {
        event.preventDefault();
    }
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartSidebarOverlay');
    const isActive = sidebar.classList.contains('active');

    if (!isActive) {
        loadSidebarContents();
    }

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

/**
 * Fetches cart contents via AJAX and populates the sidebar.
 */
function loadSidebarContents() {
    const listElement = document.getElementById('sidebarItemsList');
    const subtotalElement = document.getElementById('sidebarSubtotal');
    const existingCartCountElement = document.querySelector('.cart-item-count');
    let cartCountElement = existingCartCountElement;

    listElement.innerHTML = `
        <div class="sidebar-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading Cart...</span>
        </div>`;

    fetch('php/get_cart_contents.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                listElement.innerHTML = ''; // Clear loading spinner

                if (data.items.length === 0) {
                    listElement.innerHTML = '<p style="text-align:center; color: var(--gray-600);">Your cart is empty.</p>';
                } else {
                    data.items.forEach(item => {
                        // Normalize image source
                        let itemImageSrc = 'images/default_product.png';
                        if (item.image_url) {
                            const raw = String(item.image_url);
                            if (/^https?:\/\//i.test(raw)) {
                                itemImageSrc = raw;
                            } else {
                                const normalized = raw.replace(/^\/+/, '');
                                if (normalized.indexOf('uploads/') === 0) {
                                    itemImageSrc = normalized;
                                } else if (normalized.trim() !== '') {
                                    itemImageSrc = 'uploads/' + normalized;
                                }
                            }
                        }

                        const itemHTML = `
                          <div class="sidebar-item" id="sidebar-item-${item.product_id}" data-product-id="${item.product_id}" data-price="${item.price}" data-quantity="${item.quantity}">
                            <img src="${itemImageSrc}" alt="${item.name}">
                            <div class="sidebar-item-details">
                              <h5>${item.name}</h5>
                              <div class="sidebar-qty-controls">
                                <button class="qty-btn minus" data-action="dec" aria-label="Decrease quantity">−</button>
                                <span class="qty-value" id="sidebar-qty-${item.product_id}">${item.quantity}</span>
                                <button class="qty-btn plus" data-action="inc" aria-label="Increase quantity">+</button>
                                <button class="remove-item" data-action="remove" aria-label="Remove item">×</button>
                              </div>
                            </div>
                            <div class="sidebar-item-price" id="sidebar-line-total-${item.product_id}">
                              $${(item.quantity * item.price).toFixed(2)}
                            </div>
                          </div>`;
                        listElement.insertAdjacentHTML('beforeend', itemHTML);
                    });
                }

                subtotalElement.textContent = `$${data.subtotal}`;
                
                // Update the count in the header icon
                // Ensure header counter exists and update it
                if (!cartCountElement) {
                    const headerIcon = document.getElementById('headerCartIcon');
                    if (headerIcon) {
                        const span = document.createElement('span');
                        span.className = 'cart-item-count';
                        headerIcon.appendChild(span);
                        cartCountElement = span;
                    }
                }
                if (cartCountElement) {
                    if (data.itemCount > 0) {
                        cartCountElement.textContent = data.itemCount;
                        cartCountElement.style.display = 'flex';
                    } else {
                        cartCountElement.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading sidebar:', error);
            listElement.innerHTML = '<p style="text-align:center; color: red;">Could not load cart.</p>';
        });
}

// Bind header cart icon to open the sidebar instead of navigating
function bindHeaderCartIcon() {
    const headerIcon = document.getElementById('headerCartIcon');
    if (!headerIcon) {
        console.warn('[CartSidebar] headerCartIcon not found in DOM yet.');
        return false;
    }
    if (!headerIcon._cartBound) {
        headerIcon.addEventListener('click', function(e) {
            e.preventDefault();
            toggleCartSidebar(e);
        });
        headerIcon._cartBound = true;
        console.log('[CartSidebar] Binding attached to headerCartIcon.');
    } else {
        console.log('[CartSidebar] Binding already present on headerCartIcon.');
    }
    return true;
}

function ensureCartSidebarBinding(maxAttempts = 5, attempt = 1) {
    const bound = bindHeaderCartIcon();
    if (!bound && attempt < maxAttempts) {
        setTimeout(() => ensureCartSidebarBinding(maxAttempts, attempt + 1), 200 * attempt);
    } else if (!bound) {
        console.error('[CartSidebar] Failed to bind headerCartIcon after attempts:', maxAttempts);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ensureCartSidebarBinding());
} else {
    ensureCartSidebarBinding();
}

// Extra safety: capture-phase delegated listener to intercept any clicks on the header cart icon
// even if binding didn't attach for some reason.
document.addEventListener('click', function(e) {
    const target = e.target.closest('#headerCartIcon');
    if (target) {
        e.preventDefault();
        toggleCartSidebar(e);
        return; // avoid processing as quantity controls
    }
    // Sidebar quantity controls
    if (e.target && e.target.closest('.sidebar-qty-controls')) {
        const btn = e.target;
        const action = btn.getAttribute('data-action');
        if (!action) return;
        const itemRoot = btn.closest('.sidebar-item');
        if (!itemRoot) return;
        const productId = parseInt(itemRoot.getAttribute('data-product-id'), 10);
        const qtySpan = itemRoot.querySelector('.qty-value');
        let currentQty = parseInt(qtySpan.textContent, 10) || 1;
        if (action === 'inc') {
            currentQty += 1;
            updateSidebarQuantity(productId, currentQty);
        } else if (action === 'dec') {
            if (currentQty > 1) {
                currentQty -= 1;
                updateSidebarQuantity(productId, currentQty);
            } else {
                // If at 1 and user decreases, optionally remove
                removeSidebarItem(productId);
            }
        } else if (action === 'remove') {
            removeSidebarItem(productId);
        }
    }
}, true);

// Ensure Proceed to Checkout directs to shopping cart page (not on header icon)
function proceedToCheckout() {
    const lang = document.documentElement.lang || 'en';
    window.location.href = 'cart.php?lang=' + lang;
}

// Update quantity in backend then refresh sidebar
function updateSidebarQuantity(productId, newQty) {
    fetch('php/update_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(newQty)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadSidebarContents();
        }
    })
    .catch(err => console.error('Update sidebar qty error:', err));
}

function removeSidebarItem(productId) {
    fetch('php/remove_from_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${encodeURIComponent(productId)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadSidebarContents();
        }
    })
    .catch(err => console.error('Remove sidebar item error:', err));
}
