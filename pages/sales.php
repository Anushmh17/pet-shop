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

  <!-- Summary pills -->
  <div class="summary-pills" id="summaryStats">
    <!-- Loaded via JS -->
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

  <!-- Add Sale Section (Functional) -->
  <div style="margin-bottom: var(--sp-xl);">
    <h2 class="section-title">New Sale Record</h2>
    <div class="add-pet-form" style="padding: var(--sp-md);">
        <div class="form-group">
            <label class="form-label" for="selectPet">Select Pet *</label>
            <select id="selectPet" class="form-control" onchange="autoFillPrice()">
                <option value="">— Select Pet in Stock —</option>
                <!-- Loaded via JS -->
            </select>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm);">
            <div class="form-group">
                <label class="form-label" for="saleQty">Quantity *</label>
                <input type="number" id="saleQty" class="form-control" min="1" value="1" placeholder="1" />
            </div>
            <div class="form-group">
                <label class="form-label" for="salePrice">Unit Price (Rs.) *</label>
                <input type="number" id="salePrice" class="form-control" min="0" placeholder="0.00" />
            </div>
        </div>
        <button class="btn btn-primary btn-full" onclick="recordSale()">
            🧾 Record Sale
        </button>
    </div>
  </div>

  <!-- Filter bar -->
  <div class="flex gap-sm" style="margin-bottom: var(--sp-md); flex-wrap:wrap; align-items:center;">
    <h2 class="section-title" style="margin-bottom:0; flex:1;">Recent Sales</h2>
    <input type="month" class="form-control" id="salesFilter"
        style="max-width:150px; font-size:.82rem; padding:8px 10px;"
        value="2026-03" onchange="renderHistory()" />
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

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadPetList();
    renderHistory();
    updateStats();
});

function loadPetList() {
    const sel = document.getElementById('selectPet');
    const pets = DB.getPets().filter(p => p.qty > 0);
    sel.innerHTML += pets.map(p => `<option value="${p.id}">${p.name} (Qty: ${p.qty})</option>`).join('');
}

function autoFillPrice() {
    const id = parseInt(document.getElementById('selectPet').value);
    const pets = DB.getPets();
    const pet = pets.find(p => p.id === id);
    if (pet) {
        document.getElementById('salePrice').value = pet.price;
    }
}

function recordSale() {
    const pId   = parseInt(document.getElementById('selectPet').value);
    const qty   = parseInt(document.getElementById('saleQty').value) || 0;
    const price = parseFloat(document.getElementById('salePrice').value) || 0;

    if(!pId) { showToast('Select a pet'); return; }
    if(qty <= 0) { showToast('Enter valid quantity'); return; }

    const pet = DB.getPets().find(p => p.id === pId);
    if(pet.qty < qty) { showToast('Not enough stock! Available: ' + pet.qty); return; }

    const sale = {
        petId: pId,
        petName: pet.name,
        petIcon: pet.icon || '🐾',
        qty: qty,
        price: price,
        total: qty * price
    };

    DB.addSale(sale);
    showToast('Sale recorded successfully ✓');
    
    // Refresh
    renderHistory();
    updateStats();
    
    // Clear
    document.getElementById('selectPet').value = '';
    document.getElementById('saleQty').value = '1';
    document.getElementById('salePrice').value = '';
}

function renderHistory() {
    const filter = document.getElementById('salesFilter').value; // YYYY-MM
    const list = document.getElementById('salesList');
    const sales = DB.getSales().filter(s => s.date.startsWith(filter));

    if (sales.length === 0) {
        list.innerHTML = '';
        document.getElementById('noSales').style.display = '';
        return;
    }

    document.getElementById('noSales').style.display = 'none';
    list.innerHTML = sales.map(s => `
      <div class="sale-item">
        <div class="item-icon">${s.petIcon}</div>
        <div class="item-info">
          <div class="item-name">${s.petName}</div>
          <div class="item-meta">${s.qty} unit${s.qty > 1 ? 's' : ''} &middot; ${formatDate(s.date)}</div>
        </div>
        <div class="item-amt">Rs. ${s.total.toLocaleString('en-IN')}</div>
      </div>
    `).join('');
}

function updateStats() {
    const sales = DB.getSales();
    const totalQty = sales.reduce((sum, s) => sum + s.qty, 0);
    const totalRev = sales.reduce((sum, s) => sum + s.total, 0);
    
    document.getElementById('totalSoldMain').textContent = totalQty;
    document.getElementById('revenueMain').textContent = 'Rs. ' + totalRev.toLocaleString('en-IN');
    
    document.getElementById('summaryStats').innerHTML = `
        <span class="pill">Current Month</span>
        <span class="pill accent">Total: Rs. ${totalRev.toLocaleString('en-IN')}</span>
        <span class="pill info">${sales.length} transactions</span>
    `;

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
