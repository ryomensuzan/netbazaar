<?php
session_start();

// Redirect to the user dashboard with an error message
echo "<script>alert('Payment failed. Please try again.'); window.location.href = 'user_dashboard.php';</script>";
?>