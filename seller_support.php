<?php
session_start();
require_once 'db.php'; // Include database connection

// Check if the seller is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$feedback_message = '';
$submit_success = false;

// Handle Feedback Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback_message'])) {
    $feedback_message = trim($_POST['feedback_message']);

    if (!empty($feedback_message)) {
        $stmt = $conn->prepare("INSERT INTO support_feedback (seller_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $seller_id, $feedback_message);

        if ($stmt->execute()) {
            $submit_success = true;
            $feedback_message = ''; // Clear form
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch feedback history
$stmt = $conn->prepare("SELECT message, response, created_at, responded_at FROM support_feedback WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $seller_id);
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
    <title>Seller Support & Feedback</title>
    <link rel="stylesheet" href="src/seller_support.css">
</head>
<body>

<div class="container">
    <h2>Contact Admin / Support</h2>

    <?php if ($submit_success): ?>
        <div class="success-message">Feedback submitted successfully!</div>
    <?php endif; ?>

    <form method="POST" action="support_feedback.php">
        <textarea name="feedback_message" required placeholder="Write your message to admin..." rows="5" cols="60"><?= htmlspecialchars($feedback_message) ?></textarea>
        <br>
        <button type="submit">Submit Feedback</button>
    </form>

    <hr>

    <h3>Response History</h3>
    <?php if ($result->num_rows > 0): ?>
        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>Message</th>
                    <th>Response</th>
                    <th>Submitted At</th>
                    <th>Responded At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['message']) ?></td>
                        <td><?= $row['response'] ? htmlspecialchars($row['response']) : '<em>Pending</em>' ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td><?= $row['responded_at'] ? $row['responded_at'] : '-' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No feedback submitted yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
