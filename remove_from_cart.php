<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if cart_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    try {
        $cart_id = intval($_POST['cart_id']);
        $user_id = $_SESSION['user_id'];

        // Verify the cart item belongs to the user before deleting
        $verify_stmt = $conn->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
        $verify_stmt->bind_param("ii", $cart_id, $user_id);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();

        if ($result->num_rows === 0) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Cart item not found or unauthorized']);
            exit;
        }

        // Delete the cart item
        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $cart_id, $user_id);
        
        if ($delete_stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);
        } else {
            throw new Exception("Failed to remove item from cart");
        }

    } catch (Exception $e) {
        error_log("Error removing item from cart: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while removing the item from cart'
        ]);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method or missing cart_id'
    ]);
}

$conn->close();
?>
