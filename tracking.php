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
$logged_in = true;

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

/* Active Orders Only */
$stmt = $db->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    AND LOWER(status) NOT IN ('completed', 'delivered', 'cancelled')
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$active_orders = $stmt->get_result();
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tracking | ArmiePrints</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,700;0,800;0,900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="css/home.css">
  <link rel="stylesheet" href="css/tracking.css">
</head>

<body>

<nav class="navbar">
  <div class="nav-inner">

    <a href="index.php" class="nav-logo">
      <img src="images/logo.png" alt="ArmiePrints"
           onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span class="logo-fallback">Armie<span>Prints</span></span>
    </a>

    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="products.php">Products</a></li>
      <li><a href="customorder.php">Custom Order</a></li>
      <li><a class="active" href="tracking.php">Tracking</a></li>
      <li><a href="about.php">About</a></li>
    </ul>

    <div class="nav-actions">
      <a href="cart.php" class="cart-btn" aria-label="Cart">
        🛒
        <span class="cart-count"><?= $cart_count ?></span>
      </a>

      <a href="profile.php" class="btn-signed-in">
        Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
      </a>

      <a href="logout.php" class="btn-logout-nav">Logout</a>
    </div>

    <button class="hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>

  </div>

  <div class="mobile-menu" id="mobileMenu">
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="customorder.php">Custom Order</a>
    <a href="tracking.php">Tracking</a>
    <a href="about.php">About</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<main class="tracking-wrap">
  <div class="container">

    <section class="reco stat-card">

      <div class="reco-title">Active Orders</div>
      <div class="reco-line"></div>

      <?php if ($active_orders->num_rows > 0): ?>

        <div class="order-history-list">

          <?php while ($order = $active_orders->fetch_assoc()): ?>

            <?php
              $itemStmt = $db->prepare("
                SELECT 
                    order_items.quantity,
                    order_items.unit_price,
                    products.name,
                    products.image
                FROM order_items
                JOIN products ON order_items.product_id = products.id
                WHERE order_items.order_id = ?
              ");

              $itemStmt->bind_param("i", $order['id']);
              $itemStmt->execute();

              $items = $itemStmt->get_result();
            ?>

            <div class="tracking-order-card">

              <div class="tracking-header">
                <div>
                  <h2>Order #<?= $order['id'] ?></h2>
                  <p>
                    <?= date("F d, Y h:i A", strtotime($order['created_at'])) ?>
                  </p>
                </div>

                <div class="tracking-status">
                  <?= htmlspecialchars(ucfirst($order['status'])) ?>
                </div>
              </div>

              <div class="tracking-address">
                <?= htmlspecialchars($order['shipping_address']) ?>
              </div>

              <div class="tracking-total">
                Total: ₱ <?= number_format($order['total_amount'], 2) ?>
              </div>

              <div class="tracking-items">

                <?php while ($item = $items->fetch_assoc()): ?>

                  <?php
                    $img = 'images/products/' . ($item['image'] ?? 'placeholder.jpg');
                  ?>

                  <div class="tracking-item">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>">

                    <div class="tracking-item-info">
                      <h4><?= htmlspecialchars($item['name']) ?></h4>
                      <p>Qty: <?= $item['quantity'] ?></p>
                    </div>

                    <strong>
                      ₱ <?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                    </strong>
                  </div>

                <?php endwhile; ?>

              </div>

              <div style="margin-top:20px; text-align:right;">
                <a href="tracking-details.php?id=<?= $order['id'] ?>" class="btn-solid">
                  View Tracking
                </a>
              </div>

            </div>

          <?php endwhile; ?>

        </div>

      <?php else: ?>

        <div class="empty-history">
          <h3>No Active Orders</h3>
          <p>You currently don't have any orders being processed or shipped.</p>
          <a href="products.php">Shop Now</a>
        </div>

      <?php endif; ?>

    </section>

  </div>
</main>

<footer class="footer">
  <div class="container footer-grid">

    <div class="footer-brand">
      <div class="footer-logo">ArmiePrints</div>
      <p>Your favorite sticker-style shop bringing cute and creativity to every magnet surface. Handcrafted with love.</p>
    </div>

    <div class="footer-col">
      <h4>Shop</h4>
      <ul>
        <li><a href="products.php">New Arrivals</a></li>
        <li><a href="products.php">Best Sellers</a></li>
        <li><a href="customorder.php">Custom Orders</a></li>
        <li><a href="products.php">Sale</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Help</h4>
      <ul>
        <li><a href="tracking.php">Track Order</a></li>
        <li><a href="#">Shipping Info</a></li>
        <li><a href="#">Returns</a></li>
        <li><a href="#">FAQ</a></li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© <?= date('Y') ?> ArmiePrints. All rights reserved.</p>
  </div>
</footer>

<script src="js/home.js"></script>

</body>
</html>