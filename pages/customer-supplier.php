<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="Customer Supplier Profile — Pet Shop Management" />
  <title>Supplier Profile — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <style>
    .profile-hero {
      background: linear-gradient(135deg, var(--clr-primary) 0%, #4a8a5c 100%);
      padding: 30px 20px 40px;
      text-align: center;
      color: #fff;
      border-radius: 0 0 30px 30px;
      margin-bottom: 25px;
      box-shadow: 0 10px 30px rgba(92,158,110,0.2);
    }
    .profile-avatar {
      width: 100px; height: 100px;
      background: rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 3rem; margin: 0 auto 15px;
      border: 3px solid rgba(255,255,255,0.4);
    }
    .profile-name { font-size: 1.6rem; font-weight: 800; margin-bottom: 5px; }
    .profile-nic { font-size: 0.9rem; font-weight: 700; opacity: 0.9; letter-spacing: 0.5px; }

    .info-section {
      background: var(--clr-surface);
      border-radius: var(--r-xl);
      padding: 24px 20px;
      margin-bottom: 20px;
      border: 1.5px solid var(--clr-border);
      box-shadow: var(--shadow-sm);
    }
    .info-row { display: flex; flex-direction: column; gap: 5px; margin-bottom: 20px; }
    .info-row:last-child { margin-bottom: 0; }
    .info-label { font-size: 0.72rem; font-weight: 800; color: var(--clr-muted); text-transform: uppercase; letter-spacing: 0.8px; }
    .info-value { font-size: 1.05rem; font-weight: 700; color: var(--clr-text); line-height: 1.4; }

    .nic-preview {
      width: 100%; border-radius: 15px;
      border: 2.5px solid var(--clr-border);
      margin-top: 10px;
      box-shadow: var(--shadow-sm);
    }

    .stat-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--clr-primary-lt);
      color: var(--clr-primary);
      padding: 8px 16px;
      border-radius: 50px;
      font-weight: 800;
      font-size: 0.95rem;
      margin-top: 5px;
    }

    .desc-box {
      background: var(--clr-bg);
      border-radius: var(--r-md);
      padding: 15px;
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--clr-muted);
      line-height: 1.6;
      border-left: 4px solid var(--clr-primary);
    }

    #backBtn { position: absolute; top: 20px; left: 15px; color: #fff; text-decoration: none; font-size: 1.5rem; }
  </style>
</head>
<body id="page-body">

<div id="content-wrapper">
  
  <div class="profile-hero">
    <a href="#" onclick="history.back(); return false;" id="backBtn">&#8592;</a>
    <div class="profile-avatar">👤</div>
    <div class="profile-name" id="pName">Loading...</div>
    <div class="profile-nic" id="pNIC">—</div>
  </div>

  <div class="app-wrapper">
    
    <!-- Contact & Address -->
    <div class="info-section">
      <div class="info-row">
        <div class="info-label">🏠 Residential Address</div>
        <div class="info-value" id="pAddress">—</div>
      </div>
    </div>

    <!-- Financials -->
    <div class="info-section">
      <div class="info-row">
        <div class="info-label">💰 Transaction Amount (Paid to Customer)</div>
        <div class="stat-pill">Rs. <span id="pCost">0.00</span></div>
      </div>
    </div>

    <!-- NIC Proof -->
    <div class="info-section" id="nicSection" style="display:none;">
      <div class="info-label">🪪 NIC Verification Photo</div>
      <img src="" id="pNICPhoto" class="nic-preview" alt="NIC Photo" />
    </div>

    <!-- Description -->
    <div class="info-section" id="descSection" style="display:none;">
      <div class="info-row">
        <div class="info-label">📝 Description / Remarks</div>
        <div class="desc-box" id="pDesc"></div>
      </div>
    </div>

    <div style="height:40px;"></div>
  </div>

</div>

<div class="toast" id="toast"></div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const petId = urlParams.get('pet_id');

  if (!petId) {
    showToast('Missing Pet ID!');
    setTimeout(() => history.back(), 1500);
    return;
  }

  try {
    const data = await DB.getCustomerSupplier(petId);
    if (!data) {
      document.getElementById('pName').textContent = 'Profile Not Found';
      return;
    }

    // Fetch pet details for the "pet photo" and name
    const pets = await DB.getPets();
    const pet  = pets.find(x => parseInt(x.id) === parseInt(petId));
    const imgs = await DB.getPetImages(petId);

    document.getElementById('pName').textContent = data.full_name;
    document.getElementById('pNIC').textContent  = 'NIC: ' + data.nic;
    document.getElementById('pAddress').textContent = data.address || 'No address provided';
    document.getElementById('pCost').textContent = parseFloat(data.cost_paid).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    
    if (data.nic_photo) {
      document.getElementById('pNICPhoto').src = data.nic_photo;
      document.getElementById('nicSection').style.display = 'block';
    }

    if (data.description && data.description.trim()) {
      document.getElementById('pDesc').textContent = data.description;
      document.getElementById('descSection').style.display = 'block';
    }

    // Display pet info
    if (pet) {
      const petWrap = document.createElement('div');
      petWrap.className = 'info-section';
      petWrap.innerHTML = `
        <div class="info-label">🐾 Supplied Pet</div>
        <div style="display:flex; align-items:center; gap:15px; margin-top:12px;">
          <div style="width:70px; height:70px; border-radius:12px; overflow:hidden; border:2.5px solid var(--clr-border); flex-shrink:0;">
            ${imgs && imgs.length > 0 ? `<img src="${imgs[0]}" style="width:100%; height:100%; object-fit:cover;" />` : `<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--clr-bg); font-size:2rem;">${pet.icon||'🐾'}</div>`}
          </div>
          <div>
            <div style="font-size:1.1rem; font-weight:800; color:var(--clr-text);">${pet.name}</div>
            <div style="font-size:0.75rem; font-weight:700; color:var(--clr-muted); margin-top:2px;">${pet.pet_variety || (pet.category.charAt(0).toUpperCase() + pet.category.slice(1))}</div>
          </div>
        </div>
      `;
      document.querySelector('.app-wrapper').prepend(petWrap);
    }

  } catch (e) {
    showToast('Failed to load supplier profile.');
  }
});

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}
</script>
</body>
</html>
