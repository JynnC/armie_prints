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

// ──────────────────────────────────────────────
// AJAX handlers (modal form submissions)
// ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];

    // ── ADD PRODUCT ──
    if ($action === 'add_product') {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $category    = $_POST['category'] ?? 'other';
        $stock       = (int)($_POST['stock'] ?? 0);
        $image_name  = null;

        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Product name is required.']);
            exit;
        }

        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image format.']);
                exit;
            }
            $image_name = uniqid('prod_') . '.' . $ext;
            $dest = __DIR__ . '/images/products/' . $image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
                exit;
            }
        }

        $stmt = $db->prepare("INSERT INTO products (name, description, price, category, stock, image, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param('ssdsis', $name, $description, $price, $category, $stock, $image_name);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Product added.' : $db->error]);
        exit;
    }

    // ── EDIT PRODUCT ──
    if ($action === 'edit_product') {
        $id          = (int)($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $category    = $_POST['category'] ?? 'other';
        $stock       = (int)($_POST['stock'] ?? 0);
        $is_active   = isset($_POST['is_active']) ? 1 : 0;

        if ($id <= 0 || $name === '') {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            exit;
        }

        $image_name = null;

        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image format.']);
                exit;
            }
            $image_name = uniqid('prod_') . '.' . $ext;
            $dest = __DIR__ . '/images/products/' . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $dest);
        }

        if ($image_name) {
            $stmt = $db->prepare("UPDATE products SET name=?, description=?, price=?, category=?, stock=?, is_active=?, image=? WHERE id=?");
            $stmt->bind_param('ssdsissi', $name, $description, $price, $category, $stock, $is_active, $image_name, $id);
        } else {
            $stmt = $db->prepare("UPDATE products SET name=?, description=?, price=?, category=?, stock=?, is_active=? WHERE id=?");
            $stmt->bind_param('ssdssii', $name, $description, $price, $category, $stock, $is_active, $id);
        }
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Product updated.' : $db->error]);
        exit;
    }

    // ── RESTOCK ──
    if ($action === 'restock') {
        $id    = (int)($_POST['id'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }
        $stmt = $db->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->bind_param('ii', $stock, $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Stock updated.' : $db->error]);
        exit;
    }

    // ── DELETE PRODUCT ──
    if ($action === 'delete_product') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product.']);
            exit;
        }
        $stmt = $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Product deleted.' : $db->error]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

// ──────────────────────────────────────────────
// GET: build product list
// ──────────────────────────────────────────────
$tab      = $_GET['tab']      ?? 'all';
$category = $_GET['category'] ?? 'all';
$type     = $_GET['type']     ?? 'all';
$filter = $_GET['filter'] ?? 'all';

if ($filter === '') {
    $filter = 'all';
}
$search   = trim($_GET['search'] ?? '');

$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;

$where  = "WHERE p.is_active = 1";
$params = [];
$types  = '';

if ($search !== '') {
    $where   .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types   .= 'ss';
}

if ($category !== 'all') {
    $where   .= " AND p.category = ?";
    $params[] = $category;
    $types   .= 's';
}

if ($type !== 'all') {
    $where   .= " AND p.description LIKE ?";
    $params[] = "%$type%";
    $types   .= 's';
}

if ($filter === 'low_stock') {
    $where .= " AND CAST(p.stock AS UNSIGNED) BETWEEN 1 AND 5";
} elseif ($filter === 'out_stock') {
    $where .= " AND CAST(p.stock AS UNSIGNED) = 0";
}

if ($tab === 'top') {
    $order = "ORDER BY total_sold DESC, p.created_at DESC";
} elseif ($tab === 'popular') {
    $order = "ORDER BY order_count DESC, p.created_at DESC";
} else {
    $order = "ORDER BY p.created_at DESC";
}

$select = "SELECT p.id, p.name, p.price, p.description, p.image, p.stock, p.category,
                  COALESCE(SUM(oi.quantity), 0)  AS total_sold,
                  COUNT(DISTINCT oi.order_id)     AS order_count
           FROM products p
           LEFT JOIN order_items oi ON oi.product_id = p.id";

$total_rows = 0;
if ($params) {
    $stmt = $db->prepare("SELECT COUNT(*) AS c FROM (SELECT p.id FROM products p LEFT JOIN order_items oi ON oi.product_id = p.id $where GROUP BY p.id) AS sub");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_rows = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();
} else {
    $result     = $db->query("SELECT COUNT(*) AS c FROM (SELECT p.id FROM products p LEFT JOIN order_items oi ON oi.product_id = p.id $where GROUP BY p.id) AS sub");
    $total_rows = (int)($result->fetch_assoc()['c'] ?? 0);
}

$total_pages = max(1, (int)ceil($total_rows / $per_page));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per_page;

$sql = "$select $where GROUP BY p.id $order LIMIT ? OFFSET ?";
if ($params) {
    $stmt        = $db->prepare($sql);
    $bind_types  = $types . "ii";
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
    'tab'      => $tab,
    'category' => $category,
    'type'     => $type,
    'filter'   => $filter,
    'search'   => $search,
];

function ap_build_qs(array $base, array $override = []): string {
    $q = array_merge($base, $override);
    foreach ($q as $k => $v) {
        if ($v === null || $v === '' || $v === 'all') unset($q[$k]);
    }
    return http_build_query($q);
}

include 'admin-nav.php';
?>

<link rel="stylesheet" href="css/admin-products.css">
<link rel="stylesheet" href="css/admin-products-modal.css">

<div class="admin-wrap">
  <div class="pm-header">
    <div class="pm-title">Products</div>
  </div>

  <div id="pm-flash" class="pm-flash" style="display:none;"></div>

  <div class="pm-card">
    <div class="pm-card-top">
      <div class="pm-tabs">
        <a class="pm-tab" href="admin.php">Home</a>
        <a class="pm-tab <?= $tab === 'all'     ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['tab' => 'all',     'page' => 1]) ?>">All Products</a>
        <a class="pm-tab <?= $tab === 'latest'  ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['tab' => 'latest',  'page' => 1]) ?>">Latest</a>
        <a class="pm-tab <?= $tab === 'top'     ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['tab' => 'top',     'page' => 1]) ?>">Top Sales</a>
        <a class="pm-tab <?= $tab === 'popular' ? 'active' : '' ?>" href="?<?= ap_build_qs($qs_base, ['tab' => 'popular', 'page' => 1]) ?>">Popular</a>
      </div>

      <div class="pm-actions">
        <form class="pm-search" method="GET" action="admin-products.php">
          <input type="hidden" name="tab"      value="<?= htmlspecialchars($tab) ?>">
          <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
          <input type="hidden" name="type"     value="<?= htmlspecialchars($type) ?>">
          <input type="hidden" name="filter"   value="<?= htmlspecialchars($filter) ?>">
          <input class="pm-search-input" type="text" name="search" placeholder="Search Products" value="<?= htmlspecialchars($search) ?>">
          <button class="pm-search-btn" type="submit" aria-label="Search">⌕</button>
        </form>
        <button class="pm-menu-btn" type="button" aria-label="Menu">☰</button>
      </div>
    </div>

    <div class="pm-filters">
      <div class="pm-filter-row">
        <select class="form-select pm-select"
                onchange="location='admin-products.php?<?= ap_build_qs($qs_base, ['category' => '__VAL__', 'page' => 1]) ?>'.replace('__VAL__', encodeURIComponent(this.value))">
          <option value="">Category</option>
          <option value="all"           <?= $category === 'all'           ? 'selected' : '' ?>>All</option>
          <option value="atm_magnet"    <?= $category === 'atm_magnet'    ? 'selected' : '' ?>>ATM Size Magnets</option>
          <option value="custom_magnet" <?= $category === 'custom_magnet' ? 'selected' : '' ?>>Custom Magnets</option>
          <option value="other"         <?= $category === 'other'         ? 'selected' : '' ?>>Other</option>
        </select>

        <select class="form-select pm-select"
                onchange="location='admin-products.php?<?= ap_build_qs($qs_base, ['type' => '__VAL__', 'page' => 1]) ?>'.replace('__VAL__', encodeURIComponent(this.value))">
          <option value="">Type</option>
          <option value="all"    <?= $type === 'all'    ? 'selected' : '' ?>>All</option>
          <option value="glossy" <?= $type === 'glossy' ? 'selected' : '' ?>>Glossy</option>
          <option value="classy" <?= $type === 'classy' ? 'selected' : '' ?>>Classy</option>
        </select>

        <select class="form-select pm-select"
                onchange="location='admin-products.php?<?= ap_build_qs($qs_base, ['filter' => '__VAL__', 'page' => 1]) ?>'.replace('__VAL__', encodeURIComponent(this.value))">
          <option value="all">Filter</option>
          <option value="all"       <?= $filter === 'all'       ? 'selected' : '' ?>>All</option>
          <option value="low_stock" <?= $filter === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
          <option value="out_stock" <?= $filter === 'out_stock' ? 'selected' : '' ?>>Out of Stock</option>
        </select>
      </div>

      <button class="pm-add-btn" type="button" onclick="openAddModal()">
        <span class="pm-add-plus">+</span>
        Add Products
      </button>
    </div>

    <div class="pm-grid">
      <?php if ($products && $products->num_rows > 0): ?>
        <?php while ($p = $products->fetch_assoc()): ?>
          <?php
            $img   = 'images/products/' . ($p['image'] ?? 'placeholder.jpg');
            $price = is_numeric($p['price'] ?? null) ? number_format((float)$p['price'], 2) : '0.00';
            $desc  = trim((string)($p['description'] ?? ''));
            $stock = (int)($p['stock'] ?? 0);
            $sold  = (int)($p['total_sold'] ?? 0);

            if ($stock === 0)    $stock_class = 'stock-out';
            elseif ($stock <= 5) $stock_class = 'stock-low';
            else                 $stock_class = 'stock-ok';

            $p_json = htmlspecialchars(json_encode([
              'id'          => $p['id'],
              'name'        => $p['name'],
              'description' => $p['description'],
              'price'       => $p['price'],
              'category'    => $p['category'],
              'stock'       => $stock,
              'image'       => $p['image'],
            ]), ENT_QUOTES);
          ?>
          <div class="pm-item">
            <div class="pm-item-img">
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                   onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <div class="pm-item-img-fallback" style="display:none;">🧲</div>
            </div>

            <div class="pm-item-info">
              <div class="pm-item-name"><?= htmlspecialchars($p['name'] ?? '') ?></div>
              <div class="pm-item-price">₱ <?= $price ?></div>
              <?php if ($desc !== ''): ?>
                <div class="pm-item-desc"><?= htmlspecialchars($desc) ?></div>
              <?php endif; ?>
              <div class="pm-item-meta">
                <span class="pm-stock-badge <?= $stock_class ?>">
                  <?= $stock === 0 ? 'Out of stock' : "Stock: $stock" ?>
                </span>
                <?php if ($sold > 0): ?>
                  <span class="pm-sold-badge">Sold: <?= $sold ?></span>
                <?php endif; ?>
              </div>
            </div>

            <div class="pm-item-actions">
              <button class="pm-btn pm-btn-outline" type="button"
                      onclick="openRestockModal(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['name'])) ?>, <?= $stock ?>)">
                Restock
              </button>
              <button class="pm-btn pm-btn-outline" type="button"
                      onclick="openEditModal(<?= $p_json ?>)">
                EDIT
              </button>
              <button class="pm-btn pm-btn-delete" type="button"
                      onclick="confirmDelete(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['name'])) ?>)">
                DELETE
              </button>
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
        $end   = min($total_pages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
      ?>
        <a class="pm-page-num <?= $i === $page ? 'active' : '' ?>"
           href="?<?= ap_build_qs($qs_base, ['page' => $i]) ?>"><?= $i ?></a>
      <?php endfor; ?>

      <?php if ($end < $total_pages): ?>
        <span class="pm-page-dots">…</span>
        <a class="pm-page-num" href="?<?= ap_build_qs($qs_base, ['page' => $total_pages]) ?>"><?= $total_pages ?></a>
      <?php endif; ?>

      <a class="pm-page-btn" href="?<?= ap_build_qs($qs_base, ['page' => min($total_pages, $page + 1)]) ?>" aria-label="Next">›</a>
    </div>
  </div>
</div>

<!-- MODAL: ADD PRODUCT -->
<div id="modal-add" class="pm-modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('modal-add')">
  <div class="pm-modal">
    <div class="pm-modal-header">
      <span class="pm-modal-title">Add Product</span>
      <button class="pm-modal-close" onclick="closeModal('modal-add')">✕</button>
    </div>
    <form id="form-add" class="pm-modal-body" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add_product">
      <div class="pm-field">
        <label>Product Name <span class="req">*</span></label>
        <input type="text" name="name" required placeholder="e.g. BTS Ref Magnet">
      </div>
      <div class="pm-field-row">
        <div class="pm-field">
          <label>Price (₱) <span class="req">*</span></label>
          <input type="number" name="price" step="0.01" min="0" required placeholder="149.00">
        </div>
        <div class="pm-field">
          <label>Stock</label>
          <input type="number" name="stock" min="0" placeholder="0">
        </div>
      </div>
      <div class="pm-field">
        <label>Category</label>
        <select name="category">
          <option value="atm_magnet">ATM Size Magnets</option>
          <option value="custom_magnet">Custom Magnets</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="pm-field">
        <label>Description</label>
        <textarea name="description" rows="3" placeholder="Short product description…"></textarea>
      </div>
      <div class="pm-field">
        <label>Product Image</label>
        <input type="file" name="image" accept="image/*" onchange="previewImage(this,'preview-add')">
        <img id="preview-add" class="pm-img-preview" src="" alt="" style="display:none;">
      </div>
      <div class="pm-modal-footer">
        <button class="pm-btn pm-btn-cancel" type="button" onclick="closeModal('modal-add')">Cancel</button>
        <button class="pm-btn pm-btn-primary" type="submit">Add Product</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: EDIT PRODUCT -->
<div id="modal-edit" class="pm-modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('modal-edit')">
  <div class="pm-modal">
    <div class="pm-modal-header">
      <span class="pm-modal-title">Edit Product</span>
      <button class="pm-modal-close" onclick="closeModal('modal-edit')">✕</button>
    </div>
    <form id="form-edit" class="pm-modal-body" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit_product">
      <input type="hidden" name="id"     id="edit-id">
      <div class="pm-field">
        <label>Product Name <span class="req">*</span></label>
        <input type="text" name="name" id="edit-name" required>
      </div>
      <div class="pm-field-row">
        <div class="pm-field">
          <label>Price (₱) <span class="req">*</span></label>
          <input type="number" name="price" id="edit-price" step="0.01" min="0" required>
        </div>
        <div class="pm-field">
          <label>Stock</label>
          <input type="number" name="stock" id="edit-stock" min="0">
        </div>
      </div>
      <div class="pm-field">
        <label>Category</label>
        <select name="category" id="edit-category">
          <option value="atm_magnet">ATM Size Magnets</option>
          <option value="custom_magnet">Custom Magnets</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="pm-field">
        <label>Description</label>
        <textarea name="description" id="edit-description" rows="3"></textarea>
      </div>
      <div class="pm-field">
        <label>Replace Image (optional)</label>
        <input type="file" name="image" accept="image/*" onchange="previewImage(this,'preview-edit')">
        <img id="preview-edit" class="pm-img-preview" src="" alt="" style="display:none;">
      </div>
      <div class="pm-field pm-field-check">
        <label>
          <input type="checkbox" name="is_active" id="edit-active" value="1" checked>
          Active (visible to customers)
        </label>
      </div>
      <div class="pm-modal-footer">
        <button class="pm-btn pm-btn-cancel"  type="button" onclick="closeModal('modal-edit')">Cancel</button>
        <button class="pm-btn pm-btn-primary" type="submit">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: RESTOCK -->
<div id="modal-restock" class="pm-modal-overlay" style="display:none;" onclick="if(event.target===this)closeModal('modal-restock')">
  <div class="pm-modal pm-modal-sm">
    <div class="pm-modal-header">
      <span class="pm-modal-title">Restock Product</span>
      <button class="pm-modal-close" onclick="closeModal('modal-restock')">✕</button>
    </div>
    <form id="form-restock" class="pm-modal-body">
      <input type="hidden" name="action" value="restock">
      <input type="hidden" name="id"     id="restock-id">
      <p id="restock-label" class="pm-restock-label"></p>
      <div class="pm-field">
        <label>New Stock Quantity</label>
        <input type="number" name="stock" id="restock-stock" min="0" required>
      </div>
      <div class="pm-modal-footer">
        <button class="pm-btn pm-btn-cancel"  type="button" onclick="closeModal('modal-restock')">Cancel</button>
        <button class="pm-btn pm-btn-primary" type="submit">Update Stock</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function openAddModal() { openModal('modal-add'); }

function openEditModal(data) {
  document.getElementById('edit-id').value          = data.id;
  document.getElementById('edit-name').value        = data.name;
  document.getElementById('edit-price').value       = data.price;
  document.getElementById('edit-stock').value       = data.stock;
  document.getElementById('edit-description').value = data.description || '';
  document.getElementById('edit-category').value    = data.category || 'other';
  document.getElementById('edit-active').checked    = true;
  document.getElementById('preview-edit').style.display = 'none';
  openModal('modal-edit');
}

function openRestockModal(id, name, currentStock) {
  document.getElementById('restock-id').value    = id;
  document.getElementById('restock-stock').value = currentStock;
  document.getElementById('restock-label').textContent = 'Updating stock for: ' + name;
  openModal('modal-restock');
}

function confirmDelete(id, name) {
  if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
  const fd = new FormData();
  fd.append('action', 'delete_product');
  fd.append('id', id);
  fetch('admin-products.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      showFlash(data.message, data.success);
      if (data.success) setTimeout(() => location.reload(), 800);
    })
    .catch(() => showFlash('Request failed. Please try again.', false));
}

function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  }
}

function showFlash(msg, ok) {
  const el = document.getElementById('pm-flash');
  el.textContent = msg;
  el.className = 'pm-flash ' + (ok ? 'pm-flash-ok' : 'pm-flash-err');
  el.style.display = 'block';
  setTimeout(() => { el.style.display = 'none'; }, 3500);
}

function bindAjaxForm(formId, modalId) {
  document.getElementById(formId).addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    try {
      const res  = await fetch('admin-products.php', { method: 'POST', body: fd });
      const data = await res.json();
      showFlash(data.message, data.success);
      if (data.success) {
        closeModal(modalId);
        setTimeout(() => location.reload(), 800);
      }
    } catch (err) {
      showFlash('Request failed. Please try again.', false);
    }
  });
}

bindAjaxForm('form-add',     'modal-add');
bindAjaxForm('form-edit',    'modal-edit');
bindAjaxForm('form-restock', 'modal-restock');
</script>

</body>
</html>