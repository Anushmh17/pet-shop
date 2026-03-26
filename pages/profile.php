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
  <title>Pet Shop — Admin Profile</title>
  <link rel="stylesheet" href="../includes/css/style.css" />
  <script src="../includes/js/storage.js"></script>
  <script>
    (function() {
      const theme = localStorage.getItem('app-theme') || 'light';
      if (theme === 'dark') document.documentElement.classList.add('dark-theme');
    })();
  </script>
  <style>
    .profile-header {
      padding: 40px 20px 30px;
      text-align: center;
      background: var(--clr-surface);
      border-radius: 0 0 40px 40px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.03);
      margin-bottom: 25px;
    }
    .profile-avatar {
      width: 100px; height: 100px;
      background: var(--clr-primary-lt);
      color: var(--clr-primary);
      font-size: 3rem;
      display: flex; align-items: center; justify-content: center;
      border-radius: 35px;
      margin: 0 auto 15px;
    }
    .profile-username {
      font-size: 1.5rem; font-weight: 800; color: var(--clr-text);
      margin-bottom: 5px;
    }
    .profile-role {
      font-size: 0.8rem; font-weight: 700; color: var(--clr-muted);
      text-transform: uppercase; letter-spacing: 1px;
    }
    
    .settings-section {
      background: var(--clr-surface);
      border-radius: var(--r-lg);
      margin-bottom: 20px;
      overflow: hidden;
      border: 1.5px solid var(--clr-border);
    }
    .settings-item {
      padding: 18px 20px;
      display: flex; align-items: center;
      gap: 15px;
      border-bottom: 1.5px solid var(--clr-bg);
      transition: background 0.2s;
    }
    .settings-item:last-child { border-bottom: none; }
    
    .item-icon {
      width: 42px; height: 42px;
      background: var(--clr-bg);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
    }
    .item-label {
      flex-grow: 1;
      font-size: 0.95rem; font-weight: 700; color: var(--clr-text);
    }
    
    .form-box { padding: 20px; display:none; border-top: 1.5px solid var(--clr-bg); }
    .profile-input {
      width: 100%;
      padding: 12px 14px;
      background: var(--clr-bg);
      border: 1.5px solid var(--clr-border);
      border-radius: 12px;
      margin-bottom: 12px;
      font-family: inherit; font-size: 0.9rem; font-weight: 600;
      outline: none;
    }
    .profile-input:focus { border-color: var(--clr-primary); }
    
    .btn-save {
      width: 100%; padding: 12px;
      background: var(--clr-primary); color: white;
      border: none; border-radius: 12px; font-weight: 800;
      cursor: pointer;
    }
    
    .btn-logout {
      width: calc(100% - 40px); margin: 10px 20px 40px;
      padding: 18px; background: #fff; color: #e55;
      border: 2px solid #fee; border-radius: 20px;
      font-weight: 800; font-size: 1rem; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    
    /* Top Nav */
    .top-nav {
      position: sticky; top: 0; z-index: 100;
      background: var(--clr-surface);
      backdrop-filter: blur(10px);
      padding: 15px 20px; display: flex; align-items: center; gap: 15px;
    }
    .btn-back {
      width: 40px; height: 40px; background: var(--clr-surface); border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.2rem; border: 1.5px solid var(--clr-border);
    }
    
    /* Segmented Control */
    .segmented-control {
      display: flex; background: var(--clr-bg);
      border-radius: 12px; padding: 4px;
      gap: 4px; border: 1.5px solid var(--clr-border);
    }
    .segment {
      flex: 1; padding: 6px; text-align: center;
      font-size: 0.75rem; font-weight: 800; color: var(--clr-muted);
      border-radius: 8px; cursor: pointer; transition: all 0.2s;
    }
    .segment.active {
      background: var(--clr-primary); color: white;
      box-shadow: 0 4px 12px rgba(92,158,110,0.25);
    }
  </style>
</head>
<body id="page-body">

<div class="top-nav">
  <a href="index.php" class="btn-back">←</a>
  <h1 style="font-size:1.1rem; font-weight:800; color:var(--clr-text);">Account Settings</h1>
</div>

<div class="app-wrapper">

  <div class="profile-header">
    <div class="profile-avatar">👤</div>
    <div class="profile-username"><?php echo htmlspecialchars($_SESSION['admin_auth']['username']); ?></div>
    <div class="profile-role">Store Manager</div>
  </div>

  <div class="settings-section">
    <!-- Appearance -->
    <div class="settings-item">
      <div class="item-icon" id="themeIcon">🌖</div>
      <div class="item-label">Appearance</div>
      <div class="segmented-control" style="width: 140px;">
        <div class="segment" id="theme-light" onclick="setTheme('light')">Light</div>
        <div class="segment" id="theme-dark" onclick="setTheme('dark')">Dark</div>
      </div>
    </div>

    <!-- Language -->
    <div class="settings-item">
      <div class="item-icon">🌐</div>
      <div class="item-label">System Language</div>
      <div id="lang-holder" style="width: 130px;">
        <!-- Injected by storage.js -->
      </div>
    </div>
    
    <!-- Change Password -->
    <div class="settings-item" onclick="togglePassForm()">
      <div class="item-icon">🔒</div>
      <div class="item-label">Update Password</div>
      <div style="color:var(--clr-muted);">›</div>
    </div>
    
    <div id="passForm" class="form-box">
      <input type="password" id="currPass" class="profile-input" placeholder="Current Password">
      <input type="password" id="newPass" class="profile-input" placeholder="New Password">
      <input type="password" id="confirmPass" class="profile-input" placeholder="Confirm New Password">
      <button class="btn-save" onclick="updatePassword()">Update Account</button>
    </div>
  </div>

  <button class="btn-logout" onclick="handleLogout()">
    <span>🚪</span> Log Out from System
  </button>

</div>

<div class="toast" id="toast"></div>

<script>
function togglePassForm() {
    const f = document.getElementById('passForm');
    f.style.display = f.style.display === 'block' ? 'none' : 'block';
}

async function updatePassword() {
    const curr = document.getElementById('currPass').value;
    const n1 = document.getElementById('newPass').value;
    const n2 = document.getElementById('confirmPass').value;
    
    if(!curr || !n1 || !n2) return showToast('Fill all password fields');
    if(n1 !== n2) return showToast('Passwords do not match');
    // Enforce minimum password strength
    if(n1.length < 8) return showToast('New password must be at least 8 characters');
    
    try {
        const res = await DB.changePassword(curr, n1);
        if(res.success) {
            showToast('✅ Password changed successfully');
            document.getElementById('passForm').style.display = 'none';
            document.getElementById('currPass').value = '';
            document.getElementById('newPass').value = '';
            document.getElementById('confirmPass').value = '';
        } else {
            showToast('❌ ' + (res.error || 'Failed to update'));
        }
    } catch(e) {
        showToast('Error connecting to server');
    }
}

async function handleLogout() {
    if(!confirm('Are you sure you want to log out?')) return;
    await DB.logout();
    window.location.href = 'login.php';
}

function showToast(m) {
    const t = document.getElementById('toast');
    t.textContent = m; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

function setTheme(theme) {
    localStorage.setItem('app-theme', theme);
    document.documentElement.classList.toggle('dark-theme', theme === 'dark');
    updateThemeUI();
}

function updateThemeUI() {
    const theme = localStorage.getItem('app-theme') || 'light';
    document.querySelectorAll('.segment').forEach(s => s.classList.remove('active'));
    document.getElementById('theme-' + theme).classList.add('active');
    // Sync the appearance icon to reflect the currently active theme
    const icon = document.getElementById('themeIcon');
    if (icon) icon.textContent = theme === 'dark' ? '🌙' : '🌞';
}

document.addEventListener('DOMContentLoaded', updateThemeUI);
</script>

</body>
</html>
