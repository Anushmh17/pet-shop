<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Pet Shop Management System — Home Dashboard" />
  <title>Pet Shop — Dashboard</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
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
    <div class="greeting-name">Good Afternoon, Owner 👋</div>
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
       <!-- Loaded via JS -->
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
        <!-- bars injected by JS using real sales data -->
      </div>
      <div class="bar-divider"></div>
      <p class="text-muted mt-sm" style="font-size:.75rem; font-weight:600; padding-top:6px;">Units sold according to recent data</p>
    </div>
    <div id="noSales" class="empty-state" style="display:none; padding: 20px;">
      <p style="font-size:.8rem;">No sales recorded yet to show chart.</p>
    </div>
  </section>

</div><!-- /app-wrapper -->

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
/* ---- UI Initialization ---- */
document.addEventListener('DOMContentLoaded', () => {
    updateTodayDate();
    loadStockAlerts();
    loadBestSellingChart();
});

function updateTodayDate() {
  const d = new Date();
  document.getElementById('todayDate').textContent =
    d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}

/* ---- Stock Alert Logic ---- */
function loadStockAlerts() {
    const list = document.getElementById('stockAlertList');
    const pets = DB.getPets();
    const alertPets = pets.filter(p => !p.stopAlert && p.qty < (p.alertLevel || 10));

    if (alertPets.length === 0) {
        list.innerHTML = '';
        document.getElementById('noAlerts').style.display = '';
        return;
    }

    document.getElementById('noAlerts').style.display = 'none';
    list.innerHTML = alertPets.map(p => `
      <div class="alert-card" id="alert-${p.id}">
        <div class="alert-info">
          <span class="alert-dot"></span>
          <span class="alert-pet-name">${p.name} (Qty: ${p.qty})</span>
        </div>
        <span class="alert-badge">Low Stock</span>
        <button class="btn-stop-alert" onclick="stopAlert('${p.id}')">Stop Alert</button>
      </div>
    `).join('');
}

function stopAlert(petId) {
  const pId = parseInt(petId);
  const pets = DB.getPets();
  const pet = pets.find(p => p.id === pId);
  if (pet) {
    pet.stopAlert = true;
    DB.savePets(pets);
    showToast('Alert stopped for ' + pet.name);
    
    // Animate out
    const card = document.getElementById('alert-' + petId);
    card.classList.add('dismissed');
    setTimeout(() => {
        card.style.opacity = '0';
        card.style.maxHeight = '0';
        card.style.margin = '0';
        setTimeout(() => {
            card.remove();
            loadStockAlerts();
        }, 400);
    }, 200);
  }
}

/* ---- Best Selling Chart Logic ---- */
function loadBestSellingChart() {
    const chart = document.getElementById('barChart');
    const salesData = DB.getSalesByPet(); // Real sales count
    
    // Sample if nothing exists?
    const data = salesData.length > 0 ? salesData.slice(0, 5) : [
        {name: 'No Sales', qty: 0}
    ];

    if (salesData.length === 0) {
        document.getElementById('noSales').style.display = '';
        chart.style.display = 'none';
        return;
    }

    const max = Math.max(...data.map(d => d.qty)) || 1;
    chart.innerHTML = data.map(d => {
        const pct = Math.round((d.qty / max) * 100);
        return `
          <div class="bar-col">
            <span class="bar-val">${d.qty}</span>
            <div class="bar-fill" style="height:${pct}%"></div>
            <span class="bar-label">${d.name}</span>
          </div>
        `;
    }).join('');
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
