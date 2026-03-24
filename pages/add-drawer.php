<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Cash Drawer — Pet Shop Management" />
  <title>Cash Drawer — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <style>
    /* Stable PTR */
    #ptr-indicator {
      position: fixed; top: 60px; left: 0; width: 100%; height: 60px;
      display: flex; align-items: center; justify-content: center;
      transition: all 0.25s cubic-bezier(0,0,0.2,1); z-index: 100;
      opacity: 0; transform: scale(0.5); pointer-events: none;
    }
    .ptr-pulling #ptr-indicator { opacity: 0.7; transform: scale(1); }
    .ptr-loading #ptr-indicator { opacity: 1; transform: scale(1.1); }
    .ptr-loading .spinner { animation: spin 0.8s linear infinite; }
    
    .spinner {
      width: 32px; height: 32px; background: white; border-radius: 50%;
      border: 3.5px solid rgba(var(--clr-primary-rgb), 0.1);
      border-top-color: var(--clr-primary);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    body { overscroll-behavior-y: contain; background: var(--clr-bg); }
    #content-wrapper { transition: transform 0.25s ease-out; position: relative; }

    /* Fix Button Layering: No "Blockers" */
    .top-nav { position: sticky; top: 0; z-index: 1000; background: #fff; }
    #saveBtnContainer {
      position: fixed; bottom: 0; left: 0; width: 100%;
      background: linear-gradient(to top, var(--clr-bg) 80%, transparent);
      padding: 20px 0; z-index: 900;
      display: flex; justify-content: center;
    }
    #saveBtn { width: 90%; margin: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    
    .table-container { margin-bottom: 110px; } /* Room for fixed footer */
    .btn:active { transform: scale(0.96); opacity: 0.8; }
  </style>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="spinner"></div></div>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Cash Drawer</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper" id="content-wrapper">

  <div class="drawer-header" style="margin-top: var(--sp-sm); align-items: flex-end; gap: 10px;">
    <div class="stat-card" style="flex:1;">
      <div class="stat-label">📅 Date</div>
      <input type="date" id="drawerDate" class="form-control" style="margin-top:4px; font-size:.85rem;" onchange="loadEntries()" />
    </div>
    <div class="stat-card" style="flex:1;">
      <div class="stat-label">🏦 Opening *</div>
      <input type="number" id="openingBalance" class="form-control" style="margin-top:4px; font-size:.85rem;" placeholder="0" oninput="updateTotals()" />
    </div>
  </div>

  <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm); margin: var(--sp-md) 0;">
    <div class="stat-card">
      <div class="stat-label">💚 Cash In</div>
      <div class="stat-value" id="cashInDisplay" style="font-size:1rem; color:#2d8a4e;">Rs. 0.00</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">❤️ Cash Out</div>
      <div class="stat-value" id="cashOutDisplay" style="font-size:1rem; color:#e05c5c;">Rs. 0.00</div>
    </div>
    <div class="stat-card accent" style="grid-column:1/-1; text-align:center;">
      <div class="stat-label">🏁 Closing Balance</div>
      <div class="stat-value" id="closingDisplay" style="font-size:1.4rem;">Rs. 0.00</div>
    </div>
  </div>

  <div class="flex-between" style="margin-bottom: var(--sp-sm); position: relative; z-index: 10;">
    <h2 class="section-title" style="margin-bottom:0;">Transactions</h2>
    <button class="btn btn-primary btn-sm" id="addRowBtn" onclick="addRow();">＋ Add Row</button>
  </div>

  <div class="table-container" style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <table class="pet-table" style="width:100%; min-width:300px;">
      <colgroup><col style="width:28%;"><col style="width:40%;"><col style="width:25%;"><col style="width:7%;"></colgroup>
      <thead><tr><th>Type</th><th>Detail</th><th>Amt</th><th></th></tr></thead>
      <tbody id="drawerBody"></tbody>
    </table>
    <div id="emptyDrawer" class="empty-state" style="display:none; padding:40px 0;">
      <div class="empty-icon">📂</div>
      <p>No transactions yet.</p>
    </div>
  </div>

  <div id="lastSync" style="text-align:center; font-size:.62rem; color:var(--clr-muted); font-weight:800; text-transform:uppercase; margin-bottom: 20px; letter-spacing:0.5px;">Last Synced: Just now</div>

</div>

<!-- Fixed Footer Button -->
<div id="saveBtnContainer">
  <button class="btn btn-primary btn-full" id="saveBtn" onclick="saveData()">💾 Save Changes</button>
</div>

<div class="toast" id="toast"></div>

<script>
let entries = [];
let startY = 0, distY = 0, activePTR = false;
const body = document.getElementById('page-body');
const content = document.getElementById('content-wrapper');

// Safe, No-Hang PTR
window.addEventListener('touchstart', e => { if (window.scrollY === 0) startY = e.touches[0].pageY; }, {passive:true});
window.addEventListener('touchmove', e => {
  const y = e.touches[0].pageY;
  distY = (y - startY) * 0.4;
  if (distY > 0 && window.scrollY === 0) {
    activePTR = true;
    body.classList.add('ptr-pulling');
    content.style.transform = `translateY(${Math.min(distY, 80)}px)`;
  }
}, {passive:true});

window.addEventListener('touchend', async () => {
    if (activePTR && distY >= 50) {
        body.classList.remove('ptr-pulling');
        body.classList.add('ptr-loading');
        content.style.transform = `translateY(50px)`;
        
        // Safety timeout: ensure ptr clears even if network fails
        const safeTimeout = setTimeout(clearPTR, 3000);
        await loadEntries(); 
        clearTimeout(safeTimeout);
        setTimeout(clearPTR, 400); // Visual pause
    } else {
        clearPTR();
    }
}, {passive:true});

function clearPTR() {
    body.classList.remove('ptr-pulling', 'ptr-loading');
    content.style.transform = '';
    activePTR = false; distY = 0;
}

document.addEventListener('DOMContentLoaded', async () => {
    // Exact Local Date lookup
    const localDay = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD
    document.getElementById('drawerDate').value = localDay;
    await loadEntries();
});

async function loadEntries() {
    try {
        const date = document.getElementById('drawerDate').value;
        const res = await DB.getDrawerEntries(date);
        const data = res || {};
        document.getElementById('openingBalance').value = (data.openingBalance !== undefined) ? data.openingBalance : '';
        entries = data.entries || [];
        if (data.openingBalance === undefined && entries.length === 0) await autoFillOpening(date);
        renderTable();
        const now = new Date();
        document.getElementById('lastSync').textContent = 'Last Synced: ' + now.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', second:'2-digit'});
    } catch (e) {
        showToast('Sync Failed! Check connection.');
        clearPTR();
    }
}

async function autoFillOpening(date) {
    const d = new Date(date); d.setDate(d.getDate() - 1);
    const prev = await DB.getDrawerEntries(d.toLocaleDateString('en-CA'));
    if (prev && prev.closingBalance) {
        document.getElementById('openingBalance').value = prev.closingBalance;
        updateTotals();
    }
}

function renderTable() {
    const b = document.getElementById('drawerBody');
    const e = document.getElementById('emptyDrawer');
    if (entries.length === 0) { b.innerHTML = ''; e.style.display = 'block'; updateTotals(); return; }
    e.style.display = 'none';
    b.innerHTML = entries.map((en, idx) => `
      <tr>
        <td>
          <select class="form-control" style="font-size:.65rem; border-radius:8px; color:${en.type==='Cash In'?'#2d8a4e':'#e05c5c'};" onchange="updateEntry(${idx}, 'type', this.value)">
            <option value="Cash In" ${en.type==='Cash In'?'selected':''}>IN</option>
            <option value="Cash Out" ${en.type==='Cash Out'?'selected':''}>OUT</option>
          </select>
        </td>
        <td><input type="text" value="${en.desc||''}" class="form-control" style="font-size:.7rem;" placeholder="..." oninput="updateEntry(${idx}, 'desc', this.value)" /></td>
        <td><input type="number" value="${en.amount||''}" class="form-control" style="font-size:.7rem;" placeholder="0" oninput="updateEntry(${idx}, 'amount', parseFloat(this.value)||0)" /></td>
        <td style="text-align:center;"><button class="btn btn-sm" onclick="removeRow(${idx})" style="color:#e05c5c; padding:2px;">✖</button></td>
      </tr>
    `).join('');
    updateTotals();
}

function updateEntry(idx, key, val) {
    entries[idx][key] = val;
    if (key === 'type') renderTable(); else updateTotals();
}

function updateTotals() {
    const o = parseFloat(document.getElementById('openingBalance').value) || 0;
    const cin = entries.filter(e => e.type === 'Cash In').reduce((s, e) => s + (e.amount || 0), 0);
    const cout = entries.filter(e => e.type === 'Cash Out').reduce((s, e) => s + (e.amount || 0), 0);
    const c = o + cin - cout;
    const fmt = n => 'Rs. ' + n.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    document.getElementById('cashInDisplay').textContent = fmt(cin);
    document.getElementById('cashOutDisplay').textContent = fmt(cout);
    document.getElementById('closingDisplay').textContent = fmt(c);
}

function addRow() {
    entries.push({ type: 'Cash In', desc: '', amount: '' });
    renderTable();
    setTimeout(() => { window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }); }, 50);
}

function removeRow(idx) { entries.splice(idx, 1); renderTable(); }

async function saveData() {
    const date = document.getElementById('drawerDate').value;
    const opening = parseFloat(document.getElementById('openingBalance').value);
    if (isNaN(opening)) { showToast('Add Opening Balance!'); return; }
    
    const valid = entries.filter(e => e.desc.trim() !== '' || (e.amount && e.amount > 0));
    for (let e of valid) {
        if (!e.desc.trim() || !e.amount || e.amount <= 0) { showToast('Complete every row!'); return; }
    }
    const cin = valid.filter(e => e.type === 'Cash In').reduce((s, e) => s + (e.amount || 0), 0);
    const cout = valid.filter(e => e.type === 'Cash Out').reduce((s, e) => s + (e.amount || 0), 0);

    const res = await DB.saveDrawerEntries(date, { 
      openingBalance: opening, 
      cashIn: cin, cashOut: cout, closingBalance: opening + cin - cout,
      entries: valid 
    });
    
    if (res.error) { showToast('Error Saving'); return; }
    entries = valid; renderTable(); showToast('Saved Successfully ✓');
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2400);
}
</script>
</body>
</html>
