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

$page_title  = 'Records';
$active_page = 'records';

// Revenue analytics period (month + year)
$selected_month = (int)($_GET['month'] ?? date('n'));
$selected_year  = (int)($_GET['year'] ?? date('Y'));
$selected_month = max(1, min(12, $selected_month));
$selected_year  = max(2020, min((int)date('Y') + 5, $selected_year));

$selected_start = sprintf('%04d-%02d-01', $selected_year, $selected_month);
$selected_end = date('Y-m-t', strtotime($selected_start));
$prev_start = date('Y-m-01', strtotime($selected_start . ' -1 month'));
$prev_end = date('Y-m-t', strtotime($prev_start));

// Sales overview cards (for selected period)
$period_stmt = $db->prepare("
    SELECT
        COUNT(*) AS order_count,
        COALESCE(SUM(total_amount), 0) AS revenue
    FROM orders
    WHERE status <> 'cancelled'
      AND DATE(created_at) BETWEEN ? AND ?
");
$period_stmt->bind_param('ss', $selected_start, $selected_end);
$period_stmt->execute();
$period_totals = $period_stmt->get_result()->fetch_assoc();
$period_stmt->close();

$order_count = (int)($period_totals['order_count'] ?? 0);
$total_revenue = (float)($period_totals['revenue'] ?? 0);
$avg_order_value = $order_count > 0 ? $total_revenue / $order_count : 0;

$prev_stmt = $db->prepare("
    SELECT
        COUNT(*) AS order_count,
        COALESCE(SUM(total_amount), 0) AS revenue
    FROM orders
    WHERE status <> 'cancelled'
      AND DATE(created_at) BETWEEN ? AND ?
");
$prev_stmt->bind_param('ss', $prev_start, $prev_end);
$prev_stmt->execute();
$prev_totals = $prev_stmt->get_result()->fetch_assoc();
$prev_stmt->close();

$prev_revenue = (float)($prev_totals['revenue'] ?? 0);
$prev_orders = (int)($prev_totals['order_count'] ?? 0);
$prev_avg = $prev_orders > 0 ? $prev_revenue / $prev_orders : 0;

$calc_delta = static function (float $current, float $previous): string {
    if ($previous <= 0) {
        return $current > 0 ? '+100.0%' : '0.0%';
    }
    $pct = (($current - $previous) / $previous) * 100;
    $sign = $pct > 0 ? '+' : '';
    return $sign . number_format($pct, 1) . '%';
};

$revenue_delta = $calc_delta($total_revenue, $prev_revenue);
$orders_delta = $calc_delta((float)$order_count, (float)$prev_orders);
$avg_delta = $calc_delta($avg_order_value, $prev_avg);

// Revenue analytics (weeks inside selected month/year)
$week_rows = [];
$week_stmt = $db->prepare("
    SELECT
        YEARWEEK(created_at, 1) AS yw,
        MIN(DATE(created_at)) AS start_date,
        COALESCE(SUM(total_amount), 0) AS total
    FROM orders
    WHERE status <> 'cancelled'
      AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY YEARWEEK(created_at, 1)
");
if ($week_stmt) {
    $week_stmt->bind_param('ss', $selected_start, $selected_end);
    $week_stmt->execute();
    $week_result = $week_stmt->get_result();
    while ($row = $week_result->fetch_assoc()) {
        $week_rows[] = $row;
    }
    $week_stmt->close();
}
$max_week_total = 0.0;
foreach ($week_rows as $wk) {
    $max_week_total = max($max_week_total, (float)$wk['total']);
}

// Top selling products
$top_products = [];
$top_result = $db->query("
    SELECT
        p.id,
        p.name,
        p.category,
        p.price,
        p.stock,
        p.image,
        COALESCE(SUM(oi.quantity), 0) AS sold_qty
    FROM products p
    LEFT JOIN order_items oi ON oi.product_id = p.id
    WHERE p.is_active = 1
    GROUP BY p.id, p.name, p.category, p.price, p.stock, p.image
    ORDER BY sold_qty DESC, p.created_at DESC
    LIMIT 6
");
if ($top_result) {
    while ($row = $top_result->fetch_assoc()) {
        $top_products[] = $row;
    }
}

// Year dropdown options from existing orders
$year_options = [];
$yr_result = $db->query("
    SELECT DISTINCT YEAR(created_at) AS y
    FROM orders
    ORDER BY y DESC
");
if ($yr_result) {
    while ($yr = $yr_result->fetch_assoc()) {
        $year_options[] = (int)$yr['y'];
    }
}
if (!$year_options) {
    $year_options = [(int)date('Y')];
}

// CSV export for selected period
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $csv_stmt = $db->prepare("
        SELECT
            o.id,
            o.created_at,
            COALESCE(u.full_name, 'Guest') AS buyer_name,
            o.total_amount,
            o.status,
            o.payment_method,
            COALESCE(SUM(oi.quantity), 0) AS total_items
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.status <> 'cancelled'
          AND DATE(o.created_at) BETWEEN ? AND ?
        GROUP BY o.id, o.created_at, buyer_name, o.total_amount, o.status, o.payment_method
        ORDER BY o.created_at DESC
    ");
    $csv_stmt->bind_param('ss', $selected_start, $selected_end);
    $csv_stmt->execute();
    $csv_result = $csv_stmt->get_result();

    $filename = 'sales-report-' . $selected_year . '-' . str_pad((string)$selected_month, 2, '0', STR_PAD_LEFT) . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Order ID', 'Date', 'Buyer', 'Total Amount', 'Status', 'Payment Method', 'Items']);
    while ($row = $csv_result->fetch_assoc()) {
        fputcsv($out, [
            $row['id'],
            date('Y-m-d H:i:s', strtotime($row['created_at'])),
            $row['buyer_name'],
            number_format((float)$row['total_amount'], 2, '.', ''),
            $row['status'],
            $row['payment_method'],
            (int)$row['total_items'],
        ]);
    }
    fclose($out);
    $csv_stmt->close();
    exit;
}

function records_category_label(string $category): string {
    return match ($category) {
        'atm_magnet' => 'ATM Magnet',
        'custom_magnet' => 'Custom Magnet',
        default => 'Other',
    };
}

function records_query(array $overrides = []): string {
    $query = array_merge($_GET, $overrides);
    foreach ($query as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        }
    }
    return 'admin-records.php?' . http_build_query($query);
}

include 'admin-nav.php';
?>

<link rel="stylesheet" href="css/admin-records.css">

<main class="records-shell">
  <section class="records-board">
    <header class="records-header">
      <h1>Sales Overview</h1>
      <p>Welcome Back! Here's what's happening for <?= htmlspecialchars(date('F Y', strtotime($selected_start))) ?></p>
    </header>

    <section class="stat-cards">
      <article class="record-card teal">
        <span class="mini-icon">💳</span>
        <div class="title">Total Revenue</div>
        <div class="value">P <?= number_format($total_revenue, 0) ?></div>
        <div class="delta"><?= htmlspecialchars($revenue_delta) ?> vs previous month</div>
      </article>

      <article class="record-card pink">
        <span class="mini-icon">🛒</span>
        <div class="title">Total Orders</div>
        <div class="value"><?= number_format($order_count) ?></div>
        <div class="delta"><?= htmlspecialchars($orders_delta) ?> vs previous month</div>
      </article>

      <article class="record-card orange">
        <span class="mini-icon">📊</span>
        <div class="title">Avg. Order Value</div>
        <div class="value">P <?= number_format($avg_order_value, 0) ?></div>
        <div class="delta"><?= htmlspecialchars($avg_delta) ?> vs previous month</div>
      </article>
    </section>

    <section class="analytics-box">
      <div class="analytics-head">
        <h2>Revenue Analytics</h2>
        <form method="GET" class="analytics-month">
          <?php
            $prev_date = strtotime($selected_start . ' -1 month');
            $next_date = strtotime($selected_start . ' +1 month');
          ?>
          <a class="month-nav" href="<?= htmlspecialchars(records_query([
              'month' => (int)date('n', $prev_date),
              'year' => (int)date('Y', $prev_date),
              'export' => null
          ])) ?>">&lt;</a>
          <select name="month" onchange="this.form.submit()">
            <?php for ($m = 1; $m <= 12; $m++): ?>
              <option value="<?= $m ?>" <?= $m === $selected_month ? 'selected' : '' ?>>
                <?= htmlspecialchars(date('F', mktime(0, 0, 0, $m, 1, 2000))) ?>
              </option>
            <?php endfor; ?>
          </select>
          <select name="year" onchange="this.form.submit()">
            <?php foreach ($year_options as $yr): ?>
              <option value="<?= $yr ?>" <?= $yr === $selected_year ? 'selected' : '' ?>><?= $yr ?></option>
            <?php endforeach; ?>
          </select>
          <a class="month-nav" href="<?= htmlspecialchars(records_query([
              'month' => (int)date('n', $next_date),
              'year' => (int)date('Y', $next_date),
              'export' => null
          ])) ?>">&gt;</a>
        </form>
      </div>

      <div class="bar-grid" aria-hidden="true">
        <?php if ($week_rows): ?>
          <?php foreach ($week_rows as $wk): ?>
            <?php
              $wk_total = (float)$wk['total'];
              $height = $max_week_total > 0 ? (int)round(($wk_total / $max_week_total) * 130) : 40;
              $height = max(40, $height);
            ?>
            <div class="bar-item">
              <div class="bar" style="height: <?= $height ?>px;"></div>
              <div class="bar-label"><?= htmlspecialchars(date('M j', strtotime($wk['start_date']))) ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="bar-item">
            <div class="bar" style="height: 40px;"></div>
            <div class="bar-label">No data</div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <section class="bottom-grid">
      <article class="products-box">
        <div class="products-head">
          <h3>Top Selling Products</h3>
          <a href="javascript:void(0)">View All</a>
        </div>
        <table class="products-table">
          <thead>
            <tr>
              <th>Product Name</th>
              <th>Category</th>
              <th>Price</th>
              <th>Status</th>
              <th>Sold</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($top_products): ?>
              <?php foreach ($top_products as $product): ?>
                <?php
                  $stock = (int)($product['stock'] ?? 0);
                  $status = $stock > 0 ? 'In Stock' : 'Out of Stock';
                ?>
                <tr>
                  <td><?= htmlspecialchars((string)$product['name']) ?></td>
                  <td><?= htmlspecialchars(records_category_label((string)$product['category'])) ?></td>
                  <td>P <?= number_format((float)$product['price'], 2) ?></td>
                  <td><?= htmlspecialchars($status) ?></td>
                  <td><?= (int)($product['sold_qty'] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr class="empty-row">
                <td colspan="5">No records available yet</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </article>

      <aside class="report-box">
        <div class="report-icon">📄</div>
        <h4>Ready to Share?</h4>
        <p>Download the comprehensive monthly sales report in your preferred format.</p>
        <a class="export-btn" href="<?= htmlspecialchars(records_query(['export' => 'csv'])) ?>">Export Report</a>
      </aside>
    </section>
  </section>
</main>

</body>
</html>
