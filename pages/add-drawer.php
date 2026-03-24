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

  <div class="table-container" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <table class="pet-table" id="drawerTable" style="table-layout:fixed; width:100%; min-width:320px; border-collapse: collapse;">
      <colgroup>
        <col style="width:34%;" />   <!-- Pet Name -->
        <col style="width:14%;" />   <!-- Qty -->
        <col style="width:23%;" />   <!-- Price -->
        <col style="width:19%;" />   <!-- Total -->
        <col style="width:10%;" />   <!-- Action -->
      </colgroup>
      <thead>
        <tr>
          <th>Pet Name</th>
          <th style="text-align:center;">Qty</th>
          <th>Price</th>
          <th>Total</th>
          <th style="text-align:center;">Del</th>
        </tr>
      </thead>
      <tbody id="drawerBody">
        <!-- Rows loaded via JS -->
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right; font-weight:800; font-size:.82rem;">GRAND TOTAL</td>
          <td id="grandTotal" colspan="2" style="font-weight:800; color:var(--clr-primary); font-size:.82rem;">Rs. 0.00</td>
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

document.addEventListener('DOMContentLoaded', async () => {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('drawerDate').value = today;
    await loadEntries();
});

async function loadEntries() {
    const date = document.getElementById('drawerDate').value;
    const res = await DB.getDrawerEntries(date);
    entries = res || [];
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
        <td style="padding: 8px 4px 8px 10px; vertical-align:middle;">
          <input type="text" value="${e.name}" class="form-control"
            style="font-size:.78rem; padding:8px 10px; width:100%; height:38px; background:#fff; border-radius:10px;"
            placeholder="Pet name"
            oninput="updateEntry(${idx}, 'name', this.value)" />
        </td>
        <td style="padding: 8px 4px; vertical-align:middle;">
          <input type="number" value="${e.qty}" class="form-control"
            style="font-size:.78rem; padding:8px 2px; width:100%; height:38px; text-align:center; background:#fff; border-radius:10px;"
            min="1"
            oninput="updateEntry(${idx}, 'qty', parseInt(this.value)||0)" />
        </td>
        <td style="padding: 8px 4px; vertical-align:middle;">
          <input type="number" value="${e.price}" class="form-control"
            style="font-size:.78rem; padding:8px 10px; width:100%; height:38px; background:#fff; border-radius:10px;"
            min="0"
            oninput="updateEntry(${idx}, 'price', parseFloat(this.value)||0)" />
        </td>
        <td id="rowTotal-${idx}" style="padding: 8px 4px; vertical-align:middle; font-size:.82rem; font-weight:800; color: #2d3436; white-space:nowrap;">
          Rs. ${(e.qty * e.price).toLocaleString('en-IN')}
        </td>
        <td style="padding: 8px 10px 8px 4px; text-align:center; vertical-align:middle;">
          <button class="btn btn-danger btn-sm" onclick="removeRow(${idx})" style="padding:8px 10px; border-radius:8px;">🗑</button>
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

async function saveData() {
    const date = document.getElementById('drawerDate').value;
    const res = await DB.saveDrawerEntries(date, entries);
    if (res.error) {
        showToast('Error saving data');
        return;
    }
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
