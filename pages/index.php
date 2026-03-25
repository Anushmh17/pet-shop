<?php
session_start();
if (!isset($_SESSION['admin_auth'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Pet Shop Management System — Home Dashboard" />
  <title>Pet Shop — Dashboard</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <style>
    /* Category action card — full width below grid */
    .card-category .card-icon { background: #fef3e2; color: #e67e22; }
    .profile-link {
        position: absolute; top: 0; right: 0; width: 50px; height: 50px;
        background: white; border-radius: 50%; box-shadow: var(--shadow-sm);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; border: 1.5px solid var(--clr-border);
    }
    .payment-alert {
      background: #fff8e1;
      border: 1.5px solid #ffca28;
      border-radius: var(--r-md);
      padding: 12px 15px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.02); }
      100% { transform: scale(1); }
    }
    .overdue { color: var(--clr-danger) !important; font-weight: 800; }
  </style>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== MAIN CONTENT ===== -->
<div id="content-wrapper">
<div class="app-wrapper" style="padding-top: 25px;">

  <!-- Greeting -->
  <div class="greeting" style="position:relative;">
    <a href="profile.php" class="profile-link" aria-label="Admin Profile">👤</a>
    <div class="greeting-label">Welcome back</div>
    <div class="greeting-name" id="greetText">Good Day,<br>Owner 👋</div>
    <div class="greeting-sub" id="todayDate"></div>
  </div>

  <!-- Notification Banner -->
  <div id="notif-area"></div>

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
    <a href="suppliers.php" class="action-card" id="btn-suppliers" aria-label="Suppliers" style="border-top-color: #6c5ce7;">
      <div class="card-icon" style="background: #efecfd; color: #6c5ce7;">🚚</div>
      <span class="card-label">Suppliers</span>
    </a>
  </div>

  <!-- ===== CATEGORY BOX (full width) ===== -->
  <a href="category.php" class="action-card card-category" id="btn-category" aria-label="Browse by Category"
     style="display:flex; flex-direction:row; justify-content:flex-start; padding: 18px 20px; min-height:auto; gap:16px; margin-bottom: var(--sp-lg); border-radius: var(--r-lg);">
    <div class="card-icon" style="width:48px; height:48px; border-radius:12px; font-size:1.4rem; flex-shrink:0;">🗂️</div>
    <div style="text-align:left;">
      <span class="card-label" style="font-size:1rem; display:block;">Category</span>
      <span style="font-size:.72rem; font-weight:600; color:var(--clr-muted);">Browse pets by type</span>
    </div>
    <div style="margin-left:auto; color:var(--clr-muted); font-size:1rem; display:flex; align-items:center;">›</div>
  </a>

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

  <!-- Pending Payments Section -->
  <section class="overview-section" style="margin-top:20px;">
    <h2 class="section-title">Pending Payments</h2>
    <div id="pendingPaymentsList" style="margin-top:10px;"></div>
    <div id="noPending" class="empty-state" style="display:none; padding:30px 0;"><p>All payments are settled! ✅</p></div>
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

window.addEventListener('touchstart', e => {
  if(window.scrollY < 5){ startY = e.touches[0].pageY; pulling = true; }
}, {passive:true});

window.addEventListener('touchmove', e => {
  if(!pulling) return;
  const y = e.touches[0].pageY;
  distY = (y - startY) * 0.4;
  if(distY > 0 && window.scrollY < 5){
    if(e.cancelable) e.preventDefault();
    document.body.classList.add('ptr-pulling');
    cnt.style.transform = `translateY(${Math.min(distY, 80)}px)`;
  }
}, {passive:false});

window.addEventListener('touchend', async () => {
  if(pulling && distY >= 60){
    document.body.classList.remove('ptr-pulling');
    document.body.classList.add('ptr-loading');
    cnt.style.transform = 'translateY(50px)';
    await initDashboard();
    setTimeout(() => { document.body.classList.remove('ptr-loading'); cnt.style.transform = ''; }, 500);
  } else {
    document.body.classList.remove('ptr-pulling', 'ptr-loading');
    cnt.style.transform = '';
  }
  pulling = false; distY = 0;
}, {passive:true});

async function initDashboard() {
  updateTodayDate();
  await loadPendingPayments();
  await loadStockAlerts();
  await loadBestSellingChart();
}

function updateTodayDate() {
  const now = new Date();
  const hr  = now.getHours();
  const greet = hr < 12 ? 'Good Morning' : hr < 17 ? 'Good Afternoon' : 'Good Evening';
  document.getElementById('greetText').innerHTML = `${greet},<br>Owner 👋`;
  document.getElementById('todayDate').textContent = now.toLocaleDateString('en-US', {weekday:'long', year:'numeric', month:'long', day:'numeric'});
}

async function loadPendingPayments() {
  const list = document.getElementById('pendingPaymentsList');
  const notifArea = document.getElementById('notif-area');
  const pets = await DB.getPets();
  
  const pending = pets.filter(p => p.payment_status === 'Pending');
  
  if (pending.length === 0) {
    list.innerHTML = '';
    notifArea.innerHTML = '';
    document.getElementById('noPending').style.display = 'block';
    return;
  }

  document.getElementById('noPending').style.display = 'none';
  notifArea.innerHTML = '';

  const now = new Date();
  const todayStr = now.toISOString().split('T')[0];

  list.innerHTML = `
    <div class="table-container">
      <table class="pet-table">
        <thead>
          <tr>
            <th>Supplier / Pet</th>
            <th>Amount</th>
            <th>Due</th>
            <th style="text-align:right;">Action</th>
          </tr>
        </thead>
        <tbody>
          ${pending.map(p => {
            const isOverdue = p.due_date && p.due_date < todayStr;
            const dueDateText = p.due_date ? new Date(p.due_date).toLocaleDateString('en-IN', {day:'numeric', month:'short'}) : '—';
            
            // Notification logic (reminder alert)
            // Show alert for each pending payment
            const alertDiv = document.createElement('div');
            alertDiv.className = 'payment-alert';
            alertDiv.innerHTML = `
              <span style="font-size:1.5rem;">🔔</span>
              <div style="flex:1;">
                <div style="font-size:.85rem; font-weight:800; color:#856404;">Pending payment: ${p.name} from ${p.supplier_name || 'Individual'}</div>
                <div style="font-size:.7rem; font-weight:600; color:#856404; opacity:0.8;">Due by ${dueDateText}</div>
              </div>
            `;
            notifArea.appendChild(alertDiv);

            return `
              <tr id="pay-${p.id}">
                <td>
                  <div style="font-weight:800; font-size:.85rem;">${p.supplier_name || '—'}</div>
                  <div style="font-size:.65rem; color:var(--clr-muted); font-weight:700;">${p.icon} ${p.name}</div>
                </td>
                <td style="font-weight:800; color:var(--clr-primary);">Rs. ${parseFloat(p.cost).toLocaleString()}</td>
                <td class="${isOverdue ? 'overdue' : ''}" style="font-size:.75rem; font-weight:700;">${isOverdue ? '⚠️ ' : ''}${dueDateText}</td>
                <td style="text-align:right;">
                  <button class="btn btn-primary" style="font-size:.6rem; padding:6px 10px;" onclick="handleMarkPaid(${p.id}, '${p.name}')">Pay</button>
                </td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    </div>`;
}

async function handleMarkPaid(id, name) {
  if (!confirm(`Mark payment for "${name}" as paid?`)) return;
  const res = await DB.markAsPaid(id);
  if (res && res.success) {
    showToast('Payment marked as paid!');
    initDashboard(); // Refresh
  } else {
    showToast('Error: ' + (res?.error || 'Unknown'));
  }
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
    <div class="table-container" style="margin-top:10px;">
      <table class="pet-table" style="border-collapse:collapse;">
        <thead><tr>
          <th style="padding-left:15px;">Pet</th>
          <th style="text-align:center;">Stock</th>
          <th style="text-align:right; padding-right:15px;">Action</th>
        </tr></thead>
        <tbody>
          ${alertPets.map(p => `
            <tr id="alert-${p.id}">
              <td style="padding-left:15px;">
                <div style="font-weight:700; line-height:1.2;">${p.name}</div>
                <div style="font-size:.62rem; color:var(--clr-muted); font-weight:700;">${p.pet_variety||''}</div>
              </td>
              <td style="text-align:center; color:var(--clr-danger); font-weight:800;">${p.qty}</td>
              <td style="text-align:right; padding-right:15px;">
                <button class="btn btn-sm" style="font-size:.65rem; padding:4px 8px; border-radius:10px;" onclick="stopAlert('${p.id}')">Stop</button>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>`;
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

const CAT_COLORS = {
  dog:'#5c9e6e', cat:'#f0a047', bird:'#4a90e2', fish:'#9b59b6',
  rabbit:'#e67e22', reptile:'#8e44ad', rodent:'#16a085', other:'#95a5a6'
};

async function loadBestSellingChart() {
  const chart    = document.getElementById('barChart');
  const labels   = document.getElementById('barLabels');
  const allPets  = await DB.getPets();
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

  const maxSold = Math.max(...(salesData.length > 0 ? salesData.map(s => s.qty) : [0]), 5);

  chart.innerHTML = allPets.map(p => {
    const sold = salesMap[p.name] || 0;
    const h = (sold / maxSold) * 100;
    const barColor   = CAT_COLORS[(p.category||'other').toLowerCase()] || CAT_COLORS.other;
    const barOpacity = sold > 0 ? 1 : 0.25;
    return `
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;min-width:70px;height:100%;position:relative;">
        <div style="margin-top:auto;width:32px;height:${Math.max(h,6)}%;background:${barColor};opacity:${barOpacity};border-radius:8px 8px 0 0;transition:height .6s cubic-bezier(0.175,.885,.32,1.275);position:relative;">
          <span style="position:absolute;top:-20px;left:50%;transform:translateX(-50%);font-size:.65rem;font-weight:800;color:var(--clr-text);">${sold}</span>
        </div>
      </div>`;
  }).join('');

  labels.innerHTML = allPets.map(p => `
    <div style="flex:1;font-size:.58rem;text-align:center;font-weight:700;color:var(--clr-muted);line-height:1.2;padding-top:10px;min-width:70px;display:flex;flex-direction:column;align-items:center;gap:2px;">
      <span style="font-size:1.1rem;display:block;margin-bottom:2px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.1));">${p.icon||'🐾'}</span>
      <span style="max-width:65px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${p.name}</span>
    </div>`).join('');
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
