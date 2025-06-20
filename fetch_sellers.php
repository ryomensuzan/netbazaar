<?php
require_once 'db.php';

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sql = "SELECT * FROM seller";

if ($status_filter && in_array($status_filter, ['pending', 'approved', 'suspended', 'rejected'])) {
    $sql .= " WHERE status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status_filter);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

while ($seller = $result->fetch_assoc()): ?>
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
            <form method="POST" style="display: inline;">
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