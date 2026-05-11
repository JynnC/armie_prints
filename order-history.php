<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$db = getDB();
$user_id = (int) $_SESSION['user_id'];

/* Cart Count */
$cart_count = 0;
$cartQuery = $db->prepare("
    SELECT COALESCE(SUM(quantity), 0) AS total
    FROM cart
    WHERE user_id = ?
");
$cartQuery->bind_param("i", $user_id);
$cartQuery->execute();
$cart_count = $cartQuery->get_result()->fetch_assoc()['total'] ?? 0;

/* Orders */
$stmt = $db->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History - ArmiePrints</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">
            <img src="images/logo.png" alt="ArmiePrints">
            <span class="logo-fallback">Armie<span>Prints</span></span>
        </a>

        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="customorder.php">Custom Order</a></li>
            <li><a href="tracking.php">Tracking</a></li>
            <li><a href="about.php">About</a></li>
        </ul>

        <div class="nav-actions">
            <a href="cart.php" class="cart-btn">
                🛒
                <span class="cart-count"><?= $cart_count ?></span>
            </a>

            <a href="profile.php" class="btn-signed-in">
                Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
            </a>

            <a href="logout.php" class="btn-logout-nav">Logout</a>
        </div>
    </div>
</nav>

<main class="profile-page">

    <aside class="profile-sidebar">
        <div class="profile-avatar">
            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
        </div>

        <h2><?= htmlspecialchars($_SESSION['user_name']) ?></h2>

        <div class="profile-menu">
            <a href="profile.php">My Profile</a>
            <a href="order-history.php" class="active">Order History</a>
            <a href="cart.php">My Cart</a>
            <a href="logout.php">Logout</a>
        </div>
    </aside>

    <section class="profile-content">
        <div class="profile-card">
            <h1>Order History</h1>
            <p class="profile-subtitle">View and track your previous orders.</p>

            <?php if ($orders->num_rows > 0): ?>

                <div class="order-history-list">

                    <?php while ($order = $orders->fetch_assoc()): ?>

                        <div class="history-card">
                            <div>
                                <h3>Order #<?= $order['id'] ?></h3>
                                <p>
                                    Placed on 
                                    <?= date("F d, Y h:i A", strtotime($order['created_at'])) ?>
                                </p>
                                <span class="history-status">
                                    <?= htmlspecialchars(ucfirst($order['status'])) ?>
                                </span>
                            </div>

                            <div class="history-right">
                                <strong>₱ <?= number_format($order['total_amount'], 2) ?></strong>

                                <a href="tracking.php?id=<?= $order['id'] ?>" class="track-btn">
                                    Track Order
                                </a>
                            </div>
                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="empty-history">
                    <h3>No orders yet</h3>
                    <p>You have not placed any orders yet.</p>
                    <a href="products.php">Shop Now</a>
                </div>

            <?php endif; ?>
        </div>
    </section>

</main>

<script src="js/home.js"></script>
<?php include 'chat-widget.php'; ?>

</body>
</html>