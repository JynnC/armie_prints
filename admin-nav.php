<?php
// includes/admin-nav.php
// Usage: include with $active_page set to: 'dashboard', 'products', 'orders', 'users', 'custom_orders'
$active_page = $active_page ?? 'dashboard';
$admin_name  = htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $page_title ?? 'Admin' ?> | ArmiePrints</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* ── RESET & BASE ───────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f6f9;
      color: #1a1a1a;
      min-height: 100vh;
    }
    a { text-decoration: none; color: inherit; }
    img { max-width: 100%; display: block; }

    :root {
      --teal:       #00B89C;
      --teal-dark:  #009982;
      --teal-light: #e6faf7;
      --coral:      #E84040;
      --coral-dark: #c93232;
      --gold:       #F5A623;
      --text:       #1a1a1a;
      --muted:      #777;
      --bg:         #f4f6f9;
      --white:      #ffffff;
      --border:     #e8e8e8;
    }

    /* ── ADMIN NAVBAR ───────────────────────────────────────────── */
    .admin-nav {
      background: #fff;
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 200;
      box-shadow: 0 1px 8px rgba(0,0,0,0.06);
    }

    .admin-nav-inner {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 28px;
      height: 62px;
      display: flex;
      align-items: center;
      gap: 32px;
    }

    .admin-logo {
      font-family: 'Nunito', sans-serif;
      font-size: 20px;
      font-weight: 900;
      color: var(--coral);
      flex-shrink: 0;
    }
    .admin-logo span { color: var(--teal); }

    .admin-nav-links {
      display: flex;
      list-style: none;
      gap: 6px;
      flex: 1;
      justify-content: center;
    }

    .admin-nav-links a {
      display: block;
      padding: 8px 18px;
      font-size: 13px;
      font-weight: 600;
      color: var(--muted);
      border-radius: 8px;
      transition: all 0.2s;
    }

    .admin-nav-links a:hover {
      color: var(--text);
      background: var(--bg);
    }

    .admin-nav-links a.active {
      color: var(--teal-dark);
      background: var(--teal-light);
      font-weight: 700;
    }

    .admin-nav-right {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-shrink: 0;
    }

    .admin-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--teal);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      font-weight: 800;
      font-family: 'Nunito', sans-serif;
    }

    .admin-greeting {
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
    }

    .btn-logout-admin {
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      padding: 6px 14px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      transition: all 0.2s;
    }
    .btn-logout-admin:hover {
      border-color: var(--coral);
      color: var(--coral);
    }

    /* ── ADMIN CONTENT WRAPPER ──────────────────────────────────── */
    .admin-wrap {
      max-width: 1280px;
      margin: 0 auto;
      padding: 32px 28px 60px;
    }

    .admin-page-title {
      font-family: 'Nunito', sans-serif;
      font-size: 24px;
      font-weight: 900;
      color: var(--text);
      margin-bottom: 4px;
    }

    .admin-page-sub {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 28px;
    }

    /* ── STAT CARDS ─────────────────────────────────────────────── */
    .stat-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 28px;
    }

    .stat-card {
      background: #fff;
      border-radius: 16px;
      padding: 22px 24px;
      border: 1px solid var(--border);
      box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .stat-card .stat-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--muted);
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    .stat-card .stat-value {
      font-family: 'Nunito', sans-serif;
      font-size: 28px;
      font-weight: 900;
      color: var(--text);
      margin-bottom: 2px;
    }

    .stat-card .stat-value.teal  { color: var(--teal-dark); }
    .stat-card .stat-value.coral { color: var(--coral); }
    .stat-card .stat-value.gold  { color: var(--gold); }

    /* ── CARD ───────────────────────────────────────────────────── */
    .admin-card {
      background: #fff;
      border-radius: 16px;
      border: 1px solid var(--border);
      box-shadow: 0 2px 12px rgba(0,0,0,0.04);
      margin-bottom: 24px;
      overflow: hidden;
    }

    .admin-card-header {
      padding: 18px 24px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .admin-card-title {
      font-family: 'Nunito', sans-serif;
      font-size: 16px;
      font-weight: 900;
      color: var(--text);
    }

    .admin-card-body { padding: 0; }

    /* ── TABLE ──────────────────────────────────────────────────── */
    .admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }

    .admin-table th {
      background: #fafafa;
      padding: 12px 18px;
      text-align: left;
      font-size: 11px;
      font-weight: 700;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.4px;
      border-bottom: 1px solid var(--border);
    }

    .admin-table td {
      padding: 14px 18px;
      border-bottom: 1px solid #f0f0f0;
      vertical-align: middle;
    }

    .admin-table tr:last-child td { border-bottom: none; }
    .admin-table tr:hover td { background: #fafbff; }

    /* ── STATUS BADGE ───────────────────────────────────────────── */
    .badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.3px;
      text-transform: capitalize;
    }
    .badge-pending    { background: #fff8e6; color: #b67b2d; border: 1px solid rgba(245,166,35,0.3); }
    .badge-processing { background: #e6f0ff; color: #2d5fb6; border: 1px solid rgba(74,144,217,0.3); }
    .badge-shipped    { background: #e8f1ff; color: #2d5fb6; border: 1px solid rgba(74,144,217,0.3); }
    .badge-delivered  { background: var(--teal-light); color: var(--teal-dark); border: 1px solid rgba(0,184,156,0.3); }
    .badge-completed  { background: #e8fff4; color: #1a9968; border: 1px solid rgba(26,153,104,0.3); }
    .badge-cancelled  { background: #ffeaea; color: var(--coral-dark); border: 1px solid rgba(232,64,64,0.3); }
    .badge-reviewing  { background: #f3e8ff; color: #7c3aed; border: 1px solid rgba(124,58,237,0.3); }
    .badge-approved   { background: #e8fff4; color: #1a9968; border: 1px solid rgba(26,153,104,0.3); }
    .badge-in_production { background: #fff0e6; color: #c25c00; border: 1px solid rgba(194,92,0,0.3); }
    .badge-done       { background: var(--teal-light); color: var(--teal-dark); border: 1px solid rgba(0,184,156,0.3); }
    .badge-admin      { background: #ffeaea; color: var(--coral-dark); border: 1px solid rgba(232,64,64,0.2); }
    .badge-customer   { background: #f0f0f0; color: #555; border: 1px solid #e0e0e0; }

    /* ── BUTTONS ────────────────────────────────────────────────── */
    .btn-primary {
      display: inline-block;
      padding: 9px 20px;
      background: var(--teal);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 700;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
      text-decoration: none;
    }
    .btn-primary:hover { background: var(--teal-dark); transform: translateY(-1px); }

    .btn-danger {
      display: inline-block;
      padding: 7px 16px;
      background: #ffeaea;
      color: var(--coral-dark);
      border: 1px solid rgba(232,64,64,0.3);
      border-radius: 8px;
      font-size: 12px;
      font-weight: 700;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
    }
    .btn-danger:hover { background: var(--coral); color: #fff; }

    .btn-sm {
      padding: 5px 12px;
      font-size: 11px;
      border-radius: 6px;
    }

    /* ── FORM ELEMENTS ──────────────────────────────────────────── */
    .form-select, .form-input {
      padding: 8px 12px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      font-size: 13px;
      font-family: 'Poppins', sans-serif;
      background: #fff;
      color: var(--text);
      outline: none;
      transition: border-color 0.2s;
    }
    .form-select:focus, .form-input:focus {
      border-color: var(--teal);
    }

    /* ── ALERT ──────────────────────────────────────────────────── */
    .alert {
      padding: 12px 18px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 20px;
    }
    .alert-success { background: #e6faf7; color: var(--teal-dark); border: 1px solid rgba(0,184,156,0.25); }
    .alert-error   { background: #ffeaea; color: var(--coral-dark); border: 1px solid rgba(232,64,64,0.25); }

    /* ── EMPTY STATE ────────────────────────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 48px 24px;
      color: var(--muted);
    }
    .empty-state .empty-icon { font-size: 40px; margin-bottom: 12px; }
    .empty-state h3 { font-family: 'Nunito', sans-serif; font-size: 18px; font-weight: 900; color: var(--text); margin-bottom: 6px; }
    .empty-state p  { font-size: 13px; }

    /* ── MODAL ──────────────────────────────────────────────────── */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.45);
      backdrop-filter: blur(4px);
      z-index: 999;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-overlay.open { display: flex; }

    .modal-box {
      background: #fff;
      border-radius: 18px;
      padding: 28px 32px;
      width: 100%;
      max-width: 460px;
      box-shadow: 0 24px 60px rgba(0,0,0,0.18);
      position: relative;
    }

    .modal-box h3 {
      font-family: 'Nunito', sans-serif;
      font-size: 18px;
      font-weight: 900;
      margin-bottom: 20px;
      color: var(--text);
    }

    .modal-close-btn {
      position: absolute;
      top: 14px; right: 16px;
      background: #f0f0f0;
      border: none;
      width: 28px; height: 28px;
      border-radius: 50%;
      font-size: 12px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .modal-close-btn:hover { background: var(--coral); color: #fff; }

    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: var(--text); }
    .form-group .form-input,
    .form-group .form-select,
    .form-group textarea {
      width: 100%;
    }
    .form-group textarea {
      padding: 8px 12px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      font-size: 13px;
      font-family: 'Poppins', sans-serif;
      resize: vertical;
      outline: none;
      transition: border-color 0.2s;
    }
    .form-group textarea:focus { border-color: var(--teal); }

    .modal-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 20px;
    }

    @media (max-width: 900px) {
      .stat-grid { grid-template-columns: repeat(2, 1fr); }
      .admin-nav-links { display: none; }
    }

    @media (max-width: 600px) {
      .stat-grid { grid-template-columns: 1fr; }
      .admin-wrap { padding: 20px 16px 48px; }
    }
  </style>
</head>
<body>

<nav class="admin-nav">
  <div class="admin-nav-inner">
    <div class="admin-logo">Armie<span>Prints</span></div>
    <ul class="admin-nav-links">
      <li><a href="admin.php"              class="<?= $active_page === 'dashboard'      ? 'active' : '' ?>">Dashboard</a></li>
      <li><a href="admin-products.php"     class="<?= $active_page === 'products'       ? 'active' : '' ?>">Products</a></li>
      <li><a href="admin-orders.php"       class="<?= $active_page === 'orders'         ? 'active' : '' ?>">Orders</a></li>
      <li><a href="admin-records.php" class="<?= $active_page === 'records' ? 'active' : '' ?>">Records</a></li>
      <li><a href="admin-users.php"        class="<?= $active_page === 'users'          ? 'active' : '' ?>">Users</a></li>
    </ul>
    <div class="admin-nav-right">
      <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
      <span class="admin-greeting"><?= $admin_name ?></span>
      <a href="logout.php" class="btn-logout-admin">Logout</a>
    </div>
  </div>
</nav>
