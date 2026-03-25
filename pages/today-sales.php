<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Today's Sales — Pet Shop Management" />
  <title>Today Sales — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav" style="position:sticky; top:0; z-index:1000; background:#fff;">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Today's Sales</span>
  <div class="nav-spacer"></div>
</nav>
<!-- ===== MAIN CONTENT ===== -->
<div id="content-wrapper">
<div class="app-wrapper" style="padding-top: var(--sp-sm);">

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

  <!-- Add Sale Section (Consolidated here) -->
  <div style="margin-bottom: var(--sp-xl);">
    <h2 class="section-title">New Sale Record</h2>
    <div class="add-pet-form" style="padding: var(--sp-md);">
        <div class="form-group">
            <label class="form-label" for="selectPet">Select Pet *</label>
            <select id="selectPet" class="form-control" onchange="autoFillPrice()">
                <option value="">— Select Pet —</option>
                <!-- Loaded via JS -->
            </select>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm);">
            <div class="form-group">
                <label class="form-label" for="saleQty">Qty *</label>
                <input type="number" id="saleQty" class="form-control" min="1" value="1" />
            </div>
            <div class="form-group">
                <label class="form-label" for="salePrice">Price (Rs.) *</label>
                <input type="number" id="salePrice" class="form-control" min="0" placeholder="0.00" />
            </div>
        </div>
        <button class="btn btn-primary btn-full" onclick="recordSale()">
            🧾 Record Sale
        </button>
    </div>
  </div>

  <!-- Today's Table Header -->
  <div class="flex-between" style="margin-bottom: var(--sp-sm);">
    <h2 class="section-title" style="margin-bottom:0;">Today's Entries</h2>
  </div>

  <!-- Today's sales list -->
  <div id="todaySalesList">
      <!-- Records loaded via JS -->
  </div>

  <div id="emptyToday" class="empty-state" style="display:none;">
    <div class="empty-icon">🛒</div>
    <p>No sales recorded yet today.</p>
  </div>

</div><!-- /app-wrapper -->

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    updateTodayDate();
    await loadPetList();
    await renderTodaySales();
});

function updateTodayDate() {
  const d = new Date();
  document.getElementById('heroDate').textContent =
    d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
}

async function loadPetList() {
    const sel = document.getElementById('selectPet');
    const pets = await DB.getPets();
    const available = pets.filter(p => p.qty > 0);
    sel.innerHTML += available.map(p => {
        const title = p.petVariety ? `${p.name} (${p.petVariety})` : p.name;
        return `<option value="${p.id}">${title} — Available: ${p.qty}</option>`;
    }).join('');
}

async function autoFillPrice() {
    const id = parseInt(document.getElementById('selectPet').value);
    const pets = await DB.getPets();
    const pet = pets.find(p => p.id === id);
    if (pet) {
        document.getElementById('salePrice').value = pet.price;
    }
}

async function recordSale() {
    const pId   = parseInt(document.getElementById('selectPet').value);
    const qty   = parseInt(document.getElementById('saleQty').value) || 0;
    const price = parseFloat(document.getElementById('salePrice').value) || 0;

    if(!pId) { showToast('Select a pet'); return; }
    if(qty <= 0) { showToast('Enter valid quantity'); return; }

    const pets = await DB.getPets();
    const pet = pets.find(p => p.id === pId);
    if(pet.qty < qty) { showToast('Not enough stock! Available: ' + pet.qty); return; }

    const sale = {
        petId: pId,
        petName: pet.name,
        qty: qty,
        price: price,
        total: qty * price,
        saleDate: new Date().toISOString().split('T')[0]
    };

    const res = await DB.addSale(sale);
    if (res.error) {
        showToast('Error recording sale');
        return;
    }
    showToast('Sale recorded successfully ✓');
    
    // Refresh
    await renderTodaySales();
    
    // Clear & Re-load List (to update quantities)
    document.getElementById('selectPet').innerHTML = '<option value="">— Select Pet —</option>';
    await loadPetList();
    document.getElementById('saleQty').value = '1';
    document.getElementById('salePrice').value = '';
}

async function renderTodaySales() {
    const list = document.getElementById('todaySalesList');
    const empty= document.getElementById('emptyToday');
    const sales = await DB.getTodaySales();

    if (sales.length === 0) {
        list.innerHTML = '';
        empty.style.display = '';
        updateStats(sales);
        return;
    }

    empty.style.display = 'none';
    list.innerHTML = sales.map(s => {
      const imgHtml = s.primaryImage 
        ? `<img src="${s.primaryImage}" style="width:100%; height:100%; object-fit:cover;" />`
        : `<span style="font-size:1rem; color:var(--clr-muted); opacity:0.8;">📸</span>`;

      return `
        <div class="sale-item" style="padding: 12px 16px; align-items: center; display: flex; gap: 14px; margin-bottom: 10px; border-radius: 12px;">
          <div style="width: 46px; height: 46px; border-radius: 12px; background: var(--clr-bg); border: 2px solid var(--clr-border); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
            ${imgHtml}
          </div>

          <div style="flex:1; min-width:0;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
              <div style="font-weight:800; font-size:1.02rem; color:var(--clr-text); line-height:1.2;">${s.petName}</div>
              <div style="font-size:.62rem; font-weight:800; color:var(--clr-primary); background:var(--clr-primary-lt); padding:2px 8px; border-radius:50px; text-transform:uppercase; letter-spacing:0.4px;">${s.category}</div>
            </div>
            <div style="font-size:.78rem; font-weight:700; color:var(--clr-muted); margin-top:3px;">
              Qty: ${s.qty} &middot; @Rs. ${s.price.toLocaleString('en-IN')}
            </div>
            <div style="font-weight:800; color:var(--clr-text); font-size: 1.05rem; margin-top: 5px;">Rs. ${s.total.toLocaleString('en-IN')}</div>
          </div>
        </div>
      `;
    }).join('');

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

// --- PULL TO REFRESH LOGIC ---
let startY = 0, distY = 0, pulling = false;
window.addEventListener('touchstart', e => { if(window.scrollY === 0){ startY = e.touches[0].pageY; pulling = true; } }, {passive:true});
window.addEventListener('touchmove', e => {
    if(!pulling) return;
    distY = (e.touches[0].pageY - startY) * 0.4;
    if(distY > 0 && window.scrollY === 0){
        document.body.classList.add('ptr-pulling');
        document.getElementById('content-wrapper').style.transform = `translateY(${Math.min(distY, 80)}px)`;
    }
}, {passive:true});
window.addEventListener('touchend', async () => {
    if(pulling && distY >= 60){
        document.body.classList.remove('ptr-pulling');
        document.body.classList.add('ptr-loading');
        document.getElementById('content-wrapper').style.transform = 'translateY(40px)';
        await renderTodaySales(); 
        setTimeout(() => {
            document.body.classList.remove('ptr-loading');
            document.getElementById('content-wrapper').style.transform = '';
        }, 500);
    } else {
        document.body.classList.remove('ptr-pulling', 'ptr-loading');
        document.getElementById('content-wrapper').style.transform = '';
    }
    pulling = false; distY = 0;
}, {passive:true});
</script>
</body>
</html>
