<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare(" SELECT c.id AS cart_id, p.id AS product_id, p.name, p.price, p.image, c.quantity, c.size, c.color FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total = 0;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" href="src/user_cart.css">
</head>

<body>
    <div class="cart-container">
        <h1>Shopping Cart</h1>

        <?php if ($result->num_rows > 0): ?>
            <div class="cart-items">
                <?php while ($row = $result->fetch_assoc()):
                    $subtotal = $row['price'] * $row['quantity'];
                    $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        </div>
                        <div class="item-details">
                            <h3><?= htmlspecialchars($row['name']) ?></h3>
                            <div class="item-meta">
                                <?php if (!empty($row['size'])): ?>
                                    <span class="size">Size: <?= htmlspecialchars($row['size']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($row['color'])): ?>
                                    <span class="color">Color: <?= htmlspecialchars($row['color']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="price-qty">
                                <span class="price">NRP <?= number_format($row['price'], 2) ?></span>
                                <div class="quantity-controls">
                                    <button class="qty-btn" onclick="updateQuantity(<?= $row['cart_id'] ?>, -1)">-</button>
                                    <span class="quantity"><?= $row['quantity'] ?></span>
                                    <button class="qty-btn" onclick="updateQuantity(<?= $row['cart_id'] ?>, 1)">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="item-actions">
                            <span class="subtotal">NRP <?= number_format($subtotal, 2) ?></span>
                            <button class="remove-btn" onclick="removeItem(<?= $row['cart_id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>NRP <?= number_format($total, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (10%)</span>
                    <span>NRP <?= number_format($total * 0.1, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>NRP <?= number_format($total * 1.1, 2) ?></span>
                </div>
                <form action="process_cart_payment.php" method="POST">
                    <input type="hidden" name="cart_total" value="<?= $total ?>">
                    <button type="submit" class="checkout-btn">Proceed to Checkout</button>
                </form>
            </div>

        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <a href="user_dashboard.php" class="continue-shopping">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>

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