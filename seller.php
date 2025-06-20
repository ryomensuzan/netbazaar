<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seller_name = trim($_POST['seller_name']);
    $company_name = trim($_POST['company_name']);
    $company_type = $_POST['company_type'];
    $tax_id = trim($_POST['tax_id']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];

    // Validation
    if (
        empty($seller_name) || empty($company_name) || empty($company_type) || empty($tax_id) ||
        empty($email) || empty($address) || empty($password)
    ) {
        $_SESSION['seller_error'] = "Please fill in all fields.";
        header("Location: index.php");
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['seller_error'] = "Invalid email format.";
        header("Location: index.php");
        exit;
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT seller_id FROM seller WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['seller_error'] = "Email is already registered as a seller.";
            $stmt->close();
            $conn->close();
            header("Location: index.php");
            exit;
        }

        $stmt->close();

        // Insert new seller
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO seller 
            (seller_name, company_name, company_type, tax_id, email, address, password, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'seller', 'pending', NOW())");

        $stmt->bind_param("sssssss", $seller_name, $company_name, $company_type, $tax_id, $email, $address, $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['seller_success'] = "Seller registration submitted. Your account is under review.";
        } else {
            $_SESSION['seller_error'] = "Something went wrong. Please try again.";
        }

        $stmt->close();
        $conn->close();

        header("Location: index.php");
        exit;
    }
}
?>
