<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $new_email = trim($_POST['email']);
    $new_password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (empty($new_email)) {
        $message = "Email cannot be empty.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (!empty($new_password) && $new_password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        // Update email and/or password
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET email = ?, password = ? WHERE admin_id = ?");
            $stmt->bind_param("ssi", $new_email, $hashed, $admin_id);
        } else {
            $stmt = $conn->prepare("UPDATE admin SET email = ? WHERE admin_id = ?");
            $stmt->bind_param("si", $new_email, $admin_id);
        }

        if ($stmt->execute()) {
            $_SESSION['email'] = $new_email;
            echo "<script>alert('Account updated successfully.');
            window.location.href = 'admin_account.php';</script>";
            exit;    
        } else {
            echo "<script>alert('Error updating account. Please try again later.');
            window.location.href = 'admin_account.php';</script>";
        }
    }
}

// Get current admin details
$stmt = $conn->prepare("SELECT email FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Account - Net Bazaar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="src/admin_account.css?v=<?php echo time(); ?>">
    <link rel="icon" href="assets/logo.png" type="image/x-icon">

</head>
<body>
    <div class="admin-account-container">
        <h2>Admin Account Settings</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>

            <label>New Password</label>
            <input type="password" name="password" placeholder="Leave blank to keep unchanged">

            <label>Confirm Password</label>
            <input type="password" name="confirm" placeholder="Leave blank to keep unchanged">

            <button type="submit" name="update_account">Update Account</button>
        </form>

        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" name="logout">Logout Securely</button>
        </form>
    </div>
</body>
</html>
