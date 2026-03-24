<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Add New Pet — Pet Shop Management" />
  <title>Add Pet — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
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
      <input type="text" id="petNameInput" class="form-control" placeholder="e.g. Golden Retriever" autocomplete="off" />
    </div>

    <div class="form-group">
      <label class="form-label" for="petCategory">Category *</label>
      <select id="petCategory" class="form-control">
        <option value="">— Select Category —</option>
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

    <div style="display:grid; grid-template-columns:1fr 1fr; gap: var(--sp-sm);">
      <div class="form-group">
        <label class="form-label" for="petStock">Stock Qty *</label>
        <input type="number" id="petStock" class="form-control" min="0" placeholder="0" />
      </div>
      <div class="form-group">
        <label class="form-label" for="petAlert">Alert At</label>
        <input type="number" id="petAlert" class="form-control" min="0" placeholder="e.g. 5" />
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="petPrice2">Sale Price (Rs.) *</label>
      <input type="number" id="petPrice2" class="form-control" min="0" placeholder="0.00" step="0.01" />
    </div>

    <div class="form-group">
      <label class="form-label" for="petCost">Cost Price (Rs.)</label>
      <input type="number" id="petCost" class="form-control" min="0" placeholder="0.00" step="0.01" />
    </div>

    <div class="form-group">
      <label class="form-label" for="petNotes">Notes</label>
      <textarea id="petNotes" class="form-control" placeholder="Optional notes…" rows="3" style="resize:vertical;"></textarea>
    </div>

    <button class="btn btn-primary btn-full" id="submitPetBtn" onclick="savePet()">
      🐾 Add Pet
    </button>

  </div>

</div><!-- /app-wrapper -->

<!-- Success modal -->
<div class="modal-overlay" id="successModal" role="dialog" aria-modal="true" aria-label="Pet added successfully">
  <div class="modal-box" style="text-align:center;">
    <div style="font-size:3rem; margin-bottom: var(--sp-sm);">🎉</div>
    <div class="modal-title" style="justify-content:center;">Pet Added!</div>
    <p style="color:var(--clr-muted); font-size:.9rem; margin-bottom: var(--sp-md);">
      <strong id="successName"></strong> has been added to your stock.
    </p>
    <div class="modal-actions">
      <a href="index.php" class="btn btn-ghost" style="flex:1;">Go Home</a>
      <button class="btn btn-primary" style="flex:1;" onclick="resetForm()">Add Another</button>
    </div>
  </div>
</div>

<!-- ===== TOAST ===== -->
<div class="toast" id="toast" role="alert" aria-live="polite"></div>

<script>
const ICONS = ['🐶','🐱','🦜','🐰','🐹','🐠','🐢','🦮','🐕','🦁','🐻','🦊','🐸'];
let iconIdx = 0;

function cycleIcon() {
  iconIdx = (iconIdx + 1) % ICONS.length;
  const el = document.getElementById('petPreview');
  el.style.transform = 'scale(1.15)';
  el.textContent = ICONS[iconIdx];
  setTimeout(() => el.style.transform = '', 200);
}

function savePet() {
  const name     = document.getElementById('petNameInput').value.trim();
  const category = document.getElementById('petCategory').value;
  const stock    = document.getElementById('petStock').value;
  const price    = document.getElementById('petPrice2').value;

  if (!name)     { showToast('Please enter a pet name'); return; }
  if (!category) { showToast('Please select a category'); return; }
  if (!stock)    { showToast('Please enter stock quantity'); return; }
  if (!price)    { showToast('Please enter sale price'); return; }

  const btn = document.getElementById('submitPetBtn');
  btn.disabled = true;
  btn.textContent = '⏳ Saving…';

  setTimeout(() => {
    document.getElementById('successName').textContent = name;
    document.getElementById('successModal').classList.add('open');
    btn.disabled = false;
    btn.textContent = '🐾 Add Pet';
  }, 600);
}

function resetForm() {
  document.getElementById('successModal').classList.remove('open');
  document.getElementById('petNameInput').value  = '';
  document.getElementById('petCategory').value   = '';
  document.getElementById('petStock').value      = '';
  document.getElementById('petAlert').value      = '';
  document.getElementById('petPrice2').value     = '';
  document.getElementById('petCost').value       = '';
  document.getElementById('petNotes').value      = '';
  document.getElementById('petPreview').textContent = ICONS[0];
  iconIdx = 0;
  document.getElementById('petNameInput').focus();
}

document.getElementById('successModal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('open');
});

function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2400);
}
</script>
</body>
</html>
