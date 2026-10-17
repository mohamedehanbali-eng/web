<?php
require_once 'config/db.php';
if (!isset($_SESSION['user'])) {
    header('Location: signin.php');
    exit;
}
$user_id = $_SESSION['user']['id'];

// Fetch user orders
$orders_stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();

// For each order, fetch its items
foreach ($orders as &$order) {
    $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items_stmt->execute([$order['id']]);
    $order['items'] = $items_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head><title>My Orders - AURUM</title><link rel="stylesheet" href="style.css"></head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="orders-container" style="max-width: 1000px; margin: 100px auto 50px; padding: 20px;">
    <h1>My Orders</h1>
    <?php if (empty($orders)): ?>
        <p>You haven't placed any orders yet.</p>
        <a href="index.php" class="btn-gold">Start Shopping</a>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
        <div class="order-card" style="background: var(--dark-2); padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            <div style="display:flex; justify-content:space-between;">
                <strong>Order #<?= htmlspecialchars($order['id']) ?></strong>
                <span class="order-status status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
            </div>
            <div>Date: <?= date('F j, Y', strtotime($order['created_at'])) ?></div>
            <div>Total: $<?= number_format($order['total']) ?></div>
            <div>Shipping to: <?= htmlspecialchars($order['shipping_name']) ?>, <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_country']) ?></div>
            <div class="order-items" style="margin-top:10px;">
                <h4>Items:</h4>
                <?php foreach ($order['items'] as $item): ?>
                    <div class="order-item">
                        <?= htmlspecialchars($item['brand']) ?> <?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?> – $<?= number_format($item['price'] * $item['quantity']) ?>
                        <?php if ($item['engraving']): ?>
                            <br><small>Engraving: "<?= htmlspecialchars($item['engraving']) ?>"</small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>