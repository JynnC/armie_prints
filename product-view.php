<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$logged_in = isLoggedIn();
<<<<<<< HEAD
=======
$modal_error   = '';
$modal_success = '';
$open_modal    = '';
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: products.php'); exit; }

$db   = getDB();
$cart_count = 0;

if ($logged_in) {
    $uid = $_SESSION['user_id'];
    $cartQuery = $db->prepare("
        SELECT COALESCE(SUM(quantity), 0) AS total
        FROM cart
        WHERE user_id = ?
    ");
    $cartQuery->bind_param("i", $uid);
    $cartQuery->execute();
    $cart_count = $cartQuery->get_result()->fetch_assoc()['total'];
}

$stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) { header('Location: products.php'); exit; }

// Related products (same category, exclude current)
$related = $db->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND is_active = 1 LIMIT 4");
$related->bind_param('si', $product['category'], $id);
$related->execute();
$related_products = $related->get_result();
$related->close();

$img = 'images/products/' . ($product['image'] ?? 'placeholder.jpg');
$cat_label = match($product['category']) {
    'atm_magnet'    => 'ATM Size Magnets',
    'custom_magnet' => 'Custom Magnets',
    default         => 'Products',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['name']) ?> — ArmiePrints</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/home.css">
  <link rel="stylesheet" href="css/products.css">
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

<!-- ══ BREADCRUMB ═════════════════════════════════════════════════ -->
<div class="breadcrumb-bar">
  <div class="container">
    <a href="index.php">Home</a>
    <span class="sep">›</span>
    <a href="products.php">Shop</a>
    <span class="sep">›</span>
    <span>Product Details</span>
  </div>
</div>

<!-- ══ PRODUCT DETAIL ═════════════════════════════════════════════ -->
<div class="container pv-wrap">

  <!-- Left: image gallery -->
  <div class="pv-gallery">
    <div class="pv-main-img" id="mainImgWrap">
      <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['name']) ?>"
           id="mainImg"
           onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
      <div class="img-placeholder" style="display:none;font-size:64px;">🧲</div>
    </div>
    <!-- Thumbnail strip (same image repeated as placeholders for now) -->
    <div class="pv-thumbs">
      <?php for($t = 0; $t < 4; $t++): ?>
      <div class="pv-thumb <?= $t === 0 ? 'active' : '' ?>"
           onclick="setThumb(this, '<?= htmlspecialchars($img) ?>')">
        <img src="<?= htmlspecialchars($img) ?>" alt=""
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none;font-size:20px;">🧲</div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Right: product info -->
  <div class="pv-info">

    <div class="pv-badge"><?= htmlspecialchars($cat_label) ?></div>

    <h1 class="pv-title"><?= htmlspecialchars($product['name']) ?></h1>

    <!-- Star rating placeholder -->
    <div class="pv-rating">
      <span class="stars">★★★★★</span>
      <span class="rating-count">(125 Reviews)</span>
    </div>

    <div class="pv-price">₱ <?= number_format($product['price'], 2) ?></div>

    <p class="pv-desc">
      <?= nl2br(htmlspecialchars($product['description'] ?? 'High-quality custom magnet, perfect for collectors and fans alike. Each piece is carefully crafted and printed with vibrant, long-lasting colors.')) ?>
    </p>

    <!-- Stock info -->
    <div class="pv-stock <?= $product['stock'] > 0 ? 'in-stock' : 'out-stock' ?>">
      <?= $product['stock'] > 0
          ? '✓ In Stock (' . $product['stock'] . ' available)'
          : '✗ Out of Stock' ?>
    </div>

    <!-- Quantity selector -->
    <?php if ($product['stock'] > 0): ?>
    <div class="pv-qty">
      <label>Quantity</label>
      <div class="qty-wrap">
        <button class="qty-btn" onclick="changeQty(-1)">−</button>
        <input type="number" id="qtyInput" value="1" min="1" max="<?= $product['stock'] ?>">
        <button class="qty-btn" onclick="changeQty(1)">+</button>
      </div>
    </div>

    <div class="pv-actions">
      <button class="btn-cart pv-cart-btn" onclick="addToCartPV(<?= $product['id'] ?>)">
        Add to Cart
      </button>
      <button class="btn-buy pv-buy-btn" onclick="buyNowPV(<?= $product['id'] ?>)">
        Buy Now
      </button>
    </div>
    <?php else: ?>
    <button class="btn-cart" disabled style="opacity:0.5;cursor:not-allowed;width:100%;padding:12px;">
      Out of Stock
    </button>
    <?php endif; ?>

    <!-- Meta info -->
    <div class="pv-meta">
      <div class="pv-meta-item">
        <span class="meta-icon">🚚</span>
        <span>Free shipping on orders over ₱500</span>
      </div>
      <div class="pv-meta-item">
        <span class="meta-icon">↩️</span>
        <span>Easy 7-day returns</span>
      </div>
      <div class="pv-meta-item">
        <span class="meta-icon">✅</span>
        <span>Secure checkout</span>
      </div>
    </div>

  </div>
</div>

<!-- ══ TABS: Description / Shipping ═══════════════════════════════ -->
<div class="container pv-tabs-section">
  <div class="pv-tabs">
    <button class="pv-tab active" data-tab="desc">Description</button>
    <button class="pv-tab" data-tab="shipping">Shipping and Returns</button>
  </div>

  <div class="pv-tab-pane active" id="tab-desc">
    <h3>Premium Quality Meets Art</h3>
    <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Our magnets are printed on high-quality materials with vibrant, fade-resistant inks. Each piece is carefully crafted to bring your favorite characters and designs to life.')) ?></p>

    <div class="pv-features-grid">
      <div class="pv-feature-box">
        <div class="feat-dot"></div>
        <p>High-quality printing with vibrant, long-lasting colors that won't fade over time.</p>
      </div>
      <div class="pv-feature-box">
        <div class="feat-dot"></div>
        <p>Strong magnetic backing ensures your magnets stay in place on any magnetic surface.</p>
      </div>
      <div class="pv-feature-box">
        <div class="feat-dot"></div>
        <p>ATM-card size — compact, collectible, and perfect for fridges, whiteboards, and lockers.</p>
      </div>
    </div>
  </div>

  <div class="pv-tab-pane" id="tab-shipping">
    <h3>Shipping Information</h3>
    <p>We ship nationwide via J&T Express and LBC. Standard delivery takes 3–7 business days depending on your location.</p>
    <br>
    <h3>Returns Policy</h3>
    <p>We accept returns within 7 days of delivery for defective or damaged items. Items must be in original condition. Contact us via our Facebook page to initiate a return.</p>
  </div>
</div>

<div class="container pv-related">
  <h2 class="section-title" style="margin-bottom:24px;">You Might Also Like</h2>
  <div class="products-grid">
    <?php
    $colors = ['#FFE0E0','#E0F0FF','#E0FFE8','#FFF8E0'];
    $ci = 0;
    if ($related_products && $related_products->num_rows > 0):
      while ($r = $related_products->fetch_assoc()):
        $rimg = 'images/products/' . ($r['image'] ?? 'placeholder.jpg');
        $bg   = $colors[$ci++ % 4];
    ?>
    <div class="product-card">
      <a href="product-view.php?id=<?= $r['id'] ?>" class="product-img-wrap" style="background:<?= $bg ?>">
        <img src="<?= htmlspecialchars($rimg) ?>" alt="<?= htmlspecialchars($r['name']) ?>"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none;">🧲</div>
      </a>
      <div class="product-info">
        <h3 class="product-name"><?= htmlspecialchars($r['name']) ?></h3>
        <p class="product-desc"><?= htmlspecialchars($r['description'] ?? '') ?></p>
        <div class="product-price">₱ <?= number_format($r['price'], 2) ?></div>
        <div class="product-actions">
          <button class="btn-cart" onclick="addToCart(<?= $r['id'] ?>)">Add to Cart</button>
          <a href="product-view.php?id=<?= $r['id'] ?>" class="btn-buy">View</a>
        </div>
      </div>
    </div>
    <?php endwhile;
    else: ?>
    <p style="color:var(--muted);font-size:13px;">No related products found.</p>
    <?php endif; ?>
  </div>
</div>

<!-- ══ FOOTER ═════════════════════════════════════════════════════ -->
<footer class="footer" style="margin-top:48px;">
  <div class="container footer-grid">
    <div class="footer-brand">
      <div class="footer-logo">ArmiePrints</div>
      <p>Your favorite sticker-style ukay shop bringing cute and creativity to every magnet surface.</p>
      <div class="footer-socials">
        <a href="#">●</a><a href="#">●</a><a href="#">●</a><a href="#">●</a>
      </div>
    </div>
    <div class="footer-col"><h4>Shop</h4><ul>
      <li><a href="#">New Arrivals</a></li><li><a href="#">Best Sellers</a></li>
      <li><a href="products.php">All Products</a></li>
    </ul></div>
    <div class="footer-col"><h4>Help</h4><ul>
      <li><a href="#">Track Order</a></li><li><a href="#">Shipping Info</a></li>
      <li><a href="#">Returns</a></li><li><a href="#">FAQ</a></li>
    </ul></div>
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
<script>
// Thumbnail switcher
function setThumb(el, src) {
  document.querySelectorAll('.pv-thumb').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  const main = document.getElementById('mainImg');
  if (main) main.src = src;
}

// Quantity
function changeQty(delta) {
  const input = document.getElementById('qtyInput');
  if (!input) return;
  let val = parseInt(input.value) + delta;
  val = Math.max(1, Math.min(parseInt(input.max), val));
  input.value = val;
}

// Product view tab switching
document.querySelectorAll('.pv-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.pv-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.pv-tab-pane').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
  });
});

// Cart / Buy
window.addToCartPV = function(id) {
  const qty = parseInt(document.getElementById('qtyInput')?.value || 1);
  const badge = document.querySelector('.cart-count');

  fetch('add-to-cart.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'product_id=' + encodeURIComponent(id) + '&quantity=' + encodeURIComponent(qty)
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'login_required') {
<<<<<<< HEAD
      alert('Please login first.');
=======
      if (typeof window.promptAuthModal === 'function') {
        window.promptAuthModal();
      } else {
        alert('Please login first.');
      }
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
      return;
    }

    if (data.status === 'success') {
      if (badge) badge.textContent = data.count;
      alert('Added to cart! 🛒');
    } else {
      alert('Cart was not saved.');
    }
  })
  .catch(() => {
    alert('Something went wrong.');
  });
};

window.buyNowPV = function(id) {
<<<<<<< HEAD
=======
  if (typeof window.promptAuthModal === 'function' && window.promptAuthModal()) {
    return;
  }
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b

  const qty = parseInt(document.getElementById('qtyInput')?.value || 1);

  window.location.href =
    'checkout.php?buy_now=1&product_id=' + id + '&qty=' + qty;
};

</script>
</body>
</html>