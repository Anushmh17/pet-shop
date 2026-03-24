<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Add New Pet — Pet Shop Management" />
  <title>Add Pet — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
</head>
<body>

<!-- ===== TOP NAV ===== -->
<nav class="top-nav">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">Add New Pet</span>
  <div class="nav-spacer"></div>
</nav>

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper" style="padding-top: var(--sp-md);">

  <!-- Icon picker preview -->
  <div style="text-align:center; margin-bottom: var(--sp-lg);">
    <div id="petPreview" style="
      width: 80px; height: 80px;
      border-radius: 22px;
      background: var(--clr-primary-lt);
      font-size: 2.5rem;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto var(--sp-sm);
      border: 2.5px dashed var(--clr-primary);
      cursor: pointer;
      transition: var(--tr);
    " onclick="cycleIcon()" title="Tap to change icon">🐶</div>
    <p style="font-size:.75rem; font-weight:600; color:var(--clr-muted);">Tap to change icon</p>
  </div>

  <!-- Form card -->
  <div class="add-pet-form">

    <div class="form-group">
      <label class="form-label" for="petNameInput">Pet Name *</label>
      <input type="text" id="petNameInput" class="form-control" placeholder="e.g. Golden Retriever" autocomplete="off" required />
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm);">
      <div class="form-group">
        <label class="form-label" for="petCategory">Category *</label>
        <select id="petCategory" class="form-control" required>
          <option value="">— Select —</option>
          <option value="dog">Dog</option>
          <option value="cat">Cat</option>
          <option value="bird">Bird</option>
          <option value="rabbit">Rabbit</option>
          <option value="fish">Fish</option>
          <option value="reptile">Reptile</option>
          <option value="rodent">Rodent</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="petSource">Pet Source *</label>
        <select id="petSource" class="form-control" required>
          <option value="Dealer Supplied">Dealer Supplied</option>
          <option value="Customer Supplied">Customer Supplied</option>
        </select>
      </div>
    </div>

    <!-- Pet Type Selection -->
    <div class="form-group">
      <label class="form-label">Pet Type *</label>
      <div style="display:flex; gap: var(--sp-md); background: var(--clr-bg); padding:10px var(--sp-md); border-radius: var(--r-md); border: 1.5px solid var(--clr-border);">
        <label style="display:flex; align-items:center; gap:8px; font-weight:700; cursor:pointer; font-size:.92rem;">
          <input type="radio" name="petType" value="Single" checked onchange="togglePriceFields()" /> Single
        </label>
        <label style="display:flex; align-items:center; gap:8px; font-weight:700; cursor:pointer; font-size:.92rem;">
          <input type="radio" name="petType" value="Pair/Couple" onchange="togglePriceFields()" /> Pair / Couple
        </label>
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm);">
      <div class="form-group">
        <label class="form-label" for="petStock">Quantity *</label>
        <input type="number" id="petStock" class="form-control" min="0" value="1" placeholder="0" required />
      </div>
      <div class="form-group">
        <label class="form-label" for="petAlert">Stock Alert level</label>
        <input type="number" id="petAlert" class="form-control" min="0" value="10" placeholder="e.g. 10" />
      </div>
    </div>

    <!-- Pricing Section -->
    <div class="form-group" id="groupPriceSingle">
      <label class="form-label" for="petPriceSingle">Price for 1 Pet (Rs.) *</label>
      <input type="number" id="petPriceSingle" class="form-control" min="0" placeholder="0.00" step="0.01" />
    </div>

    <div id="groupPricePair" style="display:none; grid-template-columns:1fr 1fr; gap: var(--sp-sm); margin-bottom: var(--sp-md);">
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label" for="petPricePer">Price per Pet *</label>
        <input type="number" id="petPricePer" class="form-control" min="0" placeholder="0.00" step="0.01" oninput="calcTotalPrice()" />
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label" for="petTotalPrice">Total Price (2 Pets) *</label>
        <input type="number" id="petTotalPrice" class="form-control" min="0" placeholder="0.00" step="0.01" />
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="petCost">Cost Price (Rs.)</label>
      <input type="number" id="petCost" class="form-control" min="0" placeholder="0.00" step="0.01" />
    </div>

    <!-- Image Upload Improvements -->
    <div class="form-group">
      <label class="form-label">Upload Images (Multiple)</label>
      <div style="
        position:relative;
        border:2px dashed var(--clr-border);
        border-radius: var(--r-md);
        padding:20px;
        text-align:center;
        cursor:pointer;
        transition: var(--tr);
      " onclick="document.getElementById('imgUpload').click()" id="dropZone">
        <span style="font-size:1.4rem;">📷</span>
        <p style="font-size:.78rem; font-weight:700; color:var(--clr-muted); margin-top:5px;">Tap to select images</p>
        <input type="file" id="imgUpload" multiple accept="image/*" style="display:none;" onchange="handleImagePreviews(event)" />
      </div>
      <!-- Preview Container -->
      <div id="imgPreviewList" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:10px;"></div>
    </div>

    <div class="form-group">
      <label class="form-label" for="petNotes">Notes</label>
      <textarea id="petNotes" class="form-control" placeholder="Optional notes…" rows="3" style="resize:vertical;"></textarea>
    </div>

    <button class="btn btn-primary btn-full" id="submitPetBtn" onclick="savePet()">
      🐾 Save Pet to Inventory
    </button>

  </div>

</div><!-- /app-wrapper -->

<!-- Success modal -->
<div class="modal-overlay" id="successModal" role="dialog" aria-modal="true" aria-label="Pet added successfully">
  <div class="modal-box" style="text-align:center;">
    <div style="font-size:3rem; margin-bottom: var(--sp-sm);">🎉</div>
    <div class="modal-title" style="justify-content:center;">Inventory Updated!</div>
    <p style="color:var(--clr-muted); font-size:.9rem; margin-bottom: var(--sp-md);">
      <strong id="successName"></strong> has been added securely to your store.
    </p>
    <div class="modal-actions">
      <a href="index.php" class="btn btn-ghost" style="flex:1;">Go Home</a>
      <button class="btn btn-primary" style="flex:1;" onclick="resetForm()">Add Another</button>
    </div>
  </div>
</div>

<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
const ICONS = ['🐶','🐱','🦜','🐰','🐹','🐠','🐢','🦮','🐕','🦁','🐻','🦊','🐸'];
let iconIdx = 0;
let uploadedImages = [];

function cycleIcon() {
  iconIdx = (iconIdx + 1) % ICONS.length;
  const el = document.getElementById('petPreview');
  el.style.transform = 'scale(1.15)';
  el.textContent = ICONS[iconIdx];
  setTimeout(() => el.style.transform = '', 200);
}

function togglePriceFields() {
  const type = document.querySelector('input[name="petType"]:checked').value;
  const groupSingle = document.getElementById('groupPriceSingle');
  const groupPair   = document.getElementById('groupPricePair');

  if (type === 'Single') {
    groupSingle.style.display = 'block';
    groupPair.style.display   = 'none';
  } else {
    groupSingle.style.display = 'none';
    groupPair.style.display   = 'grid';
  }
}

function calcTotalPrice() {
  const per = parseFloat(document.getElementById('petPricePer').value) || 0;
  document.getElementById('petTotalPrice').value = (per * 2).toFixed(2);
}

function handleImagePreviews(e) {
  const files = e.target.files;
  const preview = document.getElementById('imgPreviewList');

  Array.from(files).forEach(file => {
    const reader = new FileReader();
    reader.onload = (rele) => {
      const id = Date.now() + Math.random();
      const div = document.createElement('div');
      div.style.cssText = 'position:relative; width: 68px; height: 68px; border-radius: 10px; overflow: hidden; border: 1.5px solid var(--clr-border);';
      div.id = 'img-' + id;
      div.innerHTML = `
        <img src="${rele.target.result}" style="width:100%; height:100%; object-fit:cover;" />
        <button onclick="removeImg('${id}')" style="position:absolute; top:2px; right:2px; background:rgba(0,0,0,0.5); color:#fff; border-radius:50%; width:18px; height:18px; font-size:10px; display:flex; align-items:center; justify-content:center;">✕</button>
      `;
      preview.appendChild(div);
      uploadedImages.push({ id, data: reader.result });
    };
    reader.readAsDataURL(file);
  });
}

function removeImg(id) {
  const el = document.getElementById('img-' + id);
  if (el) el.remove();
  uploadedImages = uploadedImages.filter(img => img.id !== id);
}

async function savePet() {
  const name     = document.getElementById('petNameInput').value.trim();
  const category = document.getElementById('petCategory').value;
  const source   = document.getElementById('petSource').value;
  const type     = document.querySelector('input[name="petType"]:checked').value;
  const qty      = parseInt(document.getElementById('petStock').value) || 0;
  const alertLvl = parseInt(document.getElementById('petAlert').value) || 10;
  const cost     = parseFloat(document.getElementById('petCost').value) || 0;
  const notes    = document.getElementById('petNotes').value.trim();

  let price = 0;
  if(type === 'Single') {
    price = parseFloat(document.getElementById('petPriceSingle').value) || 0;
  } else {
    price = parseFloat(document.getElementById('petTotalPrice').value) || 0;
  }

  // --- Validation ---
  if (!name) { showToast('Pet name is required'); return; }
  if (!category) { showToast('Select a category'); return; }
  if (qty <= 0) { showToast('Enter a valid quantity'); return; }
  if (price <= 0) { showToast('Enter a valid price'); return; }

  const newPet = {
    name, category, source, type, qty, price, cost, alertLevel: alertLvl,
    notes,
    icon: document.getElementById('petPreview').textContent,
    images: uploadedImages.map(img => img.data),
    stopAlert: false
  };

  const btn = document.getElementById('submitPetBtn');
  btn.disabled = true;
  btn.textContent = '⏳ Saving…';

  const res = await DB.addPet(newPet);
  
  if (res.error) {
    showToast('Error saving pet. Check database.');
    btn.disabled = false;
    btn.textContent = '🐾 Save Pet to Inventory';
    return;
  }

  setTimeout(() => {
    document.getElementById('successName').textContent = name;
    document.getElementById('successModal').classList.add('open');
    btn.disabled = false;
    btn.textContent = '🐾 Save Pet to Inventory';
  }, 400);
}

function resetForm() {
  document.getElementById('successModal').classList.remove('open');
  document.querySelectorAll('input:not([type="radio"])').forEach(i => i.value = '');
  document.getElementById('petCategory').value = '';
  document.getElementById('petSource').value = 'Dealer Supplied';
  document.getElementById('petStock').value = '1';
  document.getElementById('petAlert').value = '10';
  document.getElementById('imgPreviewList').innerHTML = '';
  uploadedImages = [];
  document.getElementById('petPreview').textContent = ICONS[0];
  iconIdx = 0;
  togglePriceFields();
  document.getElementById('petNameInput').focus();
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
