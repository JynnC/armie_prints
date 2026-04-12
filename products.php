<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$logged_in = isLoggedIn();

// Filters
$category = $_GET['category'] ?? 'all';
$sort     = $_GET['sort'] ?? 'newest';
$search   = trim($_GET['search'] ?? '');

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

$order = match($sort) {
    'price_asc'  => 'ORDER BY price ASC',
    'price_desc' => 'ORDER BY price DESC',
    'name'       => 'ORDER BY name ASC',
    default      => 'ORDER BY created_at DESC',
};

$db  = getDB();
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

<!-- ══ NAVBAR (same as index) ══════════════════════════════════════ -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <img src="images/logo.png" alt="ArmiePrints"
           onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span class="logo-fallback">Armie<span>Prints</span></span>
    </a>
    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="products.php" class="active">Products</a></li>
      <li><a href="#">Custom Order</a></li>
      <li><a href="#">Tracking</a></li>
      <li><a href="#">About</a></li>
    </ul>
    <div class="nav-actions">
      <a href="#" class="cart-btn" aria-label="Cart">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <span class="cart-count">0</span>
      </a>
      <?php if ($logged_in): ?>
        <a href="index.php" class="btn-signed-in">
          Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
        </a>
        <a href="logout.php" class="btn-logout-nav">Logout</a>
      <?php else: ?>
        <a href="index.php#" class="btn-signin">Sign in / Sign Up</a>
      <?php endif; ?>
    </div>
    <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
  </div>
  <div class="mobile-menu" id="mobileMenu">
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="#">Custom Order</a>
    <a href="#">Tracking</a>
    <a href="#">About</a>
  </div>
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
          <a href="?category=<?= $val ?>&sort=<?= $sort ?>" class="cat-link <?= $active ?>">
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
        <input type="range" id="priceRange" min="0" max="500" value="500" step="10">
        <div class="price-labels">
          <span>₱0</span>
          <span id="priceVal">₱500</span>
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
          <input type="text" name="search" placeholder="Search products..."
                 value="<?= htmlspecialchars($search) ?>">
          <button type="submit">🔍</button>
        </form>

        <select class="sort-select" onchange="location='?category=<?= $category ?>&sort='+this.value+'&search=<?= urlencode($search) ?>'">
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
            <button class="btn-cart">Add to Cart</button>
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
  if (range) range.addEventListener('input', () => val.textContent = '₱' + range.value);
</script>
</body>
</html>