<?php
session_start(); // Start the session to access session variables
require_once 'db.php'; // Include database connection
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Check if the user is logged in and has the role of 'user'
// If not, redirect to the login page

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Fetch all products from the database
$query = "SELECT id, name, description, price, image, badge, rating FROM products ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <title>Net Bazaar</title>
    <link rel="stylesheet" href="src/user_dashboard.css">
    <!-- Add Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <!-- Top row: Logo + Menu Toggle -->
        <div class="nav-top">
            <div class="logo">
                <a href="user_dashboard.php">
                    <img src="assets/logo.png" alt="Net Bazaar Logo">
                </a>
            </div>

            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search..." onkeydown="handleKey(event)" />
                <span class="search-icon">&#128269;</span>
            </div>

            <!-- Hamburger for mobile -->
            <div class="menu-toggle" onclick="toggleUserMenu(this)">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <!-- Navigation Items -->
        <div class="nav-items">

            <div class="tabs-item"><i class="fas fa-user"></i><a href="user_profile.php" data-page="user_account" class="tab-links">Profile</a></div>
            <div class="tabs-item"><i class="fas fa-compass"></i><a href="user_dashboard.php" data-page="user_dashboard">Explore</a></div>
            <div class="tabs-item"><i class="fas fa-shopping-cart"></i><a data-page="user_cart" class="tab-links">Cart</a></div>
            <div class="tabs-item"><i class="fas fa-box"></i><a data-page="user_my_orders" class="tab-links">Orders</a></div>
            <div class="tabs-item"><i class="fas fa-bell"></i><a href="user_notification.php" data-page="user_notification" class="tab-links">Notifications</a></div>
            <div class="tabs-item"><i class="fas fa-question-circle"></i><a href="user_help.php" data-page="user_help" class="tab-links">Help</a></div>
            <div class="tabs-item"><i class="fas fa-cog"></i><a href="user_settings.php" data-page="user_settings" class="tab-links">Settings</a></div>
            <div class="tabs-item"><button class="btn" onclick="window.location.href='logout.php'">Logout</button></div>
        </div>
    </nav>

    <div class="featured-products" id="main-content">
        <h2>Featured Products</h2>
        <?php if ($result->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="image-wrapper">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <div class="badge"><?php echo htmlspecialchars($product['badge'] ?? 'New'); ?></div>
                            <div class="wishlist-icon" onclick="toggleWishlist(<?php echo $product['id']; ?>, event)">❤️</div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-rating"><?php echo str_repeat('⭐', intval($product['rating'] ?? 4)); ?></div>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <div class="price-row">
                                <span class="product-price">NRP <?php echo number_format($product['price'], 2); ?></span>
                                <button class="buy-now" onclick="openProductModal(<?php echo htmlspecialchars(json_encode($product)); ?>)">Buy Now</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>

    <div class="productpage">
        
    </div>
    <!-- Add this after the featured-products div -->
    <div id="productModal" class="product-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="product-details-grid">
                <div class="product-image-section">
                    <img id="modalProductImage" src="" alt="" class="modal-product-image">
                </div>
                <div class="product-info-section">
                    <h2 id="modalProductName"></h2>
                    <div class="product-rating" id="modalProductRating"></div>
                    <p class="product-description" id="modalProductDescription"></p>
                    <p class="product-price">NRP <span id="modalProductPrice"></span></p>
                    
                    <div class="product-options">
                        <div class="size-options">
                            <h4>Select Size</h4>
                            <div class="size-buttons">
                                <button class="size-btn" data-size="S">S</button>
                                <button class="size-btn" data-size="M">M</button>
                                <button class="size-btn" data-size="L">L</button>
                                <button class="size-btn" data-size="XL">XL</button>
                            </div>
                        </div>
                        
                        <div class="color-options">
                            <h4>Select Color</h4>
                            <div class="color-circles">
                                <div class="color-circle" style="background-color: #000000;" data-color="Black"></div>
                                <div class="color-circle" style="background-color: #FFFFFF;" data-color="White"></div>
                                <div class="color-circle" style="background-color: #FF0000;" data-color="Red"></div>
                                <div class="color-circle" style="background-color: #0000FF;" data-color="Blue"></div>
                            </div>
                        </div>
                        
                        <div class="quantity-selector">
                            <h4>Quantity</h4>
                            <div class="quantity-controls">
                                <button class="qty-btn" onclick="updateQuantity(-1)">-</button>
                                <input type="number" id="quantity" value="1" min="1" max="10">
                                <button class="qty-btn" onclick="updateQuantity(1)">+</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="add-to-cart-btn" onclick="addToCart()">Add to Cart</button>
                        <button class="buy-now-btn" onclick="paymentProceed()">Proceed</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
<div id="paymentModal" class="payment-modal" style="display:none;">
    <div class="modal-content" id="paymentModalContent">
        <span class="close-payment-modal" style="position:absolute;top:10px;right:20px;font-size:2rem;cursor:pointer;">&times;</span>
        <!-- Payment content will be loaded here -->
    </div>
</div>

    <script src="script.js"></script>
    <script>
        // Function to check if user is logged in before adding to cart
        function checkuserloggedin() {
            const isLoggedIn = <?= json_encode(isset($_SESSION['user_id'])) ?>; // Check if user is logged in
            if (isLoggedIn) {
                alert("Product added to cart!");
                $.post('user_add_to_cart.php', {
                    product_id: productId
                }, function(response) {
                    if (response.success) {
                        alert("Product added to cart!");
                    } else {
                        alert("Failed to add product to cart. Please try again.");
                    }
                }, 'json');
            }
        }
    </script>
    <script>
        // JavaScript to handle tab clicks and load content dynamically
        const tabsContainer = document.getElementById('tabs-container');
        const mainContent = document.getElementById('main-content');

        // Load content dynamically when a tab is clicked
        const tabLinks = document.querySelectorAll('.tab-links');
        tabLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const page = link.getAttribute('data-page');
                loadPageContent(page);
            });
        });

        // Function to load page content dynamically
        function loadPageContent(page) {
            fetch(`${page}.php`)
                .then(response => response.text())
                .then(html => {
                    mainContent.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading page:', error);
                    mainContent.innerHTML = '<p>Error loading content. Please try again later.</p>';
                });
        }
    </script>
    <script>
        function displayForm() {
            var form = document.getElementById('changePasswordForm');
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }

        function displaydeleteoption() {
            var form = document.getElementById('deleteprofileForm');
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }
    </script>
    <script>
        // Add this to your existing script section
function openProductModal(product) {
    const modal = document.getElementById('productModal');
    const modalImg = document.getElementById('modalProductImage');
    const modalName = document.getElementById('modalProductName');
    const modalRating = document.getElementById('modalProductRating');
    const modalDesc = document.getElementById('modalProductDescription');
    const modalPrice = document.getElementById('modalProductPrice');

    // Set modal content
    modalImg.src = product.image;
    modalImg.alt = product.name;
    modalName.textContent = product.name;
    modalRating.innerHTML = '⭐'.repeat(product.rating || 4);
    modalDesc.textContent = product.description;
    modalPrice.textContent = new Intl.NumberFormat().format(product.price);

    // Add product ID to modal dataset
    modal.dataset.productId = product.id;

    // Show modal
    modal.style.display = 'block';

    // Handle close button
    const closeBtn = document.querySelector('.close-modal');
    closeBtn.onclick = () => modal.style.display = 'none';

    // Close modal when clicking outside
    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
}

// Handle size selection
document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
    });
});

// Handle color selection
document.querySelectorAll('.color-circle').forEach(circle => {
    circle.addEventListener('click', () => {
        document.querySelectorAll('.color-circle').forEach(c => c.classList.remove('selected'));
        circle.classList.add('selected');
    });
});

// Handle quantity updates
function updateQuantity(change) {
    const input = document.getElementById('quantity');
    const newValue = parseInt(input.value) + change;
    if (newValue >= 1 && newValue <= 10) {
        input.value = newValue;
    }
}

function addToCart() {
    // Get the current product data from the modal
    const productId = document.querySelector('#productModal').dataset.productId;
    const selectedSize = document.querySelector('.size-btn.selected')?.dataset.size;
    const selectedColor = document.querySelector('.color-circle.selected')?.dataset.color;
    const quantity = document.getElementById('quantity').value;
    
    if (!selectedSize || !selectedColor) {
        alert('Please select both size and color');
        return;
    }

    // Create form data
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('size', selectedSize);
    formData.append('color', selectedColor);

    // Send POST request to add to cart
    fetch('user_addto_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert('Product added to cart successfully!');
        document.getElementById('productModal').style.display = 'none';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add product to cart. Please try again.');
    });
}

function paymentProceed() {
    // Close the product modal immediately
    document.getElementById('productModal').style.display = 'none';
    document.getElementById('main-content').style.display = 'none';

    const productId = document.querySelector('#productModal').dataset.productId;
    const selectedSize = document.querySelector('.size-btn.selected')?.dataset.size;
    const selectedColor = document.querySelector('.color-circle.selected')?.dataset.color;
    const quantity = document.getElementById('quantity').value;

    if (!selectedSize || !selectedColor) {
        alert('Please select both size and color');
        return;
    }

    // Build the URL with query parameters
    const url = `process_payment.php?product_id=${encodeURIComponent(productId)}&quantity=${encodeURIComponent(quantity)}&size=${encodeURIComponent(selectedSize)}&color=${encodeURIComponent(selectedColor)}`;

    // Fetch the payment page and show in modal
    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('paymentModalContent').innerHTML = `
                <span class="close-payment-modal" style="position:absolute;top:10px;right:20px;font-size:2rem;cursor:pointer;">&times;</span>
                ${html}
            `;
            document.getElementById('paymentModal').style.display = 'flex';

            // Close handler
            document.querySelector('.close-payment-modal').onclick = function() {
                document.getElementById('paymentModal').style.display = 'none';
            };
        })
        .catch(error => {
            alert('Failed to load payment form.');
            console.error(error);
        });
}
    </script>
     <script>
        function updateQuantity(cartId, change) {
            fetch('update_cart_quantity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cart_id=${cartId}&change=${change}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        function removeItem(cartId) {
            if (confirm('Remove this item from cart?')) {
                fetch('remove_from_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `cart_id=${cartId}`
                    })
                    .then(() => {
                        location.reload();
                    });
            }
        }
    </script>
</body>

</html>