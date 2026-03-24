<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Today's Sales — Pet Shop Management" />
  <title>Today Sales — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
</head>
<body>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Today's Sales</span>
  <div class="nav-spacer"></div>
</nav>

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

  <!-- Add new sale button -->
  <div class="flex-between" style="margin-bottom: var(--sp-sm);">
    <h2 class="section-title" style="margin-bottom:0;">Today's Entries</h2>
    <button class="btn btn-primary btn-sm" onclick="openAddModal()" id="addSaleBtn">＋ Add Sale</button>
  </div>

  <!-- Today's sales list -->
  <div id="todaySalesList"></div>

  <div id="emptyToday" class="empty-state">
    <div class="empty-icon">🛒</div>
    <p>No sales recorded yet today.</p>
    <button class="btn btn-primary mt-md btn-sm" onclick="openAddModal()">Add First Sale</button>
  </div>

</div><!-- /app-wrapper -->

<!-- ===== ADD SALE MODAL ===== -->
<div class="modal-overlay" id="addModal" role="dialog" aria-modal="true" aria-label="Add new sale">
  <div class="modal-box">
    <div class="modal-title">New Sale Entry</div>

    <div class="form-group">
      <label class="form-label" for="petName">Pet Name</label>
      <input type="text" id="petName" class="form-control" placeholder="e.g. Golden Retriever" />
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm);">
      <div class="form-group">
        <label class="form-label" for="petQty">Quantity</label>
        <input type="number" id="petQty" class="form-control" min="1" value="1" placeholder="1" />
      </div>
      <div class="form-group">
        <label class="form-label" for="petPrice">Price (Rs.)</label>
        <input type="number" id="petPrice" class="form-control" min="0" value="" placeholder="0" />
      </div>
    </div>

    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeAddModal()">Cancel</button>
      <button class="btn btn-primary" onclick="addSale()">Add Sale</button>
    </div>
  </div>
</div>

<!-- Delete confirm modal -->
<div class="modal-overlay" id="delModal" role="dialog" aria-modal="true" aria-label="Confirm delete">
  <div class="modal-box">
    <div class="modal-title">Remove this sale?</div>
    <p style="color:var(--clr-muted); font-size:.9rem;">This entry will be removed from today's record.</p>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeDelModal()">Cancel</button>
      <button class="btn btn-danger" id="confirmDel">Remove</button>
    </div>
  </div>
</div>

<!-- ===== TOAST ===== -->
<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
/* ---- Today date ---- */
(function () {
  const d = new Date();
  document.getElementById('heroDate').textContent =
    d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
})();

/* ---- State ---- */
let sales = [
  { id: 1, icon: '🐶', name: 'Golden Retriever', qty: 1, price: 15000 },
  { id: 2, icon: '🐱', name: 'Persian Cat',      qty: 2, price: 8500  },
  { id: 3, icon: '🦜', name: 'Macaw Parrot',     qty: 1, price: 7500  },
];
let nextId = 4;
let pendingDelId = null;

const PET_ICONS = ['🐶','🐱','🐰','🦜','🐹','🐠','🐢','🦮','🐕','🦁'];

function getIcon() {
  return PET_ICONS[Math.floor(Math.random() * PET_ICONS.length)];
}

/* ---- Render ---- */
function render() {
  const list = document.getElementById('todaySalesList');
  const empty= document.getElementById('emptyToday');

  if (sales.length === 0) {
    list.innerHTML = '';
    empty.style.display = '';
    updateStats();
    return;
  }

  empty.style.display = 'none';

  list.innerHTML = sales.map(s => `
    <div class="sale-item" id="saleItem-${s.id}">
      <div class="item-icon">${s.icon}</div>
      <div class="item-info">
        <div class="item-name">${escHtml(s.name)}</div>
        <div class="item-meta">${s.qty} unit${s.qty > 1 ? 's' : ''} &middot; @Rs. ${s.price.toLocaleString('en-IN')}</div>
      </div>
      <div style="display:flex; align-items:center; gap:8px;">
        <div class="item-amt">Rs. ${(s.qty * s.price).toLocaleString('en-IN')}</div>
        <button class="btn btn-danger btn-icon btn-sm" onclick="requestDel(${s.id})" aria-label="Remove ${escHtml(s.name)}">🗑</button>
      </div>
    </div>
  `).join('');

  updateStats();
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function updateStats() {
  const total = sales.reduce((sum, s) => sum + s.qty * s.price, 0);
  const units = sales.reduce((sum, s) => sum + s.qty, 0);
  document.getElementById('heroAmount').textContent  =
    'Rs. ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
  document.getElementById('txCount').textContent  = sales.length;
  document.getElementById('unitCount').textContent = units;
}

/* ---- Add modal ---- */
function openAddModal() {
  document.getElementById('petName').value  = '';
  document.getElementById('petQty').value   = '1';
  document.getElementById('petPrice').value = '';
  document.getElementById('addModal').classList.add('open');
  setTimeout(() => document.getElementById('petName').focus(), 100);
}

function closeAddModal() {
  document.getElementById('addModal').classList.remove('open');
}

function addSale() {
  const name  = document.getElementById('petName').value.trim();
  const qty   = parseInt(document.getElementById('petQty').value) || 1;
  const price = parseFloat(document.getElementById('petPrice').value) || 0;

  if (!name) { showToast('Please enter a pet name'); return; }
  if (price <= 0) { showToast('Please enter a valid price'); return; }

  sales.unshift({ id: nextId++, icon: getIcon(), name, qty, price });
  closeAddModal();
  render();
  showToast('Sale added ✓');
}

/* ---- Delete modal ---- */
function requestDel(id) {
  pendingDelId = id;
  document.getElementById('delModal').classList.add('open');
}

function closeDelModal() {
  document.getElementById('delModal').classList.remove('open');
  pendingDelId = null;
}

document.getElementById('confirmDel').addEventListener('click', () => {
  sales = sales.filter(s => s.id !== pendingDelId);
  closeDelModal();
  render();
  showToast('Entry removed');
});

/* Click outside to close */
['addModal','delModal'].forEach(id => {
  document.getElementById(id).addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.remove('open');
      pendingDelId = null;
    }
  });
});

/* ---- Toast ---- */
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}

/* Initial render */
render();
</script>
</body>
</html>
