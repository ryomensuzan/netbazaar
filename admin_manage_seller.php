<?php
session_start();
require_once 'db.php';

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle approval/rejection/suspension/reactivation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seller_id'], $_POST['action'])) {
    $seller_id = intval($_POST['seller_id']);
    $action = $_POST['action'];

    $new_status = null;
    switch ($action) {
        case 'approve':
            $new_status = 'approved';
            break;
        case 'reject':
            $new_status = 'rejected';
            break;
        case 'suspend':
            $new_status = 'suspended';
            break;
        case 'reactivate':
            $new_status = 'approved';
            break;
    }

    if ($new_status !== null) {
        $stmt = $conn->prepare("UPDATE seller SET status = ? WHERE seller_id = ?");
        $stmt->bind_param("si", $new_status, $seller_id);

        if ($stmt->execute()) {
           echo "<script>alert('Seller status updated successfully.'); window.location.href = 'admin_dashboard_seller.php';</script>";
            exit;
        } else {
            error_log("Error updating seller status: " . $stmt->error);
        }
    }
}

// Filter logic
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sql = "SELECT * FROM seller";
$params = [];

if ($status_filter && in_array($status_filter, ['pending', 'approved', 'suspended', 'rejected'])) {
    $sql .= " WHERE status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status_filter);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
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
    <title>Manage Sellers - Admin Dashboard</title>
    <link rel="stylesheet" href="src/manage_seller.css? v= <?php echo filemtime('src/manage_seller.css'); ?>">
</head>
<body>
    <div class="admin-container">
        <h1>Seller Management</h1>

        <form method="GET" action="admin_manage_seller.php" style="margin-bottom: 20px;">
            <label for="status">Filter by status:</label>
            <select name="status" id="status" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="suspended" <?= $status_filter === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </form>

        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Seller Name</th>
                    <th>Company Name</th>
                    <th>Type</th>
                    <th>Tax ID</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($seller = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($seller['seller_id']) ?></td>
                        <td><?= htmlspecialchars($seller['seller_name']) ?></td>
                        <td><?= htmlspecialchars($seller['company_name']) ?></td>
                        <td><?= htmlspecialchars($seller['company_type']) ?></td>
                        <td><?= htmlspecialchars($seller['tax_id']) ?></td>
                        <td><?= htmlspecialchars($seller['email']) ?></td>
                        <td><?= htmlspecialchars($seller['status']) ?></td>
                        <td><?= htmlspecialchars($seller['created_at']) ?></td>
                        <td>
                            <form method="POST" action="manage_seller.php" style="display: inline;">     
                                <input type="hidden" name="seller_id" value="<?= $seller['seller_id'] ?>">
                                <?php if ($seller['status'] === 'pending'): ?>
                                    <button name="action" value="approve">Approve</button>
                                    <button name="action" value="reject">Reject</button>
                                <?php elseif ($seller['status'] === 'approved'): ?>
                                    <button name="action" value="suspend">Suspend</button>
                                <?php elseif ($seller['status'] === 'suspended'): ?>
                                    <button name="action" value="reactivate">Reactivate</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows === 0): ?>
                    <tr><td colspan="9">No sellers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
