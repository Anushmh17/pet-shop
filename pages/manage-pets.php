<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Manage Pets Inventory — Pet Shop Management" />
  <title>Manage Pets — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
</head>
<body id="page-body">

<div id="ptr-indicator"><div class="ptr-spinner"></div></div>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav" style="position:sticky; top:0; z-index:1000; background:#fff; border-bottom:1px solid var(--clr-border);">
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

<div class="toast" id="toast"></div>

<script>
let startY = 0, distY = 0, pulling = false;
const cnt = document.getElementById('content-wrapper');

// --- PULL TO REFRESH LOGIC ---
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

// --- CORE APP LOGIC ---
async function renderPets() {
    const list = document.getElementById('petsListGrid');
    const pets = await DB.getPets();
    
    document.getElementById('totalStockCount').textContent = pets.reduce((s, p) => s + parseInt(p.qty), 0);

    if (pets.length === 0) {
        list.innerHTML = '';
        document.getElementById('emptyPets').style.display = 'block';
        return;
    }

    document.getElementById('emptyPets').style.display = 'none';
    
    let lowStockCount = 0;
    list.innerHTML = pets.map(p => {
        const isLow = parseInt(p.qty) <= parseInt(p.alert_level);
        if(isLow && !p.stop_alert) lowStockCount++;
        const statusColor = isLow ? 'var(--clr-danger)' : 'var(--clr-primary)';
        const statusLabel = isLow ? 'LOW STOCK' : 'HEALTHY';
        
        return `
          <div class="stat-card" style="display:flex; align-items:center; gap:15px; padding:15px;">
            <div style="font-size:2.2rem; background:var(--clr-primary-lt); width:60px; height:60px; display:flex; align-items:center; justify-content:center; border-radius:15px;">${p.icon || '🐾'}</div>
            <div style="flex:1;">
               <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                   <div style="font-weight:800; font-size:1rem; color:var(--clr-text); line-height:1.2;">${p.name}</div>
                   <div style="font-size:.65rem; font-weight:800; color:${statusColor}; background:${isLow ? 'var(--clr-danger-lt)' : 'var(--clr-primary-lt)'}; padding:2px 8px; border-radius:50px;">${statusLabel}</div>
               </div>
               <div style="font-size:.7rem; font-weight:700; color:var(--clr-muted); margin-top:3px;">${p.category.toUpperCase()} • ${p.pet_variety || 'Regular'}</div>
               <div style="display:flex; align-items:center; gap:10px; margin-top:8px;">
                   <div style="font-size:.85rem; font-weight:800; color:var(--clr-text);">Qty: ${p.qty}</div>
                   <div style="font-size:.85rem; font-weight:800; color:var(--clr-primary);">Rs. ${parseFloat(p.price).toLocaleString()}</div>
               </div>
            </div>
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

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2400);
}

document.addEventListener('DOMContentLoaded', renderPets);
</script>
</body>
</html>
