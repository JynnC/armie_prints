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
$cartQuery = $db->prepare("
    SELECT COALESCE(SUM(quantity), 0) AS total
    FROM cart WHERE user_id = ?
");
$cartQuery->bind_param("i", $user_id);
$cartQuery->execute();
$cart_count = $cartQuery->get_result()->fetch_assoc()['total'] ?? 0;

/* Order */
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: tracking.php");
    exit;
}

/* Order Items */
$itemStmt = $db->prepare("
    SELECT order_items.quantity, order_items.unit_price,
           products.id AS product_id, products.name, products.image
    FROM order_items
    JOIN products ON order_items.product_id = products.id
    WHERE order_items.order_id = ?
");
$itemStmt->bind_param("i", $order['id']);
$itemStmt->execute();
$order_items = $itemStmt->get_result();

$status = strtolower($order['status']);

/* Step helpers */
function getStepIndex($status) {
    $map = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4, 'completed' => 5, 'cancelled' => 0];
    return $map[$status] ?? 1;
}

function isStepDone($current, $step) {
    return getStepIndex($current) >= $step;
}

function isCurrentStep($current, $step) {
    return getStepIndex($current) === $step;
}

/* Progress fill: 5 steps, 4 gaps = 0%, 25%, 50%, 75%, 100% */
function getProgressFill($status) {
    $idx = getStepIndex($status);
    $fills = [0 => '0%', 1 => '0%', 2 => '25%', 3 => '50%', 4 => '75%', 5 => '100%'];
    return $fills[$idx] ?? '0%';
}

function formatDateTime($date) {
    if (!$date) return '';
    return date("m/d/Y h:i A", strtotime($date));
}

$created_at = $order['created_at'] ?? '';

function getStatusBadgeStyle($status) {
    $s = strtolower($status);
    if ($s === 'pending')    return 'background:#fff8e6;color:#b67b2d;border-color:rgba(245,166,35,0.4)';
    if ($s === 'processing') return 'background:#e6faf7;color:#009982;border-color:rgba(0,184,156,0.3)';
    if ($s === 'shipped')    return 'background:#e8f1ff;color:#2d5fb6;border-color:rgba(74,144,217,0.35)';
    if ($s === 'delivered')  return 'background:#e6faf7;color:#009982;border-color:rgba(0,184,156,0.3)';
    if ($s === 'completed')  return 'background:#e8fff4;color:#1a9968;border-color:rgba(26,153,104,0.3)';
    if ($s === 'cancelled')  return 'background:#ffeaea;color:#c93232;border-color:rgba(232,64,64,0.3)';
    return 'background:#f0f0f0;color:#555;border-color:#ddd';
}

$steps = [
    1 => ['icon' => '📋', 'label' => 'Order Placed'],
    2 => ['icon' => '⚙️',  'label' => 'Processing'],
    3 => ['icon' => '🚚', 'label' => 'Shipped'],
    4 => ['icon' => '📦', 'label' => 'Delivered'],
    5 => ['icon' => '✅', 'label' => 'Completed'],
];

$currentStep = getStepIndex($status);
$progressFill = getProgressFill($status);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tracking Details | ArmiePrints</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/home.css">
  <style>
    /* ── TRACKING DETAILS — tracking-details.php ───────────────── */
    .tracking-wrap {
      padding: 40px 0 60px;
      background: var(--bg);
      min-height: calc(100vh - 64px);
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 600;
      color: var(--muted);
      text-decoration: none;
      margin-bottom: 24px;
      transition: color 0.2s;
    }

    .back-link:hover { color: var(--teal); }

    /* ── TRACK CARD (progress bar section) ──────────────────────── */
    .track-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 20px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.06);
      padding: 28px;
      margin-bottom: 20px;
    }

    .track-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--border);
      margin-bottom: 32px;
    }

    .order-id {
      font-family: 'Nunito', sans-serif;
      font-size: 20px;
      font-weight: 900;
      color: var(--text);
    }

    .order-badge {
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      padding: 7px 16px;
      border-radius: 999px;
      border: 1.5px solid transparent;
    }

    /* ── PROGRESS STEPPER ───────────────────────────────────────── */
    .progress-wrap {
      padding: 0 8px 8px;
    }

    .progress {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      position: relative;
      padding-top: 0;
    }

    /* Track line */
    .progress-track {
      position: absolute;
      left: calc(10% + 20px);
      right: calc(10% + 20px);
      top: 28px; /* center on 56px icons */
      height: 4px;
      background: #e8e8e8;
      border-radius: 999px;
      z-index: 0;
      overflow: hidden;
    }

    /* Filled portion */
    .progress-track-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--teal) 0%, var(--gold) 100%);
      border-radius: 999px;
      width: <?= $progressFill ?>;
      transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Each step */
    .pstep {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }

    /* Circle icon */
    .picon {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-bottom: 10px;
      border: 3px solid #e0e0e0;
      background: #fff;
      color: #ccc;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      transition: all 0.4s ease;
    }

    /* Done step */
    .pstep.done .picon {
      border-color: var(--teal);
      background: var(--teal-light);
      color: var(--teal);
      box-shadow: 0 4px 14px rgba(0,184,156,0.2);
    }

    /* Active/current step */
    .pstep.active .picon {
      border-color: var(--teal-dark);
      background: var(--teal);
      color: #fff;
      box-shadow: 0 4px 20px rgba(0,184,156,0.35);
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%   { box-shadow: 0 0 0 0   rgba(0,184,156,0.4); }
      70%  { box-shadow: 0 0 0 10px rgba(0,184,156,0); }
      100% { box-shadow: 0 0 0 0   rgba(0,184,156,0); }
    }

    .plabel {
      font-size: 11px;
      font-weight: 700;
      color: #bbb;
      line-height: 1.3;
      transition: color 0.3s;
    }

    .pstep.done .plabel,
    .pstep.active .plabel {
      color: var(--text);
    }

    .pdate {
      margin-top: 4px;
      font-size: 10px;
      color: var(--muted);
    }

    /* ── ORDER DETAIL CARD ───────────────────────────────────────── */
    .detail-card {
      background: #fff;
      border-radius: 20px;
      padding: 28px;
      border: 1px solid var(--border);
      box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    }

    .detail-section-label {
      font-size: 11px;
      font-weight: 800;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin-bottom: 8px;
    }

    .tracking-address {
      font-size: 13px;
      color: var(--muted);
      padding: 12px 14px;
      background: var(--bg);
      border-radius: 10px;
      border: 1px solid var(--border);
      line-height: 1.5;
      margin-bottom: 20px;
    }

    .tracking-total {
      font-family: 'Nunito', sans-serif;
      font-size: 20px;
      font-weight: 900;
      color: var(--coral);
      margin-bottom: 20px;
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
      padding: 14px 16px;
      border-bottom: 1px solid var(--border);
      background: #fff;
      transition: background 0.15s;
    }

    .tracking-item:last-child { border-bottom: none; }
    .tracking-item:hover { background: var(--bg); }

    .tracking-item img {
      width: 62px;
      height: 62px;
      border-radius: 10px;
      object-fit: cover;
      border: 1px solid var(--border);
      flex-shrink: 0;
    }

    .tracking-item-info { flex: 1; }
    .tracking-item-info h4 { font-size: 13px; font-weight: 700; margin-bottom: 3px; color: var(--text); }
    .tracking-item-info p  { color: var(--muted); font-size: 12px; }

    .tracking-item strong {
      font-size: 13px;
      font-weight: 800;
      color: var(--coral);
      flex-shrink: 0;
    }

    /* ── RESPONSIVE ─────────────────────────────────────────────── */
    @media (max-width: 768px) {
      .track-card, .detail-card { padding: 20px 16px; }

      /* Vertical stepper */
      .progress {
        grid-template-columns: 1fr;
        gap: 0;
      }

      .progress-track { display: none; }

      .pstep {
        flex-direction: row;
        text-align: left;
        align-items: flex-start;
        gap: 16px;
        padding-bottom: 20px;
        position: relative;
      }

      /* Vertical connector line */
      .pstep::after {
        content: '';
        position: absolute;
        left: 27px;
        top: 56px;
        bottom: 0;
        width: 3px;
        background: #e8e8e8;
        border-radius: 99px;
      }

      .pstep.done::after { background: var(--teal); }
      .pstep:last-child::after { display: none; }

      .picon { flex-shrink: 0; margin-bottom: 0; }

      .plabel {
        padding-top: 16px;
        font-size: 12px;
      }

      .pdate { margin-top: 3px; }

      .track-top {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
    }

    @media (max-width: 480px) {
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

    <a href="tracking.php" class="back-link">← Back to Active Orders</a>

    <!-- Progress Card -->
    <div class="track-card">

      <div class="track-top">
        <div class="order-id">Order #<?= $order['id'] ?></div>
        <div class="order-badge" style="<?= getStatusBadgeStyle($status) ?>">
          <?= strtoupper($order['status']) ?>
        </div>
      </div>

      <div class="progress-wrap">
        <div class="progress">

          <!-- Track line with filled portion -->
          <div class="progress-track">
            <div class="progress-track-fill"></div>
          </div>

          <?php foreach ($steps as $stepNum => $stepData): ?>
            <?php
              $isDone    = isStepDone($status, $stepNum);
              $isActive  = isCurrentStep($status, $stepNum);
              $classes   = 'pstep';
              if ($isDone)   $classes .= ' done';
              if ($isActive) $classes .= ' active';
            ?>
            <div class="<?= $classes ?>">
              <div class="picon"><?= $stepData['icon'] ?></div>
              <div class="plabel"><?= $stepData['label'] ?></div>
              <?php if ($stepNum === 1): ?>
                <div class="pdate"><?= formatDateTime($created_at) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

        </div>
      </div>

    </div>

    <!-- Order Detail Card -->
    <div class="detail-card">

      <div class="detail-section-label">Delivery Address</div>
      <div class="tracking-address">
        📍 <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
      </div>

      <div class="tracking-total">
        Total: ₱<?= number_format($order['total_amount'], 2) ?>
      </div>

      <div class="detail-section-label" style="margin-bottom:12px;">Items</div>
      <div class="tracking-items">
        <?php while ($item = $order_items->fetch_assoc()): ?>
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

    </div>

  </div>
</main>

<script src="js/home.js"></script>

<?php include 'chat-widget.php'; ?>
</body>
</html>