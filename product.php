<?php require_once 'config/db.php';
if (!isset($_GET['id'])) { header('Location: index.php'); exit; }
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM watches WHERE id = ?");
$stmt->execute([$id]);
$watch = $stmt->fetch();
if (!$watch) { header('Location: index.php'); exit; }

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user'])) { header('Location: signin.php'); exit; }
    $user_id = $_SESSION['user']['id'];
    $quantity = $_POST['quantity'] ?? 1;
    $color = $_POST['color'] ?? 'silver';
    $strap = $_POST['strap'] ?? 'leather';
    $engraving = trim($_POST['engraving'] ?? '');
    $total_price = $watch['price'];
    if ($color === 'gold') $total_price += 800;
    if ($strap === 'steel') $total_price += 350;
    if ($engraving) $total_price += 250;
    $check = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND watch_id = ? AND color = ? AND strap = ? AND engraving = ?");
    $check->execute([$user_id, $watch['id'], $color, $strap, $engraving]);
    if ($check->rowCount()) {
        $update = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        $update->execute([$quantity, $check->fetchColumn()]);
    } else {
        $insert = $pdo->prepare("INSERT INTO cart (user_id, watch_id, quantity, color, strap, engraving, price) VALUES (?,?,?,?,?,?,?)");
        $insert->execute([$user_id, $watch['id'], $quantity, $color, $strap, $engraving, $total_price]);
    }
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title><?= $watch['name'] ?> - AURUM</title>
<link rel="stylesheet" href="style.css">
<style>
    .product-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; max-width: 1200px; margin: 120px auto 50px; padding: 0 2rem; }
    .product-image img { width: 100%; border-radius: 12px; }
    .product-info h1 { font-family: var(--font-display); font-size: 2.5rem; margin-bottom: 0.5rem; }
    .price { font-size: 2rem; color: var(--gold); margin: 1rem 0; }
    .customization { margin: 2rem 0; }
    .color-options { display: flex; gap: 1rem; margin: 0.5rem 0 1rem; }
    .color-swatch { width: 40px; height: 40px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; }
    .color-swatch.selected { border-color: var(--gold); }
    .strap-options button { margin-right: 0.5rem; }
    .btn-gold { margin-top: 1rem; }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="product-detail">
    <div class="product-image">
        <img src="<?= $watch['image'] ?>" alt="<?= $watch['name'] ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'400\' viewBox=\'0 0 200 200\'%3E%3Crect width=\'200\' height=\'200\' fill=\'%23222\'/%3E%3Ctext x=\'100\' y=\'110\' fill=\'%23c9a84c\' font-size=\'14\'%3EWatch%3C/text%3E%3C/svg%3E'">
    </div>
    <div class="product-info">
        <h1><?= $watch['brand'] ?> <?= $watch['name'] ?></h1>
        <p><?= $watch['description'] ?></p>
        <div class="price" id="basePrice">$<?= number_format($watch['price']) ?></div>
        <form method="POST">
            <div class="customization">
                <label>Case Color:</label>
                <div class="color-options">
                    <div class="color-swatch selected" style="background:#c0c0c0" data-color="silver" data-add="0"></div>
                    <div class="color-swatch" style="background:#c9a84c" data-color="gold" data-add="800"></div>
                    <div class="color-swatch" style="background:#2c5f8a" data-color="blue" data-add="300"></div>
                </div>
                <label>Strap:</label>
                <div class="strap-options">
                    <button type="button" class="strap-btn selected" data-strap="leather" data-add="0">Leather</button>
                    <button type="button" class="strap-btn" data-strap="steel" data-add="350">Steel +$350</button>
                    <button type="button" class="strap-btn" data-strap="rubber" data-add="150">Rubber +$150</button>
                </div>
                <label>Engraving (+$250):</label>
                <input type="text" name="engraving" placeholder="Max 40 characters" maxlength="40" id="engraving">
                <input type="hidden" name="color" id="colorInput" value="silver">
                <input type="hidden" name="strap" id="strapInput" value="leather">
                <input type="hidden" name="quantity" value="1">
            </div>
            <div class="price" id="finalPrice">$<?= number_format($watch['price']) ?></div>
            <button type="submit" name="add_to_cart" class="btn btn-gold">Add to Cart</button>
        </form>
    </div>
</div>
<script>
    const basePrice = <?= $watch['price'] ?>;
    const priceSpan = document.getElementById('finalPrice');
    const colorInput = document.getElementById('colorInput');
    const strapInput = document.getElementById('strapInput');
    function updatePrice() {
        let extra = 0;
        const selectedColor = document.querySelector('.color-swatch.selected');
        if (selectedColor) extra += parseInt(selectedColor.dataset.add) || 0;
        const selectedStrap = document.querySelector('.strap-btn.selected');
        if (selectedStrap) extra += parseInt(selectedStrap.dataset.add) || 0;
        const engraving = document.getElementById('engraving').value.trim();
        if (engraving) extra += 250;
        priceSpan.innerText = '$' + (basePrice + extra).toLocaleString();
    }
    document.querySelectorAll('.color-swatch').forEach(sw => {
        sw.addEventListener('click', () => {
            document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
            sw.classList.add('selected');
            colorInput.value = sw.dataset.color;
            updatePrice();
        });
    });
    document.querySelectorAll('.strap-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.strap-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            strapInput.value = btn.dataset.strap;
            updatePrice();
        });
    });
    document.getElementById('engraving').addEventListener('input', updatePrice);
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>