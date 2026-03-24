<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Sales Overview — Pet Shop Management" />
  <title>Sales — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
</head>
<body>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Sales Overview</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper" style="padding-top: var(--sp-md);">

  <!-- Summary pills -->
  <div class="summary-pills">
    <span class="pill">📆 March 2026</span>
    <span class="pill accent">Total: Rs. 1,85,400</span>
    <span class="pill info">34 transactions</span>
  </div>

  <!-- Stats cards -->
  <div class="drawer-header" style="margin-bottom: var(--sp-lg);">
    <div class="stat-card">
      <div class="stat-label">🐾 Pets Sold</div>
      <div class="stat-value">127</div>
    </div>
    <div class="stat-card accent">
      <div class="stat-label">💰 Revenue</div>
      <div class="stat-value">1.85L</div>
    </div>
  </div>

  <!-- Filter bar -->
  <div class="flex gap-sm" style="margin-bottom: var(--sp-md); flex-wrap:wrap; align-items:center;">
    <h2 class="section-title" style="margin-bottom:0; flex:1;">Recent Sales</h2>
    <div style="display:flex; gap:6px;">
      <input type="month" class="form-control" id="salesFilter"
        style="max-width:150px; font-size:.82rem; padding:8px 10px;"
        value="2026-03" aria-label="Filter by month" />
    </div>
  </div>

  <!-- Sales list -->
  <div id="salesList">

    <div class="sale-item">
      <div class="item-icon">🐶</div>
      <div class="item-info">
        <div class="item-name">Golden Retriever</div>
        <div class="item-meta">2 units &middot; 20 Mar 2026</div>
      </div>
      <div class="item-amt">Rs. 30,000</div>
    </div>

    <div class="sale-item">
      <div class="item-icon">🐱</div>
      <div class="item-info">
        <div class="item-name">Persian Cat</div>
        <div class="item-meta">1 unit &middot; 19 Mar 2026</div>
      </div>
      <div class="item-amt">Rs. 8,500</div>
    </div>

    <div class="sale-item">
      <div class="item-icon">🦜</div>
      <div class="item-info">
        <div class="item-name">Macaw Parrot</div>
        <div class="item-meta">3 units &middot; 18 Mar 2026</div>
      </div>
      <div class="item-amt">Rs. 22,500</div>
    </div>

    <div class="sale-item">
      <div class="item-icon">🐰</div>
      <div class="item-info">
        <div class="item-name">Holland Lop Rabbit</div>
        <div class="item-meta">1 unit &middot; 17 Mar 2026</div>
      </div>
      <div class="item-amt">Rs. 4,200</div>
    </div>

    <div class="sale-item">
      <div class="item-icon">🐹</div>
      <div class="item-info">
        <div class="item-name">Syrian Hamster</div>
        <div class="item-meta">5 units &middot; 16 Mar 2026</div>
      </div>
      <div class="item-amt">Rs. 3,750</div>
    </div>

    <div class="sale-item">
      <div class="item-icon">🐠</div>
      <div class="item-info">
        <div class="item-name">Betta Fish (Pair)</div>
        <div class="item-meta">4 pairs &middot; 15 Mar 2026</div>
      </div>
      <div class="item-amt">Rs. 2,400</div>
    </div>

    <div class="sale-item">
      <div class="item-icon">🐢</div>
      <div class="item-info">
        <div class="item-name">Red-ear Slider Turtle</div>
        <div class="item-meta">2 units &middot; 14 Mar 2026</div>
      </div>
      <div class="item-amt">Rs. 5,600</div>
    </div>

    <div class="sale-item">
      <div class="item-icon">🦮</div>
      <div class="item-info">
        <div class="item-name">German Shepherd</div>
        <div class="item-meta">1 unit &middot; 12 Mar 2026</div>
      </div>
      <div class="item-amt">Rs. 25,000</div>
    </div>

  </div><!-- /salesList -->

  <!-- Revenue monthly chart -->
  <h2 class="section-title" style="margin-top:var(--sp-xl);">Monthly Revenue</h2>
  <div class="chart-wrapper" style="margin-bottom: var(--sp-xl);">
    <div class="bar-chart" id="monthChart" style="height:130px;" aria-label="Monthly revenue bar chart"></div>
    <div class="bar-divider"></div>
    <p class="text-muted mt-sm" style="font-size:.75rem; font-weight:600; padding-top:6px;">Rs. (thousands) · Last 6 months</p>
  </div>

</div><!-- /app-wrapper -->

<!-- ===== TOAST ===== -->
<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
/* ---- Monthly Revenue Chart ---- */
(function () {
  const months = [
    { label: 'Oct', value: 92 },
    { label: 'Nov', value: 115 },
    { label: 'Dec', value: 148 },
    { label: 'Jan', value: 103 },
    { label: 'Feb', value: 162 },
    { label: 'Mar', value: 185 },
  ];
  const max = Math.max(...months.map(m => m.value));
  const chart = document.getElementById('monthChart');

  months.forEach(m => {
    const pct = Math.round((m.value / max) * 100);
    const col = document.createElement('div');
    col.className = 'bar-col';
    col.innerHTML = `
      <span class="bar-val" style="font-size:.68rem;">${m.value}K</span>
      <div class="bar-fill" style="height:0%" data-pct="${pct}%"></div>
      <span class="bar-label">${m.label}</span>
    `;
    chart.appendChild(col);
  });

  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      chart.querySelectorAll('.bar-fill').forEach(bar => {
        bar.style.height = bar.dataset.pct;
      });
    });
  });
})();
</script>
</body>
</html>
