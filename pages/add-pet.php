<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Add New Pet — Pet Shop Management" />
  <title>Add Pet — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
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

  <!-- Header Section -->
  <div style="text-align:center; margin-bottom: var(--sp-lg);">
    <h2 class="section-title" style="margin-bottom: 5px;">Primary Specimen Data</h2>
    <p style="font-size:.75rem; font-weight:600; color:var(--clr-muted);">Ensure all mandatory fields (*) are completed</p>
  </div>

  <!-- Form card -->
  <div class="add-pet-form">

    <div class="form-group">
      <label class="form-label" for="petNameInput">Pet Name *</label>
      <input type="text" id="petNameInput" class="form-control" placeholder="e.g. Golden Retriever" autocomplete="off" required oninput="checkFormValidity()" />
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm);">
      <div class="form-group">
        <label class="form-label" for="petCategory">Category *</label>
        <select id="petCategory" class="form-control" required onchange="handleCategoryChange(); checkFormValidity();">
          <option value="">&mdash; Select &mdash;</option>
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
        <select id="petSource" class="form-control" required onchange="toggleCustomerFields(); syncPaymentStatus();">
          <option value="Dealer Supplied">Dealer Supplied</option>
          <option value="Customer Supplied">Customer Supplied</option>
        </select>
      </div>
    </div>

    <!-- Universal Pet Variety Field (shows for all categories) -->
    <div id="varietyGroup" class="form-group" style="display:none;">
      <label class="form-label" id="varietyLabel">Breed / Variety</label>
      <input type="text" id="petVariety" class="form-control" list="varietyList"
        placeholder="e.g. type a breed or variety" autocomplete="off" />
      <datalist id="varietyList">
        <!-- Populated by JS based on selected category -->
      </datalist>
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
        <label class="form-label" for="petStock">Batch Quantity *</label>
        <input type="number" id="petStock" class="form-control" min="1" value="1" placeholder="e.g. 10" required oninput="calcTotalPrice()" />
      </div>
      <div class="form-group">
        <label class="form-label" for="petAlert">Stock Alert level</label>
        <input type="number" id="petAlert" class="form-control" min="0" value="10" placeholder="e.g. 10" />
      </div>
    </div>

    <!-- Pricing Section -->
    <div class="form-group" id="groupPriceSingle">
      <label class="form-label" id="labelPriceSingle" for="petPriceSingle">Price per Pet (Rs.) *</label>
      <input type="number" id="petPriceSingle" class="form-control" min="0" placeholder="0.00" step="0.01" oninput="calcTotalPrice()" />
    </div>

    <div id="groupPricePair" style="display:none; grid-template-columns:1fr 1fr; gap: var(--sp-sm); margin-bottom: var(--sp-md);">
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label" for="petPricePer">Price per Pet *</label>
        <input type="number" id="petPricePer" class="form-control" min="0" placeholder="0.00" step="0.01" oninput="calcTotalPrice()" />
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label" for="petTotalPrice">Total Transaction (Rs.) *</label>
        <input type="number" id="petTotalPrice" class="form-control" min="0" placeholder="0.00" step="0.01" readonly style="background:var(--clr-bg); font-weight:800; color:var(--clr-primary);" />
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="petCost">Cost Price (Rs.)</label>
      <input type="number" id="petCost" class="form-control" min="0" placeholder="0.00" step="0.01" oninput="calcTotalPrice()" />
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

    <!-- ===== CUSTOMER SUPPLIER SECTION (shown when source = Customer) ===== -->
    <div id="customerSupplierSection" style="display:none;">
      <div style="display:flex; align-items:center; gap:10px; margin: var(--sp-md) 0 var(--sp-sm);">
        <div style="flex:1; height:1.5px; background:var(--clr-border);"></div>
        <span style="font-size:.72rem; font-weight:800; color:var(--clr-primary); text-transform:uppercase; letter-spacing:.6px; white-space:nowrap;">👤 Customer Supplier Details</span>
        <div style="flex:1; height:1.5px; background:var(--clr-border);"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="csUid">Supplier Transaction ID</label>
        <input type="text" id="csUid" class="form-control" readonly style="background:var(--clr-bg); font-family:monospace; font-weight:800; color:var(--clr-primary);" />
      </div>

      <div class="form-group">
        <label class="form-label" for="csName">Customer Full Name *</label>
        <input type="text" id="csName" class="form-control" placeholder="e.g. Arun Silva" autocomplete="off" oninput="checkFormValidity()" />
      </div>

      <div class="form-group">
        <label class="form-label" for="csNIC">NIC Number *</label>
        <input type="text" id="csNIC" class="form-control" placeholder="e.g. 199512345678" autocomplete="off" oninput="checkFormValidity()" />
      </div>

      <div class="form-group">
        <label class="form-label">NIC Photo</label>
        <div style="border:2px dashed var(--clr-border); border-radius:var(--r-md); padding:18px; text-align:center; cursor:pointer;" onclick="document.getElementById('csNICPhoto').click()" id="nicDropZone">
          <span style="font-size:1.3rem;">🪪</span>
          <p style="font-size:.76rem; font-weight:700; color:var(--clr-muted); margin-top:4px;">Tap to upload NIC photo</p>
          <input type="file" id="csNICPhoto" accept="image/*" style="display:none;" onchange="handleNICPhoto(event)" />
        </div>
        <div id="nicPhotoPreview" style="margin-top:8px;"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="csAddress">Address</label>
        <textarea id="csAddress" class="form-control" placeholder="Customer's home address…" rows="2" style="resize:vertical;"></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="csCostPaid">Amount Paid to Customer (Rs.) *</label>
        <input type="number" id="csCostPaid" class="form-control" min="0" placeholder="0.00" step="0.01" oninput="syncPaymentStatus()" />
      </div>

      <div class="form-group">
        <label class="form-label" for="csDescription">Description / Remarks</label>
        <textarea id="csDescription" class="form-control" placeholder="Any additional remarks about the transaction…" rows="2" style="resize:vertical;"></textarea>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm);">
        <div class="form-group">
          <label class="form-label" for="csPayStatus">Payment Status (Auto)</label>
          <select id="csPayStatus" class="form-control" disabled style="background:var(--clr-bg); font-weight:800;">
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
          </select>
        </div>
        <div class="form-group" id="csDueDateGroup" style="display:none;">
          <label class="form-label" for="csDueDate">Due Date *</label>
          <input type="date" id="csDueDate" class="form-control" />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="csPayNote">Payment Note (Optional)</label>
        <textarea id="csPayNote" class="form-control" placeholder="e.g. Will pay after 3 days / Check given" rows="2" style="resize:vertical;"></textarea>
      </div>
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
let uploadedImages = [];

function togglePriceFields() {
  const type = document.querySelector('input[name="petType"]:checked').value;
  const groupSingle = document.getElementById('groupPriceSingle');
  const groupPair   = document.getElementById('groupPricePair');
  const stockField  = document.getElementById('petStock');

  if (type === 'Single') {
    groupSingle.style.display = 'block';
    groupPair.style.display   = 'none';
  } else {
    groupSingle.style.display = 'none';
    groupPair.style.display   = 'grid';
    // If pair, ensure it's at least 2
    if(parseInt(stockField.value) < 2) stockField.value = 2;
  }
  calcTotalPrice();
}

function calcTotalPrice() {
  const type = document.querySelector('input[name="petType"]:checked').value;
  const qty = parseInt(document.getElementById('petStock').value) || 0;
  let pricePer = 0;
  
  if (type === 'Single') {
      pricePer = parseFloat(document.getElementById('petPriceSingle').value) || 0;
  } else {
      pricePer = parseFloat(document.getElementById('petPricePer').value) || 0;
  }

  const total = (pricePer * qty).toFixed(2);
  
  // Show total for both modes
  if (type === 'Pair/Couple') {
      document.getElementById('petTotalPrice').value = total;
  } else {
      // In single mode, total is just pricePer for 1, but we should show total for batch
      if(qty > 1) {
          document.getElementById('labelPriceSingle').textContent = 'Price per Pet (Total: ' + total + ' Rs.) *';
      } else {
          document.getElementById('labelPriceSingle').textContent = 'Price per Pet (Rs.) *';
      }
  }
  
  // High-level warnings
  const cost = parseFloat(document.getElementById('petCost').value) || 0;
  if(cost > total && total > 0) {
      document.getElementById('petCost').style.borderColor = 'var(--clr-danger)';
  } else {
      document.getElementById('petCost').style.borderColor = '';
  }

  syncPaymentStatus();
  checkFormValidity();
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

function syncPaymentStatus() {
    const source = document.getElementById('petSource').value;
    if (source !== 'Customer Supplied') return;

    // Payment status is based solely on csCostPaid (amount owed to the customer).
    // petCost is the shop's internal cost price and must NOT drive this logic.
    const paid = parseFloat(document.getElementById('csCostPaid').value) || 0;
    const statusEl = document.getElementById('csPayStatus');

    if (paid > 0) {
        statusEl.value = 'Paid';
    } else {
        statusEl.value = 'Pending';
    }
    toggleDueDate();
    checkFormValidity();
}

function checkFormValidity() {
    const name = document.getElementById('petNameInput').value.trim();
    const cat = document.getElementById('petCategory').value;
    const type = document.querySelector('input[name="petType"]:checked').value;
    const source = document.getElementById('petSource').value;
    const qty = parseInt(document.getElementById('petStock').value) || 0;
    
    let price = 0;
    if (type === 'Single') {
        price = parseFloat(document.getElementById('petPriceSingle').value) || 0;
    } else {
        price = parseFloat(document.getElementById('petTotalPrice').value) || 0;
    }

    // New validation rule: Pair must be even
    const qtyValid = (type === 'Pair/Couple') ? (qty >= 2 && qty % 2 === 0) : (qty >= 1);
    
    let isValid = (name !== '' && cat !== '' && qtyValid && price > 0);

    // Visual feedback for quantity
    document.getElementById('petStock').style.borderColor = qtyValid ? '' : 'var(--clr-danger)';

    if (source === 'Customer Supplied') {
        const csName = document.getElementById('csName').value.trim();
        const csNIC = document.getElementById('csNIC').value.trim();
        const csPaid = parseFloat(document.getElementById('csCostPaid').value) || 0;
        
        // NIC validation: 10 or 12 digits
        const nicValid = /^[0-9]{9}[vVxX]$|^[0-9]{12}$/.test(csNIC);
        
        if (csName === '' || !nicValid || csPaid <= 0) isValid = false;
        
        // Bonus: NIC field visual feedback
        document.getElementById('csNIC').style.borderColor = nicValid ? '' : 'var(--clr-danger)';
    }

    document.getElementById('submitPetBtn').disabled = !isValid;
    return isValid;
}

function removeImg(id) {
  const el = document.getElementById('img-' + id);
  if (el) el.remove();
  uploadedImages = uploadedImages.filter(img => img.id !== id);
}

let nicPhotoData = null;

function toggleCustomerFields() {
  const isCustomer = document.getElementById('petSource').value === 'Customer Supplied';
  const sec = document.getElementById('customerSupplierSection');
  sec.style.display = isCustomer ? 'block' : 'none';
  if (isCustomer && !document.getElementById('csUid').value) {
    document.getElementById('csUid').value = 'SUP-' + Date.now().toString().slice(-6) + '-' + Math.floor(Math.random() * 1000);
  }
}

function toggleDueDate() {
  const status = document.getElementById('csPayStatus').value;
  const dueDateEl = document.getElementById('csDueDate');
  document.getElementById('csDueDateGroup').style.display = (status === 'Pending') ? 'block' : 'none';
  // Enforce future-only due dates — past dates would immediately show as overdue on entry
  if (status === 'Pending') {
    dueDateEl.min = new Date().toLocaleDateString('en-CA');
  }
}

function handleNICPhoto(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = r => {
    nicPhotoData = r.target.result;
    document.getElementById('nicPhotoPreview').innerHTML = `
      <img src="${nicPhotoData}" style="width:100%; max-height:160px; object-fit:cover; border-radius:10px; border:1.5px solid var(--clr-border);" />`;
  };
  reader.readAsDataURL(file);
}

async function savePet() {
  const btn = document.getElementById('submitPetBtn');
  
  try {
      const name     = document.getElementById('petNameInput').value.trim();
      const category = document.getElementById('petCategory').value;
      const petVariety = document.getElementById('petVariety').value.trim();
      const source   = document.getElementById('petSource').value;
      const type     = document.querySelector('input[name="petType"]:checked').value;
      const qty      = parseInt(document.getElementById('petStock').value) || 0;
      const alertLvl = parseInt(document.getElementById('petAlert').value) || 10;
      const cost     = parseFloat(document.getElementById('petCost').value) || 0;
      const notes    = document.getElementById('petNotes').value.trim();

      let price = 0;
      if (type === 'Single') {
        price = parseFloat(document.getElementById('petPriceSingle').value) || 0;
      } else {
        price = parseFloat(document.getElementById('petTotalPrice').value) || 0;
      }

      // Validation
      if (!name)     { showToast('Pet name is required'); return; }
      if (!category) { showToast('Select a category');    return; }
      if (qty <= 0)  { showToast('Enter a valid quantity'); return; }
      if (price <= 0){ showToast('Enter a valid price');   return; }

      // Customer supplier validation
      if (source === 'Customer Supplied') {
        if (!document.getElementById('csName').value.trim()) { showToast('Enter customer name'); return; }
        if (!document.getElementById('csNIC').value.trim())  { showToast('Enter customer NIC');  return; }
        const csCost = parseFloat(document.getElementById('csCostPaid').value);
        if (!csCost || csCost <= 0) { showToast('Enter amount paid to customer'); return; }
      }

      const newPet = {
        name, category, source, type, qty, price, cost, alertLevel: alertLvl,
        notes, petVariety,
        icon: '',
        images: uploadedImages.map(img => img.data),
        stopAlert: false
      };

      // Add supplier details to pet table as well
      if (source === 'Customer Supplied') {
          newPet.supplierUid   = document.getElementById('csUid').value;
          newPet.supplierName  = document.getElementById('csName').value.trim();
          newPet.paymentStatus = document.getElementById('csPayStatus').value;
          newPet.dueDate       = document.getElementById('csPayStatus').value === 'Pending' ? document.getElementById('csDueDate').value : null;
          newPet.paymentNote   = document.getElementById('csPayNote').value.trim();
          newPet.cost          = parseFloat(document.getElementById('csCostPaid').value) || 0;
      }

      btn.disabled = true; btn.textContent = '⏳ Saving…';

      const res = await DB.addPet(newPet);

      if (!res || res.error) {
        showToast(res?.error || 'Error saving pet. Check connection.');
        btn.disabled = false; btn.textContent = '🐾 Save Pet to Inventory';
        return;
      }

      const petId = res.id;

      // Save customer supplier if applicable
      if (source === 'Customer Supplied' && petId) {
        await DB.saveCustomerSupplier({
          pet_id:      petId,
          full_name:   document.getElementById('csName').value.trim(),
          nic:         document.getElementById('csNIC').value.trim(),
          nic_photo:   nicPhotoData || null,
          address:     document.getElementById('csAddress').value.trim(),
          cost_paid:   parseFloat(document.getElementById('csCostPaid').value) || 0,
          description: document.getElementById('csDescription').value.trim(),
          supplier_uid: document.getElementById('csUid').value,
          payment_status: document.getElementById('csPayStatus').value,
          due_date:     document.getElementById('csPayStatus').value === 'Pending' ? document.getElementById('csDueDate').value : null,
          payment_note: document.getElementById('csPayNote').value.trim()
        });
      }

      setTimeout(() => {
        document.getElementById('successName').textContent = name;
        document.getElementById('successModal').classList.add('open');
        btn.disabled = false; btn.textContent = '🐾 Save Pet to Inventory';
      }, 400);

  } catch (err) {
      console.error(err);
      showToast('Critical Error: ' + err.message);
      btn.disabled = false; btn.textContent = '🐾 Save Pet to Inventory';
  }
}

/* ---- Universal Pet Variety Logic ---- */
const CATEGORY_DEFAULTS = {
    dog:     ['Labrador', 'Golden Retriever', 'German Shepherd', 'Poodle', 'Bulldog', 'Beagle', 'Husky'],
    cat:     ['Persian', 'Siamese', 'Maine Coon', 'Bengal', 'Ragdoll', 'Sphynx', 'British Shorthair'],
    bird:    ['Pigeon', 'Parrot', 'Budgerigar', 'Cockatiel', 'Love Bird', 'Macaw', 'Finch', 'Canary'],
    rabbit:  ['Holland Lop', 'Flemish Giant', 'Dutch Rabbit', 'Mini Rex', 'Lionhead'],
    fish:    ['Goldfish', 'Guppy', 'Betta', 'Angel Fish', 'Molly', 'Oscar', 'Discus', 'Arowana'],
    reptile: ['Bearded Dragon', 'Gecko', 'Chameleon', 'Ball Python', 'Tortoise', 'Monitor Lizard'],
    rodent:  ['Hamster', 'Guinea Pig', 'Ferret', 'Chinchilla', 'Gerbil'],
    other:   []
};

const CATEGORY_LABELS = {
    dog: 'Dog Breed', cat: 'Cat Breed', bird: 'Bird Species',
    rabbit: 'Rabbit Breed', fish: 'Fish Variety', reptile: 'Reptile Species',
    rodent: 'Rodent Type', other: 'Variety / Type'
};

async function handleCategoryChange() {
    const cat   = document.getElementById('petCategory').value;
    const group = document.getElementById('varietyGroup');
    const input = document.getElementById('petVariety');
    const label = document.getElementById('varietyLabel');

    if (!cat) {
        group.style.display = 'none';
        input.value = '';
        return;
    }

    // Show and label
    group.style.display = 'block';
    label.textContent = (CATEGORY_LABELS[cat] || 'Variety / Type') + ' (optional)';
    input.placeholder = 'e.g. ' + (CATEGORY_DEFAULTS[cat]?.[0] || 'Enter variety');
    input.value = '';

    // Populate datalist: defaults + previously saved varieties for this category
    const pets = await DB.getPets();
    const saved = [...new Set(pets.filter(p => p.category === cat && p.petVariety).map(p => p.petVariety))];
    const suggestions = [...new Set([...CATEGORY_DEFAULTS[cat] || [], ...saved])];
    document.getElementById('varietyList').innerHTML = suggestions.map(t => `<option value="${t}">`).join('');
}

function resetForm() {
  document.getElementById('successModal').classList.remove('open');
  document.querySelectorAll('input:not([type="radio"])').forEach(i => i.value = '');
  // Also clear all textarea fields — they were not reset, causing notes to persist after "Add Another"
  document.querySelectorAll('textarea').forEach(t => t.value = '');
  document.getElementById('petCategory').value = '';
  document.getElementById('petSource').value = 'Dealer Supplied';
  document.getElementById('petStock').value = '1';
  document.getElementById('petAlert').value = '10';
  document.getElementById('imgPreviewList').innerHTML = '';
  uploadedImages = [];
  togglePriceFields();
  document.getElementById('varietyGroup').style.display = 'none';
  document.getElementById('petVariety').value = '';
  // Reset customer supplier fields
  document.getElementById('customerSupplierSection').style.display = 'none';
  ['csName','csNIC','csAddress','csCostPaid','csDescription','csUid','csDueDate','csPayNote'].forEach(id => {
      const el = document.getElementById(id);
      if(el) el.value = '';
  });
  document.getElementById('csPayStatus').value = 'Paid';
  toggleDueDate();
  document.getElementById('nicPhotoPreview').innerHTML = '';
  nicPhotoData = null;
  document.getElementById('petNameInput').focus();
}

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}

document.addEventListener('DOMContentLoaded', () => {
    togglePriceFields();
    checkFormValidity();
});
</script>

</body>
</html>
