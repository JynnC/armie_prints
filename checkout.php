<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// ── SUCCESS MODAL — exit early before any cart logic
if (isset($_GET['success'])) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Placed - ArmiePrints</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
        .success-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999;
        }
        .success-card {
            background: #fff; border-radius: 20px;
            padding: 48px 36px; max-width: 420px;
            width: 90%; text-align: center;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            animation: popIn 0.3s ease;
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.92) translateY(16px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .success-emoji { font-size: 60px; margin-bottom: 16px; }
        .success-title {
            font-family: 'Nunito', sans-serif;
            font-size: 24px; font-weight: 900;
            color: #1a1a1a; margin-bottom: 10px;
        }
        .success-sub { color: #777; font-size: 14px; line-height: 1.6; margin-bottom: 28px; }
        .success-btn {
            display: inline-block; background: #00B89C; color: #fff;
            padding: 13px 32px; border-radius: 12px; text-decoration: none;
            font-weight: 700; font-size: 14px; margin-bottom: 14px;
            font-family: 'Poppins', sans-serif;
            transition: background 0.2s;
        }
        .success-btn:hover { background: #009982; }
        .success-back {
            display: block; font-size: 13px;
            color: #aaa; text-decoration: none; margin-top: 4px;
        }
        .success-back:hover { color: #777; }
    </style>
</head>
<body>
<div class="success-overlay">
    <div class="success-card">
        <div class="success-emoji">🎉</div>
        <h2 class="success-title">Order Placed!</h2>
        <p class="success-sub">
            Thank you for your order!<br>
            We'll process it right away and keep you updated.
        </p>
        <a href="tracking.php" class="success-btn">Track My Order →</a>
        <a href="index.php" class="success-back">Back to Home</a>
    </div>
</div>
<?php include 'chat-widget.php'; ?>
</body>
</html>
<?php
    exit;
}

$db = getDB();
$user_id = $_SESSION['user_id'];

$logged_in = isset($_SESSION['user_id']);

$open_modal = '';
$modal_error = '';
$modal_success = '';

$items = [];
$subtotal = 0;

if (isset($_GET['buy_now'])) {

    $product_id = (int) ($_GET['product_id'] ?? 0);
    $qty = (int) ($_GET['qty'] ?? 1);

    if ($qty < 1) {
        $qty = 1;
    }

    $stmt = $db->prepare("
        SELECT id, name, price, image
        FROM products
        WHERE id = ? AND is_active = 1
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        header("Location: products.php");
        exit;
    }

    $item_total = $product['price'] * $qty;

    $subtotal = $item_total;

    $items[] = [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image'],
        'quantity' => $qty,
        'item_total' => $item_total
    ];

} else {

    $stmt = $db->prepare("
        SELECT 
            cart.id AS cart_id,
            cart.quantity,
            products.id AS product_id,
            products.name,
            products.price,
            products.image
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.user_id = ?
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $cart_items = $stmt->get_result();

    while ($item = $cart_items->fetch_assoc()) {

        $item['item_total'] =
            $item['price'] * $item['quantity'];

        $subtotal += $item['item_total'];

        $items[] = $item;
    }

    if (count($items) === 0) {
        header("Location: cart.php");
        exit;
    }
}

$shipping_fee = 0;
$total = $subtotal + $shipping_fee;

if (count($items) === 0) {
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - ArmiePrints</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/checkout.css">

    <style>
      #successModal {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        display: flex !important;
        align-items: center;
        justify-content: center;
        z-index: 9999;
      }
      #successModal .modal-card {
        max-width: 420px;
        width: 90%;
        text-align: center;
        padding: 48px 36px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
      }
    </style>
</head>
<body>

<!-- ══ NAVBAR ══════════════════════════════════════════════════════════ -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <img src="images/logo.png" alt="ArmiePrints"
           onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span class="logo-fallback">Armie<span>Prints</span></span>
    </a>

    <ul class="nav-links">
      <li><a href="index.php" class="active">Home</a></li>
      <li><a href="products.php">Products</a></li>
      <li><a href="customorder.php">Custom Order</a></li>
      <li><a href="tracking.php">Tracking</a></li>
      <li><a href="about.php">About</a></li>
    </ul>

    <div class="nav-actions">
      <a href="cart.php" class="cart-btn" aria-label="Cart">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <?php
          $cart_count = 0;
          if ($logged_in) {
              $uid = $_SESSION['user_id'];
              $cartQuery = getDB()->prepare("
                  SELECT SUM(quantity) as total
                  FROM cart
                  WHERE user_id = ?
              ");
              $cartQuery->bind_param("i", $uid);
              $cartQuery->execute();

              $cart_count = $cartQuery->get_result()->fetch_assoc()['total'] ?? 0;
          }
        ?>
        <span class="cart-count"><?= $cart_count ?></span>
      </a>
      <?php if ($logged_in): ?>
        <a href="profile.php" class="btn-signed-in">
          Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
        </a>
        <a href="logout.php" class="btn-logout-nav">Logout</a>
      <?php else: ?>
        <button class="btn-signin" id="openModal">Sign in / Sign Up</button>
      <?php endif; ?>
    </div>

    <button class="hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <!-- Mobile menu -->
  <div class="mobile-menu" id="mobileMenu">
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="#">Custom Order</a>
    <a href="#">Tracking</a>
    <a href="about.php">About</a>
    <?php if ($logged_in): ?>
      <a href="profile.php" class="btn-signed-in">
        Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
      </a>
      <a href="logout.php" class="btn-logout-nav">Logout</a>
    <?php else: ?>
      <button class="btn-signin" id="openModal">Sign in / Sign Up</button>
    <?php endif; ?>
  </div>


  <?php if (!$logged_in): ?>
    <div class="modal-overlay" id="authModal">
      <div class="modal-card">
        <button class="modal-close" id="closeModal">✕</button>

        <div class="modal-logo">
          <img src="images/logo.png" alt="ArmiePrints"
              onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
          <span class="logo-fallback" style="font-size:18px;">Armie<span>Prints</span></span>
        </div>

        <div class="modal-tabs">
          <button class="modal-tab <?= ($open_modal !== 'signup') ? 'active' : '' ?>" data-tab="login">Login</button>
          <button class="modal-tab <?= ($open_modal === 'signup') ? 'active' : '' ?>" data-tab="signup">Sign Up</button>
        </div>

        <?php if ($modal_error): ?>
          <div class="modal-alert modal-alert--error"><?= htmlspecialchars($modal_error) ?></div>
        <?php endif; ?>
        <?php if ($modal_success): ?>
          <div class="modal-alert modal-alert--success"><?= htmlspecialchars($modal_success) ?></div>
        <?php endif; ?>

        <div class="modal-pane <?= ($open_modal !== 'signup') ? 'active' : '' ?>" id="pane-login">
          <p class="modal-welcome">Welcome Back!</p>
          <form method="POST" action="index.php">
            <input type="hidden" name="action" value="login">
            <div class="mform-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="juan@email.com" autocomplete="email" required>
            </div>
            <div class="mform-group">
              <label>Password</label>
              <div class="minput-wrap">
                <input type="password" name="password" id="loginPw" placeholder="Your password" required>
                <span class="mpw-toggle" onclick="toggleMPw('loginPw',this)">👁</span>
              </div>
            </div>
            <button type="submit" class="mbtn-primary">LOGIN</button>
          </form>
          <p class="modal-switch">No account yet? <a href="#" data-switch="signup">Sign up here</a></p>
        </div>

        <div class="modal-pane <?= ($open_modal === 'signup') ? 'active' : '' ?>" id="pane-signup">
          <p class="modal-welcome">Welcome!</p>
          <form method="POST" action="index.php">
            <input type="hidden" name="action" value="signup">
            <div class="mform-group">
              <label>Full Name</label>
              <input type="text" name="full_name" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="mform-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="juan@email.com" required>
            </div>
            <div class="mform-group">
              <label>Phone <span style="color:#888;font-weight:400;">(optional)</span></label>
              <input type="text" name="phone" placeholder="09xxxxxxxxx">
            </div>
            <div class="mform-group">
              <label>Password</label>
              <div class="minput-wrap">
                <input type="password" name="password" id="signupPw" placeholder="Min. 6 characters" required>
                <span class="mpw-toggle" onclick="toggleMPw('signupPw',this)">👁</span>
              </div>
            </div>
            <button type="submit" class="mbtn-primary">SIGN UP NOW</button>
          </form>
          <p class="modal-switch">Already have an account? <a href="#" data-switch="login">Login here</a></p>
        </div>

        <p class="modal-terms">
          By continuing you agree to ArmiePrints
          <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
        </p>
      </div>
    </div>
    <?php endif; ?>
</nav>

<main class="checkout-page">

    <aside class="checkout-steps">
        <div class="step done">✓ <span>Cart Review</span></div>
        <div class="step active">2 <span>Shipping</span></div>
        <div class="step">3 <span>Payment</span></div>
        <div class="step">4 <span>Confirmation</span></div>
    </aside>

    <section class="checkout-main">

        <div class="checkout-card">
            <div class="card-title">
                <span class="title-dot"></span>
                <h2>Your Order</h2>
            </div>

            <?php foreach ($items as $item): ?>
                <div class="checkout-item">
                    <img src="images/products/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">

                    <div class="checkout-item-info">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p>Quantity: <?= $item['quantity'] ?></p>
                    </div>

                    <strong>₱ <?= number_format($item['item_total'], 2) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <form action="place-order.php" method="POST" id="checkoutForm">

            <div class="checkout-card">
                <div class="card-title">
                    <span class="title-dot"></span>
                    <h2>Shipping Details</h2>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" placeholder="Juan Dela Cruz" required>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" placeholder="09xxxxxxxxx" required>
                    </div>

                    <div class="form-group full">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="juan@email.com" required>
                    </div>

                    <div class="form-group full">
                        <label>Street Address</label>
                        <input type="text" name="address" placeholder="House No., Street, Barangay" required>
                    </div>

                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" placeholder="City" required>
                    </div>

                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" placeholder="Zip Code" required>
                    </div>

                    <div class="form-group full">
                        <label>Delivery Notes</label>
                        <textarea name="notes" placeholder="Landmark, special instructions, etc."></textarea>
                    </div>
                </div>
            </div>

            <div class="checkout-card">
                <div class="card-title">
                    <span class="title-dot"></span>
                    <h2>Payment Details</h2>
                </div>

                <div class="payment-options">
                    <label class="payment-option active">
                        <input type="radio" name="payment_method" value="COD" checked>
                        <span>
                            <strong>Cash on Delivery</strong>
                            <small>Pay when your order arrives</small>
                        </span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="GCash">
                        <span>
                            <strong>GCash</strong>
                            <small>Pay using mobile wallet</small>
                        </span>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="Bank Transfer">
                        <span>
                            <strong>Bank Transfer</strong>
                            <small>Send payment through bank</small>
                        </span>
                    </label>
                </div>
            </div>

            <input type="hidden" name="subtotal" value="<?= $subtotal ?>">
            <input type="hidden" name="shipping_fee" value="<?= $shipping_fee ?>">
            <input type="hidden" name="total" value="<?= $total ?>">

            <?php if (isset($_GET['buy_now'])): ?>
                <input type="hidden" name="buy_now" value="1">
                <input type="hidden" name="product_id" value="<?= (int) $_GET['product_id'] ?>">
                <input type="hidden" name="qty" value="<?= (int) $_GET['qty'] ?>">
            <?php endif; ?>

        </form>

    </section>

    <aside class="order-summary">
        <h3>Order Summary</h3>

        <div class="summary-row">
            <span>Subtotal</span>
            <strong>₱ <?= number_format($subtotal, 2) ?></strong>
        </div>

        <div class="summary-row">
            <span>Shipping Fee</span>
            <strong>₱ <?= number_format($shipping_fee, 2) ?></strong>
        </div>

        <div class="summary-total">
            <span>Total:</span>
            <strong>₱ <?= number_format($total, 2) ?></strong>
        </div>

        <button type="submit" form="checkoutForm" class="place-order-btn">
            Place Order →
        </button>

        <a href="cart.php" class="back-cart">← Back to Cart</a>
    </aside>

</main>

<script src="js/checkout.js"></script>


<?php if (isset($_GET['success'])): ?>
<div class="modal-overlay" id="successModal" style="display:flex;">
  <div class="modal-card" style="max-width:420px;text-align:center;padding:48px 36px;">
    <div style="font-size:56px;margin-bottom:16px;">🎉</div>
    <h2 style="font-family:'Nunito',sans-serif;font-size:22px;font-weight:900;margin-bottom:10px;">Order Placed!</h2>
    <p style="color:#777;font-size:14px;margin-bottom:28px;">
      Thank you for your order! We'll process it right away and keep you updated.
    </p>
    <a href="profile.php" class="mbtn-primary" style="display:inline-block;margin-bottom:12px;text-decoration:none;">
      Track My Order →
    </a>
    <br>
    <a href="index.php" style="font-size:13px;color:#aaa;">Back to Home</a>
  </div>
</div>
<?php endif; ?>

<?php include 'chat-widget.php'; ?>

</body>
</html>