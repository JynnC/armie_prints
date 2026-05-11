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

$page_title  = 'Orders';
$active_page = 'orders';

$ship_tab = $_GET['ship_tab'] ?? 'all';
$order_status_filter = $_GET['order_status'] ?? 'all';
$shipping_priority = $_GET['shipping_priority'] ?? 'all';
$order_id_input = trim($_GET['order_id'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$flash = $_GET['flash'] ?? '';

$valid_ship_tabs = ['all', 'to_ship', 'shipping', 'completed', 'returns'];
$valid_order_status = ['all', 'to_process', 'processed'];
$valid_priority = ['all', 'overdue', 'today', 'tomorrow'];

if (!in_array($ship_tab, $valid_ship_tabs, true)) {
    $ship_tab = 'all';
}
if (!in_array($order_status_filter, $valid_order_status, true)) {
    $order_status_filter = 'all';
}
if (!in_array($shipping_priority, $valid_priority, true)) {
    $shipping_priority = 'all';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'arrange_shipment') {
    $order_id_to_update = (int)($_POST['order_id'] ?? 0);
    $courier = trim($_POST['courier'] ?? '');
    $shipping_type = trim($_POST['shipping_type'] ?? '');
    $tracking_number = trim($_POST['tracking_number'] ?? '');

    if ($order_id_to_update <= 0 || $courier === '' || $shipping_type === '') {
        header('Location: admin-orders.php?flash=invalid_order');
        exit;
    }

    $update_stmt = $db->prepare("
        UPDATE orders
        SET 
            status = 'processing',
            courier = ?,
            shipping_type = ?,
            tracking_number = ?,
            updated_at = NOW()
        WHERE id = ? AND status = 'pending'
    ");

    $update_stmt->bind_param(
        'sssi',
        $courier,
        $shipping_type,
        $tracking_number,
        $order_id_to_update
    );

    $update_stmt->execute();

    header('Location: admin-orders.php?flash=shipment_arranged');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['mark_shipped', 'mark_delivered', 'mark_completed'], true)) {
    $order_id_to_update = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['action'];

    $new_status = match ($action) {
        'mark_shipped' => 'shipped',
        'mark_delivered' => 'delivered',
        'mark_completed' => 'completed',
        default => 'processing'
    };

    if ($order_id_to_update > 0) {
        $update_stmt = $db->prepare("
            UPDATE orders
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $update_stmt->bind_param('si', $new_status, $order_id_to_update);
        $update_stmt->execute();
    }

    header('Location: admin-orders.php?flash=status_updated');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk_update_status') {
    $selected_orders = $_POST['selected_orders'] ?? [];
    $bulk_status = $_POST['bulk_status'] ?? '';

    $allowed_statuses = ['processing', 'shipped', 'delivered', 'completed', 'cancelled'];

    if (!empty($selected_orders) && in_array($bulk_status, $allowed_statuses, true)) {
        $ids = array_map('intval', $selected_orders);
        $ids = array_filter($ids, fn($id) => $id > 0);

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types_bulk = str_repeat('i', count($ids));

            $stmt = $db->prepare("
                UPDATE orders
                SET status = ?, updated_at = NOW()
                WHERE id IN ($placeholders)
            ");

            $bind_types = 's' . $types_bulk;
            $bind_values = array_merge([$bulk_status], $ids);

            $stmt->bind_param($bind_types, ...$bind_values);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: admin-orders.php?flash=bulk_updated');
    exit;
}

$tab_counts = $db->query("
    SELECT
        COUNT(*) AS all_count,
        SUM(CASE WHEN status IN ('pending','processing') THEN 1 ELSE 0 END) AS to_ship_count,
        SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) AS shipping_count,
        SUM(CASE WHEN status IN ('delivered','completed') THEN 1 ELSE 0 END) AS completed_count,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS returns_count
    FROM orders
")->fetch_assoc();

$status_counts = $db->query("
    SELECT
        SUM(CASE WHEN status IN ('pending','processing') THEN 1 ELSE 0 END) AS to_process_count,
        SUM(CASE WHEN status IN ('shipped','delivered','completed') THEN 1 ELSE 0 END) AS processed_count
    FROM orders
")->fetch_assoc();

$priority_counts = $db->query("
    SELECT
        SUM(CASE WHEN status IN ('pending','processing') AND DATE(created_at) < CURDATE() THEN 1 ELSE 0 END) AS overdue_count,
        SUM(CASE WHEN status IN ('pending','processing') AND DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_count,
        SUM(CASE WHEN status IN ('pending','processing') AND DATE(created_at) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS tomorrow_count
    FROM orders
")->fetch_assoc();

$where = [];
$params = [];
$types = '';

if ($ship_tab === 'to_ship') {
    $where[] = "o.status IN ('pending','processing')";
} elseif ($ship_tab === 'shipping') {
    $where[] = "o.status = 'shipped'";
} elseif ($ship_tab === 'completed') {
    $where[] = "o.status IN ('delivered','completed')";
} elseif ($ship_tab === 'returns') {
    $where[] = "o.status = 'cancelled'";
}

if ($order_status_filter === 'to_process') {
    $where[] = "o.status IN ('pending','processing')";
} elseif ($order_status_filter === 'processed') {
    $where[] = "o.status IN ('shipped','delivered','completed')";
}

if ($date_from !== '') {
    $where[] = "DATE(o.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if ($date_to !== '') {
    $where[] = "DATE(o.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if ($shipping_priority === 'overdue') {
    $where[] = "o.status IN ('pending','processing') AND DATE(o.created_at) < CURDATE()";
} elseif ($shipping_priority === 'today') {
    $where[] = "o.status IN ('pending','processing') AND DATE(o.created_at) = CURDATE()";
} elseif ($shipping_priority === 'tomorrow') {
    $where[] = "o.status IN ('pending','processing') AND DATE(o.created_at) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
}

if ($order_id_input !== '') {
    if (ctype_digit($order_id_input)) {
        $where[] = "o.id = ?";
        $params[] = (int)$order_id_input;
        $types .= 'i';
    } else {
        $where[] = "CAST(o.id AS CHAR) LIKE ?";
        $params[] = "%{$order_id_input}%";
        $types .= 's';
    }
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$count_sql = "
    SELECT COUNT(*) AS total_rows
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    {$where_sql}
";

if ($params) {
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_rows = (int)($count_stmt->get_result()->fetch_assoc()['total_rows'] ?? 0);
    $count_stmt->close();
} else {
    $total_rows = (int)($db->query($count_sql)->fetch_assoc()['total_rows'] ?? 0);
}

$total_pages = max(1, (int)ceil($total_rows / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

$orders_sql = "
    SELECT
        o.*,
        u.full_name
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    {$where_sql}
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
";

$orders = [];
if ($params) {
    $query_types = $types . 'ii';
    $query_params = [...$params, $per_page, $offset];
    $stmt = $db->prepare($orders_sql);
    $stmt->bind_param($query_types, ...$query_params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
} else {
    $stmt = $db->prepare($orders_sql);
    $stmt->bind_param('ii', $per_page, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}

function format_order_status(string $status): string {
    return match ($status) {
        'pending' => 'To Process',
        'processing' => 'Processing',
        'shipped' => 'Shipping',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'cancelled' => 'Return/Refund/Cancel',
        default => ucfirst($status),
    };
}

function order_query(array $overrides = []): string {
    $base = array_merge($_GET, $overrides);
    foreach ($base as $k => $v) {
        if ($v === null || $v === '') {
            unset($base[$k]);
        }
    }
    return 'admin-orders.php?' . http_build_query($base);
}

include 'admin-nav.php';
?>

<link rel="stylesheet" href="css/admin-orders.css">

<main class="ship-shell">
  <section class="ship-board">
    <header class="ship-title">
      <h1>My Orders</h1>
    </header>

    <?php if ($flash === 'shipment_arranged'): ?>
      <div class="ship-alert success">Shipment arranged. Tracking now moves from Order Placed to Processing.</div>
    <?php elseif ($flash === 'already_arranged'): ?>
      <div class="ship-alert info">This order is already arranged or no longer in pending status.</div>
    <?php elseif ($flash === 'not_found' || $flash === 'invalid_order'): ?>
      <div class="ship-alert error">Unable to arrange shipment: order was not found.</div>
    <?php elseif ($flash === 'not_updated'): ?>
      <div class="ship-alert error">No changes were made. Please refresh and try again.</div>
    <?php elseif ($flash === 'bulk_updated'): ?>
      <div class="ship-alert success">Selected orders were updated successfully.</div>
    <?php endif; ?>
    

    <nav class="ship-tabs">
      <a class="<?= $ship_tab === 'all' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['ship_tab' => 'all', 'page' => 1])) ?>">
        All
      </a>
      <a class="<?= $ship_tab === 'to_ship' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['ship_tab' => 'to_ship', 'page' => 1])) ?>">
        To Ship (<?= (int)($tab_counts['to_ship_count'] ?? 0) ?>)
      </a>
      <a class="<?= $ship_tab === 'shipping' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['ship_tab' => 'shipping', 'page' => 1])) ?>">
        Shipping (<?= (int)($tab_counts['shipping_count'] ?? 0) ?>)
      </a>
      <a class="<?= $ship_tab === 'completed' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['ship_tab' => 'completed', 'page' => 1])) ?>">
        Completed (<?= (int)($tab_counts['completed_count'] ?? 0) ?>)
      </a>
      <a class="<?= $ship_tab === 'returns' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['ship_tab' => 'returns', 'page' => 1])) ?>">
        Return/Refund/Cancel (<?= (int)($tab_counts['returns_count'] ?? 0) ?>)
      </a>
    </nav>

    <section class="filter-block">
      <div class="filter-row">
        <span class="label">Order Status</span>
        <a class="pill <?= $order_status_filter === 'all' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['order_status' => 'all', 'page' => 1])) ?>">All</a>
        <a class="pill <?= $order_status_filter === 'to_process' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['order_status' => 'to_process', 'page' => 1])) ?>">
          To Process (<?= (int)($status_counts['to_process_count'] ?? 0) ?>)
        </a>
        <a class="pill <?= $order_status_filter === 'processed' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['order_status' => 'processed', 'page' => 1])) ?>">
          Processed (<?= (int)($status_counts['processed_count'] ?? 0) ?>)
        </a>
      </div>

      <div class="filter-row">
        <span class="label">Shipping Priority</span>
        <a class="pill <?= $shipping_priority === 'all' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['shipping_priority' => 'all', 'page' => 1])) ?>">All</a>
        <a class="pill <?= $shipping_priority === 'overdue' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['shipping_priority' => 'overdue', 'page' => 1])) ?>">
          Overdue (<?= (int)($priority_counts['overdue_count'] ?? 0) ?>)
        </a>
        <a class="pill <?= $shipping_priority === 'today' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['shipping_priority' => 'today', 'page' => 1])) ?>">
          Ship By Today (<?= (int)($priority_counts['today_count'] ?? 0) ?>)
        </a>
        <a class="pill <?= $shipping_priority === 'tomorrow' ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['shipping_priority' => 'tomorrow', 'page' => 1])) ?>">
          Ship By Tomorrow (<?= (int)($priority_counts['tomorrow_count'] ?? 0) ?>)
        </a>
      </div>

      <form class="search-row" method="GET">
        <input type="hidden" name="ship_tab" value="<?= htmlspecialchars($ship_tab) ?>">
        <input type="hidden" name="order_status" value="<?= htmlspecialchars($order_status_filter) ?>">
        <input type="hidden" name="shipping_priority" value="<?= htmlspecialchars($shipping_priority) ?>">
        <select>
          <option>Order ID</option>
        </select>
        <input type="text" name="order_id" value="<?= htmlspecialchars($order_id_input) ?>" placeholder="Input Order ID">
        <div class="spacer"></div>

        <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
        <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">

        <button class="btn-apply" type="submit">Apply</button>
        <a class="btn-reset" href="admin-orders.php">Reset</a>
      </form>
    </section>

    <form method="POST" id="bulkUpdateForm" style="margin:0;">

      <input type="hidden" name="action" value="bulk_update_status">

      <div class="bulk-actions">

        <label>
          <input type="checkbox" id="selectAllOrders">
          Select All
        </label>

        <select name="bulk_status" required>
          <option value="">Bulk Update Status</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>

        <button type="submit" class="btn-apply">
          Update Selected
        </button>

      </div>
    </form>  
    <div class="parcel-count"><?= count($orders) ?> Parcels</div>

    <div class="order-head-row">
      <div>Product(s)</div>
      <div>Total Buyer Payment</div>
      <div>Status</div>
      <div>Countdown</div>
      <div>Shipping Channel</div>
      <div>Actions</div>
    </div>

    <?php if ($orders): ?>
      <?php foreach ($orders as $order): ?>
        <?php
          $items_stmt = $db->prepare("
              SELECT oi.quantity, oi.unit_price, p.name, p.description, p.image
              FROM order_items oi
              LEFT JOIN products p ON p.id = oi.product_id
              WHERE oi.order_id = ?
          ");
          $order_id_int = (int)$order['id'];
          $items_stmt->bind_param('i', $order_id_int);
          $items_stmt->execute();
          $items_res = $items_stmt->get_result();
          $items = [];
          while ($it = $items_res->fetch_assoc()) {
              $items[] = $it;
          }
          $items_stmt->close();

          $buyer_name = trim((string)($order['full_name'] ?? ''));
          $buyer_initial = $buyer_name !== '' ? strtoupper(substr($buyer_name, 0, 1)) : '?';
          $payment_status = format_order_status((string)($order['status'] ?? 'pending'));
          $created_ts = strtotime((string)$order['created_at']);
          $age_hours = $created_ts ? floor((time() - $created_ts) / 3600) : 0;
        ?>
        <article class="parcel-card">
          <header class="parcel-top"> 
            <label class="order-check">
              <input
                type="checkbox"
                name="selected_orders[]"
                value="<?= (int)$order['id'] ?>"
                form="bulkUpdateForm"
              >
            </label>
            <div class="buyer">
              <span class="avatar"><?= htmlspecialchars($buyer_initial) ?></span>
              <div class="buyer-name"><?= htmlspecialchars($buyer_name !== '' ? $buyer_name : 'Unknown Buyer') ?></div>
            </div>
            <div class="order-id">Order ID: <?= htmlspecialchars((string)$order['id']) ?></div>
          </header>

          <div class="parcel-grid">
            <div class="products-col">
              <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                  <div class="item-row">
                    <div class="item-thumb">
                      <?php if (!empty($item['image'])): ?>
                        <img src="<?= htmlspecialchars('images/products/' . $item['image']) ?>" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <span class="no-img" style="display:none;">No image</span>
                      <?php else: ?>
                        <span class="no-img static">No image</span>
                      <?php endif; ?>
                    </div>
                    <div class="item-meta">
                      <p class="item-name"><?= htmlspecialchars($item['name'] ?? 'Unnamed Product') ?></p>
                      <p class="item-sub"><?= htmlspecialchars($item['description'] ?? '') ?></p>
                    </div>
                    <div class="item-qty">x<?= (int)$item['quantity'] ?></div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="muted">No order items found.</p>
              <?php endif; ?>
            </div>

            <div class="pay-col">
              P <?= number_format((float)($order['total_amount'] ?? 0), 2) ?>
              <span>Paid by Payment</span>
            </div>

            <div class="status-col">
              <strong><?= htmlspecialchars($payment_status) ?></strong>
            </div>

            <div class="countdown-col">
              <strong><?= htmlspecialchars($order['courier'] ?? 'Not arranged') ?></strong>
              <span><?= htmlspecialchars($order['shipping_type'] ?? 'Shipping type not set') ?></span>
              <?php if (!empty($order['tracking_number'])): ?>
                <span>Tracking #: <?= htmlspecialchars($order['tracking_number']) ?></span>
              <?php endif; ?>
            </div>

            <div class="shipping-col">
              <strong><?= htmlspecialchars((string)($order['payment_method'] ?? 'N/A')) ?></strong>
              <span>Shipping channel not set</span>
            </div>

            <div class="actions-col">
              <?php $current_status = strtolower((string)$order['status']); ?>

              <?php if ($current_status === 'pending'): ?>

                <button type="button" class="arrange-btn" onclick="openShipmentModal(<?= (int)$order['id'] ?>)">
                  Arrange Shipment
                </button>

              <?php elseif ($current_status === 'processing'): ?>

                <form method="POST">
                  <input type="hidden" name="action" value="mark_shipped">
                  <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                  <button type="submit" class="arrange-btn">Mark as Shipped</button>
                </form>

              <?php elseif ($current_status === 'shipped'): ?>

                <form method="POST">
                  <input type="hidden" name="action" value="mark_delivered">
                  <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                  <button type="submit" class="arrange-btn">Mark as Delivered</button>
                </form>

              <?php elseif ($current_status === 'delivered'): ?>

                <form method="POST">
                  <input type="hidden" name="action" value="mark_completed">
                  <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                  <button type="submit" class="arrange-btn">Complete Order</button>
                </form>

              <?php else: ?>

                <span class="arranged-label">No Action</span>

              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <h3>No orders found</h3>
        <p>There are no orders matching the selected filters.</p>
      </div>
    <?php endif; ?>

    <?php if ($total_pages > 1): ?>
      <footer class="pagination">
        <a href="<?= htmlspecialchars(order_query(['page' => max(1, $page - 1)])) ?>">&lt;</a>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= htmlspecialchars(order_query(['page' => $i])) ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a href="<?= htmlspecialchars(order_query(['page' => min($total_pages, $page + 1)])) ?>">&gt;</a>
      </footer>
    <?php endif; ?>
    </form>
  </section>
</main>

<div class="shipment-modal" id="shipmentModal">
  <div class="shipment-box">

    <button class="close-modal" type="button" onclick="closeShipmentModal()">
      ×
    </button>

    <h2>Arrange Shipment</h2>

    <form method="POST">
      <input type="hidden" name="action" value="arrange_shipment">
      <input type="hidden" name="order_id" id="shipmentOrderId">

      <div class="ship-field">
        <label>Courier</label>
        <select name="courier" required>
          <option value="">Select Courier</option>
          <option value="J&T Express">J&T Express</option>
          <option value="Flash Express">Flash Express</option>
          <option value="LBC">LBC</option>
          <option value="Ninja Van">Ninja Van</option>
        </select>
      </div>

      <div class="ship-field">
        <label>Shipping Type</label>
        <select name="shipping_type" required>
          <option value="">Select Shipping Type</option>
          <option value="Standard">Standard</option>
          <option value="Express">Express</option>
          <option value="Same Day">Same Day</option>
        </select>
      </div>

      <div class="ship-field">
        <label>Tracking Number</label>
        <input type="text" name="tracking_number" placeholder="Optional">
      </div>

      <button type="submit" class="confirm-ship-btn">
        Confirm Shipment
      </button>
    </form>

  </div>
</div>

<script>
function openShipmentModal(orderId) {
  document.getElementById('shipmentOrderId').value = orderId;
  document.getElementById('shipmentModal').classList.add('show');
}

function closeShipmentModal() {
  document.getElementById('shipmentModal').classList.remove('show');
}
</script>

<script>
const selectAllOrders =
    document.getElementById('selectAllOrders');

if (selectAllOrders) {

    selectAllOrders.addEventListener(
        'change',
        function () {

            document
                .querySelectorAll(
                    'input[name="selected_orders[]"]'
                )
                .forEach(cb => {

                    cb.checked = this.checked;
                });
        }
    );
}
</script>

<?php include 'chat-widget.php'; ?>
</body>
</html>