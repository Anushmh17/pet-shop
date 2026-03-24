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
<body>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Sales Overview</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper" style="padding-top: var(--sp-md);">

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
    const sales = await getFilteredSales();
    renderHistory(sales);
    updateStats(sales);
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
</script>
</body>
</html>
