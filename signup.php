<?php
require_once 'config/db.php';

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($firstName) || empty($lastName)) {
        $error = 'Please enter your full name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $error = 'Email already registered. Please sign in.';
        } else {
            $fullName = $firstName . ' ' . $lastName;
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name) VALUES (?, ?, ?)");
            $stmt->execute([$email, $hashed, $fullName]);
            $_SESSION['user'] = [
                'id' => $pdo->lastInsertId(),
                'email' => $email,
                'name' => $fullName,
                'is_admin' => 0
            ];
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — AURUM Fine Timepieces</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@200;300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* نفس CSS الخاص بـ signin مع بعض التعديلات للشكل */
        .auth-page { min-height: 100vh; display: grid; grid-template-columns: 1fr 1fr; }
        .auth-visual { position: relative; background: radial-gradient(ellipse at 50% 60%, #12100a 0%, #0a0a0a 65%); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 3rem; overflow: hidden; }
        .auth-visual-rings { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
        .av-ring { position: absolute; border-radius: 50%; border: 1px solid rgba(201,168,76,0.1); animation: avPulse 4s ease-in-out infinite; }
        .av-ring:nth-child(1) { width: 320px; height: 320px; }
        .av-ring:nth-child(2) { width: 500px; height: 500px; animation-delay: 1.2s; }
        .av-ring:nth-child(3) { width: 680px; height: 680px; animation-delay: 2.4s; }
        @keyframes avPulse { 0%,100% { opacity:0.4; transform:scale(1); } 50% { opacity:1; transform:scale(1.025); } }
        .auth-visual-content { position: relative; z-index: 2; text-align: center; }
        .av-benefits { list-style: none; text-align: left; margin-top: 3rem; display: flex; flex-direction: column; gap: 1.2rem; }
        .av-benefits li { display: flex; align-items: flex-start; gap: 1rem; color: var(--grey-light); font-size: 0.88rem; }
        .av-benefits .check { width: 20px; height: 20px; border: 1px solid var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: var(--gold); flex-shrink: 0; }
        .av-benefits strong { color: var(--white); font-weight: 500; }
        .auth-visual-logo { font-family: var(--font-serif); font-size: 2.4rem; font-weight: 300; letter-spacing: 0.5em; color: var(--gold); display: block; margin-bottom: 0.5rem; }
        .auth-visual-tagline { font-family: var(--font-serif); font-style: italic; font-size: 0.95rem; color: var(--grey); }
        .auth-form-panel { background: var(--dark-2); display: flex; align-items: center; justify-content: center; padding: 4rem; position: relative; overflow-y: auto; }
        .back-link { position: absolute; top: 2rem; left: 2.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.65rem; letter-spacing: 0.2em; color: var(--grey); transition: color var(--transition); }
        .back-link:hover { color: var(--gold); }
        .auth-form-box { width: 100%; max-width: 420px; animation: fadeUp 0.7s ease both; padding: 2rem 0; }
        .form-eyebrow { font-size: 0.62rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--gold); margin-bottom: 0.6rem; }
        .auth-form-box h1 { font-family: var(--font-display); font-size: 2.4rem; font-weight: 400; color: var(--white); line-height: 1.1; margin-bottom: 0.5rem; }
        .auth-subtitle { font-size: 0.87rem; color: var(--grey); margin-bottom: 2rem; line-height: 1.6; }
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; }
        .form-group { margin-bottom: 1rem; position: relative; }
        .form-label { display: block; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--grey-light); margin-bottom: 0.4rem; }
        .auth-form-box input { width: 100%; background: var(--dark-3); border: 1px solid rgba(255,255,255,0.1); color: var(--white); padding: 0.85rem 1.1rem; font-size: 0.87rem; border-radius: var(--radius); transition: border-color var(--transition); }
        .auth-form-box input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.1); }
        .pw-wrapper { position: relative; }
        .pw-wrapper input { padding-right: 4.5rem; }
        .toggle-pw { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.62rem; letter-spacing: 0.12em; color: var(--grey); background: none; border: none; cursor: pointer; }
        .toggle-pw:hover { color: var(--gold); }
        .global-error { background: rgba(224,85,85,0.08); border: 1px solid rgba(224,85,85,0.28); color: #e05555; padding: 0.8rem 1rem; border-radius: var(--radius); margin-bottom: 1.2rem; display: none; }
        .global-error.show { display: block; }
        .terms-check { display: flex; align-items: flex-start; gap: 0.7rem; margin: 1.2rem 0; }
        .terms-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--gold); margin-top: 0.15rem; flex-shrink: 0; }
        .terms-check label { font-size: 0.76rem; color: var(--grey); line-height: 1.5; cursor: pointer; }
        .terms-check a { color: var(--gold); }
        .auth-divider { display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0; }
        .auth-divider::before, .auth-divider::after { content: ''; flex:1; height:1px; background: rgba(255,255,255,0.07); }
        .auth-divider span { font-size: 0.64rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--grey); }
        .auth-switch-link { text-align: center; font-size: 0.82rem; color: var(--grey); margin-top: 1rem; }
        .auth-switch-link a { color: var(--gold); font-weight: 500; }
        @media (max-width: 900px) { .auth-page { grid-template-columns: 1fr; } .auth-visual { display: none; } .auth-form-panel { padding: 5rem 1.5rem 3rem; } .form-row-2 { grid-template-columns: 1fr; } }
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
        <div class="auth-visual-content">
            <span class="auth-visual-logo">AURUM</span>
            <p class="auth-visual-tagline">"Crafted for those who value precision."</p>
            <ul class="av-benefits">
                <li><span class="check">✓</span><span><strong>Exclusive Access</strong> — Member-only releases.</span></li>
                <li><span class="check">✓</span><span><strong>Saved Collection</strong> — Curate your personal wishlist.</span></li>
                <li><span class="check">✓</span><span><strong>Bespoke Personalisation</strong> — Custom engravings.</span></li>
                <li><span class="check">✓</span><span><strong>White Glove Delivery</strong> — Complimentary insured delivery.</span></li>
            </ul>
        </div>
    </div>

    <!-- نموذج إنشاء الحساب -->
    <div class="auth-form-panel">
        <a href="index.php" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Store
        </a>
        <div class="auth-form-box">
            <p class="form-eyebrow">Join AURUM</p>
            <h1>Create Your Account</h1>
            <p class="auth-subtitle">Step into a world where every second matters.</p>

            <div class="global-error <?= $error ? 'show' : '' ?>" id="globalErr"><?= htmlspecialchars($error) ?></div>

            <form method="POST" id="signupForm">
                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" placeholder="James" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" placeholder="Beaumont" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="pw-wrapper">
                        <input type="password" name="password" id="password" placeholder="Min. 8 characters" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword('password')">Show</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="pw-wrapper">
                        <input type="password" name="confirm_password" id="confirm" placeholder="Repeat password" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword('confirm')">Show</button>
                    </div>
                </div>
                <div class="terms-check">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</label>
                </div>
                <button type="submit" class="btn btn-gold full-width">Create Account</button>
            </form>

            <div class="auth-divider"><span>Already a member?</span></div>
            <a href="signin.php" class="btn btn-ghost full-width">Sign In</a>
            <p class="auth-switch-link">Already have an account? <a href="signin.php">Sign In</a></p>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const inp = document.getElementById(fieldId);
        const btn = event.target;
        inp.type = inp.type === 'password' ? 'text' : 'password';
        btn.textContent = inp.type === 'password' ? 'Show' : 'Hide';
    }
</script>
</body>
</html>