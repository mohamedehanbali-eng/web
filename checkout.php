<?php require_once 'config/db.php';
if (!isset($_SESSION['user'])) { header('Location: signin.php'); exit; }
$user_id = $_SESSION['user']['id'];
$cart_stmt = $pdo->prepare("SELECT c.*, w.name, w.brand FROM cart c JOIN watches w ON c.watch_id = w.id WHERE c.user_id = ?");
$cart_stmt->execute([$user_id]);
$cart = $cart_stmt->fetchAll();
if (empty($cart)) { header('Location: cart.php'); exit; }
$addresses = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$addresses->execute([$user_id]);
$addresses = $addresses->fetchAll();
$total = array_sum(array_map(fn($i)=>$i['price']*$i['quantity'], $cart));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addr_id = $_POST['address_id'];
    $addr_stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $addr_stmt->execute([$addr_id, $user_id]);
    $addr = $addr_stmt->fetch();
    if (!$addr) die("Invalid address");
    $order_id = 'ORD-' . strtoupper(uniqid());
    $order = $pdo->prepare("INSERT INTO orders (id, user_id, total, shipping_name, shipping_street, shipping_city, shipping_postal, shipping_country) VALUES (?,?,?,?,?,?,?,?)");
    $order->execute([$order_id, $user_id, $total, $addr['name'], $addr['street'], $addr['city'], $addr['postal'], $addr['country']]);
    foreach ($cart as $item) {
        $oi = $pdo->prepare("INSERT INTO order_items (order_id, watch_id, brand, name, price, quantity, color, strap, engraving) VALUES (?,?,?,?,?,?,?,?,?)");
        $oi->execute([$order_id, $item['watch_id'], $item['brand'], $item['name'], $item['price'], $item['quantity'], $item['color'], $item['strap'], $item['engraving']]);
    }
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
    header("Location: orders.php?success=1"); exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Checkout - AURUM</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php include 'includes/navbar.php'; ?>
<div style="max-width:800px; margin:120px auto 50px; padding:20px;">
    <h1>Checkout</h1>
    <form method="POST">
        <h3>Select Address</h3>
        <?php if (empty($addresses)): ?><p>No saved addresses. <a href="profile.php">Add one</a></p><?php else: ?>
            <?php foreach ($addresses as $addr): ?>
                <label style="display:block;"><input type="radio" name="address_id" value="<?= $addr['id'] ?>" <?= $addr['is_default']?'checked':'' ?> required> <?= htmlspecialchars($addr['name']) ?>, <?= $addr['street'] ?>, <?= $addr['city'] ?>, <?= $addr['country'] ?></label>
            <?php endforeach; ?>
        <?php endif; ?>
        <h3>Order Summary</h3>
        <?php foreach ($cart as $item): ?>
            <div><?= $item['brand'] ?> <?= $item['name'] ?> x<?= $item['quantity'] ?> - $<?= number_format($item['price'] * $item['quantity']) ?></div>
        <?php endforeach; ?>
        <h2>Total: $<?= number_format($total) ?></h2>
        <button type="submit" class="btn-gold">Place Order</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>