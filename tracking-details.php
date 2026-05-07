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

$order_id = (int) ($_GET['id'] ?? 0);

if ($order_id <= 0) {
    header("Location: tracking.php");
    exit;
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

/* Order */
$stmt = $db->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
    AND user_id = ?
");

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: tracking.php");
    exit;
}

/* Order Items */
$itemStmt = $db->prepare("
    SELECT 
        order_items.quantity,
        order_items.unit_price,
        products.id AS product_id,
        products.name,
        products.image
    FROM order_items
    JOIN products
        ON order_items.product_id = products.id
    WHERE order_items.order_id = ?
");

$itemStmt->bind_param("i", $order['id']);
$itemStmt->execute();

$order_items = $itemStmt->get_result();

$status = strtolower($order['status']);

function isStepDone($current, $step) {

    $steps = [
        'pending' => 1,
        'processing' => 2,
        'shipped' => 3,
        'delivered' => 4,
        'completed' => 5,
        'cancelled' => 0
    ];

    return ($steps[$current] ?? 1) >= $step;
}

function formatDateTime($date) {

    if (!$date) return '';

    return date(
        "m/d/Y h:i A",
        strtotime($date)
    );
}

$created_at = $order['created_at'] ?? '';
$updated_at = $order['updated_at'] ?? $created_at;
?>

<!doctype html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>
Tracking Details | ArmiePrints
</title>

<link rel="stylesheet" href="css/home.css">
<link rel="stylesheet" href="css/tracking.css">

</head>

<body>

<nav class="navbar">

  <div class="nav-inner">

    <a href="index.php" class="nav-logo">

      <img src="images/logo.png"
           alt="ArmiePrints">

      <span class="logo-fallback">
        Armie<span>Prints</span>
      </span>

    </a>

    <ul class="nav-links">

      <li><a href="index.php">Home</a></li>
      <li><a href="products.php">Products</a></li>
      <li><a href="customorder.php">Custom Order</a></li>
      <li><a class="active" href="tracking.php">Tracking</a></li>
      <li><a href="about.php">About</a></li>

    </ul>

    <div class="nav-actions">

      <a href="cart.php" class="cart-btn">

        🛒

        <span class="cart-count">
          <?= $cart_count ?>
        </span>

      </a>

      <a href="profile.php" class="btn-signed-in">

        Hello,
        <?= htmlspecialchars(
          explode(' ', $_SESSION['user_name'])[0]
        ) ?>!

      </a>

      <a href="logout.php" class="btn-logout-nav">
        Logout
      </a>

    </div>

  </div>

</nav>

<main class="tracking-wrap">

  <div class="container">

    <section class="track-card stat-card">

      <div class="track-top">

        <div class="track-meta">

          <div class="order-id">
            ORDER ID. #<?= $order['id'] ?>
          </div>

        </div>

        <div class="order-badge">
          <?= strtoupper($order['status']) ?>
        </div>

      </div>

      <div class="progress">

        <div class="progress-line"></div>

        <div class="pstep <?= isStepDone($status, 1) ? 'done' : '' ?>">

          <div class="picon">✓</div>

          <div class="plabel">
            Order Placed
          </div>

          <div class="pdate">
            <?= formatDateTime($created_at) ?>
          </div>

        </div>

        <div class="pstep <?= isStepDone($status, 2) ? 'done' : '' ?>">

          <div class="picon">✓</div>

          <div class="plabel">
            Processing
          </div>

        </div>

        <div class="pstep <?= isStepDone($status, 3) ? 'done' : '' ?>">

          <div class="picon">🚚</div>

          <div class="plabel">
            Shipped
          </div>

        </div>

        <div class="pstep <?= isStepDone($status, 4) ? 'done' : '' ?>">

          <div class="picon">📦</div>

          <div class="plabel">
            Delivered
          </div>

        </div>

        <div class="pstep <?= isStepDone($status, 5) ? 'done' : '' ?>">

          <div class="picon">✓</div>

          <div class="plabel">
            Completed
          </div>

        </div>

      </div>

    </section>

    <section class="tracking-order-card stat-card">

      <div class="tracking-address">

        <strong>
          Delivery Address
        </strong>

        <br><br>

        <?= nl2br(
          htmlspecialchars(
            $order['shipping_address']
          )
        ) ?>

      </div>

      <div class="tracking-total">

        Total:
        ₱ <?= number_format(
          $order['total_amount'],
          2
        ) ?>

      </div>

      <div class="tracking-items">

        <?php while ($item = $order_items->fetch_assoc()): ?>

          <?php
            $img =
              'images/products/' .
              ($item['image'] ?? 'placeholder.jpg');
          ?>

          <div class="tracking-item">

            <img
              src="<?= htmlspecialchars($img) ?>"
              alt="<?= htmlspecialchars($item['name']) ?>"
            >

            <div class="tracking-item-info">

              <h4>
                <?= htmlspecialchars($item['name']) ?>
              </h4>

              <p>
                Qty:
                <?= $item['quantity'] ?>
              </p>

            </div>

            <strong>

              ₱ <?= number_format(
                $item['unit_price'] *
                $item['quantity'],
                2
              ) ?>

            </strong>

          </div>

        <?php endwhile; ?>

      </div>

    </section>

  </div>

</main>

<script src="js/home.js"></script>

</body>
</html>