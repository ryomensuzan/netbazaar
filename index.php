<?php
session_start();
require_once 'db.php';

$isError = false;
$message = '';
$signupError = $_SESSION['signup_error'] ?? '';
$signupSuccess = $_SESSION['signup_success'] ?? '';
$sellerError = $_SESSION['seller_error'] ?? '';
$sellerSuccess = $_SESSION['seller_success'] ?? '';


// Clear messages AFTER rendering


if (isset($_SESSION['login_error'])) {
    $message = $_SESSION['login_error'];
    $isError = true;
    unset($_SESSION['login_error']);
}

$signupMessage = "";

if (isset($_SESSION['signup_success'])) {
    $signupMessage = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']);
}
if (isset($_SESSION['signup_error'])) {
    $signupMessage = $_SESSION['signup_error'];
    unset($_SESSION['signup_error']);
}
// Fetch all products from the database
$query = "SELECT id, name, description, price, image, badge, rating FROM products ORDER BY id DESC";
$result = $conn->query($query);

unset($_SESSION['signup_error'], $_SESSION['signup_success'], $_SESSION['seller_error']);
unset($_SESSION['seller_error'], $_SESSION['seller_success']);
/* add to cart */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/netbazaar.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <title>Net Bazaar</title>
    <link rel="stylesheet" href="src/styles.css?v=<?php filemtime('src/styles.css'); ?>">
</head>

<body>

    <!-- Navbar -->
<nav class="navbar">
    <div class="nav-left">
        <a href="index.php" class="logo-link">
            <img src="assets/logo.png" alt="Net Bazaar Logo">
        </a>

        <div class="menu-toggle" onclick="toggleMenu(this)">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="nav-items">
        <a href="#">Home</a>
        <a href="#products">Products</a>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search..." onkeydown="handleKey(event)" />
            <span class="search-icon">&#128269;</span>
        </div>
        <button class="btn" onclick="openModal()">Login</button>
        <button class="btn" onclick="signupModal()">Sign Up</button>
        <button class="btn" onclick="sellerModal()">Become a Seller</button>
    </div>
</nav>



    <!-- Login Modal Content -->
    <div id="myModal" class="modal">
        <div class="form-container">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Login to Net Bazaar</h2>
            <form method="POST" action="login.php">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button class="btn centerBtn" type="submit">Login</button>
                <?php if (!empty($message)): ?>
                    <div class="error"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Sign Up Modal Content -->
    <div id="signupModal" class="modal">
        <div class="form-container">
            <span class="close" onclick="closesignupModal()">&times;</span>
            <h2>User Signup</h2>
            <form method="POST" action="signup.php">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required minlength="6">
                <button class="btn centerBtn" type="submit">Create Account</button>
                <?php if (!empty($signupError)): ?>
                    <div class="error"><?= htmlspecialchars($signupError) ?></div>
                <?php endif; ?>

                <?php if (!empty($signupSuccess)): ?>
                    <div class="success"><?= htmlspecialchars($signupSuccess) ?></div>
                <?php endif; ?>

            </form>
        </div>
    </div>
    <!-- Become a Seller Modal Content -->
    <div id="sellerModal" class="modal">
        <div class="form-container">
            <span class="close" onclick="closesellerModal()">&times;</span>
            <h2>Become a Seller on Net Bazaar</h2>
            <form method="POST" action="seller.php">
                <input type="text" name="seller_name" placeholder="Full Name" required>
                <input type="text" name="company_name" placeholder="Company Name" required>
                <select name="company_type" required>
                    <option value="">Select Company Type</option>
                    <option value="Individual">Individual</option>
                    <option value="Private">Private</option>
                    <option value="LLP">LLP</option>
                </select>
                <input type="text" name="tax_id" placeholder="Tax ID" required>
                <input type="email" name="email" placeholder="Email" required>
                <label for="">Bussiness Address:</label>
                <select name="address" required>
                    <option value="Kathmandu">Kathmandu</option>
                    <option value="Bhaktapur">Bhaktapur</option>
                    <option value="Udayapur">Udayapur</option>
                    <option value="Jhapa">Jhapa</option>
                    <option value="Sunsari">Sunsari</option>
                    <option value="Morang">Morang</option>
                    <option value="Sarlahi">Sarlahi</option>
                    <option value="Sindhuli">Sindhuli</option>
                    <option value="Makwanpur">Makwanpur</option>
                    <option value="Bara">Bara</option>
                    <option value="Parsa">Parsa</option>
                    <option value="Rautahat">Rautahat</option>
                    <option value="Dhanusa">Dhanusa</option>
                    <option value="Mahottari">Mahottari</option>
                    <option value="Saptari">Saptari</option>
                    <option value="Siraha">Siraha</option>
                    <option value="Dharan">Dharan</option>
                    <option value="Biratnagar">Biratnagar</option>
                    <option value="Itahari">Itahari</option>
                </select>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn centerBtn">Submit</button>
                <?php if (!empty($sellerError)): ?>
                    <div class="error"><?= htmlspecialchars($sellerError) ?></div>
                <?php endif; ?>

                <?php if (!empty($sellerSuccess)): ?>
                    <div class="success"><?= htmlspecialchars($sellerSuccess) ?></div>
                <?php endif; ?>



            </form>
        </div>
    </div>
    <!-- Main Content Section -->
     <!-- welcome message from Net Bazaar -->
<section class="hero-welcome">
  <div class="hero-overlay"></div>
  <div class="welcome-message fade-in">
    <h1>Welcome to <span style="color: #ffce54;">Net Bazaar</span></h1>
    <a href="#products" class="cta-button">Start Shopping</a>

  <div class="feature-grid">
    <div class="feature-item">
      🛍️
      <h3>Vast Product Range</h3>
      <p>From gadgets to fashion, find everything in one place.</p>
    </div>
    <div class="feature-item">
      ⚡
      <h3>Fast Delivery</h3>
      <p>Speedy shipping to your doorstep, hassle-free.</p>
    </div>
    <div class="feature-item">
      💰
      <h3>Exclusive Deals</h3>
      <p>Get the best prices and seasonal discounts.</p>
    </div>
  </div>
  </div>
</section>

       <div class="featured-products" id="products">
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
              <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>, event)">Add to Cart</button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>No products found.</p>
  <?php endif; ?>
</div>

    <!-- login -->
    <?php if ($isError): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                openModal();
            });
        </script>
    <?php endif; ?>
    <!-- signup -->
    <?php if (!empty($signupError) || !empty($signupSuccess)) : ?>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                signupModal();
            });
        </script>
    <?php endif; ?>

    <!-- seller -->
    <?php if (!empty($sellerError) || !empty($sellerSuccess)) : ?>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                sellerModal();
            });
        </script>
    <?php endif; ?>

    <script src="script.js"></script>
    <script>
function toggleMenu(icon) {
    icon.classList.toggle("open");
    document.querySelector('.nav-items').classList.toggle("active");
}
</script>
</body>
<!-- Footer -->
<footer class="site-footer">
  <div class="footer-container">
    <!-- Column 1: Company Info -->
    <div class="footer-column">
      <h3>Net Bazaar</h3>
      <p>Your trusted online marketplace for everything you need — electronics, fashion, home, and more.</p>
    </div>

    <!-- Column 2: Quick Links -->
    <div class="footer-column">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">Shop</a></li>
        <li><a href="#">Categories</a></li>
        <li><a href="#">Cart</a></li>
        <li><a href="#">My Account</a></li>
      </ul>
    </div>

    <!-- Column 3: Customer Service -->
    <div class="footer-column">
      <h4>Customer Support</h4>
      <ul>
        <li><a href="#">Help Center</a></li>
        <li><a href="#">Shipping Info</a></li>
        <li><a href="#">Returns & Refunds</a></li>
        <li><a href="#">Track Order</a></li>
        <li><a href="#">Contact Us</a></li>
      </ul>
    </div>

    <!-- Column 4: Newsletter + Social -->
    <div class="footer-column">
      <h4>Stay Updated</h4>
      <form class="newsletter-form">
        <input type="email" placeholder="Your email address" required>
        <button type="submit">Subscribe</button>
      </form>
      <div class="social-icons">
        <a href="#"><span>📘</span></a>
        <a href="#"><span>🐦</span></a>
        <a href="#"><span>📸</span></a>
        <a href="#"><span>🎥</span></a>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <?= date("Y") ?> Net Bazaar. All rights reserved.</p>
    <div class="legal-links">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms & Conditions</a>
    </div>
  </div>
</footer>

</html>