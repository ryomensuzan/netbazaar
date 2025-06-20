<?php
session_start();
require_once 'db.php'; // Database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";


// Change password
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($current, $hashed)) {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new, $user_id);
        $stmt->execute();
        $message = "Password changed successfully.";
    } else {
        $message = "Incorrect current password.";
    }
}

// Delete account
if (isset($_POST['delete_account'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    session_destroy();
    header("Location: goodbye.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <title>User Settings</title>
    <style>
        .settings-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 10px auto;
            text-align: center;
        }
        h3 {
            cursor: pointer;
            color: #333;
        }
        h3:hover {
            color: #007BFF;
        }
        form {
            margin-top: 10px;
        }
        input[type="password"] {
            width: 40%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .settingBtn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .settingBtn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
   <div class="settings-container">
    <h3 onclick="displayForm()">1. Change password</h3>
    <form method="POST" style="display: none;" id="changePasswordForm">
        <input type="password" name="current_password" placeholder="Current Password" required><br>
        <input type="password" name="new_password" placeholder="New Password" required><br>
        <button type="submit" name="change_password" class="settingBtn">Change Password</button>
    </form>

    <h3 onclick="displaydeleteoption()">2. Account Actions</h3>

    <form style="display: none;" id="deleteprofileForm" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
        <button type="submit" name="delete_account" class="settingBtn">Delete Account</button>
    </form>

    <?php if ($message): ?>
        <p style="color: green;"><strong><?= $message ?></strong></p>
    <?php endif; ?>
   </div>
    <script>
        function displayForm() {
            var form = document.getElementById('changePasswordForm');
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }

        function displaydeleteoption() {
            var form = document.getElementById('deleteprofileForm');
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }
    </script>
</body>
</html>
