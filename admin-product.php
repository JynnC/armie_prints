<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}
$db = getDB();

$page_title  = 'Products';
$active_page = 'products';

include 'admin-nav.php';
 
$tab = $_GET['tab'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$type = $_GET['type'] ?? 'all';
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

$where = "WHERE is_active = 1";
$params = [];
$types = '';

if ($search !== '') {
    $where .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if ($category !== 'all') {
    $where .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

// "Type" & "Filter" are UI-only for now; keep them in query string to match the screenshot layout.
// Tabs: "latest/top/popular" fall back to created_at until there are explicit analytics fields.
$order = "ORDER BY created_at DESC";
if ($tab === 'top') $order = "ORDER BY created_at DESC";
if ($tab === 'popular') $order = "ORDER BY created_at DESC";

// Total count
$total_rows = 0;
if ($params) {
    $stmt = $db->prepare("SELECT COUNT(*) AS c FROM products $where");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_rows = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
} else {
    $total_rows = (int)($db->query("SELECT COUNT(*) AS c FROM products $where")->fetch_assoc()['c'] ?? 0);
}

$total_pages = max(1, (int)ceil($total_rows / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

// Page rows
$products = null;
$sql = "SELECT id, name, price, description, image, stock, category FROM products $where $order LIMIT ? OFFSET ?";
if ($params) {
    $stmt = $db->prepare($sql);
    $bind_types = $types . "ii";
    $bind_params = array_merge($params, [$per_page, $offset]);
    $stmt->bind_param($bind_types, ...$bind_params);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ii", $per_page, $offset);
    $stmt->execute();
    $products = $stmt->get_result();
}

$qs_base = [
    'tab' => $tab,
    'category' => $category,
    'type' => $type,
    'filter' => $filter,
    'search' => $search,
];

function ap_build_qs(array $base, array $override = []): string {
    $q = array_merge($base, $override);
    foreach ($q as $k => $v) {
        if ($v === null || $v === '') unset($q[$k]);
    }
    return http_build_query($q);
}
?>

<link rel="stylesheet" href="css/admin-products.css">

<div class="admin-wrap">
  <div class="pm-header">
    <div class="pm-title">Products</div>
  </div>

  <div class="pm-card">
    <div class="pm-card-top">
      <div class="pm-tabs">
        <a class="pm-tab" href="admin.php">Home</a>
        <a class="pm-tab <?= $tab === 'all' ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['tab' => 'all', 'page' => 1]) ?>">All Products</a>
        <a class="pm-tab <?= $tab === 'latest' ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['tab' => 'latest', 'page' => 1]) ?>">Latest</a>
        <a class="pm-tab <?= $tab === 'top' ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['tab' => 'top', 'page' => 1]) ?>">Top Sales</a>
        <a class="pm-tab <?= $tab === 'popular' ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['tab' => 'popular', 'page' => 1]) ?>">Popular</a>
      </div>

      <div class="pm-actions">
        <form class="pm-search" method="GET" action="admin-products.php">
          <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
          <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
          <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
          <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
          <input class="pm-search-input" type="text" name="search" placeholder="Search Order" value="<?= htmlspecialchars($search) ?>">
          <button class="pm-search-btn" type="submit" aria-label="Search">⌕</button>
        </form>
        <button class="pm-menu-btn" type="button" aria-label="Menu">☰</button>
      </div>
    </div>

    <div class="pm-filters">
      <div class="pm-filter-row">
        <select class="form-select pm-select" onchange="location='admin-products.php?<?= ap_build_qs($qs_base, ['category' => '__VAL__', 'page' => 1]) ?>'.replace('__VAL__', encodeURIComponent(this.value))">
          <option value="" selected>Category</option>
          <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All</option>
          <option value="atm_magnet" <?= $category === 'atm_magnet' ? 'selected' : '' ?>>ATM Size Magnets</option>
          <option value="custom_magnet" <?= $category === 'custom_magnet' ? 'selected' : '' ?>>Custom Magnets</option>
          <option value="other" <?= $category === 'other' ? 'selected' : '' ?>>Other</option>
        </select>

        <select class="form-select pm-select" onchange="location='admin-products.php?<?= ap_build_qs($qs_base, ['type' => '__VAL__', 'page' => 1]) ?>'.replace('__VAL__', encodeURIComponent(this.value))">
          <option value="" selected>Type</option>
          <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All</option>
          <option value="glossy" <?= $type === 'glossy' ? 'selected' : '' ?>>Glossy</option>
          <option value="classy" <?= $type === 'classy' ? 'selected' : '' ?>>Classy</option>
        </select>

        <select class="form-select pm-select" onchange="location='admin-products.php?<?= ap_build_qs($qs_base, ['filter' => '__VAL__', 'page' => 1]) ?>'.replace('__VAL__', encodeURIComponent(this.value))">
          <option value="" selected>Filter</option>
          <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
          <option value="low_stock" <?= $filter === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
          <option value="out_stock" <?= $filter === 'out_stock' ? 'selected' : '' ?>>Out of Stock</option>
        </select>
      </div>

      <a class="pm-add-btn" href="#" role="button">
        <span class="pm-add-plus">+</span>
        Add Products
      </a>
    </div>

    <div class="pm-grid">
      <?php if ($products && $products->num_rows > 0): ?>
        <?php while ($p = $products->fetch_assoc()): ?>
          <?php
            $img = 'images/products/' . ($p['image'] ?? 'placeholder.jpg');
            $price = is_numeric($p['price'] ?? null) ? number_format((float)$p['price'], 2) : '0.00';
            $desc = trim((string)($p['description'] ?? ''));
          ?>
          <div class="pm-item">
            <div class="pm-item-img">
              <img src="<?= htmlspecialchars($img) ?>" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <div class="pm-item-img-fallback" style="display:none;">🧲</div>
            </div>

            <div class="pm-item-info">
              <div class="pm-item-name"><?= htmlspecialchars($p['name'] ?? '') ?></div>
              <div class="pm-item-price">₱ <?= $price ?></div>
              <?php if ($desc !== ''): ?>
                <div class="pm-item-desc"><?= htmlspecialchars($desc) ?></div>
              <?php endif; ?>
            </div>

            <div class="pm-item-actions">
              <button class="pm-btn pm-btn-outline" type="button">Restock</button>
              <button class="pm-btn pm-btn-outline" type="button">EDIT</button>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">📦</div>
          <h3>No products found</h3>
          <p>Try clearing filters or searching a different term.</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="pm-pagination">
      <a class="pm-page-btn" href="?<?= ap_build_qs($qs_base, ['page' => max(1, $page - 1)]) ?>" aria-label="Previous">‹</a>

      <?php
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
      ?>
        <a class="pm-page-num <?= $i === $page ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['page' => $i]) ?>"><?= $i ?></a>
      <?php endfor; ?>

      <span class="pm-page-dots">…</span>
      <a class="pm-page-btn" href="?<?= ap_build_qs($qs_base, ['page' => min($total_pages, $page + 1)]) ?>" aria-label="Next">›</a>
    </div>
  </div>
</div>

</body>
</html>