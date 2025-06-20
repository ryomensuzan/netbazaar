<?php
session_start();
require_once 'db.php';

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Validate sort and order
$allowedSortColumns = ['name', 'email', 'created_at'];
$allowedOrderDirections = ['ASC', 'DESC'];

// Retrieve values from GET and set defaults
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSortColumns) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) && in_array($_GET['order'], $allowedOrderDirections) ? $_GET['order'] : 'DESC';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the query
$query = "SELECT * FROM users WHERE full_name LIKE ? OR email LIKE ? ORDER BY $sort $order";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$searchTerm = "%$search%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

// Handle user actions (deactivate, delete, reset password)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deactivate_id'])) {
        $id = $_POST['deactivate_id'];
        $conn->query("UPDATE users SET status='deactivated' WHERE id=$id");
    } elseif (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $conn->query("DELETE FROM users WHERE id=$id");
    } elseif (isset($_POST['reset_password_id'])) {
        $id = $_POST['reset_password_id'];
        $newPassword = password_hash('default123', PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$newPassword' WHERE id=$id");
    }
   echo "<script>alert('Action completed successfully.'); window.location.href = 'admin_dashboard.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="assets/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <title>Manage Users - Net Bazaar</title>
    <link rel="stylesheet" href="src/admin.css?v=<?php echo filemtime('src/admin.css'); ?>">
    <link rel="stylesheet" href="src/manage_user.css?v=<?php echo filemtime('src/manage_user.css'); ?>">
</head>
<body>
    <h2>User Management</h2>

    <form method="GET" action="manage_user.php" class="filter-form">
        <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
        <select name="sort">
            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
            <option value="email" <?= $sort === 'email' ? 'selected' : '' ?>>Email</option>
            <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Registered Date</option>
        </select>
        <select name="order">
            <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Ascending</option>
            <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Descending</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    <table class="user-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Registered Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['created_at']) ?></td>
                <td><?= $user['status'] ?? 'active' ?></td>
                <td>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="deactivate_id" value="<?= $user['id'] ?>">
                        <button type="submit">Deactivate</button>
                    </form>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                        <button type="submit" onclick="return confirm('Delete user?')">Delete</button>
                    </form>
                    <form method="POST" style="display:inline-block">
                        <input type="hidden" name="reset_password_id" value="<?= $user['id'] ?>">
                        <button type="submit">Reset Password</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
