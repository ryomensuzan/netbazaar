<?php
session_start();
require_once 'db.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders for the logged-in user
$stmt = $conn->prepare("
    SELECT o.order_id, 
           o.total_amount,
           o.order_status,
           o.created_at,
           o.quantity,
           p.name AS product_name,
           p.image,
           p.price
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[$row['order_id']] = [
        'created_at' => $row['created_at'],
        'total_amount' => $row['total_amount'],
        'order_status' => $row['order_status'],
        'items' => [[
            'product_name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'price' => $row['price'],
            'image' => $row['image']
        ]]
    ];
}
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
    <link rel="stylesheet" href="src/user_my_order.css?v=<?= time(); ?>">
    <title>My Orders</title>
</head>

<body>
    <div class="orders-container">
        <h1 class="page-title">My Orders</h1>
        
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-bag"></i>
                <p>You haven't placed any orders yet</p>
                <a href="user_dashboard.php" class="btn btn-track">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order_id => $details): ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">Order #<?= htmlspecialchars($order_id) ?></span>
                        <span class="order-date"><?= htmlspecialchars($details['created_at']) ?></span>
                        <span class="order-status status-<?= strtolower($details['order_status']) ?>">
                            <?= htmlspecialchars($details['order_status']) ?>
                        </span>
                    </div>
                    
                    <div class="order-items">
                        <?php foreach ($details['items'] as $item): ?>
                            <div class="item">
                                <div class="item-image">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="Product Image">
                                </div>
                                <div class="item-details">
                                    <h3 class="item-name"><?= htmlspecialchars($item['product_name']) ?></h3>
                                    <span class="item-meta">Quantity: <?= htmlspecialchars($item['quantity']) ?></span>
                                </div>
                                <span class="item-price">NRP <?= number_format($item['price'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-summary">
                        <span class="total-amount">Total: NRP <?= number_format($details['total_amount'], 2) ?></span>
                        <div class="action-buttons">
                            <button class="btn btn-track">Track Order</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>