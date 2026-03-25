<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Payment Management — Pet Shop" />
  <title>Payments — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
  <style>
    /* Filters */
    .filter-tabs {
      display: flex; gap: 10px; margin-bottom: 20px;
    }
    .filter-btn {
      flex: 1; padding: 10px; border-radius: 12px; border: 1.5px solid var(--clr-border);
      background: var(--clr-surface); font-size: .85rem; font-weight: 800; color: var(--clr-muted);
      cursor: pointer; transition: all .15s;
    }
    .filter-btn.active {
      background: var(--clr-primary); color: #fff; border-color: var(--clr-primary);
      box-shadow: var(--shadow-sm);
    }

    /* List */
    .pay-card {
      background: var(--clr-surface); border: 1.5px solid var(--clr-border);
      border-radius: var(--r-lg); padding: 16px; margin-bottom: 12px;
      display: flex; flex-direction: column; gap: 12px;
    }
    .pay-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .pay-title { font-size: .95rem; font-weight: 800; color: var(--clr-text); }
    .pay-badge { font-size: .62rem; font-weight: 800; padding: 3px 10px; border-radius: 50px; text-transform: uppercase; }
    .status-paid { background: var(--clr-primary-lt); color: var(--clr-primary); }
    .status-pending { background: var(--clr-danger-lt); color: var(--clr-danger); }

    .pay-body { display: flex; align-items: center; gap: 15px; }
    .pay-pet-icon {
      width: 48px; height: 48px; border-radius: 12px; background: var(--clr-bg);
      display: flex; align-items: center; justify-content: center; font-size: 1.6rem;
    }
    .pay-details { flex: 1; }
    .pay-amount { font-size: 1.1rem; font-weight: 800; color: var(--clr-primary); }
    .pay-date { font-size: .72rem; font-weight: 700; color: var(--clr-muted); margin-top: 2px; }

    .pay-footer {
      border-top: 1.5px solid var(--clr-bg); padding-top: 12px;
      display: flex; justify-content: space-between; align-items: center;
    }
    .due-tag { font-size: .7rem; font-weight: 800; color: var(--clr-muted); }
    .due-tag.overdue { color: var(--clr-danger); }

    /* Modal */
    #editModal {
      display: none; position: fixed; inset: 0; z-index: 2000;
      background: rgba(0,0,0,.45); align-items: flex-end; justify-content: center;
    }
    #editModal.open { display: flex; }
    #editModalBox {
      background: var(--clr-surface); border-radius: 24px 24px 0 0;
      width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
      padding: 24px 20px 48px; animation: modalIn .28s cubic-bezier(.4,0,.2,1) both;
    }
    @keyframes modalIn { from { transform: translateY(100%); } to { transform: translateY(0); } }
  </style>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<nav class="top-nav" style="position:sticky; top:0; z-index:1000; background:#fff; border-bottom:1.5px solid var(--clr-border);">
  <a href="index.php" class="nav-back">&#8592;</a>
  <span class="nav-title">Manage Payments</span>
  <div class="nav-spacer"></div>
</nav>

<div id="content-wrapper">
<div class="app-wrapper" style="padding-top: 20px;">

  <div class="filter-tabs">
    <button class="filter-btn active" onclick="setFilter('all', this)">All</button>
    <button class="filter-btn" onclick="setFilter('Pending', this)">Pending</button>
    <button class="filter-btn" onclick="setFilter('Paid', this)">Paid</button>
  </div>

  <div id="paymentsList"></div>
  <div id="emptyState" class="empty-state" style="display:none; padding:60px 0;">💰<p>No payments found.</p></div>

</div>
</div>

<!-- Edit Modal -->
<div id="editModal" onclick="if(event.target===this) closeEditModal()">
  <div id="editModalBox">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="font-size:1.1rem; font-weight:800; color:var(--clr-text);">Edit Payment Details</h3>
        <button class="btn btn-sm btn-ghost" onclick="closeEditModal()">✕</button>
    </div>

    <input type="hidden" id="editPetId">

    <div class="form-group">
        <label class="form-label">Payment Amount (Rs.) *</label>
        <input type="number" id="editAmount" class="form-control" step="0.01" />
    </div>

    <div class="form-group">
        <label class="form-label">Status *</label>
        <select id="editStatus" class="form-control" onchange="toggleEditDue()">
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
        </select>
    </div>

    <div class="form-group" id="editDueGroup">
        <label class="form-label">Due Date</label>
        <input type="date" id="editDueDate" class="form-control" />
    </div>

    <div class="form-group">
        <label class="form-label">Payment Remark / Note</label>
        <textarea id="editNote" class="form-control" rows="3" placeholder="e.g. Check given, Will pay on Monday..."></textarea>
    </div>

    <button class="btn btn-primary btn-full mt-lg" onclick="saveEdit()">💾 Update Payment</button>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
let _allData = [];
let _filter = 'all';

document.addEventListener('DOMContentLoaded', loadPayments);

async function loadPayments() {
    const list = document.getElementById('paymentsList');
    list.innerHTML = '<div style="text-align:center; padding:50px;">⏳ Loading...</div>';
    
    try {
        _allData = await DB.getAllCustomerSuppliers();
        renderList();
    } catch(e) {
        list.innerHTML = '<div class="empty-state">❌<p>Failed to load data.</p></div>';
    }
}

function setFilter(val, btn) {
    _filter = val;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderList();
}

function renderList() {
    const list = document.getElementById('paymentsList');
    const empty = document.getElementById('emptyState');
    
    const filtered = _filter === 'all' ? _allData : _allData.filter(x => x.payment_status === _filter);

    if (filtered.length === 0) {
        list.innerHTML = '';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';
    const nowStr = new Date().toISOString().split('T')[0];

    list.innerHTML = filtered.map(it => {
        const isPaid = it.payment_status === 'Paid';
        const isOverdue = !isPaid && it.due_date && it.due_date < nowStr;
        const dueText = it.due_date ? new Date(it.due_date).toLocaleDateString() : 'No due date';
        
        return `
            <div class="pay-card">
                <div class="pay-header">
                    <div class="pay-title">${it.full_name}</div>
                    <span class="pay-badge ${isPaid ? 'status-paid' : 'status-pending'}">${it.payment_status}</span>
                </div>
                <div class="pay-body" style="padding: 0 5px;">
                    <div class="pay-details">
                        <div class="pay-amount">Rs. ${parseFloat(it.cost_paid).toLocaleString()}</div>
                        <div class="pay-date">${it.pet_name} • ${new Date(it.created_at).toLocaleDateString()}</div>
                    </div>
                </div>
                <div class="pay-footer">
                    <div class="due-tag ${isOverdue ? 'overdue' : ''}">
                        ${isPaid ? 'Settled ✅' : (isOverdue ? '⚠️ Overdue: ' : '⏳ Due: ') + dueText}
                    </div>
                    <button class="btn btn-sm" onclick="openEditModal(${JSON.stringify(it).replace(/"/g, '&quot;')})">Edit</button>
                </div>
            </div>
        `;
    }).join('');
}

function openEditModal(data) {
    document.getElementById('editPetId').value = data.pet_id;
    document.getElementById('editAmount').value = data.cost_paid;
    document.getElementById('editStatus').value = data.payment_status;
    document.getElementById('editDueDate').value = data.due_date || '';
    document.getElementById('editNote').value = data.payment_note || '';
    
    toggleEditDue();
    document.getElementById('editModal').classList.add('open');
}

function toggleEditDue() {
    const s = document.getElementById('editStatus').value;
    document.getElementById('editDueGroup').style.display = (s === 'Pending') ? 'block' : 'none';
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}

async function saveEdit() {
    const payload = {
        pet_id: document.getElementById('editPetId').value,
        cost_paid: document.getElementById('editAmount').value,
        payment_status: document.getElementById('editStatus').value,
        due_date: document.getElementById('editStatus').value === 'Pending' ? document.getElementById('editDueDate').value : null,
        payment_note: document.getElementById('editNote').value
    };

    if (!payload.cost_paid || payload.cost_paid <= 0) { showToast('Invalid amount'); return; }

    try {
        const res = await DB.updatePayment(payload);
        if (res.success) {
            showToast('Payment updated successfully');
            closeEditModal();
            loadPayments();
        } else {
            showToast(res.error || 'Update failed');
        }
    } catch(e) {
        showToast('System Error');
    }
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2400);
}
</script>
</body>
</html>
