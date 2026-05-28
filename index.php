<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$modal_error   = '';
$modal_success = '';
$open_modal    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $open_modal = 'login';
    if (empty($email) || empty($password)) {
        $modal_error = 'Please enter your email and password.';
    } else {
        $stmt = getDB()->prepare('SELECT id, full_name, password, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            // ADMIN LOGIN
            if ($user['role'] === 'admin') {

                header('Location: admin.php');
                exit;

            }

            // CUSTOMER LOGIN
            header('Location: index.php');
            exit;
        } else {
            $modal_error = 'Incorrect email or password.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'signup') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $open_modal = 'signup';
    if (empty($full_name) || empty($email) || empty($password)) {
        $modal_error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $modal_error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $modal_error = 'Password must be at least 6 characters.';
    } else {
        $db2  = getDB();
        $stmt = $db2->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $modal_error = 'An account with that email already exists.';
            $stmt->close();
        } else {
            $stmt->close();
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins    = $db2->prepare('INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)');
            $ins->bind_param('ssss', $full_name, $email, $phone, $hashed);
            if ($ins->execute()) {
                $modal_success = 'Account created! You can now log in. 🎉';
                $open_modal    = 'login';
            } else {
                $modal_error = 'Something went wrong. Please try again.';
            }
            $ins->close();
        }
    }
}

$logged_in = isLoggedIn();
$trending = getDB()->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ArmiePrints — Make It Stick. Make It Yours.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,700;0,800;0,900;1,800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/home.css">
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

<!-- ══ HERO ════════════════════════════════════════════════════════════ -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-badge">New collection for 2026</div>
    <h1 class="hero-title">
      MAKE IT STICK.<br>
      <span class="hero-accent">MAKE IT YOURS.</span>
    </h1>
    <p class="hero-desc">
      Your favorite sticker-style shop bringing cute and creativity to every magnet surface. Handcrafted with love.
    </p>
    <a href="products.php" class="btn-shop">Shop Now →</a>
  </div>

  <div class="hero-visual">
    <div class="magnet-stack">
        <div class="magnet-card card-back">
        <img src="images/1.png" alt="">
        </div>
        <div class="magnet-card card-mid">
        <img src="images/2.png" alt="">
        </div>
        <div class="magnet-card card-front">
        <img src="images/3.png" alt="">
        </div>
    </div>
    </div>
</section>

<!-- ══ FILTER TABS ═══════════════════════════════════════════════════ -->
<section class="products-section">
  <div class="container">
    <div class="filter-tabs">
      <button class="tab active" data-filter="all">All Products</button>
      <button class="tab" data-filter="atm_magnet">ATM Size Magnets</button>
      <button class="tab" data-filter="custom_magnet">Custom Magnets</button>
    </div>

    <!-- Trending Now -->
    <div class="section-header">
      <h2 class="section-title">Trending Now</h2>
      <p class="section-sub">Our most-loved magnet collections right now</p>
    </div>

    <div class="products-grid">
      <?php
      if ($trending && $trending->num_rows > 0):
        while ($p = $trending->fetch_assoc()):
          $img = 'images/products/' . ($p['image'] ?? 'placeholder.jpg');
          $colors = ['#FFE0E0','#E0F0FF','#E0FFE8','#FFF8E0'];
          static $ci = 0;
          $bg = $colors[$ci++ % 4];
      ?>
      <div class="product-card" data-category="<?= htmlspecialchars($p['category']) ?>">
        <a href="product-view.php?id=<?= $p['id'] ?>" class="product-img-wrap" style="background:<?= $bg ?>">
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
              onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="img-placeholder" style="display:none;">🧲</div>
        </a>
        <div class="product-info">
          <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
          <p class="product-desc"><?= htmlspecialchars($p['description'] ?? 'High-quality custom magnet, perfect for collectors.') ?></p>
          <div class="product-price">₱ <?= number_format($p['price'], 2) ?></div>
          <div class="product-actions">
            <button class="btn-cart" onclick="addToCart(<?= $p['id'] ?>)">Add to Cart</button>
            <button class="btn-buy" onclick="buyNow(<?= $p['id'] ?>)">Buy Now</button>
          </div>
        </div>
      </div>
      <?php endwhile;
      else:?>
      <?php
        $placeholders = [
          ['BTS Ref Magnet','High-quality BTS reference magnet set','149.00','#FFE0E0',''],
          ['Demon Slayer Magnet','Demon Slayer character magnet collection','149.00','#E0E8FF',''],
          ['Jujutsu Kaisen Magnet','JJK fan favorite magnet set','149.00','#E0FFE8',''],
          ['Straw Hat Magnet','One Piece Straw Hat crew magnets','149.00','#FFF8E0',''],
        ];
        foreach ($placeholders as $ph):
      ?>
      <div class="product-card" data-category="atm_magnet">

        <a href="products.php" class="product-img-wrap" style="background:<?= $ph[3] ?>">
          <div class="img-placeholder">🧲</div>
        </a>

        <div class="product-info">

          <h3 class="product-name">
            <?= htmlspecialchars($ph[0]) ?>
          </h3>

          <p class="product-desc">
            <?= htmlspecialchars($ph[1]) ?>
          </p>

          <div class="product-price">
            ₱ <?= number_format($ph[2], 2) ?>
          </div>

          <div class="product-actions">
            <a href="products.php" class="btn-cart">
              View Product
            </a>

            <a href="products.php" class="btn-buy">
              Shop Now
            </a>
          </div>

        </div>

      </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="view-all-wrap">
      <a href="products.php" class="btn-view-all">View All Products</a>
    </div>
  </div>
</section>

<!-- ══ FEATURE CARDS ════════════════════════════════════════════════ -->
<section class="features-section">
  <div class="container features-grid">

    <div class="feature-card">
      <div class="feature-icon">🎨</div>
      <div>
        <h3 class="feature-title">Custom Designs</h3>
        <p class="feature-desc">Got a unique idea? We turn your vision into a one-of-a-kind magnet set just for you.</p>
        <a href="customorder.php" class="btn-feature">Start Creating</a>
      </div>
    </div>

    <div class="feature-card feature-card--alt">
      <div class="feature-icon">🏢</div>
      <div>
        <h3 class="feature-title">Business Solutions</h3>
        <p class="feature-desc">Bulk orders, brand merch, event giveaways — we've got your business covered.</p>
        <a href="about.php" class="btn-feature btn-feature--alt">View Catalog</a>
      </div>
    </div>

  </div>
</section>

<!-- ══ FOOTER ══════════════════════════════════════════════════════ -->
<footer class="footer">
  <div class="container footer-grid">

    <div class="footer-brand">
      <div class="footer-logo">ArmiePrints</div>
      <p>Your favorite sticker-style shop bringing cute and creativity to every magnet surface. Handcrafted with love.</p>
      <div class="footer-socials">
        <a href="https://www.facebook.com/armieprints">
            <img src="images/facebook.png" alt="Facebook" width="24">
        </a>

        <a href="https://www.tiktok.com/@armieprints">
            <img src="images/tiktok.png" alt="TikTok" width="24">
        </a>
      </div>
    </div>

    <div class="footer-col">
      <h4>Shop</h4>
      <ul>
        <li><a href="customorder.php">Custom Orders</a></li>
        <li><a href="products.php">New Arrivals</a></li>
        <li><a href="products.php">Best Sellers</a></li>
        <li><a href="products.php">Sale</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Help</h4>
      <ul>
        <li><a href="tracking.php">Track Order</a></li>
        <li><a href="https://www.facebook.com/armieprints">Contact Us</a></li>
      </ul>
    </div>

    <div class="footer-col footer-newsletter">
      <h4>Stay in the loop</h4>
      <p>Get notified with the latest items!</p>
      <div class="newsletter-form">
        <input type="email" placeholder="your@email.com">
        <button type="button">→</button>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    <p>© <?= date('Y') ?> ArmiePrints. All rights reserved.</p>
  </div>
</footer>

<script src="js/home.js"></script>
<?php include 'chat-widget.php'; ?>
</body>
</html>