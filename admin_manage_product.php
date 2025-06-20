<?php
session_start();
require_once 'db.php';

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$search = $_GET['search'] ?? '';
$filter_seller = $_GET['seller'] ?? '';

// Fetch all sellers for dropdown filter
$sellersStmt = $conn->query("SELECT seller_id, company_name FROM seller WHERE status = 'approved'");
$sellers = $sellersStmt->fetch_all(MYSQLI_ASSOC);

// Build dynamic query
$query = "SELECT p.*, s.company_name FROM products p JOIN seller s ON p.seller_id = s.seller_id WHERE 1";

if ($search) {
    $query .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if ($filter_seller) {
    $query .= " AND s.seller_id = ?";
    $params[] = $filter_seller;
    $types .= 'i';
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <title>Manage Products - Admin Panel</title>
    <link rel="stylesheet" href="src/manage_products.css">
</head>
<body>
    <h2>Manage Products</h2>

    <form method="GET" class="filter-form">
        <input type="text" name="search" placeholder="Search by name or description" value="<?= htmlspecialchars($search) ?>">
        <select name="seller">
            <option value="">All Sellers</option>
            <?php foreach ($sellers as $seller): ?>
                <option value="<?= $seller['seller_id'] ?>" <?= $filter_seller == $seller['seller_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($seller['company_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Apply</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Created At</th>
                <th>Seller</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="7">No products found.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['category_id'] ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                        <td>NRP<?= number_format($product['price'], 2) ?></td>
                        <td><?= $product['created_at'] ?></td>
                        <td><?= htmlspecialchars($product['company_name']) ?></td>
                        <td>
                            <form method="POST" action="remove_product.php" onsubmit="return confirm('Are you sure to remove this product?');">
                                <input type="hidden" name="product_id" value="<?= $product['category_id'] ?>">

                                <button type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
