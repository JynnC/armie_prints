<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$db = getDB();
$user_id = $_SESSION['user_id'];

$logged_in = isset($_SESSION['user_id']);
$modal_error = '';
$modal_success = '';
$open_modal = '';

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

$subtotal = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - ArmiePrints</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/cart.css">
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
      <li><a href="index.php">Home</a></li>
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
        Hello<?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
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

<main class="cart-page">

    <aside class="checkout-steps">
        <div class="step active">✓ <span>Cart Review</span></div>
        <div class="step">2 <span>Shipping</span></div>
        <div class="step">3 <span>Payment</span></div>
        <div class="step">4 <span>Confirmation</span></div>
    </aside>

    <section class="cart-content">
        <div class="cart-card">
            <h2>Your Order</h2>

            <?php if ($cart_items->num_rows > 0): ?>

                <?php while ($item = $cart_items->fetch_assoc()): 
                    $item_total = $item['price'] * $item['quantity'];
                    $subtotal += $item_total;
                ?>

                <div class="cart-item">
                    <img src="images/products/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">

                    <div class="item-info">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p>₱ <?= number_format($item['price'], 2) ?></p>

                        <div class="qty-control">
                            <button onclick="updateQty(<?= $item['cart_id'] ?>, 'minus')">−</button>
                            <span><?= $item['quantity'] ?></span>
                            <button onclick="updateQty(<?= $item['cart_id'] ?>, 'plus')">+</button>
                        </div>
                    </div>

                    <div class="item-total">
                        <strong>₱ <?= number_format($item_total, 2) ?></strong>
                        <button onclick="removeItem(<?= $item['cart_id'] ?>)">Remove</button>
                    </div>
                </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="empty-cart">
                    <p>Your cart is empty.</p>
                    <a href="products.php">Continue Shopping</a>
                </div>

            <?php endif; ?>
        </div>
    </section>

    <aside class="order-summary">
        <h3>Order Summary</h3>

        <div class="summary-row">
            <span>Subtotal</span>
            <strong>₱ <?= number_format($subtotal, 2) ?></strong>
        </div>

        <div class="summary-row">
            <span>Shipping Fee</span>
            <strong>₱ 0.00</strong>
        </div>

        <div class="summary-total">
            <span>Total:</span>
            <strong>₱ <?= number_format($subtotal, 2) ?></strong>
        </div>

        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
    </aside>

</main>

<script src="js/cart.js"></script>

</body>
</html>