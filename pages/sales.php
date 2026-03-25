<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Sales Overview — Pet Shop Management" />
  <title>Sales — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav" style="position:sticky; top:0; z-index:1000; background:#fff;">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Sales Overview</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div id="content-wrapper">
<div class="app-wrapper" style="padding-top: var(--sp-sm);">

  <!-- Date Range Filter -->
  <div style="margin-bottom: var(--sp-md);">
    <h2 class="section-title" style="margin-bottom: var(--sp-sm);">Sales Range</h2>
    
    <!-- Quick Select Pills -->
    <div style="display:flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 4px; -webkit-overflow-scrolling: touch;">
        <button class="btn btn-sm" style="background:#fff; color:var(--clr-muted); border:1.5px solid var(--clr-border); padding:6px 14px; border-radius:100px; font-weight:700; white-space:nowrap;" onclick="setQuickRange('thisWeek')">This Week</button>
        <button class="btn btn-sm" style="background:#fff; color:var(--clr-muted); border:1.5px solid var(--clr-border); padding:6px 14px; border-radius:100px; font-weight:700; white-space:nowrap;" onclick="setQuickRange('thisMonth')">This Month</button>
        <button class="btn btn-sm" style="background:#fff; color:var(--clr-muted); border:1.5px solid var(--clr-border); padding:6px 14px; border-radius:100px; font-weight:700; white-space:nowrap;" onclick="setQuickRange('lastMonth')">Last Month</button>
    </div>

    <div style="display:flex; gap: var(--sp-sm); align-items:center; flex-wrap:wrap; background: var(--clr-bg); padding: 15px; border-radius: 18px; border: 1px solid var(--clr-border);">
      <div style="flex:1; min-width:130px;">
        <label style="font-size:.65rem; font-weight:800; color:var(--clr-muted); display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px;">Custom From</label>
        <input type="date" id="dateFrom" class="form-control"
          style="font-size:.85rem; padding:9px 10px; height:auto; background:#fff; border-radius:12px;"
          onchange="applyFilter()" />
      </div>
      <div style="flex:1; min-width:130px;">
        <label style="font-size:.65rem; font-weight:800; color:var(--clr-muted); display:block; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px;">Custom To</label>
        <input type="date" id="dateTo" class="form-control"
          style="font-size:.85rem; padding:9px 10px; height:auto; background:#fff; border-radius:12px;"
          onchange="applyFilter()" />
      </div>
    </div>
  </div>



  <!-- Stats cards -->
  <div class="drawer-header" style="margin-bottom: var(--sp-lg);">
    <div class="stat-card">
      <div class="stat-label">🐾 Pets Sold</div>
      <div class="stat-value" id="totalSoldMain">0</div>
    </div>
    <div class="stat-card accent">
      <div class="stat-label">💰 Revenue</div>
      <div class="stat-value" id="revenueMain">Rs. 0</div>
    </div>
  </div>

  <!-- Revenue Trend Chart -->
  <section class="overview-section" style="margin-bottom: var(--sp-lg);">
    <h2 class="section-title">Revenue Trend</h2>
    <div class="chart-wrapper" style="padding: 15px; background: #fff; border: 1.5px solid var(--clr-border); border-radius: 20px;">
        <div id="revenueChartWrap" style="overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none;">
            <div id="revenueChart" style="height:140px; display:flex; align-items:flex-end; gap:8px; min-width:100%; width:max-content; padding-top:20px;"></div>
            <div id="chartLabels" style="display:flex; gap:8px; margin-top:8px; border-top:1px solid var(--clr-border); padding-top:8px;"></div>
        </div>
        <div id="chartEmpty" style="display:none; text-align:center; padding:30px 0; color:var(--clr-muted); font-size:.72rem; font-weight:600;">No revenue data for this period.</div>
    </div>
  </section>

  <div style="height: 1px;"></div>
  <!-- Filter bar -->
  <div class="flex gap-sm" style="margin-bottom: var(--sp-md); margin-top: var(--sp-lg); align-items:center;">
    <h2 class="section-title" style="margin-bottom:0; flex:1;">Recent Sales</h2>
  </div>

  <!-- Sales history list -->
  <div id="salesList">
      <!-- Records loaded here -->
  </div>

  <div id="noSales" class="empty-state" style="display:none;">
    <div class="empty-icon">📊</div>
    <p>No sales found for the selected period.</p>
  </div>

</div><!-- /app-wrapper -->

<!-- ===== SALE DETAIL MODAL ===== -->
<div id="saleModal" style="
  display:none; position:fixed; inset:0; z-index:300;
  background:rgba(0,0,0,.45); align-items:flex-end; justify-content:center;
" onclick="closeSaleModal(event)">
  <div id="saleModalBox" style="
    background:#fff; border-radius:24px 24px 0 0;
    width:100%; max-width:520px;
    padding:24px 20px 36px;
    animation: modalIn .28s ease both;
    position:relative;
  ">
    <!-- close button -->
    <button onclick="closeSaleModal()" style="
      position:absolute; top:16px; right:16px;
      background:var(--clr-bg); border:none; border-radius:50%;
      width:34px; height:34px; font-size:1.1rem; cursor:pointer;
      display:flex; align-items:center; justify-content:center;
    ">✕</button>

    <!-- Pet image carousel -->
    <div id="modalImages" style="
      display:flex; gap:8px; overflow-x:auto;
      padding-bottom:8px; margin-bottom:16px;
      scrollbar-width:none;
    "></div>

    <!-- title row -->
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
      <div id="modalIcon" style="
        font-size:2rem; width:54px; height:54px;
        background:var(--clr-primary-lt); border-radius:14px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
      "></div>
      <div>
        <div id="modalPetName" style="font-size:1.05rem; font-weight:800; color:var(--clr-text);"></div>
        <div id="modalDate" style="font-size:.78rem; color:var(--clr-muted); font-weight:600; margin-top:2px;"></div>
      </div>
    </div>

    <!-- detail grid -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px;">
      <div class="stat-card" style="padding:12px;">
        <div class="stat-label">Qty Sold</div>
        <div class="stat-value" id="modalQty" style="font-size:1.2rem;"></div>
      </div>
      <div class="stat-card" style="padding:12px;">
        <div class="stat-label">Unit Price</div>
        <div class="stat-value" id="modalUnitPrice" style="font-size:1.2rem;"></div>
      </div>
      <div class="stat-card accent" style="padding:12px; grid-column:1/-1; text-align:center;">
        <div class="stat-label">Total Amount</div>
        <div class="stat-value" id="modalTotal" style="font-size:1.5rem;"></div>
      </div>
    </div>

    <!-- extra info -->
    <div id="modalExtra" style="font-size:.82rem; color:var(--clr-muted); font-weight:600;"></div>
  </div>
</div>

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    document.getElementById('dateFrom').value = `${y}-${m}-01`;
    document.getElementById('dateTo').value   = `${y}-${m}-${d}`;
    highlightActiveBtn('thisMonth');
    await applyFilter();
});

function setQuickRange(mode) {
    const now = new Date();
    const toYMD = (d) => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    let from, to;

    if (mode === 'thisWeek') {
        const start = new Date(now);
        start.setDate(now.getDate() - now.getDay()); // Sunday
        from = toYMD(start);
        to = toYMD(now);
    } else if (mode === 'thisMonth') {
        from = toYMD(new Date(now.getFullYear(), now.getMonth(), 1));
        to = toYMD(now);
    } else if (mode === 'lastMonth') {
        const first = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const last = new Date(now.getFullYear(), now.getMonth(), 0);
        from = toYMD(first);
        to = toYMD(last);
    }

    document.getElementById('dateFrom').value = from;
    document.getElementById('dateTo').value   = to;
    
    highlightActiveBtn(mode);
    applyFilter();
}

function highlightActiveBtn(mode) {
    const btns = document.querySelectorAll('[onclick^="setQuickRange"]');
    btns.forEach(b => {
        if (b.getAttribute('onclick').includes(mode)) {
            b.style.background = 'var(--clr-primary)';
            b.style.color = '#fff';
            b.style.borderColor = 'var(--clr-primary)';
        } else {
            b.style.background = '#fff';
            b.style.color = 'var(--clr-muted)';
            b.style.borderColor = 'var(--clr-border)';
        }
    });
}

async function getFilteredSales() {
    const fromVal = document.getElementById('dateFrom').value;
    const toVal   = document.getElementById('dateTo').value;
    const allSales = await DB.getSales();

    return allSales.filter(s => {
        if (fromVal && s.date < fromVal) return false;
        if (toVal   && s.date > toVal)   return false;
        return true;
    });
}

async function applyFilter() {
    const fromVal = document.getElementById('dateFrom').value;
    const toVal   = document.getElementById('dateTo').value;
    const sales = await getFilteredSales();
    renderHistory(sales);
    updateStats(sales);
    renderRevenueChart(sales, fromVal, toVal);
}

function renderHistory(sales) {
    const list = document.getElementById('salesList');

    if (sales.length === 0) {
        list.innerHTML = '';
        document.getElementById('noSales').style.display = '';
        return;
    }

    document.getElementById('noSales').style.display = 'none';
    list.innerHTML = sales.map((s, idx) => `
      <div class="sale-item" onclick="openSaleModal(${idx})" style="cursor:pointer; transition:transform .15s;" 
           onmousedown="this.style.transform='scale(.97)'" onmouseup="this.style.transform=''">
        <div class="item-icon">${s.petIcon}</div>
        <div class="item-info">
          <div class="item-name">${s.petName}</div>
          <div class="item-meta">${s.qty} unit${s.qty > 1 ? 's' : ''} &middot; ${formatDate(s.date)}</div>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
          <div class="item-amt">Rs. ${s.total.toLocaleString('en-IN')}</div>
          <span style="color:var(--clr-muted); font-size:.8rem;">›</span>
        </div>
      </div>
    `).join('');
    // store filtered sales for modal lookup
    window._currentSales = sales;
}

async function openSaleModal(idx) {
    const s = window._currentSales[idx];
    if (!s) return;

    const pets = await DB.getPets();
    const pet = pets.find(p => p.id === s.petId);

    // Images
    const imgBox = document.getElementById('modalImages');
    const images = pet && pet.images && pet.images.length > 0 ? pet.images : [];
    if (images.length > 0) {
        imgBox.style.display = 'flex';
        imgBox.innerHTML = images.map(src => `
          <img src="${src}" style="
            width:90px; height:90px; object-fit:cover;
            border-radius:14px; flex-shrink:0;
            border:2px solid var(--clr-border);
          " />
        `).join('');
    } else {
        imgBox.style.display = 'none';
        imgBox.innerHTML = '';
    }

    document.getElementById('modalIcon').textContent        = s.petIcon || '🐾';
    document.getElementById('modalPetName').textContent     = s.petName;
    document.getElementById('modalDate').textContent        = '📅 ' + new Date(s.date).toLocaleDateString('en-US', {weekday:'short', day:'numeric', month:'long', year:'numeric'});
    document.getElementById('modalQty').textContent         = s.qty + (s.qty > 1 ? ' units' : ' unit');
    document.getElementById('modalUnitPrice').textContent   = 'Rs. ' + (s.price || (s.total / s.qty)).toLocaleString('en-IN');
    document.getElementById('modalTotal').textContent       = 'Rs. ' + s.total.toLocaleString('en-IN');
    
    const extra = [];
    if (pet) {
        if (pet.category) extra.push('🏷 Category: ' + pet.category.charAt(0).toUpperCase() + pet.category.slice(1));
        if (pet.source)   extra.push('📦 Source: ' + pet.source);
    }
    document.getElementById('modalExtra').innerHTML = extra.join('&nbsp;&nbsp;|&nbsp;&nbsp;');

    const modal = document.getElementById('saleModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.style.opacity = '1', 10);
}

function closeSaleModal(e) {
    if (e && e.target !== document.getElementById('saleModal')) return;
    document.getElementById('saleModal').style.display = 'none';
}

function updateStats(filteredSales) {
    const totalQty = filteredSales.reduce((sum, s) => sum + s.qty, 0);
    const totalRev = filteredSales.reduce((sum, s) => sum + (s.total || 0), 0);
    
    document.getElementById('totalSoldMain').textContent = totalQty;
    document.getElementById('revenueMain').textContent = 'Rs. ' + totalRev.toLocaleString('en-IN');
}

function renderRevenueChart(sales, from, to) {
    const chart = document.getElementById('revenueChart');
    const labels = document.getElementById('chartLabels');
    const empty = document.getElementById('chartEmpty');
    const wrap = document.getElementById('revenueChartWrap');

    if (sales.length === 0 || !from || !to) {
        chart.innerHTML = ''; labels.innerHTML = '';
        wrap.style.display = 'none'; empty.style.display = 'block';
        return;
    }

    wrap.style.display = 'block'; empty.style.display = 'none';

    const startDate = new Date(from);
    const endDate = new Date(to);
    const diffDays = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;

    let bucketSize = 1;
    if (diffDays <= 7) {
        bucketSize = 1; // Daily
    } else if (diffDays <= 31) {
        bucketSize = Math.ceil(diffDays / 6); // ~5-day buckets to fit 6 bars
    } else {
        bucketSize = 7; // Weekly
    }

    let buckets = [];
    for (let i = 0; i < diffDays; i += bucketSize) {
        const d = new Date(startDate);
        d.setDate(d.getDate() + i);
        const nextD = new Date(d);
        nextD.setDate(nextD.getDate() + bucketSize);
        
        // Label logic
        let label = '';
        if (bucketSize === 1) {
            label = d.toLocaleDateString('en-US', { weekday: 'short' });
        } else {
            label = d.toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
        }

        buckets.push({
            label: label,
            start: d,
            end: nextD,
            value: 0
        });
    }

    // Aggregate sales
    sales.forEach(s => {
        const sDate = new Date(s.date);
        const bucket = buckets.find(b => sDate >= b.start && sDate < b.end);
        if (bucket) bucket.value += (s.total || 0);
    });

    // Handle X-axis overcrowding: for very short ranges, don't force width
    if (buckets.length <= 10) {
        chart.style.width = '100%';
        chart.style.minWidth = 'auto';
    } else {
        chart.style.width = 'max-content';
        chart.style.minWidth = '100%';
    }

    // Dynamic Y-axis scaling (Trading type)
    const maxValue = Math.max(...buckets.map(b => b.value), 100);

    chart.innerHTML = buckets.map(b => {
        const h = maxValue > 0 ? (b.value / maxValue) * 100 : 0;
        const valText = b.value >= 1000 ? (b.value / 1000).toFixed(1) + 'k' : b.value;
        return `
            <div style="flex:1; display:flex; flex-direction:column; align-items:center; min-width:30px; height:100%; position:relative;">
                <div style="margin-top:auto; width:65%; max-width:20px; height:${Math.max(h, 4)}%; background:var(--clr-primary); border-radius:4px 4px 0 0; transition:height .4s ease-out; position:relative;">
                    ${b.value > 0 ? `<span style="position:absolute; top:-18px; left:50%; transform:translateX(-50%); font-size:.58rem; font-weight:800; color:var(--clr-text); white-space:nowrap;">${valText}</span>` : ''}
                </div>
            </div>
        `;
    }).join('');

    labels.innerHTML = buckets.map(b => `
        <div style="flex:1; font-size:.55rem; text-align:center; font-weight:700; color:var(--clr-muted); line-height:1; min-width:30px;">
            ${b.label}
        </div>
    `).join('');
}

function formatDate(ds) {
    const d = new Date(ds);
    return d.toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}

// --- PULL TO REFRESH LOGIC ---
let startY = 0, distY = 0, pulling = false;
window.addEventListener('touchstart', e => { 
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
    
    if (rawDist > 20) {
        distY = Math.pow(rawDist - 20, 0.85);
        if (e.cancelable) e.preventDefault();
        if (distY > 30) document.body.classList.add('ptr-pulling');
        document.getElementById('content-wrapper').style.transform = `translateY(${Math.min(distY, 100)}px)`;
    }
}, {passive:false});

window.addEventListener('touchend', async () => {
    if (pulling && distY >= 85) {
        document.body.classList.remove('ptr-pulling');
        document.body.classList.add('ptr-loading');
        document.getElementById('content-wrapper').style.transform = 'translateY(50px)';
        await applyFilter(); 
        setTimeout(() => {
            document.body.classList.remove('ptr-loading');
            document.getElementById('content-wrapper').style.transform = '';
        }, 600);
    } else {
        document.body.classList.remove('ptr-pulling', 'ptr-loading');
        document.getElementById('content-wrapper').style.transform = '';
    }
    pulling = false; distY = 0;
}, {passive:true});
</script>
</body>
</html>
