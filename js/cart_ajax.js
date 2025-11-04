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

/**
 * Handles the "Proceed to Checkout" button click.
 */
function proceedToCheckout() {
    const isLoggedIn = document.body.getAttribute('data-logged-in') === 'true';
    if (isLoggedIn) {
        window.location.href = 'checkout.php';
    } else {
        alert('Please log in to proceed to checkout.');
        if (typeof openLoginModal === 'function') {
            openLoginModal();
        }
    }
}


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
    const cartCountElement = document.querySelector('.cart-item-count');

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
                        let itemImageSrc = '/images/default_product.png';
                        if (item.image_url) {
                            const raw = item.image_url;
                            if (/^https?:\/\//i.test(raw)) {
                                itemImageSrc = raw;
                            } else if (raw.indexOf('uploads/') === 0 || raw.indexOf('/uploads/') === 0) {
                                itemImageSrc = raw;
                            } else if (raw.trim() !== '') {
                                itemImageSrc = 'uploads/' + raw;
                            }
                        }

                        const itemHTML = `
                                <div class="sidebar-item" id="sidebar-item-${item.product_id}">
                                    <img src="${itemImageSrc}" alt="${item.name}">
                                    <div class="sidebar-item-details">
                                        <h5>${item.name}</h5>
                                        <p>${item.quantity} &times; $${parseFloat(item.price).toFixed(2)}</p>
                                    </div>
                                    <div class="sidebar-item-price">
                                        $${(item.quantity * item.price).toFixed(2)}
                                    </div>
                                </div>
                            `;
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
