<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Pet Shop Management System — Home Dashboard" />
  <title>Pet Shop — Dashboard</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav" style="position:sticky; top:0; z-index:1000; background:#fff;">
  <span class="nav-title">Paw-Farm Dashboard</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div id="content-wrapper">
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
      <span class="card-label">Drawer</span>
    </a>
  </div>

  <!-- ===== CHARTS OVERVIEW ===== -->
  <section class="overview-section">
    <h2 class="section-title">Pet Sales Chart</h2>
    <div class="chart-wrapper" style="padding: var(--sp-md) var(--sp-md) var(--sp-sm);">
      <div id="barChartWrap" style="overflow-x:auto; overflow-y:visible; -webkit-overflow-scrolling:touch; scrollbar-width:none;">
        <div class="bar-chart" id="barChart" aria-label="Bar chart of all pets" style="height:180px; align-items:flex-end; padding-top:28px; min-width:100%; width:max-content; gap:0;"></div>
        <div id="barLabels" style="display:flex; padding-top:8px; border-top:1.5px solid var(--clr-border); margin-top:4px;"></div>
      </div>
      <p class="text-muted mt-sm" style="font-size:.72rem; font-weight:600; padding-top:8px;">📦 Units sold per pet · all inventory included</p>
    </div>
    <div id="noSales" class="empty-state" style="display:none; padding: 20px;"><p style="font-size:.8rem;">No sales yet.</p></div>
  </section>

  <!-- Stock Alert Section -->
  <section class="overview-section" style="margin-top:20px; margin-bottom: 20px;">
    <div class="flex-between">
      <h2 class="section-title" style="margin:0;">Stock Alerts</h2>
      <a href="manage-pets.php" style="font-size:.7rem; color:var(--clr-primary); font-weight:800; text-decoration:none;">VIEW ALL ➔</a>
    </div>
    <div id="stockAlertsList" style="margin-top:15px;"></div>
    <div id="noAlerts" class="empty-state" style="display:none; padding:40px 0;"><p>Inventory looking healthy!</p></div>
  </section>

</div><!-- /app-wrapper -->
</div><!-- /content-wrapper -->

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
let startY = 0, distY = 0, pulling = false;
const cnt = document.getElementById('content-wrapper');

window.addEventListener('touchstart', e => { if(window.scrollY === 0){ startY = e.touches[0].pageY; pulling = true; } }, {passive:true});
window.addEventListener('touchmove', e => {
    if(!pulling) return;
    const y = e.touches[0].pageY;
    distY = (y - startY) * 0.4;
    if(distY > 0 && window.scrollY === 0){
        document.body.classList.add('ptr-pulling');
        cnt.style.transform = `translateY(${Math.min(distY, 80)}px)`;
    }
}, {passive:true});
window.addEventListener('touchend', async () => {
    if(pulling && distY >= 60){
        document.body.classList.remove('ptr-pulling');
        document.body.classList.add('ptr-loading');
        cnt.style.transform = 'translateY(40px)';
        await initDashboard(); 
        setTimeout(() => {
            document.body.classList.remove('ptr-loading');
            cnt.style.transform = '';
        }, 500);
    } else {
        document.body.classList.remove('ptr-pulling', 'ptr-loading');
        cnt.style.transform = '';
    }
    pulling = false; distY = 0;
}, {passive:true});

async function initDashboard() {
    updateTodayDate();
    await loadStockAlerts();
    await loadBestSellingChart();
}

function updateTodayDate() {
    const today = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('todayDate').textContent = today.toLocaleDateString('en-US', options);
}

async function loadStockAlerts() {
    const list = document.getElementById('stockAlertsList');
    const pets = await DB.getPets();
    const alertPets = pets.filter(p => !p.stop_alert && parseInt(p.qty) <= parseInt(p.alert_level));

    if (alertPets.length === 0) {
        list.innerHTML = '';
        document.getElementById('noAlerts').style.display = 'block';
        return;
    }

    document.getElementById('noAlerts').style.display = 'none';
    list.innerHTML = `
      <div class="table-container" style="margin-top: 10px;">
        <table class="pet-table" style="border-collapse: collapse;">
          <thead><tr><th style="padding-left:15px;">Pet</th><th style="text-align:center;">Stock</th><th style="text-align:right; padding-right:15px;">Action</th></tr></thead>
          <tbody>
            ${alertPets.map(p => `
              <tr id="alert-${p.id}">
                <td style="padding-left:15px;"><div style="font-weight:700; line-height:1.2;">${p.name}</div><div style="font-size:.62rem; color:var(--clr-muted); font-weight:700;">${p.pet_variety||''}</div></td>
                <td style="text-align:center; color:var(--clr-danger); font-weight:800;">${p.qty}</td>
                <td style="text-align:right; padding-right:15px;"><button class="btn btn-sm" style="font-size:.65rem; padding:4px 8px; border-radius:10px;" onclick="stopAlert('${p.id}')">Stop</button></td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
}

async function stopAlert(petId) {
    const res = await DB.toggleAlert(petId, true);
    if (!res || res.error) { showToast('Sync failed!'); return; }
    showToast('Alert stopped');
    const card = document.getElementById('alert-' + petId);
    if (card) {
        card.style.opacity = '0';
        setTimeout(() => { card.remove(); loadStockAlerts(); }, 300);
    }
}

async function loadBestSellingChart() {
    const chart = document.getElementById('barChart');
    const labels = document.getElementById('barLabels');
    const allPets = await DB.getPets();
    const salesData = await DB.getSalesByPet(); 
    const salesMap = {};
    salesData.forEach(s => salesMap[s.name] = s.qty);

    if (allPets.length === 0) {
        document.getElementById('noSales').style.display = 'block';
        document.getElementById('barChartWrap').style.display = 'none';
        return;
    }

    document.getElementById('noSales').style.display = 'none';
    document.getElementById('barChartWrap').style.display = 'block';

    chart.innerHTML = allPets.map(p => {
        const sold = salesMap[p.name] || 0;
        const maxSold = Math.max(...salesData.map(s => s.qty), 5);
        const heightPercent = (sold / maxSold) * 100;
        const color = sold > 0 ? 'var(--clr-primary)' : 'var(--clr-border)';
        return `
          <div style="flex:1; display:flex; flex-direction:column; align-items:center; min-width:60px;">
            <div style="width:24px; height:${Math.max(heightPercent, 5)}%; background:${color}; border-radius:6px 6px 0 0; transition:height .6s ease-out; position:relative;">
              <span style="position:absolute; top:-18px; left:50%; transform:translateX(-50%); font-size:.62rem; font-weight:800; color:var(--clr-text);">${sold}</span>
            </div>
          </div>
        `;
    }).join('');

    labels.innerHTML = allPets.map(p => `
      <div style="flex:1; font-size:.58rem; text-align:center; font-weight:700; color:var(--clr-muted); line-height:1.1; margin-top:4px; min-width:60px;">${p.name}</div>
    `).join('');
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}

document.addEventListener('DOMContentLoaded', initDashboard);
</script>
</body>
</html>
