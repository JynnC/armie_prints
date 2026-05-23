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

$page_title  = 'Customer List';
$active_page = 'users';

$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all';

$where = 'WHERE role = "customer"';
$params = [];
$types = '';

if ($search !== '') {
    $where .= ' AND full_name LIKE ?';
    $params[] = "%$search%";
    $types .= 's';
}

$sql = "SELECT id, full_name, email, phone, created_at FROM users $where ORDER BY full_name ASC";
$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();

include 'admin-nav.php';
?>

<link rel="stylesheet" href="css/admin-users.css">

<div class="admin-wrap">
  <div class="users-page-header">
    <div>
      <p class="page-label">Customer List</p>
      <h1>Customer List</h1>
    </div>
    <a href="#" class="btn-filter">+ Add Filter</a>
  </div>

  <div class="users-topbar">
    <form class="users-search" method="GET" action="admin-users.php">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
      <input class="users-search-input" type="text" name="search" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
      <button class="users-search-btn" type="submit">Search</button>
    </form>
    <div class="users-period">Jan 1-30</div>
  </div>

  <div class="users-card">
    <table class="users-table">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Status</th>
          <th>Phone Number</th>
          <th>Address</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($user = $users->fetch_assoc()): ?>
          <?php
            $status = ($user['id'] % 2 === 0) ? 'Offline' : 'Active';
            $statusClass = strtolower($status);
            $phone = trim((string)$user['phone']) ?: '(+63) 912 345 6789';
            $addresses = [
              '123 Main Street',
              '456 Rizal Avenue',
              '789 Mabini Road',
              '22 Taft Street',
              '88 P. Guevarra St.',
            ];
            $address = $addresses[$user['id'] % count($addresses)];
            $initial = strtoupper(substr(trim($user['full_name']), 0, 1));
          ?>
          <tr>
            <td>
              <div class="user-cell">
                <span class="user-avatar"><?= htmlspecialchars($initial) ?></span>
                <div>
                  <div class="user-name"><?= htmlspecialchars($user['full_name']) ?></div>
                  <div class="user-meta"><?= htmlspecialchars($user['email']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="status-dot <?= $statusClass ?>"></span>
              <span class="status-text"><?= $status ?></span>
            </td>
            <td><?= htmlspecialchars($phone) ?></td>
            <td><?= htmlspecialchars($address) ?></td>
            <td>
              <button type="button" class="btn-action">Ban</button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
