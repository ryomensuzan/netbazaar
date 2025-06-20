<?php
session_start();
require_once 'db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get parameters from either POST or GET
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : (isset($_GET['product_id']) ? intval($_GET['product_id']) : null);
$total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : (isset($_GET['total_amount']) ? floatval($_GET['total_amount']) : null);
$transaction_uuid = isset($_POST['transaction_uuid']) ? $_POST['transaction_uuid'] : (isset($_GET['transaction_uuid']) ? $_GET['transaction_uuid'] : null);

// Add logging to debug the received parameters
error_log("Payment Success - Received Parameters: " . print_r($_REQUEST, true));

// Validate required parameters
if (!$product_id || !$total_amount || !$transaction_uuid) {
    error_log("Payment Success - Missing Data: " . print_r(['POST' => $_POST, 'GET' => $_GET], true));
    echo "<script>alert('Invalid request. Missing payment details.'); window.location.href = 'user_dashboard.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate product and stock
$stmt = $conn->prepare("SELECT stock, price, name FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<script>alert('Product not found.'); window.location.href = 'user_dashboard.php';</script>";
    exit;
}

$quantity = 1; // Default quantity
if ($product['stock'] < $quantity) {
    echo "<script>alert('Product out of stock.'); window.location.href = 'user_dashboard.php';</script>";
    exit;
}

if ($total_amount < $product['price'] * 1.1) {
    echo "<script>alert('Invalid payment amount.'); window.location.href = 'user_dashboard.php';</script>";
    exit;
}

// Start transaction to ensure data consistency
$conn->begin_transaction();

try {
    // 1. Deduct stock
    $new_stock = $product['stock'] - $quantity;
    $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_stock, $product_id);
    $stmt->execute();

    // 2. Create order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, total_amount, quantity, order_status, payment_status) 
                           VALUES (?, ?, ?, ?, 'Processing', 'Completed')");
    $stmt->bind_param("iidi", $user_id, $product_id, $total_amount, $quantity);
    $stmt->execute();
    
    // 3. Clear cart if item was in cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();

    $conn->commit();
    
    // Log successful order
    error_log("Order placed successfully - User: $user_id, Product: $product_id, Amount: $total_amount");
    
    echo "<script>alert('Payment successful! Order placed.'); window.location.href = 'user_dashboard.php';</script>";
} catch (Exception $e) {
    $conn->rollback();
    error_log("Payment processing error: " . $e->getMessage());
    echo "<script>alert('An error occurred. Please try again.'); window.location.href = 'user_dashboard.php';</script>";
}
?>