<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Today's Sales — Pet Shop Management" />
  <title>Today Sales — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
</head>
<body>



<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper" style="padding-top: var(--sp-md);">

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
        petIcon: pet.icon || '🐾',
        qty: qty,
        price: price,
        total: qty * price
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
    list.innerHTML = sales.map(s => `
      <div class="sale-item">
        <div class="item-icon">${s.petIcon}</div>
        <div class="item-info">
          <div class="item-name">${s.petName}</div>
          <div class="item-meta">${s.qty} unit${s.qty > 1 ? 's' : ''} &middot; @Rs. ${s.price.toLocaleString('en-IN')}</div>
        </div>
        <div class="item-amt">Rs. ${s.total.toLocaleString('en-IN')}</div>
      </div>
    `).join('');

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
</script>
</body>
</html>
