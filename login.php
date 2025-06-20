<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in all fields.";
        header("Location: index.php");
        exit;
    }

    // Check Admin
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $adminResult = $stmt->get_result();

    if ($admin = $adminResult->fetch_assoc()) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['email'] = $admin['email'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Wrong password.";
            header("Location: index.php");
            exit;
        }
    }

    // Check Seller
    $stmt = $conn->prepare("SELECT * FROM seller WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $sellerResult = $stmt->get_result();

    if ($seller = $sellerResult->fetch_assoc()) {
        if ($seller['status'] === 'pending') {
            $_SESSION['login_error'] = "Your account is under review.";
            header("Location: index.php");
            exit;
        } elseif (password_verify($password, $seller['password'])) {
            $_SESSION['user_id'] = $seller['seller_id'];
            $_SESSION['role'] = 'seller';
            $_SESSION['email'] = $seller['email'];
            header("Location: seller_dashboard.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Wrong password.";
            header("Location: index.php");
            exit;
        }
    }

    // Check User
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $userResult = $stmt->get_result();

    if ($user = $userResult->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'user';
            $_SESSION['email'] = $user['email'];
            header("Location: user_dashboard.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Wrong password.";
            header("Location: index.php");
            exit;
        }
    }

    // No match
    $_SESSION['login_error'] = "Email does not exist.";
    header("Location: index.php");
    exit;
}
?>