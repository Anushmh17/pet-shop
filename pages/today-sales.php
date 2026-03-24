<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Today's Sales — Pet Shop Management" />
  <title>Today Sales — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
</head>
<body>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Today's Sales</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper" style="padding-top: var(--sp-md);">

  <!-- Hero card -->
  <div class="today-hero" id="todayHero">
    <div class="hero-label">Total Revenue Today</div>
    <div class="hero-amount" id="heroAmount">Rs. 0</div>
    <div class="hero-sub" id="heroDate"></div>
  </div>

  <!-- Quick stats -->
  <div class="drawer-header" style="margin-bottom: var(--sp-lg);">
    <div class="stat-card">
      <div class="stat-label">🛒 Transactions</div>
      <div class="stat-value" id="txCount">0</div>
    </div>
    <div class="stat-card accent">
      <div class="stat-label">🐾 Units Sold</div>
      <div class="stat-value" id="unitCount">0</div>
    </div>
  </div>

  <!-- Section Title -->
  <div class="flex-between" style="margin-bottom: var(--sp-sm);">
    <h2 class="section-title" style="margin-bottom:0;">Today's Entries</h2>
    <a href="sales.php" class="btn btn-primary btn-sm">＋ Record Sale</a>
  </div>

  <!-- Today's sales list -->
  <div id="todaySalesList">
      <!-- Records loaded via JS -->
  </div>

  <div id="emptyToday" class="empty-state" style="display:none;">
    <div class="empty-icon">🛒</div>
    <p>No sales recorded yet today.</p>
    <a href="sales.php" class="btn btn-primary mt-md btn-sm">Add First Sale</a>
  </div>

</div><!-- /app-wrapper -->

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    updateTodayDate();
    renderTodaySales();
});

function updateTodayDate() {
  const d = new Date();
  document.getElementById('heroDate').textContent =
    d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
}

function renderTodaySales() {
    const list = document.getElementById('todaySalesList');
    const empty= document.getElementById('emptyToday');
    const sales = DB.getTodaySales();

    if (sales.length === 0) {
        list.innerHTML = '';
        empty.style.display = '';
        updateStats(sales);
        return;
    }

    empty.style.display = 'none';
    list.innerHTML = sales.map(s => `
      <div class="sale-item">
        <div class="item-icon">${s.petIcon}</div>
        <div class="item-info">
          <div class="item-name">${s.petName}</div>
          <div class="item-meta">${s.qty} unit${s.qty > 1 ? 's' : ''} &middot; @Rs. ${s.price.toLocaleString('en-IN')}</div>
        </div>
        <div class="item-amt">Rs. ${s.total.toLocaleString('en-IN')}</div>
      </div>
    `).join('');

    updateStats(sales);
}

function updateStats(sales) {
    const totalRev = sales.reduce((sum, s) => sum + s.total, 0);
    const totalQty = sales.reduce((sum, s) => sum + s.qty, 0);

    document.getElementById('heroAmount').textContent  = 'Rs. ' + totalRev.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('txCount').textContent  = sales.length;
    document.getElementById('unitCount').textContent = totalQty;
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}
</script>
</body>
</html>
