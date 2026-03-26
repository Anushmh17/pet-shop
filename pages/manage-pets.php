<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Manage Pets Inventory — Pet Shop Management" />
  <title>Manage Pets — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
  <style>
    /* ---- Clickable Card ---- */
    .pet-stock-card {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 15px;
      background: var(--clr-surface);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-sm);
      cursor: pointer;
      transition: transform .15s, box-shadow .15s;
      -webkit-tap-highlight-color: transparent;
    }
    .pet-stock-card:active { transform: scale(.97); box-shadow: var(--shadow-md); }

    /* ---- Detail Modal ---- */
    #petModal {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 500;
      background: rgba(0,0,0,.45);
      align-items: flex-end;
      justify-content: center;
    }
    #petModal.open { display: flex; }

    #petModalBox {
      background: var(--clr-surface);
      border-radius: 24px 24px 0 0;
      width: 100%;
      max-width: 520px;
      max-height: 88vh;
      overflow-y: auto;
      padding: 24px 20px 40px;
      animation: modalIn .28s cubic-bezier(.4,0,.2,1) both;
      position: relative;
    }
    @keyframes modalIn {
      from { transform: translateY(100%); }
      to   { transform: translateY(0); }
    }

    /* drag handle */
    .modal-handle {
      width: 40px; height: 4px; background: var(--clr-border);
      border-radius: 4px; margin: 0 auto 20px;
    }

    /* Image strip */
    .img-strip {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      padding-bottom: 8px;
      margin-bottom: 20px;
      scrollbar-width: none;
    }
    .img-strip::-webkit-scrollbar { display: none; }
    .img-strip img {
      width: 110px;
      height: 110px;
      object-fit: cover;
      border-radius: 16px;
      border: 2px solid var(--clr-border);
      flex-shrink: 0;
    }
    .img-placeholder {
      width: 110px;
      height: 110px;
      border-radius: 16px;
      border: 2px dashed var(--clr-border);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.8rem;
      background: var(--clr-bg);
      flex-shrink: 0;
    }

    /* Modal header row */
    .modal-pet-name {
      font-size: 1.2rem;
      font-weight: 800;
      color: var(--clr-text);
      line-height: 1.2;
    }
    .modal-pet-sub {
      font-size: .78rem;
      font-weight: 700;
      color: var(--clr-muted);
      margin-top: 3px;
    }

    /* Detail rows */
    .detail-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin: 18px 0 14px;
    }
    .detail-cell {
      background: var(--clr-bg);
      border-radius: var(--r-md);
      padding: 12px 14px;
    }
    .detail-cell .dc-label {
      font-size: .68rem;
      font-weight: 800;
      color: var(--clr-muted);
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-bottom: 4px;
    }
    .detail-cell .dc-value {
      font-size: 1rem;
      font-weight: 800;
      color: var(--clr-text);
    }
    .detail-cell .dc-value.link {
      color: var(--clr-primary);
      text-decoration: underline;
      cursor: pointer;
    }
    .detail-cell.wide { grid-column: 1 / -1; }
    .detail-cell.accent .dc-value { color: var(--clr-primary); }
    .detail-cell.danger .dc-value { color: var(--clr-danger); }

    /* Stock status badge */
    .status-badge {
      display: inline-block;
      font-size: .68rem;
      font-weight: 800;
      padding: 4px 12px;
      border-radius: 50px;
    }

    /* Notes */
    .notes-box {
      background: var(--clr-bg);
      border-radius: var(--r-md);
      padding: 12px 14px;
      font-size: .85rem;
      font-weight: 600;
      color: var(--clr-muted);
      line-height: 1.5;
    }

    /* Close button — right side to match all other modals in the app */
    #modalCloseBtn {
      position: absolute;
      top: 18px;
      right: 18px;
      background: var(--clr-bg);
      border: none;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      font-size: 1.1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--clr-muted);
      font-weight: 800;
      z-index: 10;
    }
  </style>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav" style="position:sticky; top:0; z-index:1000; border-bottom:1px solid var(--clr-border);">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Manage Inventory</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div id="content-wrapper">
<div class="app-wrapper" style="padding-top: var(--sp-sm);">

  <!-- Hero Card -->
  <div class="today-hero" style="background: linear-gradient(135deg, #5c9e6e 0%, #4a8a5a 100%); margin-bottom: 20px;">
    <div style="font-size: .8rem; opacity: 0.9; text-transform: uppercase; font-weight: 800; letter-spacing: 1px;">Live Inventory</div>
    <div style="font-size: 2.2rem; font-weight: 800; margin: 5px 0;" id="totalStockCount">...</div>
    <div style="font-size: .75rem; font-weight: 700;">Total Pets in Shop</div>
  </div>

  <div class="flex-between" style="margin-bottom: 15px;">
    <h2 class="section-title" style="margin:0;">Current Stock</h2>
    <div style="display:flex; gap:10px;">
      <span style="font-size:.65rem; font-weight:800; color:var(--clr-muted); border:1px solid var(--clr-border); padding:4px 8px; border-radius:6px; background:white;" id="alertStatusBadge">Checking...</span>
    </div>
  </div>

  <div id="petsListGrid" style="display:grid; grid-template-columns: 1fr; gap: var(--sp-sm); margin-bottom: 20px;">
    <!-- Cards injected by JS -->
  </div>

  <div id="emptyPets" class="empty-state" style="display:none; padding:40px 0;">
    <div class="empty-icon">🏠</div>
    <p>No pets found in inventory.</p>
    <a href="add-pet.php" class="btn btn-primary btn-sm mt-md">Add First Pet</a>
  </div>

  <div id="lastSync" style="text-align:center; font-size:.62rem; color:var(--clr-muted); font-weight:800; text-transform:uppercase; margin-bottom: 20px; letter-spacing:0.5px;">Last Synced: Syncing...</div>

</div><!-- /app-wrapper -->
</div><!-- /content-wrapper -->

<!-- ===== PET DETAIL MODAL ===== -->
<div id="petModal" role="dialog" aria-modal="true" aria-label="Pet Details" onclick="handleModalBgClick(event)">
  <div id="petModalBox">
    <div class="modal-handle"></div>
    <button id="modalCloseBtn" onclick="closeModal()" aria-label="Close">✕</button>

    <!-- Header Row: Main Image + Identity -->
    <div style="display: flex; gap: 18px; align-items: flex-start; margin-bottom: 22px; padding-top: 12px; position: relative; z-index: 5;">
      <!-- Primary Visual (Left) -->
      <div id="modalImgStrip" style="display: flex; gap: 10px; overflow-x: auto; flex-shrink: 0; width: 120px; scrollbar-width: none;">
        <!-- Images injected by JS -->
      </div>
      
      <!-- Identity Metadata (Top-Right Placement) -->
      <div style="flex: 1; padding-top: 2px;">
        <div class="modal-pet-name" id="modalName" style="font-size: 1.55rem; letter-spacing: -0.4px; line-height: 1.1; margin-bottom: 2px;"></div>
        <div class="modal-pet-sub" id="modalSub" style="font-size: 0.82rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase;"></div>
        <div id="modalStatusBadge" style="margin-top: 6px;"></div>
      </div>
    </div>

    <!-- Detail grid -->
    <div class="detail-grid">
      <div class="detail-cell accent">
        <div class="dc-label">💰 Selling Price</div>
        <div class="dc-value" id="modalPrice"></div>
      </div>
      <div class="detail-cell">
        <div class="dc-label">📦 Stock (Qty)</div>
        <div class="dc-value" id="modalQty"></div>
      </div>
      <div class="detail-cell">
        <div class="dc-label">🏷 Cost Price</div>
        <div class="dc-value" id="modalCost"></div>
      </div>
      <div class="detail-cell">
        <div class="dc-label">🔢 Type</div>
        <div class="dc-value" id="modalType"></div>
      </div>
      <div class="detail-cell">
        <div class="dc-label">📋 Source</div>
        <div class="dc-value" id="modalSource"></div>
      </div>
      <div class="detail-cell">
        <div class="dc-label">⚠️ Alert Level</div>
        <div class="dc-value" id="modalAlert"></div>
      </div>
      <div class="detail-cell wide" id="modalSupplierCell" style="display:none;">
        <div class="dc-label" style="display:flex; justify-content:space-between;">
           <span>🚚 Supplier Info</span>
           <span id="modalSupStatus" style="font-size:.6rem; padding:2px 8px; border-radius:10px;"></span>
        </div>
        <div class="notes-box">
          <div style="font-weight:800; color:var(--clr-text); font-size:.9rem;" id="modalSupText"></div>
          <div style="font-size:.7rem; color:var(--clr-muted); margin-top:2px;" id="modalSupSub"></div>
          <div style="font-size:.75rem; color:var(--clr-primary); margin-top:4px; font-weight:700;" id="modalSupNote"></div>
        </div>
      </div>
      <div class="detail-cell wide" id="modalNotesCell" style="display:none;">
        <div class="dc-label" style="margin-bottom:6px;">📝 Notes</div>
        <div class="notes-box" id="modalNotes"></div>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
/* ================ STATE ================ */
let allPets = [];
let startY = 0, distY = 0, pulling = false;
const cnt = document.getElementById('content-wrapper');

/* ================ PTR ================ */
window.addEventListener('touchstart', e => { if(window.scrollY === 0){ startY = e.touches[0].pageY; pulling = true; } }, {passive:true});
window.addEventListener('touchmove', e => {
    if(!pulling) return;
    const y = e.touches[0].pageY;
    distY = (y - startY) * 0.4;
    if(distY > 0 && window.scrollY === 0){
        document.body.classList.add('ptr-pulling');
        cnt.style.transform = `translateY(${Math.min(distY, 80)}px)`;
    }
}, {passive:true});
window.addEventListener('touchend', async () => {
    if(pulling && distY >= 60){
        document.body.classList.remove('ptr-pulling');
        document.body.classList.add('ptr-loading');
        cnt.style.transform = 'translateY(40px)';
        await renderPets();
        setTimeout(() => {
            document.body.classList.remove('ptr-loading');
            cnt.style.transform = '';
        }, 500);
    } else {
        document.body.classList.remove('ptr-pulling', 'ptr-loading');
        cnt.style.transform = '';
    }
    pulling = false; distY = 0;
}, {passive:true});

/* ================ RENDER LIST ================ */
async function renderPets() {
    const list = document.getElementById('petsListGrid');
    allPets = await DB.getPets();

    document.getElementById('totalStockCount').textContent = allPets.reduce((s, p) => s + parseInt(p.qty), 0);

    if (allPets.length === 0) {
        list.innerHTML = '';
        document.getElementById('emptyPets').style.display = 'block';
        return;
    }

    document.getElementById('emptyPets').style.display = 'none';

    let lowStockCount = 0;
    list.innerHTML = allPets.map((p, idx) => {
        const isLow = parseInt(p.qty) <= parseInt(p.alert_level);
        if(isLow && !p.stop_alert) lowStockCount++;
        const statusColor = isLow && !p.stop_alert ? 'var(--clr-danger)' : 'var(--clr-primary)';
        const statusBg    = isLow && !p.stop_alert ? 'var(--clr-danger-lt)' : 'var(--clr-primary-lt)';
        // Distinguish between active low stock vs. silenced alerts vs. healthy
        const statusLabel = isLow && !p.stop_alert ? 'LOW STOCK' : (p.stop_alert && isLow ? 'ALERT OFF' : 'HEALTHY');

        const imgHtml = p.primaryImage 
            ? `<img src="${p.primaryImage}" onclick="event.stopPropagation(); maximizeImage(this.src)" style="width:100%; height:100%; object-fit:cover; cursor:zoom-in;" />`
            : `<span style="font-size:.9rem; color:var(--clr-muted); opacity:0.8;">📸</span>`;

        return `
          <div class="pet-stock-card" onclick="openModal(${idx})" id="pet-card-${p.id}" style="display:flex; align-items:center; gap:14px; padding:12px 14px;">
            <div style="width:48px; height:48px; border-radius:12px; background:var(--clr-bg); border:1.5px solid var(--clr-border); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
              ${imgHtml}
            </div>

            <div style="flex:1; min-width:0;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div style="font-weight:800; font-size:1rem; color:var(--clr-text); line-height:1.2;">${p.name}</div>
                <div style="font-size:.62rem; font-weight:800; color:${statusColor}; background:${statusBg}; padding:2px 8px; border-radius:50px; white-space:nowrap; margin-left:8px;">${statusLabel}</div>
              </div>
              <div style="font-size:.7rem; font-weight:700; color:var(--clr-muted); margin-top:3px;">${p.category.toUpperCase()} • ${p.pet_variety || 'Regular'}</div>
              <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
                <div style="font-size:.82rem; font-weight:800; color:var(--clr-text);">Qty: ${p.qty}</div>
                <div style="font-size:.82rem; font-weight:800; color:var(--clr-primary);">Rs. ${parseFloat(p.price).toLocaleString('en-IN')}</div>
              </div>
            </div>
            <div style="color:var(--clr-border); font-size:1.1rem; flex-shrink:0;">›</div>
          </div>
        `;
    }).join('');

    const badge = document.getElementById('alertStatusBadge');
    if(lowStockCount > 0){
        badge.textContent = `🚨 ${lowStockCount} LOW STOCK`;
        badge.style.color = 'var(--clr-danger)';
        badge.style.borderColor = 'var(--clr-danger)';
    } else {
        badge.textContent = '✅ INVENTORY HEALTHY';
        badge.style.color = 'var(--clr-primary)';
        badge.style.borderColor = 'var(--clr-primary)';
    }

    const now = new Date();
    document.getElementById('lastSync').textContent = 'Last Synced: ' + now.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', second:'2-digit'});
}

/* ================ MODAL ================ */
async function openModal(idx) {
    const p = allPets[idx];
    if (!p) return;

    const isLow = parseInt(p.qty) <= parseInt(p.alert_level);

    // --- Text fields ---
    document.getElementById('modalName').textContent    = p.name;
    document.getElementById('modalSub').textContent     = p.category.charAt(0).toUpperCase() + p.category.slice(1) + (p.pet_variety ? ' • ' + p.pet_variety : '');
    document.getElementById('modalPrice').textContent   = 'Rs. ' + parseFloat(p.price).toLocaleString('en-IN');
    document.getElementById('modalQty').textContent     = p.qty + ' units';
    document.getElementById('modalCost').textContent    = p.cost > 0 ? 'Rs. ' + parseFloat(p.cost).toLocaleString('en-IN') : '—';
    document.getElementById('modalType').textContent    = p.type || '—';
    
    const srcEl = document.getElementById('modalSource');
    srcEl.textContent = p.source || '—';
    
    // --- Supplier Logic ---
    const supCell = document.getElementById('modalSupplierCell');
    if (p.source === 'Customer Supplied') {
        srcEl.classList.add('link');
        srcEl.title = 'View Customer Profile';
        srcEl.onclick = () => window.location.href = `customer-supplier.php?pet_id=${p.id}`;
        
        supCell.style.display = 'block';
        document.getElementById('modalSupText').textContent = p.supplier_name || 'Individual';
        document.getElementById('modalSupSub').textContent  = `ID: ${p.supplier_uid || 'N/A'} • Due: ${p.due_date || 'N/A'}`;
        document.getElementById('modalSupNote').textContent = p.payment_note ? `Note: "${p.payment_note}"` : '';
        
        const b = document.getElementById('modalSupStatus');
        const isPaid = p.payment_status === 'Paid';
        b.textContent = p.payment_status || 'Paid';
        b.style.background = isPaid ? 'var(--clr-primary-lt)' : 'var(--clr-danger-lt)';
        b.style.color = isPaid ? 'var(--clr-primary)' : 'var(--clr-danger)';
    } else {
        srcEl.classList.remove('link');
        srcEl.onclick = null;
        supCell.style.display = 'none';
    }

    document.getElementById('modalAlert').textContent   = p.alert_level + ' units';

    // Status badge
    const badge = document.getElementById('modalStatusBadge');
    if (isLow) {
        badge.textContent = '⚠️ LOW STOCK';
        badge.style.cssText = 'background:var(--clr-danger-lt); color:var(--clr-danger); font-size:.68rem; font-weight:800; padding:4px 12px; border-radius:50px;';
    } else {
        badge.textContent = '✅ Healthy Stock';
        badge.style.cssText = 'background:var(--clr-primary-lt); color:var(--clr-primary); font-size:.68rem; font-weight:800; padding:4px 12px; border-radius:50px;';
    }

    // Notes
    const notesCell = document.getElementById('modalNotesCell');
    if (p.notes && p.notes.trim()) {
        document.getElementById('modalNotes').textContent = p.notes.trim();
        notesCell.style.display = 'block';
    } else {
        notesCell.style.display = 'none';
    }

    // --- Images ---
    const strip = document.getElementById('modalImgStrip');
    strip.innerHTML = `<div class="img-placeholder" style="font-size:1.6rem; color:var(--clr-muted);">⏳</div>`;
    document.getElementById('petModal').classList.add('open');
    document.body.style.overflow = 'hidden';

    try {
        const images = await DB.getPetImages(p.id);
        if (images && images.length > 0) {
            strip.innerHTML = images.map(src => `
                <img src="${src}" alt="${p.name}" loading="lazy" onclick="maximizeImage(this.src)" style="cursor:zoom-in;" />
            `).join('');
        } else {
            strip.innerHTML = `<div class="img-placeholder" style="font-size:1.6rem; color:var(--clr-muted);">📸</div>`;
        }
    } catch(e) {
        strip.innerHTML = `<div class="img-placeholder" style="font-size:1.6rem; color:var(--clr-muted);">📸</div>`;
    }
}

function closeModal() {
    document.getElementById('petModal').classList.remove('open');
    document.body.style.overflow = '';
}

function handleModalBgClick(e) {
    if (e.target === document.getElementById('petModal')) closeModal();
}

// Close modal on back gesture (Android)
window.addEventListener('popstate', closeModal);

/* ================ TOAST ================ */
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2400);
}

document.addEventListener('DOMContentLoaded', renderPets);
</script>
</body>
</html>
