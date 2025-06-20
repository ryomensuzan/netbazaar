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

// Fetch seller data
$stmt = $conn->prepare("SELECT * FROM seller WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $company_name = $_POST['company_name'];
    $company_type = $_POST['company_type'];
    $tax_id = $_POST['tax_id'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Optional: Logo Upload
    $logo_path = $seller['logo'];
    if ($_FILES['logo']['name']) {
        $logo_path = 'uploads/' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path);
    }

    $stmt = $conn->prepare("UPDATE seller SET company_name=?, company_type=?, tax_id=?, email=?, address=?, logo=? WHERE seller_id=?");
    $stmt->bind_param("ssssssi", $company_name, $company_type, $tax_id, $email, $address, $logo_path, $seller_id);
    $stmt->execute();
    $message = "Profile updated successfully!";
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
    <title>Seller Account Settings</title>
    <link rel="stylesheet" href="src/seller_account.css">
</head>

<body>
        <h3>Edit Company Profile</h3>
        <div>
            <?php if ($message) echo "<p class='success-message'>$message</p>"; ?>

            <form method="POST" enctype="multipart/form-data">
                <label>Company Name:</label>
                <input type="text" name="company_name" value="<?= htmlspecialchars($seller['company_name']) ?>" required>

                <label>Company Type:</label>
                <select name="company_type">
                    <option value="Private" <?= $seller['company_type'] === 'Private' ? 'selected' : '' ?>>Private</option>
                    <option value="Public" <?= $seller['company_type'] === 'Public' ? 'selected' : '' ?>>Public</option>
                    <option value="LLP" <?= $seller['company_type'] === 'LLP' ? 'selected' : '' ?>>LLP</option>
                    <option value="Non-Profit" <?= $seller['company_type'] === 'Non-Profit' ? 'selected' : '' ?>>Non-Profit</option>
                </select>

                <label>Tax ID:</label>
                <input type="text" name="tax_id" value="<?= htmlspecialchars($seller['tax_id']) ?>" required>

                <label>Contact Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($seller['email']) ?>" required>

                <label>Address:</label>
                <textarea name="address" required><?= htmlspecialchars($seller['address']) ?></textarea>

                <label>Logo (optional):</label>
                <input type="file" name="logo" accept="image/*">

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </div>
</body>

</html>