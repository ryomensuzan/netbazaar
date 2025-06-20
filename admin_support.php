<?php
require_once 'db.php';
session_start();

// Ensure only admin can access this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$responseMessage = "";

// Handle admin's response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response']) && isset($_POST['message_id'])) {
    $stmt = $conn->prepare("UPDATE support_feedback SET response = ?, responded_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $_POST['response'], $_POST['message_id']);
    if ($stmt->execute()) {
        $responseMessage = "Response sent successfully.";
    } else {
        $responseMessage = "Failed to send response.";
    }
}

// Fetch all messages
$stmt = $conn->prepare(" SELECT sf.id, sf.message, sf.response, sf.created_at, sf.responded_at, s.seller_name, s.email 
    FROM support_feedback sf
    JOIN seller s ON sf.seller_id = s.seller_id
    ORDER BY sf.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
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
    <title>Admin Support Panel</title>
    <link rel="stylesheet" href="src/admin_support.css">
</head>
<body>
    <h2>Seller Support Feedback</h2>

    <?php if ($responseMessage): ?>
        <p style="color: green;"><strong><?= htmlspecialchars($responseMessage) ?></strong></p>
    <?php endif; ?>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Seller Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Received</th>
                <th>Response</th>
                <th>Responded</th>
                <th>Reply</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['seller_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= $row['response'] ? nl2br(htmlspecialchars($row['response'])) : '<em>Not Responded</em>' ?></td>
                <td><?= $row['responded_at'] ?: '-' ?></td>
                <td>
                    <?php if (!$row['response']): ?>
                        <form method="POST" action="admin_support.php">
                            <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
                            <textarea name="response" rows="3" cols="30" placeholder="Type response..." required></textarea><br>
                            <button type="submit">Send</button>
                        </form>
                    <?php else: ?>
                        <em>Responded</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
