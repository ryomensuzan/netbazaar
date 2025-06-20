<?php
// Include database connection
include 'db.php';

// Start session to get user ID
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(401);
    die("You must be logged in to add items to the cart.");
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get data from POST request
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $size = $_POST['size'] ?? '';
    $color = $_POST['color'] ?? '';

    // Validate product exists
    $check_product = "SELECT id FROM products WHERE id = ?";
    $stmt = $conn->prepare($check_product);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        die("Product not found");
    }

    // Check if the product is already in the cart
    $query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update quantity if product already exists in cart
        $row = $result->fetch_assoc();
        $new_quantity = $row['quantity'] + $quantity;

        $update_query = "UPDATE cart 
                        SET quantity = ?, 
                            size = ?, 
                            color = ? 
                        WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("issi", $new_quantity, $size, $color, $row['id']);
        
        if ($update_stmt->execute()) {
            echo "Cart updated successfully";
        } else {
            http_response_code(500);
            echo "Error updating cart";
        }
    } else {
        // Insert new product into cart
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity, size, color) 
                        VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iiiss", $user_id, $product_id, $quantity, $size, $color);
        
        if ($insert_stmt->execute()) {
            echo "Product added to cart successfully";
        } else {
            http_response_code(500);
            echo "Error adding product to cart";
        }
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}

$conn->close();
?>