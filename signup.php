<?php
session_start();
require_once 'db.php'; // your mysqli connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['signup_error'] = "All fields are required.";
        header("Location: index.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = "Invalid email format.";
        header("Location: index.php");
        exit;
    }

    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['signup_error'] = "Email already registered.";
        header("Location: index.php");
        exit;
    }

    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['signup_success'] = "Signup successful! Please log in.";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['signup_error'] = "Something went wrong. Please try again.";
        header("Location: index.php");
        exit;
    }
}
