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

function getStatusStyle($status) {
    $s = strtolower($status);
    if ($s === 'pending')    return ['bg' => '#fff8e6', 'color' => '#b67b2d', 'border' => 'rgba(245,166,35,0.35)'];
    if ($s === 'processing') return ['bg' => '#e6faf7', 'color' => '#009982', 'border' => 'rgba(0,184,156,0.3)'];
    if ($s === 'shipped')    return ['bg' => '#e8f1ff', 'color' => '#2d5fb6', 'border' => 'rgba(74,144,217,0.35)'];
    return ['bg' => '#e6faf7', 'color' => '#009982', 'border' => 'rgba(0,184,156,0.3)'];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tracking | ArmiePrints</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/home.css">
  <style>
    /* ── TRACKING PAGE — tracking.php ─────────────────────────── */
    .tracking-wrap {
      padding: 40px 0 60px;
      background: var(--bg);
      min-height: calc(100vh - 64px);
    }

    .page-header {
      margin-bottom: 32px;
    }

    .page-header h1 {
      font-family: 'Nunito', sans-serif;
      font-size: 26px;
      font-weight: 900;
      color: var(--text);
      margin-bottom: 4px;
    }

    .page-header p {
      font-size: 13px;
      color: var(--muted);
    }

    .page-header .accent-line {
      width: 44px;
      height: 3px;
      background: var(--teal);
      border-radius: 99px;
      margin-top: 10px;
    }

    /* Order cards */
    .order-history-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .tracking-order-card {
      background: #fff;
      border-radius: 18px;
      padding: 24px;
      border: 1px solid var(--border);
      box-shadow: 0 2px 12px rgba(0,0,0,0.05);
      transition: box-shadow 0.2s, transform 0.2s;
    }

    .tracking-order-card:hover {
      box-shadow: 0 8px 28px rgba(0,0,0,0.09);
      transform: translateY(-2px);
    }

    .tracking-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      margin-bottom: 16px;
    }

    .tracking-header h2 {
      font-family: 'Nunito', sans-serif;
      font-size: 17px;
      font-weight: 900;
      color: var(--text);
      margin-bottom: 3px;
    }

    .tracking-header p {
      color: var(--muted);
      font-size: 12px;
    }

    .tracking-status {
      padding: 6px 14px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.4px;
      text-transform: uppercase;
      white-space: nowrap;
      flex-shrink: 0;
      border: 1.5px solid transparent;
    }

    .tracking-address {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 14px;
      padding: 10px 14px;
      background: var(--bg);
      border-radius: 10px;
      border: 1px solid var(--border);
      line-height: 1.5;
    }

    .tracking-total {
      font-family: 'Nunito', sans-serif;
      font-size: 18px;
      font-weight: 900;
      color: var(--coral);
      margin-bottom: 16px;
    }

    .tracking-items {
      display: flex;
      flex-direction: column;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid var(--border);
    }

    .tracking-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 13px 16px;
      border-bottom: 1px solid var(--border);
      background: #fff;
      transition: background 0.15s;
    }

    .tracking-item:last-child { border-bottom: none; }
    .tracking-item:hover { background: var(--bg); }

    .tracking-item img {
      width: 58px;
      height: 58px;
      border-radius: 10px;
      object-fit: cover;
      border: 1px solid var(--border);
      flex-shrink: 0;
    }

    .tracking-item-info { flex: 1; }
    .tracking-item-info h4 { font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 3px; }
    .tracking-item-info p  { color: var(--muted); font-size: 12px; }

    .tracking-item strong {
      font-size: 13px;
      font-weight: 800;
      color: var(--coral);
      flex-shrink: 0;
    }

    .card-footer {
      margin-top: 18px;
      display: flex;
      justify-content: flex-end;
    }

    .btn-solid {
      display: inline-block;
      padding: 10px 26px;
      border-radius: 999px;
      border: none;
      background: var(--coral);
      color: #fff;
      font-size: 12px;
      font-weight: 800;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      text-decoration: none;
      box-shadow: 0 6px 16px rgba(232,64,64,0.22);
      transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
    }

    .btn-solid:hover {
      background: var(--coral-dark);
      transform: translateY(-2px);
      box-shadow: 0 10px 22px rgba(232,64,64,0.3);
    }

    /* Empty state */
    .empty-history {
      text-align: center;
      padding: 60px 24px;
      color: var(--muted);
    }

    .empty-history .empty-icon {
      font-size: 52px;
      margin-bottom: 16px;
    }

    .empty-history h3 {
      font-family: 'Nunito', sans-serif;
      font-size: 22px;
      font-weight: 900;
      color: var(--text);
      margin-bottom: 8px;
    }

    .empty-history p {
      font-size: 14px;
      margin-bottom: 24px;
      max-width: 320px;
      margin-left: auto;
      margin-right: auto;
    }

    .empty-history a {
      display: inline-block;
      padding: 12px 32px;
      background: var(--teal);
      color: #fff;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
      box-shadow: 0 6px 18px rgba(0,184,156,0.25);
      transition: background 0.2s, transform 0.2s;
      text-decoration: none;
    }

    .empty-history a:hover {
      background: var(--teal-dark);
      transform: translateY(-2px);
    }

    @media (max-width: 600px) {
      .tracking-header { flex-direction: column; gap: 8px; }
      .tracking-wrap { padding: 24px 0 40px; }
    }
  </style>
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

    <div class="page-header">
      <h1>Active Orders</h1>
      <p>Track your orders in real time</p>
      <div class="accent-line"></div>
    </div>

    <?php if ($active_orders->num_rows > 0): ?>

      <div class="order-history-list">
        <?php while ($order = $active_orders->fetch_assoc()): ?>

          <?php
            $itemStmt = $db->prepare("
              SELECT order_items.quantity, order_items.unit_price,
                     products.name, products.image
              FROM order_items
              JOIN products ON order_items.product_id = products.id
              WHERE order_items.order_id = ?
            ");
            $itemStmt->bind_param("i", $order['id']);
            $itemStmt->execute();
            $items = $itemStmt->get_result();
            $style = getStatusStyle($order['status']);
          ?>

          <div class="tracking-order-card">

            <div class="tracking-header">
              <div>
                <h2>Order #<?= $order['id'] ?></h2>
                <p><?= date("F d, Y h:i A", strtotime($order['created_at'])) ?></p>
              </div>
              <div class="tracking-status" style="
                background: <?= $style['bg'] ?>;
                color: <?= $style['color'] ?>;
                border-color: <?= $style['border'] ?>;
              ">
                <?= htmlspecialchars(ucfirst($order['status'])) ?>
              </div>
            </div>

            <div class="tracking-address">
              📍 <?= htmlspecialchars($order['shipping_address']) ?>
            </div>

            <div class="tracking-total">
              Total: ₱<?= number_format($order['total_amount'], 2) ?>
            </div>

            <div class="tracking-items">
              <?php while ($item = $items->fetch_assoc()): ?>
                <?php $img = 'images/products/' . ($item['image'] ?? 'placeholder.jpg'); ?>
                <div class="tracking-item">
                  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                  <div class="tracking-item-info">
                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                    <p>Qty: <?= $item['quantity'] ?></p>
                  </div>
                  <strong>₱<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></strong>
                </div>
              <?php endwhile; ?>
            </div>

            <div class="card-footer">
              <a href="tracking-details.php?id=<?= $order['id'] ?>" class="btn-solid">
                View Tracking →
              </a>
            </div>

          </div>

        <?php endwhile; ?>
      </div>

    <?php else: ?>

      <div class="empty-history">
        <div class="empty-icon">📭</div>
        <h3>No Active Orders</h3>
        <p>You don't have any orders being processed or shipped right now.</p>
        <a href="products.php">Shop Now</a>
      </div>

    <?php endif; ?>

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