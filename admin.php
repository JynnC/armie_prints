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

// Auto-update released orders
$db->query("
    UPDATE orders
    SET payment_status = 'released',
        released_at = COALESCE(released_at, created_at)
    WHERE status IN ('shipped','delivered','completed')
");

// Income values
$pending_income = $db->query("
    SELECT COALESCE(SUM(total_amount),0) AS total
    FROM orders
    WHERE status IN ('pending','processing')
")->fetch_assoc()['total'];

$released_income = $db->query("
    SELECT COALESCE(SUM(total_amount),0) AS total
    FROM orders
    WHERE status IN ('shipped','delivered','completed')
")->fetch_assoc()['total'];

<<<<<<< HEAD
// Income details tab selection
$income_tab = $_GET['income_tab'] ?? 'released';
if (!in_array($income_tab, ['pending', 'released'], true)) {
    $income_tab = 'released';
}
$search = trim((string)($_GET['search'] ?? ''));
$search_sql = $db->real_escape_string($search);
$search_clause = '';

if ($search !== '') {
    $search_like = "%{$search_sql}%";
    $order_id_condition = is_numeric($search) ? "o.id = " . (int)$search . " OR " : '';
    $search_clause = "AND ({$order_id_condition}u.full_name LIKE '{$search_like}' OR u.phone LIKE '{$search_like}' OR o.shipping_address LIKE '{$search_like}' OR o.payment_method LIKE '{$search_like}' OR o.status LIKE '{$search_like}')";

    $current_statuses = $income_tab === 'pending' ? "('pending','processing')" : "('shipped','delivered','completed')";
    $other_statuses = $income_tab === 'pending' ? "('shipped','delivered','completed')" : "('pending','processing')";

    $current_count = $db->query("SELECT COUNT(DISTINCT o.id) AS c FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.status IN {$current_statuses} {$search_clause}")->fetch_assoc()['c'];
    if ($current_count === 0) {
        $other_count = $db->query("SELECT COUNT(DISTINCT o.id) AS c FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.status IN {$other_statuses} {$search_clause}")->fetch_assoc()['c'];
        if ($other_count > 0) {
            $income_tab = $income_tab === 'pending' ? 'released' : 'pending';
        }
    }
}
$income_statuses = $income_tab === 'pending' ? "('pending','processing')" : "('shipped','delivered','completed')";
$base_url = htmlspecialchars($_SERVER['PHP_SELF']);

$income_details = $db->query("
    SELECT
        o.id,
        o.user_id,
        o.total_amount,
        o.shipping_address,
        o.payment_status,
        o.payment_method,
        o.status,
        o.created_at,
        o.released_at,
        COALESCE(u.full_name, 'Guest') AS full_name,
        COALESCE(u.phone, '') AS user_phone,
        COALESCE(SUM(oi.quantity), 0) AS total_items
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.status IN $income_statuses
    {$search_clause}
    GROUP BY
        o.id,
        o.user_id,
        o.total_amount,
        o.shipping_address,
        o.payment_status,
        o.payment_method,
        o.status,
        o.created_at,
        o.released_at,
        full_name,
        user_phone
=======
// Recent income details
$income_details = $db->query("
    SELECT o.*, u.full_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.status NOT IN ('cancelled')
>>>>>>> daf6634f62d7b468a4474b8f1482a12e923157ca
    ORDER BY o.created_at DESC
    LIMIT 10
");

// Income statements by week
$income_statements = $db->query("
    SELECT 
        YEARWEEK(created_at, 1) AS week_code,
        MIN(DATE(created_at)) AS start_date,
        MAX(DATE(created_at)) AS end_date,
        COALESCE(SUM(total_amount),0) AS total
    FROM orders
    WHERE status NOT IN ('cancelled')
    GROUP BY YEARWEEK(created_at, 1)
    ORDER BY week_code DESC
    LIMIT 5
");

// ── STATS ─────────────────────────────
$total_orders = $db->query("
    SELECT COUNT(*) AS c 
    FROM orders
")->fetch_assoc()['c'];

$pending_ship = $db->query("
    SELECT COUNT(*) AS c 
    FROM orders 
    WHERE status IN ('pending','processing')
")->fetch_assoc()['c'];

$processed_ship = $db->query("
    SELECT COUNT(*) AS c 
    FROM orders 
    WHERE status IN ('shipped','delivered','completed')
")->fetch_assoc()['c'];

$cancelled = $db->query("
    SELECT COUNT(*) AS c 
    FROM orders 
    WHERE status = 'cancelled'
")->fetch_assoc()['c'];

$total_revenue = $db->query("
    SELECT COALESCE(SUM(total_amount),0) AS r 
    FROM orders 
    WHERE status NOT IN ('cancelled')
")->fetch_assoc()['r'];

$total_products = $db->query("
    SELECT COUNT(*) AS c 
    FROM products 
    WHERE is_active = 1
")->fetch_assoc()['c'];

$total_users = $db->query("
    SELECT COUNT(*) AS c 
    FROM users 
    WHERE role = 'customer'
")->fetch_assoc()['c'];

$custom_pending = $db->query("
    SELECT COUNT(*) AS c 
    FROM custom_orders 
    WHERE status = 'pending'
")->fetch_assoc()['c'];


// ── WEEK + MONTH INCOME ─────────────────────────────
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end   = date('Y-m-d', strtotime('sunday this week'));

$income_week = $db->query("
    SELECT COALESCE(SUM(total_amount),0) AS r 
    FROM orders
    WHERE status NOT IN ('cancelled')
    AND DATE(created_at) BETWEEN '$week_start' AND '$week_end'
")->fetch_assoc()['r'];

$income_month = $db->query("
    SELECT COALESCE(SUM(total_amount),0) AS r 
    FROM orders
    WHERE status NOT IN ('cancelled')
    AND MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at) = YEAR(CURDATE())
")->fetch_assoc()['r'];


<<<<<<< HEAD
=======
// ── RECENT ORDERS ─────────────────────────────
$recent_orders = $db->query("
    SELECT o.*, u.full_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 8
");

>>>>>>> daf6634f62d7b468a4474b8f1482a12e923157ca
$page_title  = 'Dashboard';
$active_page = 'dashboard';

include 'admin-nav.php';
?>

<link rel="stylesheet" href="css/admin-dashboard.css">

<div class="admin-wrap">

    <!-- TOP STAT STRIP -->
    <div class="top-stat-strip">

        <div class="top-stat-item">
            <h3><?= $pending_ship ?></h3>
            <p>To-Process Shipment</p>
        </div>

        <div class="top-stat-item">
            <h3><?= $processed_ship ?></h3>
            <p>Processed Shipment</p>
        </div>

        <div class="top-stat-item">
            <h3><?= $cancelled ?></h3>
            <p>Return/Refund/Cancel</p>
        </div>

        <div class="top-stat-item">
            <h3><?= $total_products ?></h3>
            <p>Banned/Deactivated Products</p>
        </div>

    </div>


    <!-- BUSINESS INSIGHTS -->
    <div class="dashboard-card business-card">

        <div class="card-title-row">
            <h3>Business Insights</h3>
            <span>Real Time Data until <?= date('M d, Y') ?></span>
        </div>

        <div class="business-grid">

            <div class="business-item">
                <p>Sales</p>
                <h2>₱<?= number_format($total_revenue, 0) ?></h2>
                <span class="down">▼ ₱0.00</span>
            </div>

            <div class="business-item">
                <p>Visitors</p>
                <h2><?= $total_users ?></h2>
                <span class="down">▼ 0.00%</span>
            </div>

            <div class="business-item">
                <p>Product Clicks</p>
                <h2><?= $total_products ?></h2>
                <span class="down">▼ 0.00%</span>
            </div>

            <div class="business-item">
                <p>Orders</p>
                <h2><?= $total_orders ?></h2>
                <span class="down">▼ 0.00%</span>
            </div>

            <div class="business-item">
                <p>Order Conversion Rate</p>
                <h2><?= $total_users > 0 ? number_format(($total_orders / $total_users) * 100, 2) : '0.00' ?>%</h2>
                <span class="down">▼ 0.00%</span>
            </div>

        </div>

    </div>


    <!-- INCOME ROW -->
    <div class="income-row">

        <div class="dashboard-card income-overview">

            <h3>Income Overview</h3>

            <div class="income-grid">

                <div>
                    <p>Pending</p>
                    <h2>₱<?= number_format($pending_income, 2) ?></h2>
                </div>

                <div>
                    <p>Released</p>
                    <h2>₱<?= number_format($released_income, 2) ?></h2>
                </div>

                <div>
                    <p>This month</p>
                    <h2>₱<?= number_format($income_month, 2) ?></h2>
                </div>

                <div>
                    <p>Total</p>
                    <h2>₱<?= number_format($total_revenue, 2) ?></h2>
                </div>

            </div>

            <div class="bank-row">
                <span>My Bank Account: **** 1234</span>
                <a href="#">My Balance ></a>
            </div>

        </div>


        <div class="dashboard-card income-statements">

            <div class="card-title-row">
                <h3>Income Statements</h3>
                <a href="#">More ></a>
            </div>

            <ul>
                <?php if ($income_statements && $income_statements->num_rows > 0): ?>
                    <?php while ($st = $income_statements->fetch_assoc()): ?>
                        <li>
                            <span>
                                <?= date('d M', strtotime($st['start_date'])) ?>
                                -
                                <?= date('d M Y', strtotime($st['end_date'])) ?>
                            </span>
                            <a href="#">₱<?= number_format($st['total'], 2) ?></a>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li><span>No income statements yet</span></li>
                <?php endif; ?>
            </ul>

        </div>

    </div>


    <!-- INCOME DETAILS -->
    <div class="dashboard-card income-details">

        <div class="income-details-header">
            <h3>Income Details</h3>

<<<<<<< HEAD
            <form class="search-box" method="get" action="<?= $base_url ?>">
                <input type="text" name="search" placeholder="Search Order" value="<?= htmlspecialchars($search) ?>">
                <input type="hidden" name="income_tab" value="<?= htmlspecialchars($income_tab) ?>">
                <button type="submit">🔍</button>
            </form>
        </div>

        <div class="income-tabs">
            <button type="button" class="<?= $income_tab === 'pending' ? 'active' : '' ?>" onclick="window.location='<?= $base_url ?>?<?= http_build_query(['income_tab' => 'pending', 'search' => $search]) ?>'">Pending</button>
            <button type="button" class="<?= $income_tab === 'released' ? 'active' : '' ?>" onclick="window.location='<?= $base_url ?>?<?= http_build_query(['income_tab' => 'released', 'search' => $search]) ?>'">Released</button>
        </div>

        <?php if ($income_details && $income_details->num_rows > 0): ?>
=======
            <div class="search-box">
                <input type="text" placeholder="Search Order">
                <span>🔍</span>
            </div>
        </div>

        <div class="income-tabs">
            <button>Pending</button>
            <button class="active">Released</button>
        </div>

        <?php if ($recent_orders->num_rows > 0): ?>
>>>>>>> daf6634f62d7b468a4474b8f1482a12e923157ca

            <table class="income-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Payment Released</th>
<<<<<<< HEAD
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Released Amount</th>
                        <th>items ordered</th>
                        <th>address</th>
                        <th>Customer Contact</th>
                        <th>Delivery Status</th>
=======
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Released Amount</th>
>>>>>>> daf6634f62d7b468a4474b8f1482a12e923157ca
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = $income_details->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="order-cell">
                                <div class="order-thumb">🧲</div>
                                <div>
                                    <strong>#<?= $row['id'] ?></strong>
<<<<<<< HEAD
                                    <p>Buyer: <?= htmlspecialchars($row['full_name']) ?></p>
=======
                                    <p>Buyer: <?= htmlspecialchars($row['full_name'] ?? 'Customer') ?></p>
>>>>>>> daf6634f62d7b468a4474b8f1482a12e923157ca
                                </div>
                            </div>
                        </td>

                        <td>
                            <?= $row['released_at'] ? date('m/d/Y', strtotime($row['released_at'])) : date('m/d/Y', strtotime($row['created_at'])) ?>
                        </td>

                        <td>
<<<<<<< HEAD
                            <?= $row['payment_status'] === 'released' ? 'Released' : 'Pending' ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['payment_method'] ?: 'Cash on Delivery') ?>
                        </td>

                        <td>
                            ₱<?= number_format($row['total_amount'], 2) ?>
                        </td>

                        <td><?= (int)$row['total_items'] ?></td>

                        <td><?= htmlspecialchars($row['shipping_address'] ?: 'N/A') ?></td>

                        <td><?= htmlspecialchars($row['user_phone'] ?: 'N/A') ?></td>

                        <td>
                            <?= htmlspecialchars(match ($row['status']) {
                                'pending' => 'Order Placed',
                                'processing' => 'Processing',
                                default => ucfirst($row['status']),
                            }) ?>
                        </td>
=======
                            <?= $row['payment_status'] === 'released' ? 'Payment transferred successfully' : 'Payment pending' ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['payment_method'] ?? 'Cash on Delivery') ?>
                        </td>

                        <td>
                            ₱<?= number_format($row['total_amount'], 2) ?>⌄
                        </td>

                        <td><?= date('m/d/Y', strtotime($row['created_at'])) ?></td>

                        <td>Payment transferred successfully</td>

                        <td>Cash on Delivery</td>

                        <td>₱<?= number_format($row['total_amount'], 2) ?>⌄</td>
>>>>>>> daf6634f62d7b468a4474b8f1482a12e923157ca
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>

            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No orders yet</h3>
            </div>

        <?php endif; ?>

    </div>

</div>

<?php include 'chat-widget.php'; ?>
</body>
</html>