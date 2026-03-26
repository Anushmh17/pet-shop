<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Cash Drawer — Pet Shop Management" />
  <title>Cash Drawer — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
  <style>
    body { overscroll-behavior-y: contain; background: var(--clr-bg); }
    #content-wrapper { transition: transform 0.25s ease-out; position: relative; }
    .top-nav { position: sticky; top: 0; z-index: 1000; background: #fff; }

    /* ---- Input Group ---- */
    .field-block {
      background: var(--clr-surface);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-sm);
      padding: var(--sp-md);
      margin-bottom: var(--sp-sm);
    }
    .field-block .field-label {
      font-size: .72rem;
      font-weight: 800;
      color: var(--clr-muted);
      text-transform: uppercase;
      letter-spacing: .6px;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .field-block input {
      width: 100%;
      border: none;
      outline: none;
      font-size: 1.5rem;
      font-weight: 800;
      font-family: 'Nunito', sans-serif;
      color: var(--clr-text);
      background: transparent;
      padding: 0;
    }
    .field-block input[type="date"] {
      font-size: 1rem;
      font-weight: 700;
      color: var(--clr-text);
    }
    .field-block input::placeholder { color: var(--clr-border); }
    .field-block input[readonly] { color: var(--clr-primary); cursor: default; }

    /* ---- Summary Card ---- */
    .summary-card {
      background: linear-gradient(135deg, var(--clr-primary) 0%, #4a8a5c 100%);
      border-radius: var(--r-xl);
      padding: var(--sp-lg) var(--sp-md);
      color: #fff;
      margin-bottom: var(--sp-md);
      box-shadow: 0 6px 24px rgba(92,158,110,.3);
      position: relative;
      overflow: hidden;
    }
    .summary-card::before {
      content: '💰';
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 4rem;
      opacity: .15;
    }
    .summary-card .sc-label { font-size: .72rem; font-weight: 700; opacity: .8; text-transform: uppercase; letter-spacing: .8px; }
    .summary-card .sc-value { font-size: 2.4rem; font-weight: 800; line-height: 1.1; margin: 4px 0 2px; }
    .summary-card .sc-sub { font-size: .78rem; opacity: .7; font-weight: 600; }

    /* ---- Row Breakdown ---- */
    .breakdown-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px var(--sp-md);
      background: var(--clr-surface);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-md);
      margin-bottom: var(--sp-xs);
      box-shadow: var(--shadow-sm);
    }
    .breakdown-row .br-label { font-size: .8rem; font-weight: 700; color: var(--clr-muted); }
    .breakdown-row .br-value { font-size: .95rem; font-weight: 800; }
    .br-value.green { color: #2d8a4e; }
    .br-value.red   { color: #e05c5c; }
    .br-value.blue  { color: var(--clr-primary); }

    /* ---- Net Change Pill ---- */
    .net-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: .75rem;
      font-weight: 800;
      padding: 5px 14px;
      border-radius: 50px;
      margin-bottom: var(--sp-md);
    }
    .net-pill.positive { background: #e8f5ec; color: #2d8a4e; }
    .net-pill.negative { background: #fdeaea; color: #e05c5c; }
    .net-pill.neutral  { background: var(--clr-bg); color: var(--clr-muted); border: 1.5px solid var(--clr-border); }

    /* ---- Save Button ---- */
    #saveBtnContainer {
      position: fixed; bottom: 0; left: 0; width: 100%;
      background: linear-gradient(to top, var(--clr-bg) 80%, transparent);
      padding: 20px 0; z-index: 900;
      display: flex; justify-content: center;
    }
    #saveBtn { width: 90%; margin: 0; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    .btn:active { transform: scale(0.96); opacity: 0.8; }

    /* Bottom padding for fixed footer */
    .bottom-pad { height: 100px; }

    /* Date field special sizing */
    .field-block.date-field input { font-size: .95rem; }

    /* Custom Add Button inside input */
    .btn-inline-add {
      background: var(--clr-primary);
      color: #fff;
      font-size: .72rem;
      font-weight: 800;
      padding: 6px 14px;
      border-radius: 8px;
      text-transform: uppercase;
      transition: all 0.2s;
      flex-shrink: 0;
      box-shadow: 0 4px 10px rgba(92,158,110,0.2);
    }
    .btn-inline-add:disabled {
      background: var(--clr-border);
      color: var(--clr-muted);
      box-shadow: none;
      opacity: 0.6;
    }
    .btn-inline-add:active:not(:disabled) { transform: scale(0.92); }

    .spent-entry-pill {
      background: var(--clr-danger-lt);
      color: var(--clr-danger);
      font-size: .68rem;
      font-weight: 800;
      padding: 4px 10px;
      border-radius: 6px;
      margin-top: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    /* Last Synced */
    #lastSync { text-align:center; font-size:.62rem; color:var(--clr-muted); font-weight:800; text-transform:uppercase; margin-bottom:10px; letter-spacing:0.5px; }
    
    input[readonly] { opacity: 0.7; pointer-events: none; }
    
    .field-error {
      color: var(--clr-danger);
      font-size: .62rem;
      font-weight: 800;
      margin-top: 4px;
      display: none;
    }
    .field-error.show { display: block; }
  </style>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Cash Drawer</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper" id="content-wrapper" style="padding-top: var(--sp-md);">

  <!-- Date Picker -->
  <div class="field-block date-field">
    <div class="field-label">📅 Date</div>
    <input type="date" id="drawerDate" onchange="loadDrawer()" />
  </div>

  <!-- Opening Balance -->
  <div class="field-block" id="openingBlock">
    <div class="field-label">🏦 Opening Balance <span style="color:var(--clr-danger);">*</span></div>
    <div style="display:flex; align-items:center; gap:10px;">
      <input type="number" id="openingBalance" min="0" placeholder="0" style="flex:1;" />
      <button class="btn-inline-add" id="addOpeningBtn" onclick="addOpeningAction()">Add</button>
    </div>
  </div>

  <!-- Cash Added -->
  <div class="field-block">
    <div class="field-label">💚 Cash Added <span style="font-weight:600; text-transform:none; font-size:.68rem;">(optional)</span></div>
    <input type="number" id="cashAdded" min="0" placeholder="0" oninput="calc()" />
  </div>

  <!-- Cash Spent Entry Block -->
  <div class="field-block">
    <div class="field-label">❤️ Record Cash Spent</div>
    <div style="display:flex; flex-direction:column;">
      
      <!-- Amount Sub-field -->
      <div style="margin-bottom: 12px;">
        <label for="spentAmt" style="display:block; font-size:.65rem; color:var(--clr-muted); font-weight:800; text-transform:uppercase; margin-bottom:4px;">Amount (Rs.)</label>
        <div style="display:flex; align-items:center; gap:10px;">
          <input type="number" id="spentAmt" min="1" placeholder="0" style="flex:1; font-size: 1.35rem;" oninput="validateSpentBtn()" />
          <button class="btn-inline-add" id="addSpentBtn" onclick="addSpentAction()" disabled title="Amount and Reason required">Add</button>
        </div>
        <div id="err-amt" class="field-error">Please enter a valid amount</div>
      </div>

      <!-- Subtle separator -->
      <div style="height:1.5px; background:var(--clr-bg); margin: 2px 0 12px; border-radius:1px;"></div>

      <!-- Reason Sub-field -->
      <div style="margin-bottom: 6px;">
        <label for="spentReason" style="display:block; font-size:.65rem; color:var(--clr-muted); font-weight:800; text-transform:uppercase; margin-bottom:4px;">Reason for Cash Spent (Required)</label>
        <input type="text" id="spentReason" placeholder="Purpose of this expense" style="font-size: .95rem; border-bottom: 1.5px solid var(--clr-border); padding-bottom: 4px;" oninput="validateSpentBtn()" />
        <div id="err-reason" class="field-error">Reason is required for this entry</div>
      </div>
      
    </div>
    <div id="spentHistory" style="margin-top: 8px;"></div>
  </div>

  <!-- Section Divider -->
  <div style="margin: var(--sp-md) 0 var(--sp-sm);">
    <h2 class="section-title" style="margin-bottom: var(--sp-sm);">Daily Summary</h2>
  </div>

  <!-- Net Change Pill -->
  <div id="netPillWrap" style="margin-bottom: var(--sp-sm);"></div>

  <!-- Closing Balance Hero -->
  <div class="summary-card">
    <div class="sc-label">Remaining Cash</div>
    <div class="sc-value" id="closingDisplay">Rs. 0.00</div>
    <div class="sc-sub">Closing Balance for today</div>
  </div>

  <!-- Breakdown -->
  <div class="breakdown-row">
    <div class="br-label">🏦 Opening Balance</div>
    <div class="br-value blue" id="bd-opening">Rs. 0.00</div>
  </div>
  <div class="breakdown-row">
    <div class="br-label">💚 Cash Added</div>
    <div class="br-value green" id="bd-added">Rs. 0.00</div>
  </div>
  <div class="breakdown-row">
    <div class="br-label">❤️ Cash Spent</div>
    <div class="br-value red" id="bd-spent">Rs. 0.00</div>
  </div>

  <div id="lastSync" style="margin-top: var(--sp-md);">Not yet synced</div>

  <div class="bottom-pad"></div>
</div>

<!-- Fixed Footer Button -->
<div id="saveBtnContainer">
  <button class="btn btn-primary btn-full" id="saveBtn" onclick="saveDrawer()">💾 Save Daily Summary</button>
</div>

<div class="toast" id="toast"></div>

<script>
const body    = document.getElementById('page-body');
const content = document.getElementById('content-wrapper');
let startY = 0, distY = 0, activePTR = false;

// Functional State
let drawerState = {
  opening: null,
  added: 0,
  spentEntries: [], // Array of {amt, reason}
  openingLocked: false
};

/* ---- Pull-to-Refresh ---- */
window.addEventListener('touchstart', e => { if (window.scrollY === 0) { startY = e.touches[0].pageY; activePTR = false; } }, {passive:true});
window.addEventListener('touchmove', e => {
  distY = (e.touches[0].pageY - startY) * 0.4;
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
    content.style.transform = 'translateY(50px)';
    const safe = setTimeout(clearPTR, 3000);
    await loadDrawer();
    clearTimeout(safe);
    setTimeout(clearPTR, 400);
  } else { clearPTR(); }
}, {passive:true});
function clearPTR() {
  body.classList.remove('ptr-pulling', 'ptr-loading');
  content.style.transform = '';
  activePTR = false; distY = 0;
}

/* ---- Init ---- */
document.addEventListener('DOMContentLoaded', async () => {
  document.getElementById('drawerDate').value = new Date().toLocaleDateString('en-CA');
  await loadDrawer();
});

/* ---- Load Saved Data ---- */
async function loadDrawer() {
  try {
    const date = document.getElementById('drawerDate').value;
    const res  = await DB.getDrawerEntries(date);
    const data = res || {};

    if (data.openingBalance !== undefined) {
      drawerState.opening = data.openingBalance;
      drawerState.openingLocked = true;
      drawerState.added = data.cashAdded || 0;
      drawerState.spentEntries = data.spentEntries || [];
      
      // If we loaded a simple spent value from a previous version, convert it
      if (typeof data.cashSpent === 'number' && drawerState.spentEntries.length === 0 && data.cashSpent > 0) {
        drawerState.spentEntries.push({ amt: data.cashSpent, reason: 'Previous Entry' });
      }
    } else {
      resetState();
      await autoFillOpening(date);
    }

    renderUI();
    stamp();
  } catch(e) {
    showToast('Sync Failed!');
    console.error(e);
  }
}

function resetState() {
  drawerState = { opening: null, added: 0, spentEntries: [], openingLocked: false };
  document.getElementById('openingBalance').value = '';
  document.getElementById('cashAdded').value = '';
  document.getElementById('spentAmt').value = '';
  document.getElementById('spentReason').value = '';
}

async function autoFillOpening(date) {
  try {
    const d = new Date(date);
    d.setDate(d.getDate() - 1);
    const prev = await DB.getDrawerEntries(d.toLocaleDateString('en-CA'));
    if (prev && prev.closingBalance !== undefined) {
      document.getElementById('openingBalance').value = prev.closingBalance;
    }
  } catch(e) { }
}

/* ---- Actions ---- */
function addOpeningAction() {
  const input = document.getElementById('openingBalance');
  const val = parseFloat(input.value);
  if (isNaN(val) || val <= 0) {
    showToast('Enter valid opening balance > 0'); return;
  }
  drawerState.opening = val;
  drawerState.openingLocked = true;
  showToast('Opening Balance Locked ✓');
  renderUI();
}

function addSpentAction() {
  const amtInput = document.getElementById('spentAmt');
  const reasonInput = document.getElementById('spentReason');
  const amt = parseFloat(amtInput.value);
  const reason = reasonInput.value.trim();

  // Reset errors
  document.getElementById('err-amt').classList.remove('show');
  document.getElementById('err-reason').classList.remove('show');

  if (!drawerState.openingLocked) {
    showToast('Please add Opening Balance first!'); return;
  }
  
  let hasError = false;
  if (isNaN(amt) || amt <= 0) {
    document.getElementById('err-amt').classList.add('show');
    hasError = true;
  }
  if (!reason) {
    document.getElementById('err-reason').classList.add('show');
    hasError = true;
  }
  if (hasError) return;

  // Check Remaining Cash constraint
  const currentRemaining = (drawerState.opening || 0) + (parseFloat(document.getElementById('cashAdded').value) || 0) - getTotalSpent();
  if (amt > currentRemaining) {
    document.getElementById('err-amt').textContent = 'Insufficient funds in drawer';
    document.getElementById('err-amt').classList.add('show');
    showToast('Exceeds remaining cash! ⚠️'); 
    return;
  }

  drawerState.spentEntries.push({ amt, reason });
  amtInput.value = '';
  reasonInput.value = '';
  document.getElementById('addSpentBtn').disabled = true;
  
  showToast('Expense Added ✓');
  renderUI();
}

function validateSpentBtn() {
  const amtStr = document.getElementById('spentAmt').value;
  const amt = parseFloat(amtStr);
  const reason = document.getElementById('spentReason').value.trim();
  
  // Basic real-time cleanup of errors if they fixed it
  if (amt > 0) document.getElementById('err-amt').classList.remove('show');
  if (reason) document.getElementById('err-reason').classList.remove('show');

  document.getElementById('addSpentBtn').disabled = (isNaN(amt) || amt <= 0 || !reason);
}

function getTotalSpent() {
  return drawerState.spentEntries.reduce((sum, e) => sum + e.amt, 0);
}

/* ---- UI Refresh ---- */
function renderUI() {
  const openingInput = document.getElementById('openingBalance');
  const openingBtn   = document.getElementById('addOpeningBtn');
  const cashAddedInput = document.getElementById('cashAdded');
  
  // Opening Logic
  if (drawerState.openingLocked) {
    openingInput.value = drawerState.opening;
    openingInput.readOnly = true;
    openingBtn.disabled = true;
    openingBtn.textContent = 'Locked';
  } else {
    openingInput.readOnly = false;
    openingBtn.disabled = false;
    openingBtn.textContent = 'Add';
  }

  // Cash Added
  cashAddedInput.value = drawerState.added || (drawerState.added === 0 ? '' : drawerState.added);

  // Spent History — each entry gets a remove button to allow correcting data-entry errors
  const history = document.getElementById('spentHistory');
  history.innerHTML = drawerState.spentEntries.map((e, idx) => `
    <div class="spent-entry-pill">
      <span>${e.reason}</span>
      <div style="display:flex; align-items:center; gap:8px;">
        <span>Rs. ${e.amt.toLocaleString('en-IN')}</span>
        <button onclick="removeSpentEntry(${idx})" style="background:transparent; color:inherit; border:none; font-size:.9rem; cursor:pointer; padding:0 2px; font-weight:800; line-height:1;" title="Remove this entry">&times;</button>
      </div>
    </div>
  `).join('');

  calc();
}

function calc() {
  const opening = drawerState.opening || 0;
  const added   = parseFloat(document.getElementById('cashAdded').value) || 0;
  drawerState.added = added; // Sync with manual input

  const spent   = getTotalSpent();
  const closing = opening + added - spent;
  const net     = added - spent;

  const fmt = n => 'Rs. ' + Math.abs(n).toLocaleString('en-IN', { minimumFractionDigits: 2 });

  document.getElementById('bd-opening').textContent = fmt(opening);
  document.getElementById('bd-added').textContent   = fmt(added);
  document.getElementById('bd-spent').textContent   = fmt(spent);

  // Show closing in red when negative — signals that cashAdded has been reduced below spent total
  const closingEl = document.getElementById('closingDisplay');
  closingEl.textContent = (closing < 0 ? '-' : '') + fmt(closing);
  closingEl.style.color = closing < 0 ? '#ee6b6b' : '#fff';

  const pillWrap = document.getElementById('netPillWrap');
  let cls, icon, label;
  if (net > 0)       { cls = 'positive'; icon = '▲'; label = `Net +${fmt(net)}`; }
  else if (net < 0)  { cls = 'negative'; icon = '▼'; label = `Net −${fmt(Math.abs(net))}`; }
  else               { cls = 'neutral';  icon = '↔';  label = 'Net Change: —'; }
  pillWrap.innerHTML = `<span class="net-pill ${cls}">${icon} ${label}</span>`;
}

async function saveDrawer() {
  const date = document.getElementById('drawerDate').value;
  if (!drawerState.openingLocked) {
    showToast('Please add and lock Opening Balance 🏦'); return;
  }

  const added   = drawerState.added;
  const spent   = getTotalSpent();
  const closing = drawerState.opening + added - spent;

  // Block saving if closing balance is negative — indicates cashAdded was reduced below spent total
  if (closing < 0) {
    showToast('Cannot save: closing balance is negative ⚠️'); return;
  }

  const res = await DB.saveDrawerEntries(date, {
    openingBalance: drawerState.opening,
    cashAdded:      added,
    spentEntries:   drawerState.spentEntries,
    cashSpent:      spent, // for backward compatibility/quick reporting
    closingBalance: closing
  });

  if (res && res.error) { showToast('Error saving ❌'); return; }
  showToast('Saved ✓');
  stamp();
}

function stamp() {
  const now = new Date();
  document.getElementById('lastSync').textContent =
    'Last Synced: ' + now.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', second:'2-digit'});
}

// Allows removing a spent entry before saving — corrects accidental entries
function removeSpentEntry(idx) {
  drawerState.spentEntries.splice(idx, 1);
  renderUI();
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}
</script>
</body>
</html>
