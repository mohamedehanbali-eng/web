<?php require_once 'config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AURUM — Fine Timepieces</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@200;300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* تنسيق الفيديو في الهيرو */
       .hero-video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;  /* تأكد أنه أعلى من الخلفية لكن تحت المحتوى */
    pointer-events: none; /* حتى لا يمنع النقر على الأزرار */
}
        .hero-section {
            position: relative;
            overflow: hidden;
        }
       .hero-content {
    z-index: 2;
    position: relative;
}
        .hero-content h1, .hero-content p, .hero-content a {
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }

        .hero-bg {
    z-index: 0;
}

        .hero-section::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0,0,0,0.4), rgba(201,168,76,0.1));
    pointer-events: none;
    z-index: 1;
}
        @media (max-width: 768px) {
            .hero-video {
                object-position: 30% 50%;
            }
        }
        /* تنسيق الفلاتر والكروت */
        .catalog-filters { display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 2rem; flex-wrap: wrap; }
        .filter-btn { background: transparent; border: 1px solid #555; padding: 0.5rem 1rem; color: #ccc; cursor: pointer; transition: 0.3s; }
        .filter-btn.active, .filter-btn:hover { background: var(--gold); color: black; border-color: var(--gold); }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- Hero section مع فيديو خلفية محلي -->
<section id="home" class="hero-section">
    <!-- فيديو الساعة الحقيقية (من المسار المحلي) -->
   <video class="hero-video" autoplay muted loop playsinline id="heroVideo">
    <source src="video/6328383-uhd_4096_2160_25fps.mp4" type="video/mp4">
    <!-- إذا فشل الملف الأول، جرب صيغة WebM (إذا كنت تملكها) -->
    متصفحك لا يدعم تشغيل الفيديو.
</video>
    
    <div class="hero-bg"><div class="hero-overlay"></div></div>
    <div class="hero-content">
        <p class="hero-pretitle">Est. 1847 · Geneva, Switzerland</p>
        <h1 class="hero-title"><span>Time Is Not</span><em>Measured.</em><span>It Is Experienced.</span></h1>
        <p class="hero-subtitle">Masterpieces born from centuries of horological excellence.</p>
        <a href="#catalog" class="btn btn-gold">Explore Collection</a>
    </div>
</section>

<!-- باقي الموقع (كروت الساعات) -->
<section id="catalog" class="catalog-section">
    <div class="section-header"><h2 class="section-title">Masterpieces</h2></div>
    <div class="catalog-filters" id="filters">
        <button class="filter-btn active" data-brand="all">All</button>
        <?php
        $brands = $pdo->query("SELECT DISTINCT brand FROM watches")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($brands as $b): ?>
            <button class="filter-btn" data-brand="<?= $b ?>"><?= $b ?></button>
        <?php endforeach; ?>
    </div>
    <div class="catalog-grid" id="catalogGrid">
        <?php
        $watches = $pdo->query("SELECT * FROM watches")->fetchAll();
        foreach ($watches as $watch):
            $fallback_svg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Crect width='200' height='200' fill='%23222'/%3E%3Ctext x='100' y='110' text-anchor='middle' fill='%23c9a84c' font-size='14'%3EWatch%3C/text%3E%3C/svg%3E";
        ?>
        <div class="watch-card" data-brand="<?= $watch['brand'] ?>" onclick="openProduct('<?= $watch['id'] ?>')">
            <div class="watch-card-visual">
                <img src="<?= htmlspecialchars($watch['image']) ?>" alt="<?= $watch['name'] ?>" class="watch-card-img" onerror="this.onerror=null; this.src='<?= $fallback_svg ?>';">
                <div class="watch-card-overlay"><button class="btn btn-gold">Quick View</button></div>
            </div>
            <div class="watch-card-info">
                <div class="watch-brand-tag"><?= $watch['brand'] ?></div>
                <div class="watch-name"><?= $watch['name'] ?></div>
                <div class="watch-price-row"><span class="watch-price">$<?= number_format($watch['price']) ?></span><button class="quick-view-btn" onclick="event.stopPropagation();openProduct('<?= $watch['id'] ?>')">Details →</button></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
    // فلترة الكروت حسب العلامة التجارية
    const filterBtns = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.watch-card');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const brand = btn.dataset.brand;
            cards.forEach(card => {
                if (brand === 'all' || card.dataset.brand === brand) card.style.display = '';
                else card.style.display = 'none';
            });
        });
    });
    function openProduct(id) { window.location.href = `product.php?id=${id}`; }

     const heroVideo = document.getElementById('heroVideo');
    if (heroVideo) {
        heroVideo.addEventListener('error', function(e) {
            console.log('فشل تحميل الفيديو، جاري إظهار صورة بديلة');
            // إخفاء الفيديو وإظهار خلفية ثابتة
            heroVideo.style.display = 'none';
            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                heroSection.style.background = 'radial-gradient(ellipse at 60% 50%, #1a1208 0%, #0a0a0a 65%)';
                heroSection.style.backgroundSize = 'cover';
            }
        });
        // إذا لم يبدأ التشغيل تلقائياً (بعض المتصفحات)
        heroVideo.play().catch(function(err) {
            console.log('التشغيل التلقائي غير مسموح:', err);
        });
    }
</script>
</body>
</html>