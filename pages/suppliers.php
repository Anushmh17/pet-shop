<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Supplier Management — Pet Shop" />
  <title>Suppliers — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
  <style>
    .breadcrumb {
      display: flex; align-items: center; gap: 8px;
      font-size: .78rem; font-weight: 700; color: var(--clr-muted);
      margin-bottom: 20px;
    }
    .breadcrumb span { cursor: pointer; transition: color .15s; }
    .breadcrumb span:hover { color: var(--clr-primary); }
    .breadcrumb span.active { color: var(--clr-text); font-weight: 800; cursor: default; }

    /* Grid choices */
    .sup-type-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;
    }
    .type-card {
      background: var(--clr-surface);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-lg);
      padding: 30px 20px;
      text-align: center;
      cursor: pointer;
      transition: transform .15s, box-shadow .15s;
      display: flex; flex-direction: column; align-items: center; gap: 12px;
    }
    .type-card:active { transform: scale(.96); box-shadow: var(--shadow-md); }
    .type-card .icon { font-size: 2.8rem; }
    .type-card .label { font-size: 1rem; font-weight: 800; color: var(--clr-text); }
    .type-card .sub { font-size: .7rem; font-weight: 700; color: var(--clr-muted); line-height: 1.3; }

    /* List items */
    .sup-item {
      display: flex; align-items: center; gap: 15px;
      background: var(--clr-surface);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-lg);
      padding: 15px 18px; margin-bottom: 12px;
      cursor: pointer; transition: transform .1s;
    }
    .sup-item:active { transform: scale(.98); }
    .sup-icon {
      width: 50px; height: 50px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.8rem; background: var(--clr-bg); flex-shrink: 0;
    }
    .sup-info { flex: 1; min-width: 0; }
    .sup-name { font-size: 1rem; font-weight: 800; color: var(--clr-text); }
    .sup-meta { font-size: .72rem; font-weight: 700; color: var(--clr-muted); margin-top: 2px; }
    .sup-status {
        font-size: .62rem; font-weight: 800; padding: 2px 8px; border-radius: 50px;
        text-transform: uppercase; margin-left: 10px;
    }

    /* Detail view stuff */
    .detail-hero {
        background: var(--clr-bg); border-radius: var(--r-xl);
        padding: 25px 20px; margin-bottom: 20px; border: 1.5px solid var(--clr-border);
    }
    .detail-label { font-size: .65rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
    .detail-value { font-size: 1.1rem; font-weight: 800; color: var(--clr-text); margin-bottom: 15px; }
    .detail-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--clr-primary-lt); color: var(--clr-primary);
        padding: 4px 12px; border-radius: 50px; font-weight: 800; font-size: .8rem;
    }

    .view-anim { animation: slideIn .25s ease-out both; }
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(15px); }
      to { opacity: 1; transform: translateX(0); }
    }

    /* ---- Pet Detail Modal (Copied/Adapted) ---- */
    #petModal {
      display: none; position: fixed; inset: 0; z-index: 2000;
      background: rgba(0,0,0,.45); align-items: flex-end; justify-content: center;
    }
    #petModal.open { display: flex; }
    #petModalBox {
      background: var(--clr-surface); border-radius: 24px 24px 0 0;
      width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
      padding: 24px 20px 48px; animation: modalIn .28s cubic-bezier(.4,0,.2,1) both;
      position: relative;
    }
    @keyframes modalIn { from { transform: translateY(100%); } to { transform: translateY(0); } }
    .modal-handle { width: 40px; height: 4px; background: var(--clr-border); border-radius: 4px; margin: 0 auto 20px; }
    #modalCloseBtn {
      position: absolute; top: 18px; left: 18px; border: none; background: var(--clr-bg);
      border-radius: 50%; width: 36px; height: 36px; font-weight: 800; color: var(--clr-muted);
      z-index: 10; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center;
    }
    .img-strip { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 20px; scrollbar-width: none; }
    .img-strip::-webkit-scrollbar { display: none; }
    .img-strip img { width: 110px; height: 110px; object-fit: cover; border-radius: 16px; border: 2px solid var(--clr-border); flex-shrink: 0; }
    .img-placeholder { width: 110px; height: 110px; border-radius: 16px; border: 2px dashed var(--clr-border); display: flex; align-items: center; justify-content: center; font-size: 2.8rem; background: var(--clr-bg); flex-shrink: 0; }

    .det-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
    .det-cell { background: var(--clr-bg); border-radius: var(--r-md); padding: 12px 14px; }
    .det-label { font-size: .65rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase; margin-bottom: 4px; }
    .det-value { font-size: 1rem; font-weight: 800; color: var(--clr-text); }
    .det-cell.accent .det-value { color: var(--clr-primary); }
    .det-cell.wide { grid-column: 1 / -1; }
  </style>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<nav class="top-nav" style="position:sticky; top:0; z-index:1000; background:#fff; border-bottom:1.5px solid var(--clr-border);">
  <button class="nav-back" id="backBtn" onclick="handleBack()">&#8592;</button>
  <span class="nav-title" id="pageTitle">Suppliers</span>
  <div class="nav-spacer"></div>
</nav>

<div id="content-wrapper">
<div class="app-wrapper" style="padding-top: 20px;">

  <div class="breadcrumb" id="breadcrumb">
    <span class="active">Suppliers</span>
  </div>

  <div id="mainView" class="view-anim">
    <!-- Selection View initially -->
  </div>

</div>
</div>

<!-- ===== PET DETAIL MODAL ===== -->
    <div id="petModal" onclick="if(event.target===this) closePetModal()">
  <div id="petModalBox">
    <div class="modal-handle"></div>
    <button id="modalCloseBtn" onclick="closePetModal()">✕</button>

    <!-- Header Row: Image Visual (Left) + Identity Text (Right) -->
    <div style="display: flex; gap: 18px; align-items: flex-start; margin-bottom: 22px; padding-top: 12px; position: relative; z-index: 5;">
      <!-- Image Gallery -->
      <div id="modalImgStrip" style="display: flex; gap: 10px; overflow-x: auto; flex-shrink: 0; width: 120px; scrollbar-width: none;">
        <!-- Images injected by JS -->
      </div>
      
      <!-- Identity Metadata -->
      <div style="flex: 1; padding-top: 2px;">
        <div class="modal-pet-name" id="modalPetName" style="font-size: 1.55rem; letter-spacing: -0.4px; line-height: 1.1; margin-bottom: 2px; font-weight: 800; color: var(--clr-text);"></div>
        <div class="modal-pet-sub" id="modalPetSub"  style="font-size: .8rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase;"></div>
      </div>
    </div>

    <div class="det-grid">
      <div class="det-cell accent">
        <div class="det-label">💰 Selling Price</div>
        <div class="det-value" id="mPrice"></div>
      </div>
      <div class="det-cell">
        <div class="det-label">📦 Stock (Qty)</div>
        <div class="det-value" id="mQty"></div>
      </div>
      <div class="det-cell">
        <div class="det-label">🏷 Cost Price</div>
        <div class="det-value" id="mCost"></div>
      </div>
      <div class="det-cell">
        <div class="det-label">🔢 Transaction Status</div>
        <div class="det-value" id="mStatus"></div>
      </div>
      <div class="det-cell wide" id="mNotesCell" style="display:none;">
        <div class="det-label">📝 Internal Notes</div>
        <div style="font-size:.85rem; color:var(--clr-muted); font-weight:600; line-height:1.5;" id="mNotes"></div>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
let _view = 'selection'; // selection | customer_list | dealer_list | customer_detail | dealer_detail
let _history = [];

document.addEventListener('DOMContentLoaded', () => {
    renderSelection();
});

function handleBack() {
    if (_history.length > 0) {
        const last = _history.pop();
        if (last === 'selection') renderSelection(false);
        else if (last === 'customer_list') renderCustomerList(false);
        else if (last === 'dealer_list') renderDealerList(false);
    } else {
        window.location.href = 'index.php';
    }
}

function updateBreadcrumb(items) {
    const b = document.getElementById('breadcrumb');
    let html = '';
    items.forEach((it, idx) => {
        const isActive = idx === items.length - 1;
        html += `<span class="${isActive ? 'active' : ''}" onclick="${it.fn ? it.fn : ''}">${it.label}</span>`;
        if (!isActive) html += `<span class="sep">›</span>`;
    });
    b.innerHTML = html;
}

// ========================
// 1. SELECTION VIEW
// ========================
function renderSelection(pushHist = true) {
    if (pushHist && _view !== 'selection') _history.push(_view);
    _view = 'selection';
    document.getElementById('pageTitle').textContent = 'Suppliers';
    updateBreadcrumb([{ label: 'Suppliers' }]);

    const main = document.getElementById('mainView');
    main.innerHTML = `
        <div class="sup-type-grid">
            <div class="type-card" onclick="renderCustomerList()">
                <div class="icon">👥</div>
                <div class="label">Customer Suppliers</div>
                <div class="sub">Individual residents supplying pets</div>
            </div>
            <div class="type-card" onclick="renderDealerList()">
                <div class="icon">🏢</div>
                <div class="label">Wholesale Dealers</div>
                <div class="sub">Large scale pet farm dealers</div>
            </div>
        </div>
    `;
}

// ========================
// 2. CUSTOMER LIST
// ========================
async function renderCustomerList(pushHist = true) {
    if (pushHist) _history.push(_view);
    _view = 'customer_list';
    document.getElementById('pageTitle').textContent = 'Customers';
    updateBreadcrumb([
        { label: 'Suppliers', fn: 'renderSelection()' },
        { label: 'Customers' }
    ]);

    const main = document.getElementById('mainView');
    main.innerHTML = '<div style="text-align:center; padding:50px;">⏳ Loading customers...</div>';

    try {
        const data = await DB.getAllCustomerSuppliers();
        if (data.length === 0) {
            main.innerHTML = '<div class="empty-state">🏠<p>No customer records found.</p></div>';
            return;
        }

        main.innerHTML = data.map(it => {
            const isPaid = it.payment_status === 'Paid';
            const statColor = isPaid ? 'var(--clr-primary)' : 'var(--clr-danger)';
            const statBg    = isPaid ? 'var(--clr-primary-lt)' : 'var(--clr-danger-lt)';
            
            return `
                <div class="sup-item" onclick="renderCustomerDetail(${JSON.stringify(it).replace(/"/g, '&quot;')})">
                    <div class="sup-info" style="padding-left: 5px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div class="sup-name">${it.full_name}</div>
                            <span class="sup-status" style="background:${statBg}; color:${statColor};">${it.payment_status}</span>
                        </div>
                        <div class="sup-meta">${it.pet_name} • ${new Date(it.created_at).toLocaleDateString()}</div>
                    </div>
                    <div style="color:var(--clr-border);">›</div>
                </div>
            `;
        }).join('');
    } catch(e) {
        main.innerHTML = '<div class="empty-state">❌<p>Failed to load data.</p></div>';
    }
}

// ========================
// 3. DEALER LIST
// ========================
async function renderDealerList(pushHist = true) {
    if (pushHist) _history.push(_view);
    _view = 'dealer_list';
    document.getElementById('pageTitle').textContent = 'Dealers';
    updateBreadcrumb([
        { label: 'Suppliers', fn: 'renderSelection()' },
        { label: 'Dealers' }
    ]);

    const main = document.getElementById('mainView');
    main.innerHTML = '<div style="text-align:center; padding:50px;">⏳ Loading dealers...</div>';

    try {
        const data = await DB.getUniqueDealers();
        if (data.length === 0) {
            main.innerHTML = '<div class="empty-state">🏢<p>No dealer records found.</p></div>';
            return;
        }

        main.innerHTML = data.map(name => `
            <div class="sup-item" onclick="renderDealerDetail('${name}')">
                <div class="sup-icon">🏬</div>
                <div class="sup-info">
                    <div class="sup-name">${name}</div>
                    <div class="sup-meta">Business / Wholesale Supplier</div>
                </div>
                <div style="color:var(--clr-border);">›</div>
            </div>
        `).join('');
    } catch(e) {
        main.innerHTML = '<div class="empty-state">❌<p>Failed to load data.</p></div>';
    }
}

// ========================
// 4. CUSTOMER DETAIL
// ========================
function renderCustomerDetail(data) {
    _history.push(_view);
    _view = 'customer_detail';
    document.getElementById('pageTitle').textContent = 'Supplier Profile';
    updateBreadcrumb([
        { label: 'Suppliers', fn: 'renderSelection()' },
        { label: 'Customers', fn: 'renderCustomerList()' },
        { label: 'Detail' }
    ]);

    const isPaid = data.payment_status === 'Paid';
    const main = document.getElementById('mainView');
    
    main.innerHTML = `
        <div class="detail-hero">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                <div>
                    <div class="detail-label">Supplier Name</div>
                    <div class="detail-value" style="margin-bottom:5px;">${data.full_name}</div>
                    <div class="detail-pill">${data.payment_status}</div>
                </div>
                <div style="text-align:right;">
                    <div class="detail-label">Total Cost</div>
                    <div class="detail-value" style="color:var(--clr-primary);">Rs. ${parseFloat(data.cost_paid).toLocaleString()}</div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div>
                    <div class="detail-label">NIC Number</div>
                    <div style="font-size:.9rem; font-weight:700;">${data.nic}</div>
                </div>
                <div>
                    <div class="detail-label">Transaction Date</div>
                    <div style="font-size:.9rem; font-weight:700;">${new Date(data.created_at).toLocaleDateString()}</div>
                </div>
                <div style="grid-column: 1 / -1;">
                    <div class="detail-label">Residential Address</div>
                    <div style="font-size:.85rem; font-weight:600; color:var(--clr-muted);">${data.address || 'Not provided'}</div>
                </div>
            </div>
        </div>

        <h3 style="font-size:.85rem; font-weight:800; color:var(--clr-muted); text-transform:uppercase; margin:25px 0 12px; letter-spacing:.5px;">📦 Supplied Pet Details</h3>
        <div class="sup-item" onclick="openPetModal(${data.pet_id})">
            <div class="sup-info" style="padding-left: 5px;">
                <div class="sup-name">${data.pet_name}</div>
                <div class="sup-meta">${data.category.toUpperCase()} • ${data.pet_variety || 'Regular'}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:.85rem; font-weight:800; color:var(--clr-primary);">Rs. ${parseFloat(data.cost_paid).toLocaleString()}</div>
                <div style="font-size:.6rem; font-weight:700; color:var(--clr-muted);">Click to view details ›</div>
            </div>
        </div>

        ${data.description ? `
            <div style="margin-top:20px;">
                <div class="detail-label">Remarks / Description</div>
                <div style="background:var(--clr-bg); border-radius:12px; padding:15px; font-size:.85rem; color:var(--clr-muted); line-height:1.5; border-left:4px solid var(--clr-primary);">
                    ${data.description}
                </div>
            </div>
        ` : ''}

        ${!isPaid ? `
            <div style="margin-top:30px;">
                <button class="btn btn-primary btn-full" onclick="markPaidDetail(${data.pet_id})">✅ Mark as Fully Paid</button>
            </div>
        ` : ''}
    `;
}

// ========================
// 5. DEALER DETAIL
// ========================
async function renderDealerDetail(name) {
    _history.push(_view);
    _view = 'dealer_detail';
    document.getElementById('pageTitle').textContent = name;
    updateBreadcrumb([
        { label: 'Suppliers', fn: 'renderSelection()' },
        { label: 'Dealers', fn: 'renderDealerList()' },
        { label: 'Inventory' }
    ]);

    const main = document.getElementById('mainView');
    main.innerHTML = '<div style="text-align:center; padding:50px;">⏳ Fetching inventory...</div>';

    try {
        const pets = await DB.getDealerPets(name);
        main.innerHTML = `
            <div style="background:var(--clr-primary-lt); border-radius:15px; padding:15px; margin-bottom:20px; display:flex; align-items:center; gap:12px;">
                <div style="font-size:1.8rem;">📦</div>
                <div>
                    <div style="font-size:.85rem; font-weight:800; color:var(--clr-primary);">${pets.length} Items Found</div>
                    <div style="font-size:.65rem; font-weight:700; color:var(--clr-muted);">Current inventory supplied by ${name}</div>
                </div>
            </div>
            ${pets.map(p => {
                const imgHtml = p.primaryImage 
                    ? `<img src="${p.primaryImage}" onclick="event.stopPropagation(); maximizeImage(this.src)" style="width:100%; height:100%; object-fit:cover; cursor:zoom-in;" />`
                    : `<span style="font-size:1rem; color:var(--clr-muted); opacity:0.8;">📸</span>`;
                
                return `
                    <div class="sup-item" onclick="openPetModal(${p.id})" style="display:flex; align-items:center; gap:14px; background:var(--clr-surface); border-radius:14px; border:1.5px solid var(--clr-border); padding:12px 14px; margin-bottom:10px; cursor:pointer; line-height: 1.2;">
                        <div style="width:48px; height:48px; border-radius:12px; background:var(--clr-bg); border:1.5px solid var(--clr-border); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                            ${imgHtml}
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <div style="font-weight:800; font-size:1.02rem; color:var(--clr-text);">${p.name}</div>
                                <span style="font-size:.62rem; font-weight:800; color:var(--clr-primary); background:var(--clr-primary-lt); padding:2px 8px; border-radius:50px; text-transform:uppercase;">${p.category}</span>
                            </div>
                            <div style="font-size:.78rem; font-weight:700; color:var(--clr-muted); margin-top:3px;">Qty: ${p.qty} • Cost: Rs. ${parseFloat(p.cost).toLocaleString('en-IN')}</div>
                            <div style="font-weight:800; color:var(--clr-text); font-size:1rem; margin-top:4px;">Rs. ${parseFloat(p.price).toLocaleString('en-IN')}</div>
                        </div>
                        <div style="color:var(--clr-border); font-size:1.1rem; flex-shrink:0;">›</div>
                    </div>`;
            }).join('')}
        `;
    } catch(e) {
        main.innerHTML = '<div class="empty-state">❌<p>Failed to load inventory.</p></div>';
    }
}

async function markPaidDetail(petId) {
    if(!confirm('Confirm payment of this transaction?')) return;
    const res = await DB.markAsPaid(petId);
    if(res && res.success) {
        showToast('Payment updated!');
        // Reload detail view (need to fetch latest data)
        const all = await DB.getAllCustomerSuppliers();
        const updated = all.find(x => parseInt(x.pet_id) === parseInt(petId));
        if(updated) renderCustomerDetail(updated);
    } else {
        showToast(res?.error || 'Error');
    }
}

// ========================
// PET MODAL LOGIC
// ========================
async function openPetModal(petId) {
    const pets = await DB.getPets();
    const p = pets.find(x => parseInt(x.id) === parseInt(petId));
    if (!p) return;

    document.getElementById('modalPetName').textContent = p.name;
    document.getElementById('modalPetSub').textContent = (p.category||'').toUpperCase() + (p.pet_variety ? ' · '+p.pet_variety : '');
    
    document.getElementById('mPrice').textContent = 'Rs. ' + parseFloat(p.price).toLocaleString();
    document.getElementById('mQty').textContent = p.qty + ' in stock';
    document.getElementById('mCost').textContent = 'Rs. ' + parseFloat(p.cost).toLocaleString();
    document.getElementById('mStatus').textContent = p.payment_status || 'Paid';
    
    const notesCell = document.getElementById('mNotesCell');
    if (p.notes && p.notes.trim()) {
        document.getElementById('mNotes').textContent = p.notes;
        notesCell.style.display = 'block';
    } else {
        notesCell.style.display = 'none';
    }

    // Images
    const strip = document.getElementById('modalImgStrip');
    strip.innerHTML = '<div class="img-placeholder">⏳</div>';
    document.getElementById('petModal').classList.add('open');

    try {
        const imgs = await DB.getPetImages(petId);
        if (imgs && imgs.length > 0) {
            strip.innerHTML = imgs.map(src => `<img src="${src}" loading="lazy" onclick="maximizeImage(this.src)" style="cursor:zoom-in;" />`).join('');
        } else {
            strip.innerHTML = `<div class="img-placeholder" style="font-size:1.6rem; color:var(--clr-muted);">📸</div>`;
        }
    } catch(e) {
        strip.innerHTML = `<div class="img-placeholder" style="font-size:1.6rem; color:var(--clr-muted);">📸</div>`;
    }
}

function closePetModal() {
    document.getElementById('petModal').classList.remove('open');
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2400);
}

function handleBackGesture() {
  window.addEventListener('popstate', (e) => {
    if (_history.length > 0) {
        handleBack();
        history.pushState(null, null, window.location.pathname);
    }
  });
  history.pushState(null, null, window.location.pathname);
}
handleBackGesture();

</script>
</body>
</html>
