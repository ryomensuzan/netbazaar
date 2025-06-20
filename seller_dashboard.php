<?php
session_start();
require_once 'db.php'; // Adjust path as needed

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Total Listed Products
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$stmt->bind_result($total_listed_products);
$stmt->fetch();
$stmt->close();

// Total Orders
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT o.order_id) as total_orders 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE p.seller_id = ?
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$total_orders = $result->fetch_assoc()['total_orders'];
$stmt->close();

// Total Sales (sum of quantity sold)
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(o.quantity), 0) as total_sales 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE p.seller_id = ?
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$total_sales = $result->fetch_assoc()['total_sales'];
$stmt->close();

// Total Earnings (sum of amount)  
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(o.total_amount), 0) as total_earnings 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE p.seller_id = ? 
    AND o.payment_status = 'Completed'
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$total_earnings = $result->fetch_assoc()['total_earnings'];
$stmt->close();

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
    <link rel="stylesheet" href="src/seller_dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Vertical Navbar -->
        <nav class="navbar-vertical">
            <div class="nav-brand">
                <a href="seller_dashboard.php" class="logo-link">
                    <img src="assets/logo.png" alt="Net Bazaar Logo" height="40" width="200">
                </a>
            </div>

            <div class="nav-links">
                <a href="seller_manage_product.php" class="nav-link" data-page="seller_manage_product">
                    <i class="nav-icon">📦</i>
                    <span>Products</span>
                </a>
                <a href="seller_order_manage.php" class="nav-link" data-page="seller_order_manage">
                    <i class="nav-icon">📝</i>
                    <span>Orders</span>
                </a>
                <a href="payment_earnings.php" class="nav-link" data-page="payment_earnings">
                    <i class="nav-icon">💰</i>
                    <span>Earnings</span>
                </a>
                <a href="seller_support.php" class="nav-link" data-page="seller_support">
                    <i class="nav-icon">💬</i>
                    <span>Support</span>
                </a>
                <a href="seller_account.php" class="nav-link" data-page="seller_account">
                    <i class="nav-icon">👤</i>
                    <span>Account</span>
                </a>
                <a href="seller_settings.php" class="nav-link" data-page="seller_settings">
                    <i class="nav-icon">⚙️</i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <h2 class="sales-analytics">Dashboard Overview / Analytics</h2>
            <div class="dashboard-flex">

                <div class="total-listed-products">
                    <h3>Total Listed Products</h3>
                    <div id="total-listed-products"><strong><?= $total_listed_products ?></strong></div>
                </div>

                <div class="total-orders">
                    <h3>Total Orders</h3>
                    <div id="total-orders"><strong><?= $total_orders ?></strong></div>
                </div>

                <div class="total-sales">
                    <h3>Total Sales</h3>
                    <div id="total-sales"><strong><?= $total_sales ?></strong></div>
                </div>

                <div class="total-earnings">
                    <h3>Total Earnings</h3>
                    <div id="total-earnings"><strong>NRP <?= number_format($total_earnings, 2) ?></strong></div>
                </div>

            </div>
            <div class="dashboard-graph">
                <!-- sales trends graph -->
                <div class="graph">
                    <h3>Sales Trends</h3>
                    <div id="sales-trends-graph">[Coming Soon]</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // JavaScript to handle nav link clicks and load content dynamically
        const navLinks = document.querySelectorAll('.nav-link');
        const mainContent = document.querySelector('.main-content');

        navLinks.forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();

                // Remove active class from all links
                navLinks.forEach(l => l.classList.remove('active'));
                // Add active class to clicked link
                link.classList.add('active');

                const page = link.getAttribute('data-page');
                loadPageContent(page);
            });
        });

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
</body>

</html>