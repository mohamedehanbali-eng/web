<?php
require_once 'config/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user'])) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// جلب بيانات المستخدم
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// معالجة رفع الصورة الشخصية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowed) && $file['size'] < 2 * 1024 * 1024) {
            $imgData = base64_encode(file_get_contents($file['tmp_name']));
            $avatar = 'data:' . $file['type'] . ';base64,' . $imgData;
            $upd = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $upd->execute([$avatar, $user_id]);
            $_SESSION['user']['avatar'] = $avatar;
            header('Location: profile.php');
            exit;
        } else { $avatar_error = "Invalid image or too large (max 2MB)"; }
    }
}

// تحديث المعلومات الشخصية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $upd = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
    $upd->execute([$full_name, $phone, $user_id]);
    $_SESSION['user']['name'] = $full_name;
    $profile_success = "Profile updated successfully.";
    header('Location: profile.php');
    exit;
}

// تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if (strlen($new) < 8) { $pw_error = "New password must be at least 8 characters."; }
    elseif ($new !== $confirm) { $pw_error = "Passwords do not match."; }
    else {
        $check = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $check->execute([$user_id]);
        $userPw = $check->fetchColumn();
        if (password_verify($old, $userPw)) {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->execute([$newHash, $user_id]);
            $pw_success = "Password changed successfully.";
        } else { $pw_error = "Current password is incorrect."; }
    }
}

// إضافة عنوان جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $name = trim($_POST['name']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $postal = trim($_POST['postal']);
    $country = trim($_POST['country']);
    $phone_addr = trim($_POST['phone']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    if ($name && $street && $city && $country) {
        if ($is_default) {
            $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
        }
        $ins = $pdo->prepare("INSERT INTO addresses (user_id, name, street, city, postal, country, phone, is_default) VALUES (?,?,?,?,?,?,?,?)");
        $ins->execute([$user_id, $name, $street, $city, $postal, $country, $phone_addr, $is_default]);
        header('Location: profile.php');
        exit;
    }
}

// حذف عنوان
if (isset($_GET['delete_addr'])) {
    $addr_id = $_GET['delete_addr'];
    $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?")->execute([$addr_id, $user_id]);
    header('Location: profile.php');
    exit;
}

// تعيين عنوان افتراضي
if (isset($_GET['set_default'])) {
    $addr_id = $_GET['set_default'];
    $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$addr_id, $user_id]);
    header('Location: profile.php');
    exit;
}

// إزالة من المفضلة
if (isset($_GET['remove_wish'])) {
    $wish_id = $_GET['remove_wish'];
    $pdo->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?")->execute([$wish_id, $user_id]);
    header('Location: profile.php');
    exit;
}

// حذف الحساب
if (isset($_GET['delete_account'])) {
    if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

// جلب العناوين
$addresses = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC");
$addresses->execute([$user_id]);
$addresses = $addresses->fetchAll();

// جلب المفضلة
$wishlist = $pdo->prepare("SELECT w.*, watches.name, watches.brand, watches.price, watches.image 
                           FROM wishlist w JOIN watches ON w.watch_id = watches.id 
                           WHERE w.user_id = ?");
$wishlist->execute([$user_id]);
$wishlist = $wishlist->fetchAll();

// جلب الطلبات
$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->execute([$user_id]);
$orders = $orders->fetchAll();
foreach ($orders as &$order) {
    $items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items->execute([$order['id']]);
    $order['items'] = $items->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — AURUM</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@200;300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* تنسيقات الصفحة الشخصية */
        .profile-page { max-width: 1200px; margin: 120px auto 50px; padding: 0 20px; }
        .profile-header { background: var(--dark-2); padding: 2rem; border-radius: 12px; display: flex; align-items: center; gap: 2rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .avatar-container { position: relative; width: 100px; height: 100px; }
        .avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 2px solid var(--gold); cursor: pointer; background: var(--dark-3); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: var(--gold); }
        .avatar-edit { position: absolute; bottom: 0; right: 0; background: var(--gold); border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: black; font-size: 0.8rem; }
        .profile-info h2 { margin-bottom: 0.25rem; }
        .profile-stats { display: flex; gap: 1.5rem; margin: 1rem 0; flex-wrap: wrap; }
        .stat-card { background: var(--dark-3); padding: 0.8rem 1.2rem; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 1.5rem; font-family: var(--font-serif); color: var(--gold); }
        .tabs { display: flex; gap: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 1.5rem; flex-wrap: wrap; }
        .tab-btn { background: none; border: none; color: var(--grey); padding: 0.5rem 1.2rem; cursor: pointer; font-size: 0.9rem; transition: 0.3s; }
        .tab-btn.active { color: var(--gold); border-bottom: 2px solid var(--gold); }
        .tab-pane { display: none; background: var(--dark-2); padding: 2rem; border-radius: 12px; margin-bottom: 2rem; }
        .tab-pane.active { display: block; }
        .order-card { background: var(--dark-3); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .order-header { display: flex; justify-content: space-between; flex-wrap: wrap; cursor: pointer; }
        .order-items { margin-top: 0.5rem; display: none; }
        .order-items.open { display: block; }
        .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem; }
        .wishlist-card { background: var(--dark-3); padding: 1rem; border-radius: 8px; text-align: center; }
        .wishlist-card img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 0.5rem; }
        .address-card { background: var(--dark-3); padding: 1rem; border-radius: 8px; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .form-group { margin-bottom: 1rem; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.6rem; background: var(--dark-3); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 4px; }
        .btn-danger { background: transparent; border: 1px solid #e05555; color: #e05555; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; }
        .success { background: rgba(129,199,132,0.1); color: #81c784; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem; }
        .error { background: rgba(224,85,85,0.1); color: #e05555; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem; }
        @media (max-width: 700px) { .profile-header { flex-direction: column; text-align: center; } .address-card { flex-direction: column; gap: 0.5rem; } }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="profile-page">
    <!-- الهيدر مع الصورة الشخصية -->
    <div class="profile-header">
        <div class="avatar-container">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= htmlspecialchars($user['avatar']) ?>" class="avatar" id="avatarImg">
            <?php else: ?>
                <div class="avatar" id="avatarInitials"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div class="avatar-edit" onclick="document.getElementById('avatarUpload').click()">✎</div>
            <form method="POST" enctype="multipart/form-data" style="display:none;">
                <input type="file" name="avatar" id="avatarUpload" accept="image/*" onchange="this.form.submit()">
                <input type="hidden" name="update_avatar" value="1">
            </form>
        </div>
        <div class="profile-info">
            <h2><?= htmlspecialchars($user['full_name']) ?></h2>
            <p><?= htmlspecialchars($user['email']) ?></p>
            <div class="profile-stats">
                <div class="stat-card"><div class="stat-number"><?= count($orders) ?></div>Orders</div>
                <div class="stat-card"><div class="stat-number"><?= count($wishlist) ?></div>Wishlist</div>
                <div class="stat-card"><div class="stat-number"><?= count($addresses) ?></div>Addresses</div>
            </div>
        </div>
    </div>

    <!-- التبويبات -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="orders">Orders</button>
        <button class="tab-btn" data-tab="wishlist">Wishlist</button>
        <button class="tab-btn" data-tab="addresses">Addresses</button>
        <button class="tab-btn" data-tab="account">Account</button>
    </div>

    <!-- Orders Tab -->
    <div id="ordersTab" class="tab-pane active">
        <h3>Your Orders</h3>
        <?php if (empty($orders)): ?>
            <p>You haven't placed any orders yet.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header" onclick="toggleOrder('<?= $order['id'] ?>')">
                        <div><strong>Order #<?= htmlspecialchars($order['id']) ?></strong> - <?= date('F j, Y', strtotime($order['created_at'])) ?></div>
                        <div>Total: $<?= number_format($order['total']) ?></div>
                        <div class="order-status status-<?= $order['status'] ?>"><?= $order['status'] ?></div>
                        <div style="color:var(--grey);">⌄</div>
                    </div>
                    <div class="order-items" id="order-<?= $order['id'] ?>">
                        <?php foreach ($order['items'] as $item): ?>
                            <div style="padding:0.5rem 0; border-bottom:1px solid #333;">
                                <?= htmlspecialchars($item['brand']) ?> <?= htmlspecialchars($item['name']) ?> x<?= $item['quantity'] ?> – $<?= number_format($item['price'] * $item['quantity']) ?>
                                <?php if ($item['engraving']): ?><br><small>Engraving: "<?= htmlspecialchars($item['engraving']) ?>"</small><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div>Shipping: <?= htmlspecialchars($order['shipping_name']) ?>, <?= $order['shipping_city'] ?>, <?= $order['shipping_country'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Wishlist Tab -->
    <div id="wishlistTab" class="tab-pane">
        <h3>Your Wishlist</h3>
        <?php if (empty($wishlist)): ?>
            <p>Your wishlist is empty.</p>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlist as $item): ?>
                    <div class="wishlist-card">
                        <img src="<?= htmlspecialchars($item['image']) ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%23222\'/%3E%3C/svg%3E'">
                        <div><strong><?= htmlspecialchars($item['brand']) ?></strong> <?= htmlspecialchars($item['name']) ?></div>
                        <div>$<?= number_format($item['price']) ?></div>
                        <a href="profile.php?remove_wish=<?= $item['id'] ?>" class="btn-danger">Remove</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Addresses Tab -->
    <div id="addressesTab" class="tab-pane">
        <h3>Saved Addresses</h3>
        <?php foreach ($addresses as $addr): ?>
            <div class="address-card">
                <div>
                    <strong><?= htmlspecialchars($addr['name']) ?></strong> <?= $addr['is_default'] ? '(Default)' : '' ?><br>
                    <?= htmlspecialchars($addr['street']) ?>, <?= htmlspecialchars($addr['city']) ?> <?= htmlspecialchars($addr['postal']) ?><br>
                    <?= htmlspecialchars($addr['country']) ?> - <?= htmlspecialchars($addr['phone']) ?>
                </div>
                <div>
                    <a href="profile.php?delete_addr=<?= $addr['id'] ?>" class="btn-danger" onclick="return confirm('Delete this address?')">Delete</a>
                    <?php if (!$addr['is_default']): ?>
                        <a href="profile.php?set_default=<?= $addr['id'] ?>" class="btn-ghost" style="margin-left:0.5rem;">Set Default</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <details style="margin-top:1rem;">
            <summary style="cursor:pointer; color:var(--gold);">+ Add New Address</summary>
            <form method="POST" style="margin-top:1rem;">
                <div class="form-group"><input type="text" name="name" placeholder="Full name" required></div>
                <div class="form-group"><input type="text" name="street" placeholder="Street address" required></div>
                <div class="form-group"><input type="text" name="city" placeholder="City" required></div>
                <div class="form-group"><input type="text" name="postal" placeholder="Postal code" required></div>
                <div class="form-group"><input type="text" name="country" placeholder="Country" required></div>
                <div class="form-group"><input type="tel" name="phone" placeholder="Phone"></div>
                <label><input type="checkbox" name="is_default"> Set as default</label><br><br>
                <button type="submit" name="add_address" class="btn-gold">Save Address</button>
            </form>
        </details>
    </div>

    <!-- Account Tab (الملف الشخصي وتغيير كلمة المرور) -->
    <div id="accountTab" class="tab-pane">
        <h3>Personal Information</h3>
        <?php if (isset($profile_success)): ?><div class="success"><?= $profile_success ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required></div>
            <div class="form-group"><input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled></div>
            <div class="form-group"><input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="Phone number"></div>
            <button type="submit" name="update_profile" class="btn-gold">Update Profile</button>
        </form>

        <hr style="margin: 2rem 0;">
        <h3>Change Password</h3>
        <?php if (isset($pw_success)): ?><div class="success"><?= $pw_success ?></div><?php endif; ?>
        <?php if (isset($pw_error)): ?><div class="error"><?= $pw_error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><input type="password" name="old_password" placeholder="Current password" required></div>
            <div class="form-group"><input type="password" name="new_password" placeholder="New password (min 8 chars)" required></div>
            <div class="form-group"><input type="password" name="confirm_password" placeholder="Confirm new password" required></div>
            <button type="submit" name="change_password" class="btn-gold">Change Password</button>
        </form>

        <hr style="margin: 2rem 0;">
        <div style="border:1px solid #e05555; padding:1rem; border-radius:8px;">
            <h3 style="color:#e05555;">Danger Zone</h3>
            <p>Once you delete your account, all data will be permanently removed.</p>
            <a href="profile.php?delete_account=1" class="btn-danger" onclick="return confirm('Are you absolutely sure? This cannot be undone.')">Delete Account</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    function toggleOrder(orderId) {
        const el = document.getElementById('order-' + orderId);
        el.classList.toggle('open');
    }
    // Tab switching
    const tabs = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.tab-pane');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            tabs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            panes.forEach(pane => pane.classList.remove('active'));
            if (tabId === 'orders') document.getElementById('ordersTab').classList.add('active');
            if (tabId === 'wishlist') document.getElementById('wishlistTab').classList.add('active');
            if (tabId === 'addresses') document.getElementById('addressesTab').classList.add('active');
            if (tabId === 'account') document.getElementById('accountTab').classList.add('active');
        });
    });
</script>
</body>
</html>