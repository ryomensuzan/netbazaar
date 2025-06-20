<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.image 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    header('Location: user_cart.php');
    exit;
}

// Calculate amounts
$amount = 0;
foreach ($cart_items as $item) {
    $amount += $item['price'] * $item['quantity'];
}
$tax_amount = $amount * 0.1; // 10% tax
$total_amount = $amount + $tax_amount;

// Generate unique transaction ID
$transaction_uuid = uniqid('NET_', true);

// Define the fields to be signed
$fields_to_sign = [
    'total_amount' => number_format($total_amount, 2, '.', ''),
    'transaction_uuid' => $transaction_uuid,
    'product_code' => 'EPAYTEST'
];

// Create signed field string
$signed_field_string = '';
foreach ($fields_to_sign as $key => $value) {
    $signed_field_string .= $key . '=' . $value . ',';
}
$signed_field_string = rtrim($signed_field_string, ',');

// Generate signature using eSewa's secret key
$secret_key = "8gBm/:&EnhH.1/q";
$signature = base64_encode(hash_hmac('sha256', $signed_field_string, $secret_key, true));

// Set success and failure URLs
$current_domain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
$success_url = $current_domain . "/net_bazaar/payment_success.php?transaction_uuid=" . $transaction_uuid . "&total_amount=" . $total_amount;
$failure_url = $current_domain . "/net_bazaar/payment_failure.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - Net Bazaar</title>
    <link rel="stylesheet" href="src/user_cart.css">
    <style>
        .payment-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.order-details {
    margin: 20px 0;
}

.order-item {
    display: flex;
    gap: 20px;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.order-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.total-summary {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.pay-button {
    display: block;
    width: 100%;
    padding: 15px;
    background: #00B000;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: 20px;
    transition: background 0.3s ease;
}

.pay-button:hover {
    background: #009000;
}
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Order Summary</h2>
        <div class="order-details">
            <?php foreach ($cart_items as $item): ?>
                <div class="order-item">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <div class="item-details">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p>Size: <?= htmlspecialchars($item['size']) ?></p>
                        <p>Color: <?= htmlspecialchars($item['color']) ?></p>
                        <p>Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                        <p>Price: NRP <?= number_format($item['price'], 2) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="total-summary">
                <p>Subtotal: NRP <?= number_format($amount, 2) ?></p>
                <p>Tax: NRP <?= number_format($tax_amount, 2) ?></p>
                <p>Total: NRP <?= number_format($total_amount, 2) ?></p>
            </div>
        </div>

        <form action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
            <input type="hidden" name="amount" value="<?= number_format($amount, 2, '.', '') ?>" required>
            <input type="hidden" name="tax_amount" value="<?= number_format($tax_amount, 2, '.', '') ?>" required>
            <input type="hidden" name="total_amount" value="<?= number_format($total_amount, 2, '.', '') ?>" required>
            <input type="hidden" name="transaction_uuid" value="<?= htmlspecialchars($transaction_uuid) ?>" required>
            <input type="hidden" name="product_code" value="EPAYTEST" required>
            <input type="hidden" name="product_service_charge" value="0" required>
            <input type="hidden" name="product_delivery_charge" value="0" required>
            <input type="hidden" name="success_url" value="<?= $success_url ?>" required>
            <input type="hidden" name="failure_url" value="<?= $failure_url ?>" required>
            <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code" required>
            <input type="hidden" name="signature" value="<?= htmlspecialchars($signature) ?>" required>
            
            <button type="submit" class="pay-button">Pay with eSewa</button>
        </form>
    </div>
</body>
</html>