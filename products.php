<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$logged_in = isLoggedIn();
$modal_error   = '';
$modal_success = '';
$open_modal    = '';

// Filters
$category = $_GET['category'] ?? 'all';
$sort     = $_GET['sort'] ?? 'newest';
$search   = trim($_GET['search'] ?? '');
$max_price = isset($_GET['max_price']) ? (float) $_GET['max_price'] : null;

$db = getDB();
$priceRangeRow = $db->query("
    SELECT
      COALESCE(MIN(price), 0) AS min_price,
      COALESCE(MAX(price), 0) AS max_price
    FROM products
    WHERE is_active = 1
")->fetch_assoc();

$price_min = floor((float)($priceRangeRow['min_price'] ?? 0));
$price_max = ceil((float)($priceRangeRow['max_price'] ?? 0));
if ($price_max < 10) {
    $price_max = 10;
}

// Build query
$where = "WHERE is_active = 1";
$params = [];
$types  = '';

if ($category !== 'all') {
    $where .= " AND category = ?";
    $params[] = $category;
    $types   .= 's';
}
if ($search !== '') {
    $where .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= 'ss';
}
if ($max_price !== null) {
    if ($max_price < $price_min) $max_price = $price_min;
    if ($max_price > $price_max) $max_price = $price_max;
    $where .= " AND price <= ?";
    $params[] = $max_price;
    $types   .= 'd';
}

$order = match($sort) {
    'price_asc'  => 'ORDER BY price ASC',
    'price_desc' => 'ORDER BY price DESC',
    'name'       => 'ORDER BY name ASC',
    default      => 'ORDER BY created_at DESC',
};

$sql = "SELECT * FROM products $where $order";

if ($params) {
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $db->query($sql);
}

$total = $products ? $products->num_rows : 0;

// Category counts
$counts = [];
$cr = $db->query("SELECT category, COUNT(*) as cnt FROM products WHERE is_active=1 GROUP BY category");
while ($row = $cr->fetch_assoc()) $counts[$row['category']] = $row['cnt'];
$counts['all'] = array_sum($counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products — ArmiePrints</title>
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
      <li><a href="index.php" >Home</a></li>
      <li><a href="products.php" class="active">Products</a></li>
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

<!-- ══ BREADCRUMB ══════════════════════════════════════════════════ -->
<div class="breadcrumb-bar">
  <div class="container">
    <span><a href="index.php">Home</a></span>
    <span class="sep">›</span>
    <span>Products</span>
  </div>
</div>

<!-- ══ PAGE BODY ═══════════════════════════════════════════════════ -->
<div class="container products-page">

  <!-- Sidebar -->
  <aside class="prod-sidebar">
    <div class="sidebar-block">
      <h3 class="sidebar-title">Categories</h3>
      <ul class="cat-list">
        <?php
        $cats = [
          'all'           => 'All Products',
          'atm_magnet'    => 'ATM Size Magnets',
          'custom_magnet' => 'Custom Magnets',
          'other'         => 'Other',
        ];
        foreach ($cats as $val => $label):
          $cnt = $counts[$val] ?? 0;
          $active = $category === $val ? 'active' : '';
        ?>
        <li>
          <a href="?category=<?= $val ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode($search) ?>&max_price=<?= urlencode((string)($max_price ?? $price_max)) ?>" class="cat-link <?= $active ?>">
            <?= $label ?>
            <span class="cat-count"><?= $cnt ?></span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="sidebar-block">
      <h3 class="sidebar-title">Price Range</h3>
      <div class="price-filter">
        <input type="range" id="priceRange" min="<?= $price_min ?>" max="<?= $price_max ?>" value="<?= $max_price ?? $price_max ?>" step="1">
        <div class="price-labels">
          <span>₱<?= number_format($price_min, 0) ?></span>
          <span id="priceVal">₱<?= number_format($max_price ?? $price_max, 0) ?></span>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main content -->
  <main class="prod-main">

    <!-- Top bar -->
    <div class="prod-topbar">
      <div class="prod-count">
        Showing <strong><?= $total ?></strong> product<?= $total !== 1 ? 's' : '' ?>
        <?php if ($search): ?> for "<em><?= htmlspecialchars($search) ?></em>"<?php endif; ?>
      </div>

      <div class="prod-controls">
        <form method="GET" class="search-form">
          <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
          <input type="hidden" name="max_price" value="<?= htmlspecialchars((string)($max_price ?? $price_max)) ?>">
          <input type="text" name="search" placeholder="Search products..."
                 value="<?= htmlspecialchars($search) ?>">
          <button type="submit">🔍</button>
        </form>

        <select class="sort-select" onchange="location='?category=<?= urlencode($category) ?>&sort='+this.value+'&search=<?= urlencode($search) ?>&max_price=<?= urlencode((string)($max_price ?? $price_max)) ?>'">
          <option value="newest"     <?= $sort==='newest'     ?'selected':'' ?>>Newest</option>
          <option value="price_asc"  <?= $sort==='price_asc'  ?'selected':'' ?>>Price: Low to High</option>
          <option value="price_desc" <?= $sort==='price_desc' ?'selected':'' ?>>Price: High to Low</option>
          <option value="name"       <?= $sort==='name'       ?'selected':'' ?>>Name A–Z</option>
        </select>
      </div>
    </div>

    <!-- Grid -->
    <div class="products-grid">
      <?php
      $colors = ['#FFE0E0','#E0F0FF','#E0FFE8','#FFF8E0'];
      $ci = 0;

      if ($products && $products->num_rows > 0):
        while ($p = $products->fetch_assoc()):
          $img = 'images/products/' . ($p['image'] ?? 'placeholder.jpg');
          $bg  = $colors[$ci++ % 4];
      ?>
      <div class="product-card" data-category="<?= htmlspecialchars($p['category']) ?>">
        <a href="product-view.php?id=<?= $p['id'] ?>" class="product-img-wrap" style="background:<?= $bg ?>">
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
               onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
          <div class="img-placeholder" style="display:none;">🧲</div>
          <?php if ($p['stock'] < 5 && $p['stock'] > 0): ?>
            <span class="stock-badge">Only <?= $p['stock'] ?> left!</span>
          <?php elseif ($p['stock'] == 0): ?>
            <span class="stock-badge out">Out of Stock</span>
          <?php endif; ?>
        </a>
        <div class="product-info">
          <h3 class="product-name"><?= htmlspecialchars($p['name']) ?></h3>
          <p class="product-desc"><?= htmlspecialchars($p['description'] ?? '') ?></p>
          <div class="product-price">₱ <?= number_format($p['price'], 2) ?></div>
          <div class="product-actions">
            <?php if ($p['stock'] > 0): ?>
                <button class="btn-cart" onclick="addToCart(<?= $p['id'] ?>)">
                    Add to Cart
                </button>
            <?php else: ?>
                <button class="btn-cart" disabled>
                    Out of Stock
                </button>
            <?php endif; ?>

            <a href="product-view.php?id=<?= $p['id'] ?>" class="btn-buy">View</a>
          </div>
        </div>
      </div>
      <?php endwhile;
      else: ?>
      <div class="empty-products">
        <div style="font-size:48px;">🔍</div>
        <h3>No products found</h3>
        <p>Try a different category or search term.</p>
        <a href="products.php" class="btn-view-all" style="display:inline-block;margin-top:16px;">Clear Filters</a>
      </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<!-- ══ FOOTER ═════════════════════════════════════════════════════ -->
<footer class="footer">
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
      <li><a href="#">Custom Orders</a></li><li><a href="#">Sale</a></li>
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
  // Price range filter
  const range = document.getElementById('priceRange');
  const val   = document.getElementById('priceVal');
  if (range && val) {
    range.addEventListener('input', () => {
      val.textContent = '₱' + Number(range.value).toLocaleString();
    });
    range.addEventListener('change', () => {
      const url = new URL(window.location.href);
      url.searchParams.set('category', <?= json_encode($category) ?>);
      url.searchParams.set('sort', <?= json_encode($sort) ?>);
      url.searchParams.set('search', <?= json_encode($search) ?>);
      url.searchParams.set('max_price', range.value);
      window.location.href = url.toString();
    });
  }
</script>
</body>
</html>