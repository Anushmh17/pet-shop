<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Add Drawer — Pet Shop Management" />
  <title>Add Drawer — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
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
        onchange="loadEntries()"
      />
    </div>

    <div class="stat-card accent">
        <div class="stat-label">💰 Today Drawer Cash</div>
        <div class="stat-value" id="totalCashDisplay">Rs. 0.00</div>
    </div>

  </div>

  <!-- ===== TABLE ===== -->
  <div class="flex-between" style="margin-bottom: var(--sp-sm);">
    <h2 class="section-title" style="margin-bottom:0;">Drawer Entries</h2>
    <button class="btn btn-primary btn-sm" onclick="addRow()">＋ Add Row</button>
  </div>

  <div class="table-container">
    <table class="pet-table" id="drawerTable">
      <thead>
        <tr>
          <th>Pet Name</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Total</th>
          <th style="text-align:right;">Action</th>
        </tr>
      </thead>
      <tbody id="drawerBody">
        <!-- Rows loaded via JS -->
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right; font-weight:800;">GRAND TOTAL</td>
          <td id="grandTotal" style="font-weight:800; color:var(--clr-primary);">Rs. 0.00</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div id="emptyDrawer" class="empty-state" style="display:none;">
    <div class="empty-icon">📂</div>
    <p>No entries for this date.</p>
  </div>

  <!-- Save button -->
  <button class="btn btn-primary btn-full mt-md" id="saveBtn" onclick="saveData()">
    💾 Save Drawer Entries
  </button>

</div><!-- /app-wrapper -->

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
let entries = [];

document.addEventListener('DOMContentLoaded', () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('drawerDate').value = today;
    loadEntries();
});

function loadEntries() {
    const date = document.getElementById('drawerDate').value;
    entries = DB.getDrawerEntries(date);
    renderTable();
}

function renderTable() {
    const body = document.getElementById('drawerBody');
    const empty= document.getElementById('emptyDrawer');

    if (entries.length === 0) {
        body.innerHTML = '';
        empty.style.display = '';
        updateTotals();
        return;
    }

    empty.style.display = 'none';
    body.innerHTML = entries.map((e, idx) => `
      <tr id="row-${idx}">
        <td>
          <input type="text" value="${e.name}" class="form-control" style="font-size:.8rem; padding:6px 10px;" oninput="updateEntry(${idx}, 'name', this.value)" />
        </td>
        <td>
          <input type="number" value="${e.qty}" class="form-control" style="font-size:.8rem; padding:6px 5px; width:50px; text-align:center;" min="1" oninput="updateEntry(${idx}, 'qty', parseInt(this.value)||0)" />
        </td>
        <td>
          <input type="number" value="${e.price}" class="form-control" style="font-size:.8rem; padding:6px 10px; width:80px;" min="0" oninput="updateEntry(${idx}, 'price', parseFloat(this.value)||0)" />
        </td>
        <td id="rowTotal-${idx}" style="font-size:.8rem; font-weight:700;">
          Rs. ${(e.qty * e.price).toLocaleString('en-IN', {minimumFractionDigits: 2})}
        </td>
        <td style="text-align:right;">
          <button class="btn btn-danger btn-icon btn-sm" onclick="removeRow(${idx})">🗑</button>
        </td>
      </tr>
    `).join('');

    updateTotals();
}

function updateEntry(idx, key, val) {
    entries[idx][key] = val;
    const rowTotal = (entries[idx].qty * entries[idx].price).toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById(`rowTotal-${idx}`).textContent = 'Rs. ' + rowTotal;
    updateTotals();
}

function updateTotals() {
    const total = entries.reduce((sum, e) => sum + (e.qty * e.price), 0);
    const fmt = 'Rs. ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('grandTotal').textContent = fmt;
    document.getElementById('totalCashDisplay').textContent = fmt;
}

function addRow() {
    entries.push({ name: '', qty: 1, price: 0 });
    renderTable();
}

function removeRow(idx) {
    entries.splice(idx, 1);
    renderTable();
}

function saveData() {
    const date = document.getElementById('drawerDate').value;
    DB.saveDrawerEntries(date, entries);
    showToast('Drawer data saved successfully ✓');
    
    // Pulse effect
    const btn = document.getElementById('saveBtn');
    btn.textContent = '✓ Saved Successfully';
    setTimeout(() => { btn.textContent = '💾 Save Drawer Entries'; }, 2000);
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
