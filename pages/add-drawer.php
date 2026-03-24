<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Cash Drawer — Pet Shop Management" />
  <title>Cash Drawer — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
</head>
<body>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Cash Drawer</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper">

  <!-- ===== ROW 1: Date + Opening Balance ===== -->
  <div class="drawer-header" style="margin-top: var(--sp-md); align-items: flex-end;">

    <div class="stat-card" style="flex:1;">
      <div class="stat-label">📅 Date</div>
      <input
        type="date"
        id="drawerDate"
        class="form-control"
        style="margin-top:6px; font-size:.9rem;"
        onchange="loadEntries()"
      />
    </div>

    <div class="stat-card" style="flex:1;">
      <div class="stat-label">🏦 Opening Balance *</div>
      <input
        type="number"
        id="openingBalance"
        class="form-control"
        style="margin-top:6px; font-size:.9rem;"
        placeholder="0.00"
        min="0"
        oninput="updateTotals()"
      />
    </div>

  </div>

  <!-- ===== ROW 2: Summary Display ===== -->
  <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm); margin: var(--sp-md) 0;">

    <div class="stat-card">
      <div class="stat-label">💚 Cash In Total</div>
      <div class="stat-value" id="cashInDisplay" style="font-size:1rem; color:#2d8a4e;">Rs. 0.00</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">❤️ Cash Out Total</div>
      <div class="stat-value" id="cashOutDisplay" style="font-size:1rem; color:#e05c5c;">Rs. 0.00</div>
    </div>

    <div class="stat-card accent" style="grid-column:1/-1; text-align:center;">
      <div class="stat-label">🏁 Closing Balance</div>
      <div class="stat-value" id="closingDisplay" style="font-size:1.5rem;">Rs. 0.00</div>
    </div>

  </div>

  <!-- ===== TABLE ===== -->
  <div class="flex-between" style="margin-bottom: var(--sp-sm);">
    <h2 class="section-title" style="margin-bottom:0;">Transaction Rows</h2>
    <button class="btn btn-primary btn-sm" onclick="addRow()">＋ Add Transaction</button>
  </div>

  <div class="table-container" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <table class="pet-table" id="drawerTable" style="table-layout:fixed; width:100%; min-width:300px; border-collapse: collapse;">
      <colgroup>
        <col style="width:26%;" />   <!-- Type -->
        <col style="width:42%;" />   <!-- Description -->
        <col style="width:22%;" />   <!-- Amount -->
        <col style="width:10%;" />   <!-- Action -->
      </colgroup>
      <thead>
        <tr>
          <th>Type</th>
          <th>Description</th>
          <th>Amount</th>
          <th style="text-align:center;">Del</th>
        </tr>
      </thead>
      <tbody id="drawerBody">
        <!-- Rows loaded via JS -->
      </tbody>
    </table>
  </div>

  <div id="emptyDrawer" class="empty-state" style="display:none;">
    <div class="empty-icon">📂</div>
    <p>No transactions for this date.</p>
  </div>

  <!-- Suggestion Datalist -->
  <datalist id="descSuggestions">
    <option value="Pet Sale">
    <option value="Pet Purchase">
    <option value="Food Expense">
    <option value="Medicine">
    <option value="Shop Maintenance">
    <option value="Rent Payment">
    <option value="Other">
  </datalist>

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
    const data = res || {};

    // Restore opening balance
    document.getElementById('openingBalance').value = data.openingBalance || '';

    // Restore entries
    entries = data.entries || [];

    // If no data saved for today, check yesterday's closing
    if (!data.openingBalance && entries.length === 0) {
        await autoFillOpening(date);
    }

    renderTable();
}

async function autoFillOpening(date) {
    const d = new Date(date);
    d.setDate(d.getDate() - 1);
    const prevDate = d.toISOString().split('T')[0];
    const prev = await DB.getDrawerEntries(prevDate);
    if (prev && prev.closingBalance) {
        document.getElementById('openingBalance').value = prev.closingBalance;
        updateTotals();
    }
}

function renderTable() {
    const body  = document.getElementById('drawerBody');
    const empty = document.getElementById('emptyDrawer');

    if (entries.length === 0) {
        body.innerHTML = '';
        empty.style.display = '';
        updateTotals();
        return;
    }

    empty.style.display = 'none';
    body.innerHTML = entries.map((e, idx) => {
        const typeColor = e.type === 'Cash In' ? '#2d8a4e' : '#e05c5c';
        return `
          <tr id="row-${idx}">
            <td style="padding:8px 4px 8px 6px; vertical-align:middle;">
              <select class="form-control"
                style="font-size:.75rem; padding:7px 5px; height:38px; background:#fff; border-radius:10px; color:${typeColor}; font-weight:700;"
                onchange="updateEntry(${idx}, 'type', this.value)">
                <option value="Cash In"  ${e.type === 'Cash In'  ? 'selected' : ''}>💚 In</option>
                <option value="Cash Out" ${e.type === 'Cash Out' ? 'selected' : ''}>❤️ Out</option>
              </select>
            </td>
            <td style="padding:8px 4px; vertical-align:middle;">
              <input type="text" value="${e.desc || ''}" class="form-control" list="descSuggestions"
                style="font-size:.78rem; padding:8px 10px; width:100%; height:38px; background:#fff; border-radius:10px;"
                placeholder="Transaction detail"
                oninput="updateEntry(${idx}, 'desc', this.value)" />
            </td>
            <td style="padding:8px 4px; vertical-align:middle;">
              <input type="number" value="${e.amount || ''}" class="form-control"
                style="font-size:.78rem; padding:8px 6px; width:100%; height:38px; background:#fff; border-radius:10px;"
                min="0.01" step="0.01" placeholder="0.00"
                oninput="updateEntry(${idx}, 'amount', parseFloat(this.value)||0)" />
            </td>
            <td style="padding:8px 6px; text-align:center; vertical-align:middle;">
              <button class="btn btn-danger btn-sm" onclick="removeRow(${idx})" style="padding:8px 10px; border-radius:8px;">🗑</button>
            </td>
          </tr>
        `;
    }).join('');

    updateTotals();
}

function updateEntry(idx, key, val) {
    entries[idx][key] = val;
    if (key === 'type') renderTable(); // re-render only for color change
    else updateTotals();
}

function updateTotals() {
    const opening  = parseFloat(document.getElementById('openingBalance').value) || 0;
    const cashIn   = entries.filter(e => e.type === 'Cash In').reduce((s, e) => s + (e.amount || 0), 0);
    const cashOut  = entries.filter(e => e.type === 'Cash Out').reduce((s, e) => s + (e.amount || 0), 0);
    const closing  = opening + cashIn - cashOut;

    const fmt = (n) => 'Rs. ' + n.toLocaleString('en-IN', { minimumFractionDigits: 2 });

    document.getElementById('cashInDisplay').textContent  = fmt(cashIn);
    document.getElementById('cashOutDisplay').textContent = fmt(cashOut);
    document.getElementById('closingDisplay').textContent = fmt(closing);
}

function addRow() {
    entries.push({ type: 'Cash In', desc: '', amount: '' });
    renderTable();
    const body = document.getElementById('drawerBody');
    if (body.lastElementChild) body.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function removeRow(idx) {
    entries.splice(idx, 1);
    renderTable();
}

async function saveData() {
    const date    = document.getElementById('drawerDate').value;
    const openingInput = document.getElementById('openingBalance');
    const opening = parseFloat(openingInput.value);

    // --- Validation ---
    if (isNaN(opening)) {
        showToast('Opening balance is required!');
        openingInput.focus();
        return;
    }

    // Filter out completely empty rows, but validate rows with partial data
    const validEntries = entries.filter(e => e.desc.trim() !== '' || (e.amount && e.amount > 0));
    
    for (let e of validEntries) {
        if (!e.desc.trim()) { showToast('Description is required for all rows'); return; }
        if (!e.amount || e.amount <= 0) { showToast('Amount must be greater than 0'); return; }
    }

    const cashIn  = validEntries.filter(e => e.type === 'Cash In').reduce((s, e) => s + (e.amount || 0), 0);
    const cashOut = validEntries.filter(e => e.type === 'Cash Out').reduce((s, e) => s + (e.amount || 0), 0);
    const closing = opening + cashIn - cashOut;

    const payload = {
        openingBalance: opening,
        cashIn,
        cashOut,
        closingBalance: closing,
        entries: validEntries
    };

    const res = await DB.saveDrawerEntries(date, payload);
    if (res && res.error) {
        showToast('Error saving data');
        return;
    }
    
    entries = validEntries; // Update local state to cleaned list
    renderTable();
    showToast('Drawer data persisted to MySQL ✓');

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
