<?php
session_start();
if (!isset($_SESSION['admin_auth'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="description" content="AI Animal Counter — Pet Shop Management" />
  <title>AI Counter — Pet Shop</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
  <style>
    /* ─── AI Counter Page Specific Styles ─── */

    /* Hero gradient banner */
    .ai-hero {
      background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
      border-radius: var(--r-xl);
      padding: var(--sp-lg) var(--sp-md);
      color: #fff;
      margin-bottom: var(--sp-md);
      box-shadow: 0 6px 24px rgba(108,92,231,.35);
      display: flex;
      flex-direction: column;
      gap: 4px;
      position: relative;
      overflow: hidden;
    }
    .ai-hero::before {
      content: '🐾';
      position: absolute;
      right: 20px; top: 50%;
      transform: translateY(-50%);
      font-size: 4rem;
      opacity: .15;
    }
    .ai-hero .hero-label  { font-size:.8rem; font-weight:700; opacity:.85; text-transform:uppercase; letter-spacing:.8px; }
    .ai-hero .hero-title  { font-size:1.6rem; font-weight:800; line-height:1.1; }
    .ai-hero .hero-sub    { font-size:.78rem; opacity:.75; font-weight:600; }

    /* ─── Capture Zone ─── */
    .capture-zone {
      background: var(--clr-surface);
      border: 2px dashed var(--clr-border);
      border-radius: var(--r-xl);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 14px;
      padding: 32px 20px;
      cursor: pointer;
      transition: border-color .2s, background .2s;
      position: relative;
      overflow: hidden;
      min-height: 200px;
      text-align: center;
      -webkit-tap-highlight-color: transparent;
    }
    .capture-zone.has-image {
      padding: 0;
      border-style: solid;
      border-color: #6c5ce7;
      min-height: 240px;
    }
    .capture-zone:active { background: var(--clr-bg); }

    #previewImg {
      width: 100%;
      max-height: 300px;
      object-fit: cover;
      border-radius: calc(var(--r-xl) - 2px);
      display: none;
    }
    .capture-zone.has-image #previewImg { display: block; }
    .capture-zone.has-image .capture-placeholder { display: none; }

    .capture-placeholder { pointer-events: none; }
    .capture-icon { font-size: 2.8rem; }
    .capture-title { font-size:.95rem; font-weight:800; color:var(--clr-text); }
    .capture-sub   { font-size:.72rem; font-weight:600; color:var(--clr-muted); }

    /* ─── Action Buttons ─── */
    .action-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-sm);
      margin-top: var(--sp-sm);
    }
    .btn-camera {
      background: linear-gradient(135deg, #6c5ce7, #a29bfe);
      color: #fff;
      border: none;
      box-shadow: 0 4px 14px rgba(108,92,231,.35);
    }
    .btn-camera:active { transform: scale(.97); }
    .btn-gallery {
      background: var(--clr-surface);
      color: var(--clr-text);
      border: 1.5px solid var(--clr-border);
    }
    .btn-gallery:active { transform: scale(.97); }

    /* Analyse button — full width */
    #analyseBtn {
      width: 100%;
      margin-top: var(--sp-sm);
      background: linear-gradient(135deg, #6c5ce7, #a29bfe);
      color: #fff;
      border: none;
      padding: 14px;
      font-size: 1rem;
      font-weight: 800;
      border-radius: var(--r-lg);
      box-shadow: 0 4px 18px rgba(108,92,231,.35);
      display: none;
      align-items: center;
      justify-content: center;
      gap: 10px;
      cursor: pointer;
      transition: opacity .2s, transform .1s;
    }
    #analyseBtn:active { transform: scale(.97); }
    #analyseBtn.visible { display: flex; }

    /* Loading spinner inside button */
    .btn-spinner {
      width: 18px; height: 18px;
      border: 3px solid rgba(255,255,255,.35);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .7s linear infinite;
      display: none;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ─── Results Card ─── */
    #resultsCard {
      display: none;
      background: var(--clr-surface);
      border: 1.5px solid var(--clr-border);
      border-radius: var(--r-xl);
      overflow: hidden;
      margin-top: var(--sp-md);
      box-shadow: var(--shadow-md);
    }
    #resultsCard.visible { display: block; }

    .results-header {
      background: linear-gradient(135deg, #6c5ce7, #a29bfe);
      color: #fff;
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .results-header .rh-label { font-size:.72rem; font-weight:800; opacity:.8; text-transform:uppercase; letter-spacing:.6px; }
    .results-header .rh-total { font-size:2.4rem; font-weight:800; line-height:1; }
    .results-header .rh-sub   { font-size:.72rem; opacity:.8; margin-top:2px; }

    /* Species grid */
    .species-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      padding: 16px;
    }
    .species-card {
      background: var(--clr-bg);
      border-radius: var(--r-lg);
      padding: 14px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .species-icon { font-size: 1.8rem; flex-shrink: 0; }
    .species-info .s-name  { font-size:.72rem; font-weight:800; color:var(--clr-muted); text-transform:uppercase; letter-spacing:.5px; }
    .species-info .s-count { font-size:1.6rem; font-weight:800; color:var(--clr-text); line-height:1.1; }

    /* Detection list */
    .det-list { padding: 0 16px 16px; }
    .det-list-title {
      font-size:.7rem; font-weight:800; color:var(--clr-muted);
      text-transform:uppercase; letter-spacing:.5px;
      margin-bottom: 10px;
      display: flex; align-items: center; gap: 6px;
    }
    .det-item {
      display: flex; align-items: center; justify-content: space-between;
      padding: 9px 12px;
      background: var(--clr-bg);
      border-radius: var(--r-md);
      margin-bottom: 6px;
      font-size: .82rem;
    }
    .det-item .di-label { font-weight:800; color:var(--clr-text); text-transform:capitalize; }
    .det-item .di-conf  { font-size:.7rem; font-weight:700; color:var(--clr-muted); }
    .det-item .di-badge {
      font-size:.62rem; font-weight:800;
      background: #efecfd; color: #6c5ce7;
      padding: 2px 8px; border-radius: 50px;
    }

    /* No animals found state */
    .no-animals {
      padding: 30px 20px;
      text-align: center;
      color: var(--clr-muted);
      font-size: .88rem;
      font-weight: 600;
    }

    /* ─── API status banner ─── */
    #apiStatus {
      font-size:.7rem; font-weight:800; text-transform:uppercase;
      padding: 8px 14px; border-radius: var(--r-md);
      display: flex; align-items: center; gap: 6px;
      margin-bottom: var(--sp-sm);
    }
    #apiStatus.ok   { background:#e6f7f4; color:#00b894; }
    #apiStatus.err  { background: var(--clr-danger-lt); color: var(--clr-danger); }
    #apiStatus.chk  { background: var(--clr-bg); color: var(--clr-muted); }

    /* Info box */
    .info-box {
      background: #efecfd;
      border-radius: var(--r-md);
      padding: 12px 14px;
      font-size: .75rem;
      font-weight: 600;
      color: #6c5ce7;
      line-height: 1.6;
      margin-top: var(--sp-md);
    }

    /* Supported animals chips */
    .animal-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .animal-chip {
      font-size: .72rem; font-weight: 800;
      padding: 4px 12px; border-radius: 50px;
      background: var(--clr-surface);
      color: var(--clr-text);
      border: 1.5px solid var(--clr-border);
    }
  </style>
</head>
<body id="page-body">

<!-- ===== TOP NAV ===== -->
<nav class="top-nav" style="position:sticky; top:0; z-index:1000; border-bottom:1px solid var(--clr-border);">
  <a href="index.php" class="nav-back" id="backBtn" aria-label="Go back">&#8592;</a>
  <span class="nav-title">AI Animal Counter</span>
  <div class="nav-spacer"></div>
</nav>

<!-- Hidden file inputs -->
<input type="file" id="galleryInput" accept="image/*" style="display:none;" />
<input type="file" id="cameraInput"  accept="image/*" capture="environment" style="display:none;" />

<!-- ===== MAIN CONTENT ===== -->
<div class="app-wrapper" style="padding-top: var(--sp-md);">

  <!-- Hero Banner -->
  <div class="ai-hero">
    <div class="hero-label">AI Powered</div>
    <div class="hero-title">Animal Counter</div>
    <div class="hero-sub">Snap a photo · Get instant counts 🐾</div>
  </div>

  <!-- API Status -->
  <div id="apiStatus" class="chk">
    <span id="apiDot">⏳</span>
    <span id="apiStatusText">Connecting to AI engine…</span>
  </div>

  <!-- ─── Capture Zone ─── -->
  <div class="capture-zone" id="captureZone" onclick="document.getElementById('galleryInput').click()">
    <div class="capture-placeholder">
      <div class="capture-icon">📷</div>
      <div class="capture-title">Tap to select a photo</div>
      <div class="capture-sub">Or use the camera button below</div>
    </div>
    <img id="previewImg" alt="Preview" />
  </div>

  <!-- Action Buttons -->
  <div class="action-row">
    <button class="btn btn-camera" id="btnCamera" onclick="document.getElementById('cameraInput').click()">
      📸 Camera
    </button>
    <button class="btn btn-gallery" id="btnGallery" onclick="document.getElementById('galleryInput').click()">
      🖼️ Gallery
    </button>
  </div>

  <!-- Analyse Button (shown after image is selected) -->
  <button id="analyseBtn" onclick="runAnalysis()">
    <span id="btnSpinnerEl" class="btn-spinner"></span>
    <span id="btnLabel">🔍 Analyse Animals</span>
  </button>

  <!-- ─── Results Card ─── -->
  <div id="resultsCard">
    <div class="results-header">
      <div>
        <div class="rh-label">Total Detected</div>
        <div class="rh-total" id="rTotal">0</div>
        <div class="rh-sub" id="rSub">animals found</div>
      </div>
      <div style="font-size:3rem; opacity:.6;">🐾</div>
    </div>

    <!-- Per-species breakdown -->
    <div class="species-grid" id="speciesGrid"></div>

    <!-- Per-detection list -->
    <div class="det-list" id="detList"></div>

    <!-- Correction / Feedback Box -->
    <div id="correctionBox" style="padding:16px; border-top:1.5px solid var(--clr-border); background:var(--clr-bg);">
      <div class="det-list-title">✍️ Correction / Comment</div>
      <div style="font-size:.72rem; color:var(--clr-muted); margin-bottom:10px; font-weight:600;">
        Are these not birds? Type the correct name (e.g. Goldfish) to help the AI learn.
      </div>
      <div style="display:flex; gap:8px;">
        <input type="text" id="correctionInput" placeholder="Enter correct animal name..." 
               style="flex:1; padding:10px; border-radius:var(--r-md); border:1.5px solid var(--clr-border); font-size:.85rem; font-weight:600;" />
        <button id="submitCorrectionBtn" onclick="submitCorrection()" 
                style="padding:0 16px; background:#6c5ce7; color:#fff; border:none; border-radius:var(--r-md); font-weight:800; font-size:.75rem;">
          Submit
        </button>
      </div>
      <div id="correctionStatus" style="margin-top:8px; font-size:.7rem; font-weight:700;"></div>
    </div>
  </div>

  <!-- Info box -->
  <div class="info-box">
    <strong>📖 How it works</strong><br>
    Take a clear, well-lit photo of your animals. The AI detects and counts each one in seconds.<br><br>
    <strong>Supported animals:</strong>
    <div class="animal-chips">
      <span class="animal-chip">🐟 Fish</span>
      <span class="animal-chip">🐇 Rabbit</span>
      <span class="animal-chip">🐦 Bird</span>
      <span class="animal-chip">🐱 Cat</span>
      <span class="animal-chip">🐶 Dog</span>
    </div>
    <br>
    <strong>🔒 Privacy:</strong> Your photo is analysed instantly and never stored.
  </div>

</div><!-- /app-wrapper -->

<div class="toast" id="toast"></div>

<script>
/* =============================================================
   AI Counter — Frontend Logic
   ============================================================= */

// ─── Configuration ────────────────────────────────────────────
// The Python AI backend runs on the same machine on port 8000.
// Using window.location.hostname allows mobile phones on the same Wi-Fi to connect.
const AI_API_BASE = `${window.location.protocol}//${window.location.hostname}:8000`;
const DETECT_ENDPOINT = `${AI_API_BASE}/detect-and-count`;

// ─── Animal emoji map ────────────────────────────────────────
const ANIMAL_EMOJI = {
  fish:   '🐟',
  rabbit: '🐇',
  bird:   '🐦',
  cat:    '🐱',
  dog:    '🐶',
};

// ─── State ───────────────────────────────────────────────────
let selectedFile = null;

// ─── Check AI backend status on load ─────────────────────────
async function checkApiStatus() {
  const statusEl  = document.getElementById('apiStatus');
  const dotEl     = document.getElementById('apiDot');
  const textEl    = document.getElementById('apiStatusText');

  try {
    const res = await fetch(AI_API_BASE + '/', { signal: AbortSignal.timeout(3000) });
    if (res.ok) {
      statusEl.className = 'ok';
      dotEl.textContent  = '✅';
      textEl.textContent = 'AI engine ready';
    } else {
      throw new Error('bad status');
    }
  } catch {
    statusEl.className = 'err';
    dotEl.textContent  = '❌';
    textEl.textContent = 'AI engine offline — start the Python server';
  }
}

// ─── Image selection (gallery or camera) ─────────────────────
function handleFileSelect(file) {
  if (!file) return;
  selectedFile = file;

  // Show preview
  const reader = new FileReader();
  reader.onload = (e) => {
    const img  = document.getElementById('previewImg');
    const zone = document.getElementById('captureZone');
    img.src    = e.target.result;
    zone.classList.add('has-image');
  };
  reader.readAsDataURL(file);

  // Show Analyse button
  const btn = document.getElementById('analyseBtn');
  btn.classList.add('visible');

  // Hide old results
  document.getElementById('resultsCard').classList.remove('visible');
}

document.getElementById('galleryInput').addEventListener('change', (e) => {
  handleFileSelect(e.target.files[0]);
});
document.getElementById('cameraInput').addEventListener('change', (e) => {
  handleFileSelect(e.target.files[0]);
});

// ─── Run Detection ───────────────────────────────────────────
async function runAnalysis() {
  if (!selectedFile) {
    showToast('Please select or capture a photo first.');
    return;
  }

  // Show loading state
  setLoading(true);

  // Build FormData with the image
  const form = new FormData();
  form.append('image', selectedFile, selectedFile.name);

  try {
    const res = await fetch(DETECT_ENDPOINT, {
      method: 'POST',
      body: form,
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.detail || `Server error ${res.status}`);
    }

    const data = await res.json();
    renderResults(data);

  } catch (err) {
    showToast('❌ ' + err.message);
    console.error('Detection error:', err);
  } finally {
    setLoading(false);
  }
}

// ─── Render Results ──────────────────────────────────────────
function renderResults(data) {
  const card    = document.getElementById('resultsCard');
  const total   = data.total_animals || 0;
  const animals = data.animals || {};
  const dets    = data.detections || [];

  // Total count
  document.getElementById('rTotal').textContent = total;
  document.getElementById('rSub').textContent   =
    total === 1 ? 'animal found' : 'animals found';

  // Per-species cards
  const grid = document.getElementById('speciesGrid');
  if (Object.keys(animals).length === 0) {
    grid.innerHTML = `<div class="no-animals" style="grid-column:1/-1;">
      No recognisable animals found.<br>Try a clearer, better-lit photo.
    </div>`;
  } else {
    grid.innerHTML = Object.entries(animals)
      .sort((a, b) => b[1] - a[1])   // highest count first
      .map(([label, count]) => `
        <div class="species-card">
          <div class="species-icon">${ANIMAL_EMOJI[label] || '🐾'}</div>
          <div class="species-info">
            <div class="s-name">${label}</div>
            <div class="s-count">${count}</div>
          </div>
        </div>
      `).join('');
  }

  // Detection detail list
  const detList = document.getElementById('detList');
  if (dets.length > 0) {
    detList.innerHTML = `
      <div class="det-list-title">🔍 Individual Detections (${dets.length})</div>
      ${dets.map((d, i) => `
        <div class="det-item">
          <span class="di-label">${ANIMAL_EMOJI[d.label] || '🐾'} ${d.label}</span>
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="di-conf">${Math.round(d.confidence * 100)}% conf.</span>
            <span class="di-badge">#${i + 1}</span>
          </div>
        </div>
      `).join('')}
    `;
  } else {
    detList.innerHTML = '';
  }

  card.classList.add('visible');

  // Scroll to results
  card.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ─── Submit Correction ──────────────────────────────────────
async function submitCorrection() {
  const inputEl  = document.getElementById('correctionInput');
  const statusEl = document.getElementById('correctionStatus');
  const btn      = document.getElementById('submitCorrectionBtn');
  const label    = inputEl.value.trim();
  const img      = document.getElementById('previewImg');

  if (!label) {
    showToast('Please enter a name first.');
    return;
  }

  // Visual feedback
  btn.disabled         = true;
  statusEl.textContent = '💾 Saving feedback...';
  statusEl.style.color = '#6c5ce7';

  try {
    const res = await fetch(`${AI_API_BASE}/submit-correction`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        image_data: img.src,   // uses the base64 from the preview
        label: label
      })
    });

    if (!res.ok) throw new Error('Failed to save correction');

    statusEl.textContent = '✅ Thank you! Feedback saved for the next AI update.';
    statusEl.style.color = '#00b894';
    inputEl.value        = '';
    
    // Disable after success
    btn.style.opacity = '0.5';
    btn.onclick       = null;
    
  } catch (err) {
    statusEl.textContent = '❌ Error saving feedback.';
    statusEl.style.color = 'var(--clr-danger)';
    btn.disabled         = false;
  }
}

// ─── Loading state helpers ───────────────────────────────────
function setLoading(loading) {
  const btn     = document.getElementById('analyseBtn');
  const spinner = document.getElementById('btnSpinnerEl');
  const label   = document.getElementById('btnLabel');

  btn.disabled            = loading;
  spinner.style.display   = loading ? 'block' : 'none';
  label.textContent       = loading ? 'Analysing…' : '🔍 Analyse Animals';
}

// ─── Toast notification ──────────────────────────────────────
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

// ─── Init ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', checkApiStatus);
</script>
</body>
</html>
