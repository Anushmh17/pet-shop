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
    <h2 class="section-title">Pet Sales Chart</h2>
    <div class="chart-wrapper" style="padding: var(--sp-md) var(--sp-md) var(--sp-sm);">

      <!-- Scrollable bar chart -->
      <div id="barChartWrap" style="overflow-x:auto; overflow-y:visible; -webkit-overflow-scrolling:touch; scrollbar-width:none;">
        <div class="bar-chart" id="barChart" aria-label="Bar chart of all pets" style="height:180px; align-items:flex-end; padding-top:28px; min-width:100%; width:max-content; gap:0;">
          <!-- bars injected by JS -->
        </div>

        <!-- Full name labels row -->
        <div id="barLabels" style="display:flex; padding-top:8px; border-top:1.5px solid var(--clr-border); margin-top:4px;"></div>
      </div>

      <p class="text-muted mt-sm" style="font-size:.72rem; font-weight:600; padding-top:8px;">
        📦 Units sold per pet · all inventory included
      </p>
    </div>
    <div id="noSales" class="empty-state" style="display:none; padding: 20px;">
      <p style="font-size:.8rem;">No pets in inventory yet.</p>
    </div>
  </section>

</div><!-- /app-wrapper -->

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
/* ---- UI Initialization ---- */
document.addEventListener('DOMContentLoaded', async () => {
    updateTodayDate();
    await loadStockAlerts();
    await loadBestSellingChart();
});

function updateTodayDate() {
  const d = new Date();
  document.getElementById('todayDate').textContent =
    d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}

/* ---- Stock Alert Logic ---- */
async function loadStockAlerts() {
    const list = document.getElementById('stockAlertList');
    const pets = await DB.getPets();
    const alertPets = pets.filter(p => !p.stopAlert && p.qty < (p.alertLevel || 10));

    if (alertPets.length === 0) {
        list.innerHTML = '';
        document.getElementById('noAlerts').style.display = '';
        return;
    }

    document.getElementById('noAlerts').style.display = 'none';
    list.innerHTML = `
      <div class="table-container" style="margin-top: 10px;">
        <table class="pet-table" style="border-collapse: collapse;">
          <thead>
            <tr>
              <th style="padding-left: 15px;">Pet Name</th>
              <th style="text-align: center;">Stock</th>
              <th style="text-align: right; padding-right: 15px;">Action</th>
            </tr>
          </thead>
          <tbody>
            ${alertPets.map(p => `
              <tr id="alert-${p.id}">
                <td style="padding-left: 15px;">
                  <div style="display:flex; align-items:center; gap:8px;">
                    <span class="alert-dot" style="margin:0;"></span>
                    <div>
                        <div style="font-weight:700; line-height:1.2;">${p.name}</div>
                        ${p.petVariety ? `<div style="font-size:.62rem; color:var(--clr-muted); font-weight:700;">${p.petVariety}</div>` : ''}
                    </div>
                  </div>
                </td>
                <td style="text-align: center; color: var(--clr-danger); font-weight:800;">${p.qty}</td>
                <td style="text-align: right; padding-right: 15px;">
                  <button class="btn-stop-alert" style="margin:0; font-size:.7rem; padding:4px 10px;" onclick="stopAlert('${p.id}')">Stop Alert</button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
}

async function stopAlert(petId) {
  const pId = parseInt(petId);
  await DB.toggleAlert(pId, true);
  showToast('Alert stopped');
  
  const card = document.getElementById('alert-' + petId);
  if (card) {
      card.style.opacity = '0';
      setTimeout(() => {
          card.remove();
          loadStockAlerts();
      }, 300);
  }
}

/* ---- Best Selling Chart Logic ---- */
async function loadBestSellingChart() {
    const chart  = document.getElementById('barChart');
    const labels = document.getElementById('barLabels');
    const wrap   = document.getElementById('barChartWrap');

    // Build full pet list with their sales qty
    const allPets   = await DB.getPets();
    const salesData = await DB.getSalesByPet(); // [{name, qty}]
    const salesMap  = {};
    salesData.forEach(s => salesMap[s.name] = s.qty);

    if (allPets.length === 0) {
        document.getElementById('noSales').style.display = '';
        wrap.style.display = 'none';
        return;
    }

    // Merge: every pet gets a bar (0 if never sold)
    const data = allPets.map(p => ({
        name:  p.name,
        icon:  p.icon || '🐾',
        qty:   salesMap[p.name] || 0,
        color: p.chartColor || null
    }));

    // Sort: most sold first
    data.sort((a, b) => b.qty - a.qty);

    const max = Math.max(...data.map(d => d.qty), 1);
    const COLORS = [
        '#5c9e6e','#3b6de0','#f0a047','#9c5fe0',
        '#e05c5c','#0ea5e9','#f43f5e','#10b981',
        '#f59e0b','#6366f1'
    ];

    const colW = Math.max(72, Math.floor(300 / data.length)); // responsive col width

    chart.innerHTML = data.map((d, i) => {
        const pct   = max > 0 ? Math.max(Math.round((d.qty / max) * 100), d.qty > 0 ? 6 : 3) : 3;
        const color = COLORS[i % COLORS.length];
        return `
          <div class="bar-col" style="min-width:${colW}px; flex:0 0 ${colW}px; padding:0 6px;">
            <span class="bar-val" style="color:${color}; font-size:.72rem;">${d.qty > 0 ? d.qty : ''}</span>
            <div class="bar-fill" style="
              height:${pct}%;
              background: linear-gradient(180deg, ${color}cc 0%, ${color} 100%);
              border-radius:6px 6px 0 0;
              width:100%;
              max-width:none;
              opacity: ${d.qty === 0 ? '0.25' : '1'};
            "></div>
          </div>
        `;
    }).join('');

    // Full-name label row below chart
    labels.innerHTML = data.map((d, i) => {
        const color = COLORS[i % COLORS.length];
        return `
          <div style="
            min-width:${colW}px; flex:0 0 ${colW}px;
            padding:6px 4px 0;
            text-align:center;
          ">
            <div style="font-size:1.1rem; line-height:1;">${d.icon}</div>
            <div style="
              font-size:.65rem; font-weight:700;
              color:${color};
              margin-top:3px;
              word-break:break-word;
              line-height:1.3;
            ">${d.name}</div>
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
