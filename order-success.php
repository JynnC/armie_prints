<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Placed - ArmiePrints</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>

<div style="max-width:600px; margin:100px auto; text-align:center; font-family:Poppins, sans-serif; background:#fff; padding:40px; border-radius:20px; box-shadow:0 8px 25px rgba(0,0,0,0.08);">
    <h1>Order Placed Successfully! 🎉</h1>
    <p>Your order number is:</p>
    <h2>#<?= $order_id ?></h2>

    <a href="products.php" style="display:inline-block; margin-top:25px; background:#ff4f6d; color:#fff; padding:12px 24px; border-radius:12px; text-decoration:none;">
        Continue Shopping
    </a>
</div>

<?php include 'chat-widget.php'; ?>
</body>
</html>