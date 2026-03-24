<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Add Drawer — Pet Shop Management" />
  <title>Add Drawer — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
</head>
<body>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Add Drawer</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper">

  <!-- ===== HEADER: Date + Today Cash ===== -->
  <div class="drawer-header" style="margin-top: var(--sp-md);">

    <div class="stat-card">
      <div class="stat-label">📅 Date</div>
      <input
        type="date"
        id="drawerDate"
        class="form-control"
        style="margin-top:6px; font-size:.9rem;"
        aria-label="Select Date"
      />
    </div>

    <div class="stat-card accent">
      <div class="stat-label">💰 Today Drawer Cash</div>
      <div class="stat-value" id="totalCash">Rs. 0.00</div>
    </div>

  </div>

  <!-- ===== TABLE ===== -->
  <div class="flex-between" style="margin-bottom: var(--sp-sm);">
    <h2 class="section-title" style="margin-bottom:0;">Drawer Entries</h2>
    <button class="btn btn-primary btn-sm" id="addRowBtn" onclick="addRow()">
      ＋ Add Row
    </button>
  </div>

  <div class="table-container">
    <table class="pet-table" id="drawerTable" aria-label="Drawer entries table">
      <thead>
        <tr>
          <th>Pet Name</th>
          <th>Qty</th>
          <th style="min-width:70px;">Price</th>
          <th>Total</th>
          <th style="text-align:right;">Action</th>
        </tr>
      </thead>
      <tbody id="drawerBody">
        <!-- rows added by JS -->
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right; letter-spacing:.4px;">TOTAL</td>
          <td id="footTotal">Rs. 0.00</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div id="emptyTable" class="empty-state" style="display:none;">
    <div class="empty-icon">📋</div>
    <p>No entries yet. Tap "Add Row" to begin.</p>
  </div>

  <!-- Save button -->
  <button class="btn btn-primary btn-full mt-md" onclick="saveDrawer()" id="saveBtn">
    💾 Save Drawer
  </button>

</div><!-- /app-wrapper -->

<!-- ===== DELETE CONFIRM MODAL ===== -->
<div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true" aria-label="Confirm delete">
  <div class="modal-box">
    <div class="modal-title">Delete Row?</div>
    <p style="color:var(--clr-muted); font-size:.9rem;">This row will be removed permanently.</p>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-danger" id="confirmDelete">Delete</button>
    </div>
  </div>
</div>

<!-- ===== TOAST ===== -->
<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
/* ---- Set today's date ---- */
(function () {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('drawerDate').value = today;
})();

/* ---- Row state ---- */
let rows = [];
let rowCount = 0;
let pendingDeleteId = null;

/* ---- Pre-loaded sample rows ---- */
const sampleRows = [
  { id: ++rowCount, name: 'Golden Retriever', qty: 2, price: 15000 },
  { id: ++rowCount, name: 'Persian Cat',      qty: 1, price: 8500  },
];
sampleRows.forEach(r => rows.push(r));

/* ---- Render ---- */
function render() {
  const tbody = document.getElementById('drawerBody');
  const emptyEl = document.getElementById('emptyTable');

  if (rows.length === 0) {
    tbody.innerHTML = '';
    const container = document.querySelector('.table-container');
    if (container) container.style.display = 'none';
    emptyEl.style.display = '';
    updateTotal();
    return;
  }

  emptyEl.style.display = 'none';
  const container = document.querySelector('.table-container');
  if (container) container.style.display = '';

  tbody.innerHTML = rows.map(r => `
    <tr id="row-${r.id}" data-id="${r.id}">
      <td>
        <input type="text" value="${escHtml(r.name)}" placeholder="Pet Name"
          onchange="updateField(${r.id},'name',this.value)"
          aria-label="Pet name" />
      </td>
      <td>
        <input type="number" value="${r.qty}" min="1" placeholder="0"
          style="width:56px; text-align:center;"
          onchange="updateField(${r.id},'qty',+this.value)"
          aria-label="Quantity" />
      </td>
      <td>
        <input type="number" value="${r.price}" min="0" placeholder="0"
          style="width:80px;"
          onchange="updateField(${r.id},'price',+this.value)"
          aria-label="Price" />
      </td>
      <td style="font-weight:800; color:var(--clr-primary);">
        Rs. ${(r.qty * r.price).toLocaleString('en-IN', {minimumFractionDigits:2})}
      </td>
      <td class="actions">
        <button class="btn btn-danger btn-sm btn-icon" onclick="requestDelete(${r.id})"
          aria-label="Delete row ${r.id}" title="Delete">🗑</button>
      </td>
    </tr>
  `).join('');

  updateTotal();
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function updateField(id, field, val) {
  const row = rows.find(r => r.id === id);
  if (!row) return;
  row[field] = val;
  // Re-render only the total cell of this row
  const tr = document.getElementById('row-' + id);
  if (tr) {
    tr.querySelectorAll('td')[3].textContent =
      'Rs. ' + (row.qty * row.price).toLocaleString('en-IN', {minimumFractionDigits:2});
  }
  updateTotal();
}

function addRow() {
  rows.push({ id: ++rowCount, name: '', qty: 1, price: 0 });
  render();
  // Focus first input of new row
  const newRow = document.getElementById('row-' + rowCount);
  if (newRow) newRow.querySelector('input').focus();
}

function requestDelete(id) {
  pendingDeleteId = id;
  document.getElementById('deleteModal').classList.add('open');
}

function closeModal() {
  document.getElementById('deleteModal').classList.remove('open');
  pendingDeleteId = null;
}

document.getElementById('confirmDelete').addEventListener('click', () => {
  if (pendingDeleteId !== null) {
    rows = rows.filter(r => r.id !== pendingDeleteId);
    pendingDeleteId = null;
    closeModal();
    render();
    showToast('Row deleted');
  }
});

/* Click outside modal to close */
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

function updateTotal() {
  const total = rows.reduce((s, r) => s + (r.qty * r.price), 0);
  const fmt = 'Rs. ' + total.toLocaleString('en-IN', {minimumFractionDigits:2});
  document.getElementById('footTotal').textContent = fmt;
  document.getElementById('totalCash').textContent = fmt;
}

function saveDrawer() {
  const date = document.getElementById('drawerDate').value;
  if (rows.length === 0) { showToast('Add at least one row first'); return; }
  const btn = document.getElementById('saveBtn');
  btn.disabled = true;
  btn.textContent = '✓ Saved!';
  showToast(`Drawer saved for ${date} ✓`);
  setTimeout(() => { btn.disabled = false; btn.textContent = '💾 Save Drawer'; }, 2000);
}

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
