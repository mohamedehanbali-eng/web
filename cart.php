<?php require_once 'config/db.php';
if (!isset($_SESSION['user'])) { header('Location: signin.php'); exit; }
$user_id = $_SESSION['user']['id'];
if (isset($_GET['remove'])) {
    $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")->execute([$_GET['remove'], $user_id]);
    header('Location: cart.php'); exit;
}
if (isset($_POST['update_qty'])) {
    $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?")->execute([$_POST['quantity'], $_POST['cart_id'], $user_id]);
    header('Location: cart.php'); exit;
}
$stmt = $pdo->prepare("SELECT c.*, w.name, w.brand, w.image FROM cart c JOIN watches w ON c.watch_id = w.id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();
$total = 0;
foreach ($items as $item) $total += $item['price'] * $item['quantity'];
?>
<!DOCTYPE html>
<html>
<head><title>My Cart - AURUM</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="cart-container" style="max-width:1000px; margin:120px auto 50px; padding:20px;">
    <h1>Shopping Cart</h1>
    <?php if (empty($items)): ?>
        <div class="empty-cart"><p>Your cart is empty.</p><a href="index.php" class="btn-gold">Continue Shopping</a></div>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
        <div class="cart-item" style="display:flex; gap:20px; background:#1a1a1a; margin-bottom:20px; padding:20px; border-radius:8px; align-items:center;">
            <img src="<?= $item['image'] ?>" width="100" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%23222\'/%3E%3C/svg%3E'">
            <div style="flex:1"><h3><?= $item['brand'] ?> <?= $item['name'] ?></h3><p>Color: <?= $item['color'] ?> | Strap: <?= $item['strap'] ?></p><?php if($item['engraving']): ?><p>Engraving: "<?= htmlspecialchars($item['engraving']) ?>"</p><?php endif; ?><div>$<?= number_format($item['price']) ?> each</div></div>
            <form method="POST"><input type="hidden" name="cart_id" value="<?= $item['id'] ?>"><input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" style="width:60px;"><button type="submit" name="update_qty" class="btn-ghost">Update</button></form>
            <a href="cart.php?remove=<?= $item['id'] ?>" class="btn-danger">Remove</a>
        </div>
        <?php endforeach; ?>
        <div style="text-align:right"><h2>Total: $<?= number_format($total) ?></h2><a href="checkout.php" class="btn-gold">Proceed to Checkout</a></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>