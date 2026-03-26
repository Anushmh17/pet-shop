<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Pet Categories — Pet Shop Management" />
  <title>Categories — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
  <style>
    /* ---- Breadcrumb ---- */
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: .75rem;
      font-weight: 700;
      color: var(--clr-muted);
      margin-bottom: var(--sp-md);
      flex-wrap: wrap;
    }
    .breadcrumb span { cursor: pointer; transition: color .15s; }
    .breadcrumb span:hover { color: var(--clr-primary); }
    .breadcrumb span.active { color: var(--clr-text); font-weight: 800; cursor: default; }
    .breadcrumb .sep { color: var(--clr-border); }

    /* ---- Category Grid (Level 1) ---- */
    .cat-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-sm);
    }
    .cat-big-card {
      background: var(--clr-surface);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-sm);
      padding: 22px 14px 18px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: transform .15s, box-shadow .15s;
      -webkit-tap-highlight-color: transparent;
      position: relative;
    }
    .cat-big-card:active { transform: scale(.95); box-shadow: var(--shadow-md); }
    .cat-big-card .cbc-emoji { font-size: 2.2rem; line-height: 1; }
    .cat-big-card .cbc-name  { font-size: .88rem; font-weight: 800; color: var(--clr-text); }
    .cat-big-card .cbc-count { font-size: .68rem; font-weight: 700; color: var(--clr-muted); }
    .cat-big-card .cbc-dot {
      position: absolute; top: 10px; right: 10px;
      width: 8px; height: 8px; border-radius: 50%;
    }
    .cbc-dot.has-low { background: var(--clr-danger); }
    .cbc-dot.all-ok  { background: var(--clr-primary); }

    /* ---- Pet List (Level 2) ---- */
    .pet-list-item {
      display: flex;
      align-items: center;
      gap: 14px;
      background: var(--clr-surface);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-sm);
      padding: 14px 16px;
      margin-bottom: var(--sp-xs);
      cursor: pointer;
      transition: transform .15s;
      -webkit-tap-highlight-color: transparent;
    }
    .pet-list-item:active { transform: scale(.97); }
    .pli-icon {
      width: 52px; height: 52px; border-radius: 13px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.7rem; flex-shrink: 0;
    }
    .pli-name    { font-size: .95rem; font-weight: 800; color: var(--clr-text); }
    .pli-variety { font-size: .72rem; font-weight: 700; color: var(--clr-muted); margin-top: 2px; }
    .pli-price   { font-size: .82rem; font-weight: 800; color: var(--clr-primary); margin-top: 4px; }
    .pli-badge {
      font-size: .58rem; font-weight: 800; padding: 2px 8px; border-radius: 50px; flex-shrink: 0;
    }
    .badge-low { background: var(--clr-danger-lt); color: var(--clr-danger); }
    .badge-ok  { background: var(--clr-primary-lt); color: var(--clr-primary); }

    /* ---- Detail Modal ---- */
    #petModal {
      display: none;
      position: fixed; inset: 0; z-index: 500;
      background: rgba(0,0,0,.45);
      align-items: flex-end; justify-content: center;
    }
    #petModal.open { display: flex; }
    #petModalBox {
      background: var(--clr-surface);
      border-radius: 24px 24px 0 0;
      width: 100%; max-width: 520px;
      max-height: 90vh; overflow-y: auto;
      padding: 20px 18px 48px;
      animation: modalIn .28s cubic-bezier(.4,0,.2,1) both;
      position: relative;
    }
    .modal-handle {
      width: 40px; height: 4px; background: var(--clr-border);
      border-radius: 4px; margin: 0 auto 18px;
    }
    #modalCloseBtn {
      position: absolute; top: 18px; left: 18px;
      background: var(--clr-bg); border: none; border-radius: 50%;
      width: 36px; height: 36px; font-size: 1.1rem;
      cursor: pointer; display: flex; align-items: center; justify-content: center;
      z-index: 10;
      color: var(--clr-muted); font-weight: 800;
    }
    .img-strip {
      display: flex; gap: 10px; overflow-x: auto;
      padding-bottom: 8px; margin-bottom: 16px; scrollbar-width: none;
    }
    .img-strip::-webkit-scrollbar { display: none; }
    .img-strip img {
      width: 100px; height: 100px; object-fit: cover;
      border-radius: 14px; border: 2px solid var(--clr-border); flex-shrink: 0;
    }
    .img-placeholder {
      width: 100px; height: 100px; border-radius: 14px;
      border: 2px dashed var(--clr-border); flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      font-size: 2.6rem; background: var(--clr-bg);
    }
    .supplier-banner {
      display: flex; align-items: center; gap: 12px;
      background: var(--clr-primary-lt);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-md);
      padding: 12px 14px; margin-bottom: 12px;
    }
    .supplier-banner .sb-icon { font-size: 1.6rem; }
    .supplier-banner .sb-label { font-size: .66rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase; letter-spacing: .5px; }
    .supplier-banner .sb-value { font-size: .92rem; font-weight: 800; color: var(--clr-primary); cursor: default; }
    .supplier-banner .sb-value.link { text-decoration: underline; cursor: pointer; }
    .det-grid {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 8px; margin: 12px 0 8px;
    }
    .det-cell { background: var(--clr-bg); border-radius: var(--r-md); padding: 10px 12px; }
    .det-cell .dl { font-size: .63rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
    .det-cell .dv { font-size: .92rem; font-weight: 800; color: var(--clr-text); }
    .det-cell.accent .dv { color: var(--clr-primary); }
    .det-cell.wide { grid-column: 1 / -1; }
    .notes-box {
      background: var(--clr-bg); border-radius: var(--r-md);
      padding: 10px 12px; font-size: .82rem; font-weight: 600;
      color: var(--clr-muted); line-height: 1.5; margin-top: 3px;
    }

    /* Page transition animation */
    .view { animation: fadeSlideIn .22s ease both; }
    @keyframes fadeSlideIn {
      from { opacity: 0; transform: translateX(20px); }
      to   { opacity: 1; transform: translateX(0); }
    }
  </style>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav" style="position:sticky; top:0; z-index:1000; border-bottom:1px solid var(--clr-border);">
  <button class="nav-back" id="backBtn" onclick="goBack()" aria-label="Go back">&#8592;</button>
  <span class="nav-title" id="navTitle">Categories</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div id="content-wrapper">
<div class="app-wrapper" style="padding-top: var(--sp-md);">

  <!-- Breadcrumb -->
  <div class="breadcrumb" id="breadcrumb">
    <span class="active">All Categories</span>
  </div>

  <!-- Dynamic content area -->
  <div id="mainView">
    <!-- Injected by JS -->
  </div>

</div><!-- /app-wrapper -->
</div><!-- /content-wrapper -->

<!-- ===== PET DETAIL MODAL ===== -->
<div id="petModal" onclick="handleModalBg(event)">
  <div id="petModalBox">
    <div class="modal-handle"></div>
    <button id="modalCloseBtn" onclick="closeModal()">✕</button>

    <!-- Header Row: Image Visual (Left) + Identity Text (Right) -->
    <div style="display: flex; gap: 18px; align-items: flex-start; margin-bottom: 22px; padding-top: 12px; position: relative; z-index: 5;">
      <!-- Image Gallery -->
      <div id="modalImgStrip" style="display: flex; gap: 10px; overflow-x: auto; flex-shrink: 0; width: 120px; scrollbar-width: none;">
        <!-- Images injected by JS -->
      </div>
      
      <!-- Identity Metadata -->
      <div style="flex: 1; padding-top: 2px;">
        <div class="modal-pet-name" id="modalName" style="font-size: 1.55rem; letter-spacing: -0.4px; line-height: 1.1; margin-bottom: 2px; font-weight: 800; color: var(--clr-text);"></div>
        <div class="modal-pet-sub" id="modalSub"  style="font-size: .8rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase;"></div>
        <div id="modalStatus" style="margin-top: 6px;"></div>
      </div>
    </div>

    <div class="supplier-banner" id="supplierBanner" style="display:none;">
      <div class="sb-icon">🚚</div>
      <div style="flex:1;">
        <div class="sb-label" id="sbLabel">Supplied by</div>
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <div class="sb-value" id="modalSupplier">—</div>
          <span id="sbStatusBadge" style="font-size:.6rem; padding:2px 8px; border-radius:10px; font-weight:800;"></span>
        </div>
        <div id="sbSub" style="font-size:.62rem; color:var(--clr-muted); font-weight:700; margin-top:2px;"></div>
      </div>
    </div>

    <!-- Details -->
    <div class="det-grid">
      <div class="det-cell accent">
        <div class="dl">💰 Sell Price</div>
        <div class="dv" id="modalPrice"></div>
      </div>
      <div class="det-cell">
        <div class="dl">📦 Stock</div>
        <div class="dv" id="modalQty"></div>
      </div>
      <div class="det-cell">
        <div class="dl">🏷 Cost Price</div>
        <div class="dv" id="modalCost"></div>
      </div>
      <div class="det-cell">
        <div class="dl">🔢 Pet Type</div>
        <div class="dv" id="modalType"></div>
      </div>
      <div class="det-cell">
        <div class="dl">⚠️ Alert Level</div>
        <div class="dv" id="modalAlert"></div>
      </div>
      <div class="det-cell">
        <div class="dl">📅 Added</div>
        <div class="dv" id="modalDate"></div>
      </div>
      <div class="det-cell wide" id="modalNotesCell" style="display:none;">
        <div class="dl" style="margin-bottom:5px;">📝 Notes</div>
        <div class="notes-box" id="modalNotes"></div>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
/* ============================================================
   CONFIG
   ============================================================ */
const CATEGORIES = [
  { key: 'dog',     emoji: '🐶', label: 'Dogs'     },
  { key: 'cat',     emoji: '🐱', label: 'Cats'     },
  { key: 'bird',    emoji: '🦜', label: 'Birds'    },
  { key: 'fish',    emoji: '🐠', label: 'Fish'     },
  { key: 'rabbit',  emoji: '🐰', label: 'Rabbits'  },
  { key: 'reptile', emoji: '🦎', label: 'Reptiles' },
  { key: 'rodent',  emoji: '🐹', label: 'Rodents'  },
  { key: 'other',   emoji: '🐾', label: 'Other'    },
];
const CAT_COLORS = {
  dog:'#5c9e6e', cat:'#f0a047', bird:'#4a90e2', fish:'#9b59b6',
  rabbit:'#e67e22', reptile:'#8e44ad', rodent:'#16a085', other:'#95a5a6'
};
const CAT_MAP = {};
CATEGORIES.forEach(c => CAT_MAP[c.key] = c);

/* ============================================================
   STATE
   ============================================================ */
let _allPets     = [];
let _currentView = 'categories'; // 'categories' | 'pets'
let _currentCat  = null;

/* ============================================================
   INIT
   ============================================================ */
document.addEventListener('DOMContentLoaded', async () => {
  _allPets = await DB.getPets();
  showCategories();
});

/* ============================================================
   PULL-TO-REFRESH — refreshes category counts and stock dots
   ============================================================ */
let _catPtrStart = 0, _catPtrDist = 0, _catPulling = false;
window.addEventListener('touchstart', e => { if(window.scrollY === 0){ _catPtrStart = e.touches[0].pageY; _catPulling = false; } }, {passive:true});
window.addEventListener('touchmove', e => {
  _catPtrDist = (e.touches[0].pageY - _catPtrStart) * 0.4;
  if(_catPtrDist > 0 && window.scrollY === 0){
    _catPulling = true;
    document.body.classList.add('ptr-pulling');
    document.getElementById('content-wrapper').style.transform = `translateY(${Math.min(_catPtrDist, 80)}px)`;
  }
}, {passive:true});
window.addEventListener('touchend', async () => {
  if(_catPulling && _catPtrDist >= 60){
    document.body.classList.remove('ptr-pulling');
    document.body.classList.add('ptr-loading');
    document.getElementById('content-wrapper').style.transform = 'translateY(40px)';
    _allPets = await DB.getPets();
    if (_currentView === 'pets' && _currentCat) showPets(_currentCat);
    else showCategories();
    setTimeout(() => { document.body.classList.remove('ptr-loading'); document.getElementById('content-wrapper').style.transform = ''; }, 500);
  } else {
    document.body.classList.remove('ptr-pulling', 'ptr-loading');
    document.getElementById('content-wrapper').style.transform = '';
  }
  _catPulling = false; _catPtrDist = 0;
}, {passive:true});

/* ============================================================
   NAV BACK
   ============================================================ */
function goBack() {
  if (_currentView === 'pets') {
    showCategories();
  } else {
    window.location.href = 'index.php';
  }
}

/* ============================================================
   LEVEL 1 — CATEGORIES
   ============================================================ */
function showCategories() {
  _currentView = 'categories';
  _currentCat  = null;
  document.getElementById('navTitle').textContent    = 'Categories';
  document.getElementById('breadcrumb').innerHTML    = `<span class="active">All Categories</span>`;

  // Only show categories that have pets
  const present = {};
  _allPets.forEach(p => {
    const k = (p.category || 'other').toLowerCase();
    if (!present[k]) present[k] = { total: 0, hasLow: false };
    present[k].total++;
    if (parseInt(p.qty) <= parseInt(p.alert_level) && !p.stop_alert) present[k].hasLow = true;
  });

  const visible = CATEGORIES.filter(c => present[c.key]);

  if (visible.length === 0) {
    document.getElementById('mainView').innerHTML = `
      <div class="empty-state" style="padding:60px 0;">
        <div class="empty-icon">🏠</div>
        <p>No pets in inventory yet.</p>
        <a href="add-pet.php" class="btn btn-primary btn-sm mt-md">Add First Pet</a>
      </div>`;
    return;
  }

  document.getElementById('mainView').innerHTML = `
    <div class="cat-grid view">
      ${visible.map(c => {
        const info  = present[c.key];
        const color = CAT_COLORS[c.key] || CAT_COLORS.other;
        return `
          <div class="cat-big-card" onclick="showPets('${c.key}')" style="border-top: 4px solid ${color};">
            <div class="cbc-dot ${info.hasLow ? 'has-low' : 'all-ok'}"></div>
            <div class="cbc-emoji">${c.emoji}</div>
            <div class="cbc-name">${c.label}</div>
            <div class="cbc-count">${info.total} pet${info.total > 1 ? 's' : ''}</div>
          </div>`;
      }).join('')}
    </div>`;
}

/* ============================================================
   LEVEL 2 — PETS IN CATEGORY
   ============================================================ */
function showPets(catKey) {
  _currentView = 'pets';
  _currentCat  = catKey;
  const catInfo  = CAT_MAP[catKey] || { emoji: '🐾', label: catKey };
  const color    = CAT_COLORS[catKey] || CAT_COLORS.other;
  const filtered = _allPets.filter(p => (p.category || 'other').toLowerCase() === catKey);

  document.getElementById('navTitle').textContent = catInfo.label;
  document.getElementById('breadcrumb').innerHTML = `
    <span onclick="showCategories()">All Categories</span>
    <span class="sep">›</span>
    <span class="active">${catInfo.label}</span>`;

  if (filtered.length === 0) {
    document.getElementById('mainView').innerHTML = `
      <div class="empty-state" style="padding:60px 0;">
        <div class="empty-icon">${catInfo.emoji}</div>
        <p>No pets in this category.</p>
      </div>`;
    return;
  }

  document.getElementById('mainView').innerHTML = `
    <div class="view" style="display:flex; flex-direction:column; gap:10px; margin-top:10px;">
      ${filtered.map(p => {
        const isLow = parseInt(p.qty) <= parseInt(p.alert_level);
        const imgHtml = p.primaryImage 
            ? `<img src="${p.primaryImage}" onclick="event.stopPropagation(); maximizeImage(this.src)" style="width:100%; height:100%; object-fit:cover; cursor:zoom-in;" />`
            : `<span style="font-size:1rem; color:var(--clr-muted); opacity:0.8;">📸</span>`;

        return `
          <div class="pet-list-item" onclick="openModal(${p.id})" style="display:flex; align-items:center; gap:14px; background:var(--clr-surface); border-radius:14px; border:1.5px solid var(--clr-border); padding:12px 14px; box-shadow:var(--shadow-sm); cursor:pointer; transition:transform .1s;">
            
            <div style="width:48px; height:48px; border-radius:12px; background:var(--clr-bg); border:1.5px solid var(--clr-border); display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
              ${imgHtml}
            </div>

            <div style="flex:1; min-width:0;">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="pli-name" style="font-weight:800; font-size:1.02rem; color:var(--clr-text); line-height:1.2;">${p.name}</div>
                <span class="pli-badge ${isLow ? 'badge-low' : 'badge-ok'}" style="font-size:.62rem; font-weight:800; padding:2px 8px; border-radius:50px;">${isLow ? 'LOW' : 'OK'}</span>
              </div>
              <div class="pli-variety" style="font-size:.78rem; font-weight:700; color:var(--clr-muted); margin-top:3px;">${p.pet_variety || catInfo.label}</div>
              <div class="pli-price" style="font-weight:800; color:var(--clr-text); font-size:1rem; margin-top:4px;">Rs. ${parseFloat(p.price).toLocaleString('en-IN')}</div>
            </div>
            <div style="color:var(--clr-border); font-size:1.1rem; flex-shrink:0;">›</div>
          </div>`;
      }).join('')}
    </div>`;
}

/* ============================================================
   DETAIL MODAL
   ============================================================ */
async function openModal(petId) {
  const p = _allPets.find(x => parseInt(x.id) === parseInt(petId));
  if (!p) return;

  const isLow = parseInt(p.qty) <= parseInt(p.alert_level);

  document.getElementById('modalName').textContent    = p.name;
  document.getElementById('modalSub').textContent     = (p.category || '').charAt(0).toUpperCase() + (p.category || '').slice(1) + (p.pet_variety ? ' · ' + p.pet_variety : '');
  
  const supEl = document.getElementById('modalSupplier');
  const banner = document.getElementById('supplierBanner');
  const status = document.getElementById('sbStatusBadge');
  const sub    = document.getElementById('sbSub');

  if (p.source === 'Customer Supplied') {
    banner.style.display = 'flex';
    supEl.textContent = p.supplier_name || 'Individual';
    supEl.classList.add('link');
    supEl.onclick = () => window.location.href = `customer-supplier.php?pet_id=${p.id}`;
    
    sub.textContent = `ID: ${p.supplier_uid || 'N/A'} • Due: ${p.due_date || 'N/A'}`;
    const pStat = p.payment_status || 'Paid';
    const isPaid = pStat === 'Paid';
    status.textContent = pStat;
    status.style.background = isPaid ? 'var(--clr-primary-lt)' : 'var(--clr-danger-lt)';
    status.style.color = isPaid ? 'var(--clr-primary)' : 'var(--clr-danger)';
  } else {
    banner.style.display = p.source ? 'flex' : 'none';
    supEl.textContent = p.source || 'Dealer Supplied';
    supEl.classList.remove('link');
    supEl.onclick = null;
    status.textContent = '';
    sub.textContent = '';
  }

  document.getElementById('modalPrice').textContent   = 'Rs. ' + parseFloat(p.price).toLocaleString('en-IN');
  document.getElementById('modalQty').textContent     = p.qty + ' units';
  document.getElementById('modalCost').textContent    = parseFloat(p.cost) > 0 ? 'Rs. ' + parseFloat(p.cost).toLocaleString('en-IN') : '—';
  document.getElementById('modalType').textContent    = p.type || '—';
  document.getElementById('modalAlert').textContent   = (p.alert_level || '—') + ' units';

  const added = p.created_at
    ? new Date(p.created_at).toLocaleDateString('en-IN', {day:'numeric', month:'short', year:'numeric'})
    : '—';
  document.getElementById('modalDate').textContent = added;

  const sb = document.getElementById('modalStatus');
  if (isLow) {
    sb.textContent = '⚠️ Low Stock';
    sb.style.cssText = 'background:var(--clr-danger-lt); color:var(--clr-danger); font-size:.65rem; font-weight:800; padding:3px 10px; border-radius:50px; display:inline-block; margin-top:5px;';
  } else {
    sb.textContent = '✅ In Stock';
    sb.style.cssText = 'background:var(--clr-primary-lt); color:var(--clr-primary); font-size:.65rem; font-weight:800; padding:3px 10px; border-radius:50px; display:inline-block; margin-top:5px;';
  }

  const nc = document.getElementById('modalNotesCell');
  if (p.notes && p.notes.trim()) {
    document.getElementById('modalNotes').textContent = p.notes.trim();
    nc.style.display = 'block';
  } else { nc.style.display = 'none'; }

  // Show modal, load images async
  const strip = document.getElementById('modalImgStrip');
  strip.innerHTML = `<div class="img-placeholder" style="font-size:1.4rem; color:var(--clr-muted);">⏳</div>`;
  document.getElementById('petModal').classList.add('open');
  document.body.style.overflow = 'hidden';

    try {
      const imgs = await DB.getPetImages(p.id);
      if (imgs && imgs.length > 0) {
        strip.innerHTML = imgs.map(src => `<img src="${src}" alt="${p.name}" loading="lazy" onclick="maximizeImage(this.src)" style="cursor:zoom-in;" />`).join('');
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
function handleModalBg(e) {
  if (e.target === document.getElementById('petModal')) closeModal();
}
window.addEventListener('popstate', closeModal);

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}
</script>
</body>
</html>
