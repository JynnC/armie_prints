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

include 'admin-nav.php';
?>

<style>
  .records-shell {
    background: #efefef;
    min-height: calc(100vh - 62px);
    padding: 22px 16px 40px;
  }

  .records-board {
    max-width: 1180px;
    margin: 0 auto;
    background: #f6f6f6;
    border: 1px solid #d7d7d7;
    border-radius: 2px;
    padding: 20px 14px 24px;
  }

  .records-header {
    margin-bottom: 16px;
  }

  .records-header h1 {
    font-family: 'Nunito', sans-serif;
    font-size: 30px;
    line-height: 1.1;
    font-weight: 900;
    color: #151515;
    margin-bottom: 4px;
  }

  .records-header p {
    font-size: 14px;
    color: #2c2c2c;
  }

  .stat-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 22px;
  }

  .record-card {
    background: #f8f8f8;
    border-radius: 12px;
    padding: 14px 14px 12px;
    min-height: 112px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    position: relative;
  }

  .record-card.teal   { border-left: 3px solid #10bea8; border-bottom: 2px solid #10bea8; }
  .record-card.pink   { border-left: 3px solid #ff5975; border-bottom: 2px solid #ff5975; }
  .record-card.orange { border-left: 3px solid #ff9a38; border-bottom: 2px solid #ff9a38; }

  .record-card .title {
    font-size: 12px;
    font-weight: 700;
    color: #222;
    margin-bottom: 8px;
  }

  .record-card .value {
    font-family: 'Nunito', sans-serif;
    font-size: 38px;
    line-height: 1;
    font-weight: 900;
    color: #111;
    margin-bottom: 6px;
  }

  .record-card .delta {
    font-size: 10px;
    color: #b9b9b9;
    font-weight: 500;
  }

  .record-card .mini-icon {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #fff;
  }

  .record-card.teal .mini-icon   { background: #97ded1; }
  .record-card.pink .mini-icon   { background: #f3c3ce; }
  .record-card.orange .mini-icon { background: #eec4a0; }

  .analytics-box {
    background: #f8f8f8;
    border: 1px solid #e4e4e4;
    border-radius: 10px;
    padding: 12px 14px 12px;
    margin-bottom: 22px;
  }

  .analytics-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
  }

  .analytics-head h2 {
    font-size: 12px;
    font-weight: 700;
    color: #212121;
  }

  .analytics-month {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: #1e1e1e;
    font-weight: 600;
  }

  .analytics-month button {
    width: 28px;
    height: 22px;
    border: 1px solid #d4d4d4;
    background: #f1f1f1;
    border-radius: 6px;
    cursor: default;
    color: #525252;
  }

  .bar-grid {
    min-height: 220px;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    align-items: end;
    padding: 8px 42px 0;
  }

  .bar-item {
    text-align: center;
  }

  .bar {
    width: 72%;
    margin: 0 auto;
    border-radius: 8px;
    background: #0fb6a2;
  }

  .bar-label {
    font-size: 12px;
    color: #2a2a2a;
    margin-top: 14px;
    font-weight: 600;
  }

  .bottom-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 14px;
  }

  .products-box {
    background: #f8f8f8;
    border: 1px solid #10bea8;
    border-radius: 10px;
    overflow: hidden;
  }

  .products-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-bottom: 1px solid #dcdcdc;
  }

  .products-head h3 {
    font-size: 12px;
    font-weight: 700;
    color: #262626;
  }

  .products-head a {
    font-size: 12px;
    color: #08a995;
    font-weight: 600;
    pointer-events: none;
  }

  .products-table {
    width: 100%;
    border-collapse: collapse;
    background: #f8f8f8;
  }

  .products-table th,
  .products-table td {
    padding: 9px 10px;
    font-size: 10px;
    text-align: left;
    border-bottom: 1px solid #e6e6e6;
    color: #242424;
  }

  .products-table th {
    font-weight: 500;
    color: #4f4f4f;
  }

  .empty-row td {
    text-align: center;
    color: #9a9a9a;
    font-size: 11px;
    padding: 18px 10px;
  }

  .report-box {
    border-radius: 10px;
    padding: 16px;
    background: linear-gradient(180deg, #f3ebef 0%, #ff4566 100%);
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    min-height: 240px;
  }

  .report-icon {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: rgba(255,255,255,0.82);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #b33d4e;
    font-size: 22px;
    margin-bottom: 12px;
  }

  .report-box h4 {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 6px;
  }

  .report-box p {
    font-size: 10px;
    max-width: 220px;
    line-height: 1.4;
    margin-bottom: 16px;
    opacity: 0.95;
  }

  .export-btn {
    min-width: 136px;
    border: none;
    border-radius: 16px;
    padding: 7px 14px;
    background: #fdfdfd;
    color: #cc324b;
    font-size: 12px;
    font-weight: 700;
    pointer-events: none;
  }

  @media (max-width: 1000px) {
    .stat-cards,
    .bottom-grid {
      grid-template-columns: 1fr;
    }
    .bar-grid {
      padding: 8px 16px 0;
      gap: 14px;
    }
  }
</style>

<main class="records-shell">
  <section class="records-board">
    <header class="records-header">
      <h1>Sales Overview</h1>
      <p>Welcome Back! Here's what's happening today</p>
    </header>

    <section class="stat-cards">
      <article class="record-card teal">
        <span class="mini-icon">💳</span>
        <div class="title">Total Revenue</div>
        <div class="value">&mdash;</div>
        <div class="delta">&mdash;</div>
      </article>

      <article class="record-card pink">
        <span class="mini-icon">🛒</span>
        <div class="title">Total Orders</div>
        <div class="value">&mdash;</div>
        <div class="delta">&mdash;</div>
      </article>

      <article class="record-card orange">
        <span class="mini-icon">📊</span>
        <div class="title">Avg. Order Value</div>
        <div class="value">&mdash;</div>
        <div class="delta">&mdash;</div>
      </article>
    </section>

    <section class="analytics-box">
      <div class="analytics-head">
        <h2>Revenue Analytics</h2>
        <div class="analytics-month">
          <button type="button">&lt;</button>
          <span>&mdash;</span>
          <button type="button">&gt;</button>
        </div>
      </div>

      <div class="bar-grid" aria-hidden="true">
        <div class="bar-item">
          <div class="bar" style="height: 75px;"></div>
          <div class="bar-label">&mdash;</div>
        </div>
        <div class="bar-item">
          <div class="bar" style="height: 105px;"></div>
          <div class="bar-label">&mdash;</div>
        </div>
        <div class="bar-item">
          <div class="bar" style="height: 70px;"></div>
          <div class="bar-label">&mdash;</div>
        </div>
        <div class="bar-item">
          <div class="bar" style="height: 130px;"></div>
          <div class="bar-label">&mdash;</div>
        </div>
        <div class="bar-item">
          <div class="bar" style="height: 105px;"></div>
          <div class="bar-label">&mdash;</div>
        </div>
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
            <tr class="empty-row">
              <td colspan="5">No records available yet</td>
            </tr>
          </tbody>
        </table>
      </article>

      <aside class="report-box">
        <div class="report-icon">📄</div>
        <h4>Ready to Share?</h4>
        <p>Download the comprehensive monthly sales report in your preferred format.</p>
        <button class="export-btn" type="button">Export Report</button>
      </aside>
    </section>
  </section>
</main>

</body>
</html>
