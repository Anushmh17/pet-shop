<?php
session_start();
if (isset($_SESSION['admin_auth'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pet Shop — Admin Login</title>
    <link rel="stylesheet" href="../includes/css/style.css">
    <script src="../includes/js/storage.js"></script>
    <style>
        body {
            background: #f7f5f0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .login-card {
            background: white;
            width: 100%;
            max-width: 360px;
            padding: 40px 30px;
            border-radius: 28px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            text-align: center;
            margin: 20px;
        }
        .login-logo {
            font-size: 3rem;
            margin-bottom: 15px;
            background: var(--clr-primary-lt);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 22px;
            margin: 0 auto 20px;
        }
        .login-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--clr-text);
            margin-bottom: 8px;
        }
        .login-sub {
            font-size: 0.85rem;
            color: var(--clr-muted);
            margin-bottom: 30px;
            font-weight: 600;
        }
        .input-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .input-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--clr-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            margin-left: 4px;
        }
        .login-input {
            width: 100%;
            padding: 14px 18px;
            background: #f9f9f9;
            border: 1.5px solid #eee;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            outline: none;
            transition: all 0.2s;
        }
        .login-input:focus {
            border-color: var(--clr-primary);
            background: white;
            box-shadow: 0 0 0 4px var(--clr-primary-lt);
        }
        .login-btn {
            width: 100%;
            padding: 16px;
            background: var(--clr-primary);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 800;
            margin-top: 10px;
            box-shadow: 0 10px 20px rgba(92, 158, 110, 0.2);
            transition: transform 0.2s;
        }
        .login-btn:active {
            transform: scale(0.98);
        }
        .error-msg {
            background: #fee;
            color: #e55;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-logo">🐾</div>
    <h1 class="login-title">Admin Login</h1>
    <p class="login-sub">Sign in to manage your shop</p>

    <div id="loginError" class="error-msg"></div>

    <form id="loginForm" onsubmit="handleLogin(event)">
        <div class="input-group">
            <span class="input-label">Username</span>
            <input type="text" id="username" class="login-input" required autocomplete="username">
        </div>
        <div class="input-group" style="margin-bottom: 25px;">
            <span class="input-label">Password</span>
            <input type="password" id="password" class="login-input" required autocomplete="current-password">
        </div>
        <button type="submit" id="loginBtn" class="login-btn">Sign In</button>
    </form>
</div>

<script>
async function handleLogin(e) {
    e.preventDefault();
    const user = document.getElementById('username').value.trim();
    const pass = document.getElementById('password').value;
    const btn = document.getElementById('loginBtn');
    const err = document.getElementById('loginError');

    if (!user || !pass) return;

    btn.disabled = true;
    btn.textContent = 'Authenticating...';
    err.style.display = 'none';

    try {
        const res = await DB.login(user, pass);
        if (res.success) {
            window.location.href = 'index.php';
        } else {
            err.textContent = res.error || 'Invalid credentials';
            err.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Sign In';
        }
    } catch (e) {
        console.error(e);
        err.textContent = 'Connection error. Please try again.';
        err.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Sign In';
    }
}
</script>

</body>
</html>
