<?php
require_once 'config/db.php';

// إذا كان المستخدم مسجلاً بالفعل، أعده للصفحة الرئيسية
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['full_name'],
            'is_admin' => (bool)$user['is_admin']
        ];
        if ($user['is_admin']) {
            header('Location: admin.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — AURUM Fine Timepieces</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@200;300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* نفس التصميم الأصلي */
        body { overflow-x: hidden; }
        .auth-page { min-height: 100vh; display: grid; grid-template-columns: 1fr 1fr; }
        .auth-visual { position: relative; background: var(--black); display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; padding: 4rem 3rem; background: radial-gradient(ellipse at 60% 50%, #1a1208 0%, #0a0a0a 65%); }
        .auth-visual-rings { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; }
        .av-ring { position: absolute; border-radius: 50%; border: 1px solid rgba(201,168,76,0.12); animation: avRingPulse 4s ease-in-out infinite; }
        .av-ring:nth-child(1) { width: 360px; height: 360px; }
        .av-ring:nth-child(2) { width: 520px; height: 520px; animation-delay: 1s; }
        .av-ring:nth-child(3) { width: 700px; height: 700px; animation-delay: 2s; }
        @keyframes avRingPulse { 0%,100% { opacity:0.5; transform:scale(1); } 50% { opacity:1; transform:scale(1.02); } }
        .auth-visual-watch { position: relative; z-index: 2; animation: watchFloat 6s ease-in-out infinite; }
        @keyframes watchFloat { 0%,100% { transform: translateY(0) rotate(-2deg); } 50% { transform: translateY(-18px) rotate(2deg); } }
        .auth-visual-copy { position: relative; z-index: 2; text-align: center; margin-top: 3rem; }
        .auth-visual-copy .site-name { font-family: var(--font-serif); font-size: 2.2rem; font-weight: 300; letter-spacing: 0.5em; color: var(--gold); display: block; margin-bottom: 0.8rem; }
        .auth-visual-copy blockquote { font-family: var(--font-serif); font-style: italic; font-size: 1rem; color: var(--grey); max-width: 300px; margin: 0 auto; }
        .auth-form-panel { background: var(--dark-2); display: flex; align-items: center; justify-content: center; padding: 5rem 4rem; position: relative; }
        .auth-form-panel .back-link { position: absolute; top: 2rem; left: 2.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.65rem; letter-spacing: 0.2em; color: var(--grey); transition: color var(--transition); }
        .auth-form-panel .back-link:hover { color: var(--gold); }
        .auth-form-box { width: 100%; max-width: 400px; animation: fadeUp 0.7s ease both; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }
        .form-eyebrow { font-size: 0.62rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--gold); margin-bottom: 0.7rem; }
        .auth-form-box h1 { font-family: var(--font-display); font-size: 2.6rem; font-weight: 400; color: var(--white); line-height: 1.1; margin-bottom: 0.5rem; }
        .auth-subtitle { font-size: 0.87rem; color: var(--grey); margin-bottom: 2.5rem; line-height: 1.6; }
        .form-group { margin-bottom: 1.1rem; position: relative; }
        .form-label { display: block; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--grey-light); margin-bottom: 0.45rem; }
        .auth-form-box input { width: 100%; background: var(--dark-3); border: 1px solid rgba(255,255,255,0.1); color: var(--white); padding: 0.9rem 1.2rem; font-size: 0.88rem; border-radius: var(--radius); transition: border-color var(--transition); outline: none; }
        .auth-form-box input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.1); }
        .pw-wrapper { position: relative; }
        .pw-wrapper input { padding-right: 4.5rem; }
        .toggle-pw { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.62rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--grey); background: none; border: none; cursor: pointer; }
        .toggle-pw:hover { color: var(--gold); }
        .global-error { background: rgba(224,85,85,0.08); border: 1px solid rgba(224,85,85,0.3); color: #e05555; padding: 0.8rem 1rem; border-radius: var(--radius); margin-bottom: 1.2rem; display: none; line-height: 1.5; }
        .global-error.show { display: block; }
        .forgot-link { display: block; text-align: right; font-size: 0.7rem; color: var(--grey); margin-top: -0.5rem; margin-bottom: 1.5rem; }
        .forgot-link:hover { color: var(--gold); }
        .auth-divider { display: flex; align-items: center; gap: 1rem; margin: 1.8rem 0; }
        .auth-divider::before, .auth-divider::after { content: ''; flex:1; height:1px; background: rgba(255,255,255,0.08); }
        .auth-divider span { font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--grey); white-space: nowrap; }
        .auth-switch-link { text-align: center; font-size: 0.82rem; color: var(--grey); margin-top: 1.5rem; }
        .auth-switch-link a { color: var(--gold); font-weight: 500; }
        @media (max-width: 900px) { .auth-page { grid-template-columns: 1fr; } .auth-visual { display: none; } .auth-form-panel { padding: 5rem 2rem 4rem; } }
    </style>
</head>
<body>

<div class="auth-page">
    <!-- الجانب البصري الأيسر -->
    <div class="auth-visual">
        <div class="auth-visual-rings">
            <div class="av-ring"></div>
            <div class="av-ring"></div>
            <div class="av-ring"></div>
        </div>
        <div class="auth-visual-watch">
            <svg viewBox="0 0 280 280" width="260" height="260">
                <rect x="105" y="5" width="70" height="40" rx="8" fill="#2a2a2a"/>
                <rect x="108" y="8" width="64" height="36" rx="6" fill="#1a1a1a"/>
                <rect x="105" y="235" width="70" height="40" rx="8" fill="#2a2a2a"/>
                <rect x="108" y="237" width="64" height="36" rx="6" fill="#1a1a1a"/>
                <circle cx="140" cy="140" r="100" fill="#2c2c2c"/>
                <circle cx="140" cy="140" r="95" fill="#1a1a1a"/>
                <circle cx="140" cy="140" r="92" fill="none" stroke="url(#bezGrad)" stroke-width="6"/>
                <defs>
                    <radialGradient id="bezGrad" cx="35%" cy="30%">
                        <stop offset="0%" stop-color="#e0c06a"/>
                        <stop offset="50%" stop-color="#c9a84c"/>
                        <stop offset="100%" stop-color="#8a6020"/>
                    </radialGradient>
                    <radialGradient id="dialGrad" cx="40%" cy="35%">
                        <stop offset="0%" stop-color="#1a1830"/>
                        <stop offset="100%" stop-color="#0a0910"/>
                    </radialGradient>
                </defs>
                <circle cx="140" cy="140" r="84" fill="url(#dialGrad)"/>
                <g fill="#c9a84c">
                    <rect x="137" y="63" width="6" height="16" rx="2"/>
                    <rect x="137" y="201" width="6" height="16" rx="2"/>
                    <rect x="63" y="137" width="16" height="6" rx="2"/>
                    <rect x="201" y="137" width="16" height="6" rx="2"/>
                </g>
                <g fill="#c9a84c" opacity="0.5">
                    <circle cx="140" cy="71" r="3"/><circle cx="140" cy="209" r="3"/>
                    <circle cx="71" cy="140" r="3"/><circle cx="209" cy="140" r="3"/>
                    <circle cx="191" cy="89" r="2.5"/><circle cx="89" cy="191" r="2.5"/>
                    <circle cx="89" cy="89" r="2.5"/><circle cx="191" cy="191" r="2.5"/>
                </g>
                <text x="140" y="116" text-anchor="middle" font-family="Cormorant Garamond,serif" font-size="13" fill="#c9a84c" letter-spacing="3">AURUM</text>
                <text x="140" y="129" text-anchor="middle" font-family="Montserrat,sans-serif" font-size="6" fill="#888" letter-spacing="4">GENÈVE</text>
                <line x1="140" y1="140" x2="140" y2="78" stroke="white" stroke-width="3" stroke-linecap="round" opacity="0.95"/>
                <line x1="140" y1="140" x2="192" y2="140" stroke="white" stroke-width="2.5" stroke-linecap="round" opacity="0.95"/>
                <line x1="140" y1="140" x2="122" y2="178" stroke="#c9a84c" stroke-width="1.8" stroke-linecap="round"/>
                <circle cx="140" cy="140" r="5" fill="#c9a84c"/><circle cx="140" cy="140" r="2.5" fill="#0a0910"/>
                <rect x="234" y="134" width="12" height="12" rx="3" fill="#888"/>
            </svg>
        </div>
        <div class="auth-visual-copy">
            <span class="site-name">AURUM</span>
            <blockquote>"Time is not measured. It is experienced."</blockquote>
        </div>
    </div>

    <!-- الجانب الأيمن (النموذج) -->
    <div class="auth-form-panel">
        <a href="index.php" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Store
        </a>
        <div class="auth-form-box">
            <p class="form-eyebrow">Welcome Back</p>
            <h1>Sign In</h1>
            <p class="auth-subtitle">Access your account and continue your journey through horological excellence.</p>

            <div class="global-error <?= $error ? 'show' : '' ?>" id="globalErr"><?= htmlspecialchars($error) ?></div>

            <form method="POST" id="signinForm">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="pw-wrapper">
                        <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword()">Show</button>
                    </div>
                </div>
                <a href="#" class="forgot-link">Forgot your password?</a>
                <button type="submit" class="btn btn-gold full-width">Sign In</button>
            </form>

            <div class="auth-divider"><span>New to AURUM?</span></div>
            <a href="signup.php" class="btn btn-ghost full-width">Create an Account</a>
            <p class="auth-switch-link">Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const inp = document.getElementById('password');
        const btn = event.target;
        inp.type = inp.type === 'password' ? 'text' : 'password';
        btn.textContent = inp.type === 'password' ? 'Show' : 'Hide';
    }
</script>
</body>
</html>