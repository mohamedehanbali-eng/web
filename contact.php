<?php require_once 'config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — AURUM Fine Timepieces</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@200;300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Custom styles for contact page */
        .contact-hero { position: relative; background: var(--black); padding: 10rem 3rem 6rem; text-align: center; overflow: hidden; }
        .contact-hero-bg { position: absolute; inset: 0; background: radial-gradient(ellipse at 50% 80%, #131006 0%, #0a0a0a 60%); }
        .contact-hero-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(201,168,76,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(201,168,76,0.04) 1px, transparent 1px); background-size: 60px 60px; mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 100%); }
        .contact-hero-content { position: relative; z-index: 2; max-width: 600px; margin: 0 auto; }
        .contact-hero-content .eyebrow { font-size: 0.62rem; letter-spacing: 0.35em; text-transform: uppercase; color: var(--gold); margin-bottom: 1rem; }
        .contact-hero-content h1 { font-family: var(--font-display); font-size: clamp(2.8rem, 6vw, 5rem); font-weight: 400; color: var(--white); line-height: 1.1; margin-bottom: 1.2rem; }
        .contact-hero-content h1 em { font-style: italic; color: var(--gold); }
        .contact-hero-content p { font-size: 0.95rem; color: var(--grey-light); line-height: 1.8; }
        .contact-info-strip { background: var(--dark-2); border-top: 1px solid rgba(201,168,76,0.12); border-bottom: 1px solid rgba(201,168,76,0.12); }
        .contact-info-strip-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
        .info-card { padding: 2.5rem 2rem; border-right: 1px solid rgba(255,255,255,0.05); transition: background var(--transition); }
        .info-card:last-child { border-right: none; }
        .info-card:hover { background: var(--dark-3); }
        .info-card-icon { width: 40px; height: 40px; border: 1px solid rgba(201,168,76,0.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: var(--gold); }
        .info-card-icon svg { width: 18px; height: 18px; }
        .info-card-label { font-size: 0.6rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--gold); margin-bottom: 0.5rem; }
        .info-card-value { font-size: 0.88rem; color: var(--white); line-height: 1.6; }
        .info-card-sub { font-size: 0.75rem; color: var(--grey); margin-top: 0.2rem; }
        .contact-main { padding: 7rem 3rem; background: var(--dark); }
        .contact-main-inner { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1.1fr 1fr; gap: 6rem; align-items: start; }
        .form-heading { font-size: 0.62rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--gold); margin-bottom: 0.7rem; }
        .contact-form-side h2 { font-family: var(--font-display); font-size: 2.6rem; font-weight: 400; color: var(--white); line-height: 1.15; margin-bottom: 0.5rem; }
        .contact-form-side h2 em { font-style: italic; color: var(--gold); }
        .form-intro { font-size: 0.88rem; color: var(--grey); line-height: 1.8; margin-bottom: 2.5rem; }
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { margin-bottom: 1.1rem; }
        .form-label { display: block; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--grey-light); margin-bottom: 0.45rem; }
        .form-input, .form-select, .form-textarea { width: 100%; background: var(--dark-3); border: 1px solid rgba(255,255,255,0.1); color: var(--white); padding: 0.9rem 1.2rem; font-size: 0.87rem; border-radius: var(--radius); transition: border-color var(--transition); outline: none; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,0.1); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 1.2rem center; cursor: pointer; }
        .char-count { text-align: right; font-size: 0.65rem; color: var(--grey); margin-top: 0.2rem; }
        .char-count.near { color: #f0a500; }
        .char-count.over { color: #e05555; }
        .contact-success { display: none; text-align: center; padding: 3rem 2rem; animation: fadeUp 0.5s ease; background: var(--dark-2); border-radius: 12px; }
        .contact-success.show { display: block; }
        .contact-success .success-icon { width: 64px; height: 64px; border: 2px solid var(--gold); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: var(--gold); margin: 0 auto 1.5rem; }
        .contact-success h3 { font-family: var(--font-display); font-size: 2rem; font-weight: 400; color: var(--white); margin-bottom: 0.7rem; }
        .contact-success p { font-size: 0.9rem; color: var(--grey-light); line-height: 1.7; }
        .map-placeholder { background: var(--dark-2); border: 1px solid rgba(201,168,76,0.15); border-radius: var(--radius-lg); height: 260px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.8rem; color: var(--grey); position: relative; overflow: hidden; cursor: pointer; }
        .map-placeholder::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(201,168,76,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(201,168,76,0.05) 1px, transparent 1px); background-size: 30px 30px; }
        .map-placeholder .map-pin { position: relative; z-index: 2; color: var(--gold); font-size: 2rem; animation: mapPinBounce 2s ease-in-out infinite; }
        @keyframes mapPinBounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .hours-table { width: 100%; margin-bottom: 2.5rem; }
        .hours-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.82rem; }
        .hours-row.today { background: rgba(201,168,76,0.06); padding: 0.6rem 0.8rem; border-radius: 3px; margin: 0 -0.8rem; }
        .hours-row.today .day { color: var(--gold); }
        .side-socials { display: flex; gap: 0.8rem; }
        .side-social-btn { width: 40px; height: 40px; border: 1px solid rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--grey); transition: all var(--transition); }
        .side-social-btn:hover { border-color: var(--gold); color: var(--gold); transform: translateY(-2px); }
        .side-social-btn svg { width: 16px; height: 16px; }
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity 0.8s ease, transform 0.8s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        @media (max-width: 1024px) { .contact-main-inner { grid-template-columns: 1fr; gap: 4rem; } .contact-info-strip-inner { grid-template-columns: repeat(2,1fr); } }
        @media (max-width: 640px) { .contact-hero { padding: 8rem 1.5rem 4rem; } .contact-main { padding: 4rem 1.5rem; } .form-row-2 { grid-template-columns: 1fr; } .contact-info-strip-inner { grid-template-columns: 1fr; } .info-card { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.05); } }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- HERO -->
<section class="contact-hero">
    <div class="contact-hero-bg"></div>
    <div class="contact-hero-grid"></div>
    <div class="contact-hero-content">
        <p class="eyebrow">We're Here for You</p>
        <h1>Let's <em>Connect</em></h1>
        <p>Whether you seek a consultation, a private viewing, or simply wish to speak with one of our master advisors — we are at your service.</p>
    </div>
</section>

<!-- INFO STRIP -->
<div class="contact-info-strip">
    <div class="contact-info-strip-inner">
        <div class="info-card reveal">
            <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
            <div class="info-card-label">Visit Us</div>
            <div class="info-card-value">12 Rue de la Paix</div>
            <div class="info-card-sub">Geneva, Switzerland · CH-1204</div>
        </div>
        <div class="info-card reveal">
            <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
            <div class="info-card-label">Call Us</div>
            <div class="info-card-value">+41 22 000 0000</div>
            <div class="info-card-sub">Mon – Sat, 10:00 – 19:00</div>
        </div>
        <div class="info-card reveal">
            <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
            <div class="info-card-label">Email Us</div>
            <div class="info-card-value">concierge@aurum.com</div>
            <div class="info-card-sub">We reply within 24 hours</div>
        </div>
        <div class="info-card reveal">
            <div class="info-card-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div class="info-card-label">Private Appointments</div>
            <div class="info-card-value">By Arrangement</div>
            <div class="info-card-sub">Exclusive after-hours viewings available</div>
        </div>
    </div>
</div>

<!-- MAIN FORM SECTION -->
<?php
$message_sent = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $subject = $_POST['subject'];
    $msg = trim($_POST['message']);
    if (empty($first_name) || empty($last_name) || empty($email) || empty($subject) || empty($msg)) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $full_name = $first_name . ' ' . $last_name;
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$full_name, $email, $msg]);
        $message_sent = true;
        // Also store subject optionally (you can add column to messages table)
    }
}
?>

<section class="contact-main">
    <div class="contact-main-inner">
        <!-- FORM -->
        <div class="contact-form-side reveal">
            <p class="form-heading">Send a Message</p>
            <h2>We'd Love to<br/><em>Hear From You</em></h2>
            <p class="form-intro">Complete the form below and a member of our team will respond within one business day. For urgent matters, please call us directly.</p>

            <?php if ($message_sent): ?>
            <div class="contact-success show">
                <div class="success-icon">✓</div>
                <h3>Message Received</h3>
                <p>Thank you for reaching out to AURUM. Your message has been noted and a member of our team will respond within 24 hours.</p>
                <p class="gold-text">"Elegance in every second."</p>
                <a href="contact.php" class="btn btn-ghost" style="margin-top:1rem;">Send Another Message</a>
            </div>
            <?php else: ?>
            <div id="contactFormWrap">
                <?php if ($error): ?><div class="error" style="margin-bottom:1rem;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <form method="POST" id="contactForm">
                    <div class="form-row-2">
                        <div class="form-group"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-input" required></div>
                        <div class="form-group"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-input" required></div>
                    </div>
                    <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Subject</label>
                        <select name="subject" class="form-select" required>
                            <option value="">Select a topic…</option>
                            <option value="inquiry">Product Inquiry</option>
                            <option value="appointment">Request Private Appointment</option>
                            <option value="servicing">Watch Servicing</option>
                            <option value="custom">Bespoke / Custom Order</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Your Message</label>
                        <textarea name="message" class="form-textarea" rows="5" maxlength="1000" oninput="updateCharCount(this)" required></textarea>
                        <div class="char-count" id="charCount">0 / 1000</div>
                    </div>
                    <button type="submit" class="btn btn-gold" style="min-width:200px;">Send Message</button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- SIDE PANEL -->
        <div class="contact-side-panel reveal">
            <div class="map-placeholder" onclick="window.open('https://maps.google.com/?q=12+Rue+de+la+Paix+Geneva','_blank')">
                <div class="map-pin">◉</div>
                <span>12 Rue de la Paix, Geneva</span>
                <small>Click to open in Maps</small>
            </div>
            <p class="side-panel-title" style="margin-top:0;">Opening Hours</p>
            <div class="hours-table" id="hoursTable"></div>
            <p class="side-panel-title" style="margin-top:2rem">Follow AURUM</p>
            <div class="side-socials">
                <a href="#" class="side-social-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
                <a href="#" class="side-social-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                <a href="#" class="side-social-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg></a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    // Opening hours dynamic
    const HOURS = [
        { day: 'Monday', time: '10:00 – 19:00' },
        { day: 'Tuesday', time: '10:00 – 19:00' },
        { day: 'Wednesday', time: '10:00 – 19:00' },
        { day: 'Thursday', time: '10:00 – 20:00' },
        { day: 'Friday', time: '10:00 – 19:00' },
        { day: 'Saturday', time: '10:00 – 18:00' },
        { day: 'Sunday', time: 'Closed' },
    ];
    const today = new Date().getDay(); // 0=Sun
    const todayIdx = today === 0 ? 6 : today - 1;
    const hoursTable = document.getElementById('hoursTable');
    if (hoursTable) {
        hoursTable.innerHTML = HOURS.map((h, i) => `
            <div class="hours-row${i === todayIdx ? ' today' : ''}">
                <span class="day">${h.day}${i === todayIdx ? ' <em style="font-style:normal;font-size:0.6rem;color:var(--gold);letter-spacing:0.1em;"> · TODAY</em>' : ''}</span>
                <span class="time" style="${h.time==='Closed'?'color:var(--grey)':''}">${h.time}</span>
            </div>
        `).join('');
    }

    // Character counter
    function updateCharCount(ta) {
        const el = document.getElementById('charCount');
        if (!el) return;
        const len = ta.value.length;
        el.textContent = len + ' / 1000';
        el.className = 'char-count' + (len > 950 ? ' over' : len > 800 ? ' near' : '');
    }

    // Scroll reveal
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.12 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>