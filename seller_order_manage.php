<?php
session_start();
require_once 'db.php';

// Check if seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$message = "";

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ? AND product_id IN (SELECT id FROM products WHERE seller_id = ?)");
    $stmt->bind_param("sii", $new_status, $order_id, $seller_id);

    if ($stmt->execute()) {
        $message = "Order status updated successfully.";
    } else {
        $message = "Failed to update order status.";
    }
}

// Fetch orders for seller's products with detailed information
$query = "
    SELECT 
        o.order_id,
        o.total_amount,
        o.order_status,
        o.payment_status,
        o.quantity,
        o.created_at,
        p.name AS product_name,
        p.price AS unit_price,
        p.image AS product_image,
        u.full_name AS buyer_name,
        u.email AS buyer_email
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN users u ON o.user_id = u.id
    WHERE p.seller_id = ?
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <title>Manage Orders - Seller Dashboard</title>
    <link rel="stylesheet" href="src/seller_order_manage.css">
</head>
<body>
    <h2>Manage Orders</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product Details</th>
                <th>Buyer Details</th>
                <th>Order Info</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($row['order_id']) ?></td>
                        <td>
                            <div class="product-info">
                                <img src="<?= htmlspecialchars($row['product_image']) ?>" alt="Product" width="50">
                                <div>
                                    <strong><?= htmlspecialchars($row['product_name']) ?></strong>
                                    <p>Unit Price: NRP <?= number_format($row['unit_price'], 2) ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['buyer_name']) ?></strong><br>
                            <?= htmlspecialchars($row['buyer_email']) ?>
                        </td>
                        <td>
                            <p>Quantity: <?= htmlspecialchars($row['quantity']) ?></p>
                            <p>Total: NRP <?= number_format($row['total_amount'], 2) ?></p>
                            <p>Date: <?= date('M d, Y', strtotime($row['created_at'])) ?></p>
                        </td>
                        <td>
                            <span class="payment-status <?= strtolower($row['payment_status']) ?>">
                                <?= htmlspecialchars($row['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="order-status <?= strtolower($row['order_status']) ?>">
                                <?= htmlspecialchars($row['order_status']) ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                <select name="status">
                                    <option value="Processing" <?= $row['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="Shipped" <?= $row['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="Out for Delivery" <?= $row['order_status'] === 'Out for Delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                    <option value="Delivered" <?= $row['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-orders">No orders found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
