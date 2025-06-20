<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get product details
$product_id = $_GET['product_id'] ?? null;
$quantity = $_GET['quantity'] ?? 1;
$size = $_GET['size'] ?? '';
$color = $_GET['color'] ?? '';

// Validate product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<script>alert('Product not found'); window.location.href='user_dashboard.php';</script>";
    exit;
}

// Calculate amounts
$amount = $product['price'] * $quantity;
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

// Success and failure URLs
$current_domain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
$success_url = $current_domain . "/net_bazaar/payment_success.php?product_id=" . $product_id . "&transaction_uuid=" . $transaction_uuid . "&total_amount=" . $total_amount;
$failure_url = $current_domain . "/net_bazaar/payment_failure.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Processing Payment - Net Bazaar</title>
    <style>
        body {
            font-family: 'Familjen Grotesk', sans-serif;
            background-color: #f0f0f0;
            color: #333;
        }
        .payment-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .product-details {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="product-details">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p>Quantity: <?= htmlspecialchars($quantity) ?></p>
            <p>Size: <?= htmlspecialchars($size) ?></p>
            <p>Color: <?= htmlspecialchars($color) ?></p>
            <p>Amount: NRP <?= number_format($amount, 2) ?></p>
            <p>Tax: NRP <?= number_format($tax_amount, 2) ?></p>
            <p>Total: NRP <?= number_format($total_amount, 2) ?></p>
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
            
            <button type="submit" style="
                background-color: #00B000;
                color: white;
                padding: 12px 24px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
            ">Pay with eSewa</button>
        </form>
    </div>
</body>
</html>