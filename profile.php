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
$user_id = $_SESSION['user_id'];
$logged_in = true;

$success = '';
$error = '';

$stmt = $db->prepare("
    SELECT id, full_name, email, phone, role, created_at
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($full_name === '') {

        $error = 'Full name is required.';

    } else {

        $update = $db->prepare("
            UPDATE users
            SET full_name = ?, phone = ?
            WHERE id = ?
        ");

        $update->bind_param(
            "ssi",
            $full_name,
            $phone,
            $user_id
        );

        if ($update->execute()) {

            $_SESSION['user_name'] = $full_name;

            $success = 'Profile updated successfully.';

            $stmt = $db->prepare("
                SELECT id, full_name, email, phone, role, created_at
                FROM users
                WHERE id = ?
            ");

            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $user = $stmt->get_result()->fetch_assoc();

        } else {

            $error = 'Something went wrong.';
        }
    }
}

/* Cart Count */
$cart_count = 0;

$cartQuery = $db->prepare("
    SELECT COALESCE(SUM(quantity), 0) AS total
    FROM cart
    WHERE user_id = ?
");

$cartQuery->bind_param("i", $user_id);
$cartQuery->execute();

$cart_count =
    $cartQuery->get_result()->fetch_assoc()['total'] ?? 0;

/* Order Stats */
$orderStmt = $db->prepare("
    SELECT 
        COUNT(*) AS order_count,
        COALESCE(SUM(total_amount), 0) AS order_total
    FROM orders
    WHERE user_id = ?
");

$orderStmt->bind_param("i", $user_id);
$orderStmt->execute();

$orderData = $orderStmt->get_result()->fetch_assoc();

$order_count = $orderData['order_count'];
$order_total = $orderData['order_total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ArmiePrints</title>

    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>

<!-- ══ NAVBAR ═══════════════════════════════════════ -->
<nav class="navbar">

    <div class="nav-inner">

        <a href="index.php" class="nav-logo">
            <img src="images/logo.png" alt="ArmiePrints"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">

            <span class="logo-fallback">
                Armie<span>Prints</span>
            </span>
        </a>

        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="customorder.php">Custom Order</a></li>
            <li><a href="tracking.php">Tracking</a></li>
            <li><a href="about.php">About</a></li>
        </ul>

        <div class="nav-actions">

            <a href="cart.php" class="cart-btn" aria-label="Cart">

                <svg width="22" height="22"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round">

                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>

                <span class="cart-count">
                    <?= $cart_count ?>
                </span>

            </a>

            <a href="profile.php" class="btn-signed-in">
                Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
            </a>

            <a href="logout.php" class="btn-logout-nav">
                Logout
            </a>

        </div>

        <button class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">

        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="#">Custom Order</a>
        <a href="#">Tracking</a>
        <a href="about.php">About</a>

        <a href="profile.php" class="btn-signed-in">
            Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
        </a>

        <a href="logout.php" class="btn-logout-nav">
            Logout
        </a>

    </div>

</nav>

<!-- ══ PROFILE PAGE ═══════════════════════════════ -->
<main class="profile-page">

    <!-- Sidebar -->
    <aside class="profile-sidebar">

        <div class="profile-avatar">
            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
        </div>

        <h2>
            <?= htmlspecialchars($user['full_name']) ?>
        </h2>

        <p>
            <?= htmlspecialchars($user['email']) ?>
        </p>

        <div class="profile-menu">

            <a href="profile.php" class="active">
                My Profile
            </a>

            <a href="order-history.php">
                Order History
            </a>

            <a href="cart.php">
                My Cart
            </a>

            <a href="logout.php">
                Logout
            </a>

        </div>

    </aside>

    <!-- Content -->
    <section class="profile-content">

        <!-- Stats -->
        <div class="profile-stats">

            <div class="stat-card">
                <span>Total Orders</span>
                <strong><?= $order_count ?></strong>
            </div>

            <div class="stat-card">
                <span>Total Spent</span>
                <strong>₱ <?= number_format($order_total, 2) ?></strong>
            </div>

            <div class="stat-card">
                <span>Account Type</span>
                <strong>
                    <?= htmlspecialchars($user['role'] ?? 'customer') ?>
                </strong>
            </div>

        </div>

        <!-- Form -->
        <div class="profile-card">

            <h1>Manage Profile</h1>

            <p class="profile-subtitle">
                Update your personal information here.
            </p>

            <?php if ($success): ?>
                <div class="alert success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">

                    <label>Full Name</label>

                    <input
                        type="text"
                        name="full_name"
                        value="<?= htmlspecialchars($user['full_name']) ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>Email Address</label>

                    <input
                        type="email"
                        value="<?= htmlspecialchars($user['email']) ?>"
                        disabled
                    >

                    <small>
                        Email cannot be changed.
                    </small>

                </div>

                <div class="form-group">

                    <label>Phone Number</label>

                    <input
                        type="text"
                        name="phone"
                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                        placeholder="09xxxxxxxxx"
                    >

                </div>

                <button type="submit" class="save-btn">
                    Save Changes
                </button>

            </form>

        </div>

    </section>

</main>

<script src="js/home.js"></script>

</body>
</html>