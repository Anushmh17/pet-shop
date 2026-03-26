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
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
  <style>
    /* Category action card — full width below grid */
    .card-category .card-icon { background: #fef3e2; color: #e67e22; }
    /* Notification Bell & Badges */
    .nav-actions {
        position: absolute; top: 0; right: 0; 
        display: flex; align-items: center; gap: 10px;
    }
    .notif-bell {
        width: 44px; height: 44px; background: var(--clr-surface); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; border: 1.5px solid var(--clr-border);
        box-shadow: var(--shadow-sm); position: relative;
        cursor: pointer; transition: transform .15s;
    }
    .notif-bell:active { transform: scale(.92); }
    .notif-badge {
        position: absolute; top: -2px; right: -2px;
        background: #ff4757; color: white; font-size: 0.65rem; font-weight: 800;
        min-width: 18px; height: 18px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        padding: 0 4px; border: 2px solid var(--clr-surface);
        display: none; /* Hidden by default */
    }
    .card-badge {
        position: absolute; top: 12px; right: 12px;
        background: #f1c40f; color: #92400e; font-size: 0.6rem;
        font-weight: 800; padding: 2px 8px; border-radius: 50px;
        display: none; /* Hidden by default */
    }
    .profile-link {
        width: 44px; height: 44px; background: var(--clr-surface); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; border: 1.5px solid var(--clr-border);
        box-shadow: var(--shadow-sm); 
    }

    /* Notification Popup Dropdown */
    .notif-popup {
        display: none; position: absolute; top: 55px; right: 0; width: 280px;
        background: var(--clr-surface); border-radius: 20px; border: 1.5px solid var(--clr-border);
        box-shadow: 0 10px 40px rgba(0,0,0,0.12); z-index: 2005; overflow: hidden;
        animation: dropIn .2s ease-out;
    }
    .notif-popup.open { display: block; }
    @keyframes dropIn { from { opacity:0; transform: translateY(-10px); } to { opacity:1; transform: translateY(0); } }
    
    .popup-header {
        padding: 12px 15px; background: var(--clr-bg); border-bottom: 1.5px solid var(--clr-border);
        font-size: .75rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase;
    }
    .popup-list { max-height: 350px; overflow-y: auto; }
    .popup-group-label {
        font-size: .62rem; font-weight: 800; color: var(--clr-muted);
        padding: 5px 15px; background: var(--clr-bg); letter-spacing: .4px;
    }
    .popup-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 15px; border-bottom: 1px solid var(--clr-bg);
        cursor: pointer; transition: background .15s;
    }
    .popup-item:last-child { border-bottom: none; }
    .popup-item:active { background: var(--clr-bg); }
    .popup-icon { font-size: 1.2rem; }
    .popup-info { flex: 1; min-width: 0; }
    .popup-name { font-size: .82rem; font-weight: 800; color: var(--clr-text); }
    .popup-detail { font-size: .68rem; font-weight: 600; color: var(--clr-muted); }
    .popup-meta {
        font-size: .6rem; font-weight: 800; padding: 2px 6px; border-radius: 6px;
        background: var(--clr-bg); color: var(--clr-muted); margin-top: 2px; display: inline-block;
    }
  </style>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== MAIN CONTENT ===== -->
<div id="content-wrapper">
<div class="app-wrapper" style="padding-top: 25px;">

  <!-- Greeting -->
  <div class="greeting" style="position:relative;">
    <div class="nav-actions">
      <div class="notif-bell" id="topBell" onclick="toggleNotifPopup(event)">
        🔔<span class="notif-badge" id="bellCount">0</span>
      </div>
      <a href="profile.php" class="profile-link" aria-label="Admin Profile">👤</a>
      
      <!-- Notification Popup -->
      <div class="notif-popup" id="notifPopup">
        <div class="popup-header">Notifications</div>
        <div class="popup-list" id="popupList"></div>
      </div>
    </div>
    <div class="greeting-label">Welcome back</div>
    <div class="greeting-name" id="greetText">Good Day,<br>Owner 👋</div>
    <div class="greeting-sub" id="todayDate"></div>
  </div>

  <!-- notif-area removed: legacy element, no longer used -->

  <!-- Notification Banner (REMOVED) -->

  <!-- ===== ACTION GRID 2×2 ===== -->
  <div class="action-grid" role="navigation" aria-label="Main Actions">
    <a href="add-pet.php" class="action-card card-add-pet" id="btn-add-pet" aria-label="Add Pet">
      <div class="card-icon">🐶</div>
      <span class="card-label">Add Pet</span>
    </a>
    <a href="manage-pets.php" class="action-card card-inventory" id="btn-manage-pets" aria-label="Manage Inventory" style="border-top-color: #00b894;">
      <div class="card-icon" style="background: #e6f7f4; color: #00b894;">📋</div>
      <span class="card-label">Inventory</span>
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
    <a href="payments.php" class="action-card" id="btn-payments" aria-label="Payments" style="border-top-color: #f1c40f; position:relative;">
      <div class="card-icon" style="background: #fff9e6; color: #f1c40f;">💰</div>
      <span class="card-label">Payments</span>
      <span class="card-badge" id="payCount">0</span>
    </a>
    <a href="ai-counter.php" class="action-card" id="btn-ai-counter" aria-label="AI Animal Counter" style="border-top-color: #6c5ce7;">
      <div class="card-icon" style="background: #efecfd; color: #6c5ce7;">🤖</div>
      <span class="card-label">AI Counter</span>
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

  <!-- Stock Alert Section -->
  <section class="overview-section" style="margin-top:20px; margin-bottom: 20px;">
    <div class="flex-between">
      <h2 class="section-title" style="margin:0;">Stock Alerts</h2>
      <a href="manage-pets.php" style="font-size:.7rem; color:var(--clr-primary); font-weight:800; text-decoration:none;">MANAGE INVENTORY ➔</a>
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
  // Only allow starting a pull if we're at the very top and NOT scrolling
  if (window.scrollY <= 0) {
    startY = e.touches[0].pageY;
    pulling = true;
  } else {
    pulling = false;
  }
}, {passive:true});

window.addEventListener('touchmove', e => {
  if (!pulling || window.scrollY > 0) return;
  
  const y = e.touches[0].pageY;
  const rawDist = y - startY;
  
  // Only trigger visual movement after a deliberate 20px intent
  if (rawDist > 20) {
    // Apply logarithmic resistance: the further you pull, the harder it gets
    distY = Math.pow(rawDist - 20, 0.85); 
    
    if (e.cancelable) e.preventDefault();
    
    // Only show "pulling" state once distance is substantial
    if (distY > 30) document.body.classList.add('ptr-pulling');
    
    // Smooth transform with a hard cap
    cnt.style.transform = `translateY(${Math.min(distY, 100)}px)`;
  }
}, {passive:false});

window.addEventListener('touchend', async () => {
  if (pulling && distY >= 85) { // Substantial threshold for deliberate refresh
    document.body.classList.remove('ptr-pulling');
    document.body.classList.add('ptr-loading');
    cnt.style.transform = 'translateY(50px)';
    await initDashboard();
    setTimeout(() => { 
      document.body.classList.remove('ptr-loading'); 
      cnt.style.transform = ''; 
    }, 600);
  } else {
    document.body.classList.remove('ptr-pulling', 'ptr-loading');
    cnt.style.transform = '';
  }
  pulling = false; distY = 0;
}, {passive:true});

async function initDashboard() {
  updateTodayDate();
  await updateNotificationSignals();
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

async function updateNotificationSignals() {
  const pets = await DB.getPets();
  const now = new Date();
  const todayStr = now.toISOString().split('T')[0];
  
  // 1. Pending Payments
  const pending = pets.filter(p => p.payment_status === 'Pending');
  const payBadge = document.getElementById('payCount');
  if (pending.length > 0) {
    payBadge.textContent = `${pending.length} PENDING`;
    payBadge.style.display = 'block';
  } else {
    payBadge.style.display = 'none';
  }

  // 2. Low Stock Alerts
  const stockAlerts = pets.filter(p => !p.stop_alert && parseInt(p.qty) <= parseInt(p.alert_level));
  
  // 3. Combined Top Badge
  const total = pending.length + stockAlerts.length;
  const bellBadge = document.getElementById('bellCount');
  if (total > 0) {
    bellBadge.textContent = total;
    bellBadge.style.display = 'flex';
  } else {
    bellBadge.style.display = 'none';
  }

  // 4. Update Popup HTML
  const list = document.getElementById('popupList');
  if (total === 0) {
    list.innerHTML = '<div style="padding:40px 15px; text-align:center; font-size:.75rem; color:var(--clr-muted); font-weight:600;">Inventory looks healthy! 🎉</div>';
    return;
  }

  let html = '';
  
  // Group 1: Payments
  if (pending.length > 0) {
    html += `<div class="popup-group-label">PAYMENT REMINDERS</div>`;
    pending.forEach(p => {
        const isOverdue = p.due_date && p.due_date < todayStr;
        const dateText = p.due_date ? new Date(p.due_date).toLocaleDateString('en-IN', {day:'numeric', month:'short'}) : '—';
        html += `
            <div class="popup-item" onclick="window.location.href='payments.php'">
                <div class="popup-icon">${isOverdue ? '⚠️' : '🔔'}</div>
                <div class="popup-info">
                    <div class="popup-name">${p.name}</div>
                    <div class="popup-detail">from ${p.supplier_name || 'Individual'}</div>
                    <div class="popup-meta" style="${isOverdue ? 'color:#b91c1c; background:#fee2e2;' : ''}">
                        ${isOverdue ? 'OVERDUE' : 'Due ' + dateText}
                    </div>
                </div>
            </div>
        `;
    });
  }

  // Group 2: Stock
  if (stockAlerts.length > 0) {
    html += `<div class="popup-group-label">LOW STOCK ALERTS</div>`;
    stockAlerts.forEach(p => {
        html += `
            <div class="popup-item" onclick="window.location.href='manage-pets.php'">
                <div class="popup-icon">📦</div>
                <div class="popup-info">
                    <div class="popup-name">${p.name}</div>
                    <div class="popup-detail">${p.category.toUpperCase()} • Qty: ${p.qty}</div>
                    <div class="popup-meta" style="color:#0369a1; background:#e0f2fe;">Restock Needed</div>
                </div>
            </div>
        `;
    });
  }

  list.innerHTML = html;
}

function toggleNotifPopup(e) {
  if (e) e.stopPropagation();
  const p = document.getElementById('notifPopup');
  p.classList.toggle('open');
  
  if (p.classList.contains('open')) {
    const closer = (ev) => {
        if (!p.contains(ev.target) && !document.getElementById('topBell').contains(ev.target)) {
            p.classList.remove('open');
            window.removeEventListener('click', closer);
        }
    };
    window.addEventListener('click', closer);
  }
}

// Legacy function removed — notif-area div no longer exists in DOM

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
  // Confirmation required — stopping an alert is persistent and has no undo
  if (!confirm('Stop this stock alert? It will not fire again until re-enabled from inventory.')) return;
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
