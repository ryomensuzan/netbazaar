<?php
session_start();
require_once 'db.php'; // Include database connection

// Check if the seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$message = "";

// Handle Password Change
if (isset($_POST['change_password'])) {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE seller SET password=? WHERE seller_id=?");
    $stmt->bind_param("si", $new_password, $seller_id);
    $stmt->execute();
    $message = "Password changed successfully!";
}

// Handle Account Deactivation
if (isset($_POST['deactivate_account'])) {
    $stmt = $conn->prepare("UPDATE seller SET status='suspended' WHERE seller_id=?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    session_destroy();
    header("Location: seller_login.php");
    exit();
}

// Handle Reactivation (Only if needed separately)
if (isset($_POST['reactivate_account'])) {
    $stmt = $conn->prepare("UPDATE seller SET status='approved' WHERE seller_id=?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $message = "Account reactivated!";
}
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
    <title>Seller Settings</title>
    <style>
        body {
            font-family: 'Familjen Grotesk', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .container {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        form {
            margin-bottom: 20px;
        }

        input[type="password"] {
            width: 95%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button:hover {
            background-color: #218838;
        }

        .success-message {
            color: green;
        }

        .logout {
            text-align: center;
            margin-top: 20px;
        }

        .logout button {
            background-color: #dc3545;
        }

        .logout button:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <h2>Seller Account Settings</h2>
    <div class="container">
        <div>
            <?php if ($message) echo "<p class='success-message'>$message</p>"; ?>
            <form method="POST">
                <h3>Change Password</h3>
                <input type="password" name="new_password" placeholder="New Password" required>
                <button type="submit" name="change_password">Update Password</button>
            </form>
        </div>

        <div>
            <?php if ($message) echo "<p class='success-message'>$message</p>"; ?>
            <form method="POST">
                <h3>Delete/Deactivate Account</h3>
                <button type="submit" name="deactivate_account" onclick="return confirm('Are you sure you want to deactivate your account?')">Deactivate Account</button>
            </form>
        </div>

        <div class="logout">
            <form method="POST" action="logout.php">
                <h3>Logout</h3>
                <button type="submit">Logout</button>
            </form>
        </div>

    </div>
</body>

</html>