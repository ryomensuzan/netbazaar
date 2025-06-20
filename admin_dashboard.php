<?php
session_start();
require_once 'db.php';

// Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch total users
$userQuery = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$total_users = $userQuery->fetch_assoc()['total_users'] ?? 0;

// Fetch total sellers
$sellerQuery = $conn->query("SELECT COUNT(*) AS total_sellers FROM seller WHERE status = 'approved'");
$total_sellers = $sellerQuery->fetch_assoc()['total_sellers'] ?? 0;

// Fetch total products
$productQuery = $conn->query("SELECT COUNT(*) AS total_products FROM products");
$total_products = $productQuery->fetch_assoc()['total_products'] ?? 0;
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
    <link href="src/styles.css" rel="stylesheet">
    <link href="src/admin_dashboard.css" rel="stylesheet"> <!-- Add your custom CSS file for admin dashboard -->
</head>

<body>
    <div class="dashboard-container">
        <!-- Vertical Navbar -->
        <nav class="navbar-vertical">

            <div class="nav-links">
                <a href="admin_dashboard.php" class="nav-link active" data-page="dashboard">
                    <i class="nav-icon">📊</i>
                    <span>Dashboard</span>
                </a>
                <a href="admin_manage_seller.php" class="nav-link" data-page="admin_manage_seller">
                    <i class="nav-icon">👥</i>
                    <span>Manage Sellers</span>
                </a>
                <a href="admin_manage_user.php" class="nav-link" data-page="admin_manage_user">
                    <i class="nav-icon">👤</i>
                    <span>Manage Users</span>
                </a>
                <a href="admin_manage_product.php" class="nav-link" data-page="admin_manage_product">
                    <i class="nav-icon">📦</i>
                    <span>Manage Products</span>
                </a>
                <a href="admin_support.php" class="nav-link" data-page="admin_support">
                    <i class="nav-icon">💬</i>
                    <span>Support & Feedback</span>
                </a>
                <a href="admin_account.php" class="nav-link" data-page="admin_account">
                    <i class="nav-icon">⚙️</i>
                    <span>Admin Account</span>
                </a>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">

            <!-- Main Content Section Dashboard Overview / Analytics-->
            <div id="main-content">
                <h2 class="sales-analytics">Dashboard Overview / Analytics</h2>
                <div class="dashboard-flex">

                    <div class="total-users overview">
                        <h3>Total Users</h3>
                        <div id="total-users"><strong><?= $total_users ?></strong></div>
                    </div>

                    <div class="total-sellers overview">
                        <h3>Total Sellers</h3>
                        <div id="total-sellers"><strong><?= $total_sellers ?></strong></div>
                    </div>

                    <div class="total-products overview">
                        <h3>Total Products</h3>
                        <div id="total-products"><strong><?= $total_products ?></strong></div>
                    </div>
                </div>
                <div class="dashboard-graph">

                    <div class="graph">
                        <h3>Sales Graph</h3>
                        <div id="sales-graph">[Coming Soon]</div>
                    </div>
                </div>
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
        </main>
    </div>
</body>

</html>