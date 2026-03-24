<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Pet Farm Management System — Home Dashboard" />
  <title>Pet Shop — Dashboard</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
</head>
<body>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <div class="nav-spacer"></div>
  <span class="nav-title">🐾 Pet Shop</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper">

  <!-- Greeting -->
  <div class="greeting">
    <div class="greeting-label">Welcome back</div>
    <div class="greeting-name">Good Morning, Owner 👋</div>
    <div class="greeting-sub" id="todayDate"></div>
  </div>

  <!-- ===== ACTION GRID 2×2 ===== -->
  <div class="action-grid" role="navigation" aria-label="Main Actions">

    <a href="add-pet.php" class="action-card card-add-pet" id="btn-add-pet" aria-label="Add Pet">
      <div class="card-icon">🐶</div>
      <span class="card-label">Add Pet</span>
    </a>

    <a href="sales.php" class="action-card card-sales" id="btn-sales" aria-label="Sales">
      <div class="card-icon">📊</div>
      <span class="card-label">Sales</span>
    </a>

    <a href="today-sales.php" class="action-card card-today" id="btn-today-sales" aria-label="Today Sales">
      <div class="card-icon">🛒</div>
      <span class="card-label">Today Sales</span>
    </a>

    <a href="add-drawer.php" class="action-card card-drawer" id="btn-add-drawer" aria-label="Add Drawer">
      <div class="card-icon">📝</div>
      <span class="card-label">Add Drawer</span>
    </a>

  </div>

  <!-- ===== STOCK ALERTS ===== -->
  <section class="stock-section" aria-label="Stock Alerts">
    <h2 class="section-title">Stock Alerts</h2>

    <div id="stockAlertList">

      <div class="alert-card" id="alert-1">
        <div class="alert-info">
          <span class="alert-dot"></span>
          <span class="alert-pet-name">Golden Retriever</span>
        </div>
        <span class="alert-badge">Low Stock</span>
        <button class="btn-stop-alert" onclick="stopAlert('alert-1')" aria-label="Stop alert for Golden Retriever">Stop Alert</button>
      </div>

      <div class="alert-card" id="alert-2">
        <div class="alert-info">
          <span class="alert-dot"></span>
          <span class="alert-pet-name">Persian Cat</span>
        </div>
        <span class="alert-badge">Low Stock</span>
        <button class="btn-stop-alert" onclick="stopAlert('alert-2')" aria-label="Stop alert for Persian Cat">Stop Alert</button>
      </div>

      <div class="alert-card" id="alert-3">
        <div class="alert-info">
          <span class="alert-dot"></span>
          <span class="alert-pet-name">Budgerigar</span>
        </div>
        <span class="alert-badge">Critical</span>
        <button class="btn-stop-alert" onclick="stopAlert('alert-3')" aria-label="Stop alert for Budgerigar">Stop Alert</button>
      </div>

    </div>

    <div id="noAlerts" class="empty-state" style="display:none;">
      <div class="empty-icon">✅</div>
      <p>All stock levels are healthy!</p>
    </div>

  </section>

  <!-- ===== BEST SELLING CHART ===== -->
  <section class="chart-section" aria-label="Best Selling Pets">
    <h2 class="section-title">Best Selling Pets</h2>

    <div class="chart-wrapper">
      <div class="bar-chart" id="barChart" aria-label="Bar chart of best selling pets">
        <!-- bars injected by JS -->
      </div>
      <div class="bar-divider"></div>
      <p class="text-muted mt-sm" style="font-size:.75rem; font-weight:600; padding-top:6px;">Units sold this month</p>
    </div>
  </section>

</div><!-- /app-wrapper -->

<!-- ===== TOAST ===== -->
<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
/* ---- Date greeting ---- */
(function () {
  const d = new Date();
  document.getElementById('todayDate').textContent =
    d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
})();

/* ---- Stop alert ---- */
function stopAlert(id) {
  const card = document.getElementById(id);
  if (!card) return;
  card.classList.add('dismissed');
  card.querySelector('.btn-stop-alert').disabled = true;

  setTimeout(() => {
    card.style.maxHeight = card.offsetHeight + 'px';
    card.style.overflow  = 'hidden';
    card.style.transition = 'max-height .4s ease, margin .4s ease, padding .4s ease, opacity .4s ease';
    requestAnimationFrame(() => {
      card.style.maxHeight = '0';
      card.style.marginBottom = '0';
      card.style.paddingTop = '0';
      card.style.paddingBottom = '0';
      card.style.opacity = '0';
    });
    setTimeout(() => {
      card.remove();
      checkNoAlerts();
    }, 420);
  }, 180);

  showToast('Alert stopped ✓');
}

function checkNoAlerts() {
  const list = document.getElementById('stockAlertList');
  if (!list || list.children.length === 0) {
    document.getElementById('noAlerts').style.display = '';
  }
}

/* ---- Bar chart ---- */
(function () {
  const pets = [
    { name: 'Retriever', value: 42 },
    { name: 'Persian', value: 31 },
    { name: 'Parrot', value: 27 },
    { name: 'Rabbit', value: 18 },
    { name: 'Hamster', value: 14 },
  ];
  const max = Math.max(...pets.map(p => p.value));
  const chart = document.getElementById('barChart');

  pets.forEach(pet => {
    const pct = Math.round((pet.value / max) * 100);
    const col = document.createElement('div');
    col.className = 'bar-col';
    col.innerHTML = `
      <span class="bar-val">${pet.value}</span>
      <div class="bar-fill" style="height:0%" data-pct="${pct}%"></div>
      <span class="bar-label">${pet.name}</span>
    `;
    chart.appendChild(col);
  });

  /* Animate bars after paint */
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      chart.querySelectorAll('.bar-fill').forEach(bar => {
        bar.style.height = bar.dataset.pct;
      });
    });
  });
})();

/* ---- Toast helper ---- */
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}
</script>
</body>
</html>
