<?php
require_once 'config/db.php';

// التأكد من أن المستخدم مسجل الدخول وهو مدير
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: signin.php');
    exit;
}

// --- معالجة الإجراءات (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // إضافة منتج جديد
    if (isset($_POST['add_product'])) {
        $id = trim($_POST['id']);
        $brand = trim($_POST['brand']);
        $name = trim($_POST['name']);
        $model = trim($_POST['model']);
        $price = floatval($_POST['price']);
        $image = trim($_POST['image']);
        $description = trim($_POST['description']);
        $theme = trim($_POST['theme']);
        
        $stmt = $pdo->prepare("INSERT INTO watches (id, brand, name, model, price, image, description, theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $brand, $name, $model, $price, $image, $description, $theme]);
        header('Location: admin.php?msg=product_added');
        exit;
    }
    
    // تعديل منتج
    if (isset($_POST['edit_product'])) {
        $id = trim($_POST['id']);
        $brand = trim($_POST['brand']);
        $name = trim($_POST['name']);
        $model = trim($_POST['model']);
        $price = floatval($_POST['price']);
        $image = trim($_POST['image']);
        $description = trim($_POST['description']);
        $theme = trim($_POST['theme']);
        
        $stmt = $pdo->prepare("UPDATE watches SET brand=?, name=?, model=?, price=?, image=?, description=?, theme=? WHERE id=?");
        $stmt->execute([$brand, $name, $model, $price, $image, $description, $theme, $id]);
        header('Location: admin.php?msg=product_updated');
        exit;
    }
    
    // حذف منتج
    if (isset($_POST['delete_product'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM watches WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: admin.php?msg=product_deleted');
        exit;
    }
    
    // تحديث حالة الطلب
    if (isset($_POST['update_order_status'])) {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        header('Location: admin.php?msg=order_updated');
        exit;
    }
    
    // حذف طلب
    if (isset($_POST['delete_order'])) {
        $order_id = $_POST['order_id'];
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        header('Location: admin.php?msg=order_deleted');
        exit;
    }
    
    // حذف مستخدم
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        // حذف المستخدم (سيتم حذف كل شيء مرتبط به بسبب ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
        $stmt->execute([$user_id]);
        header('Location: admin.php?msg=user_deleted');
        exit;
    }
    
    // حذف رسالة
    if (isset($_POST['delete_message'])) {
        $msg_id = $_POST['msg_id'];
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$msg_id]);
        header('Location: admin.php?msg=message_deleted');
        exit;
    }
}

// --- جلب البيانات للعرض ---
$products = $pdo->query("SELECT * FROM watches ORDER BY brand, name")->fetchAll();
$orders = $pdo->query("SELECT o.*, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();
$users = $pdo->query("SELECT id, email, full_name, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC")->fetchAll();
$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

// إحصائيات سريعة
$stats = [
    'products' => count($products),
    'orders' => count($orders),
    'users' => count($users),
    'messages' => count($messages)
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AURUM</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@200;300;400;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* تنسيقات خاصة بلوحة التحكم */
        .admin-container { max-width: 1400px; margin: 120px auto 50px; padding: 0 20px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: var(--dark-2); padding: 1.5rem; border-radius: 12px; text-align: center; border: 1px solid rgba(255,255,255,0.05); }
        .stat-number { font-size: 2.5rem; font-family: var(--font-serif); color: var(--gold); }
        .stat-label { color: var(--grey); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; }
        .admin-tabs { display: flex; gap: 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 2rem; flex-wrap: wrap; }
        .tab-btn { background: none; border: none; color: var(--grey); padding: 0.5rem 1.2rem; cursor: pointer; font-size: 0.9rem; transition: 0.3s; }
        .tab-btn.active { color: var(--gold); border-bottom: 2px solid var(--gold); }
        .tab-pane { display: none; background: var(--dark-2); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; overflow-x: auto; }
        .tab-pane.active { display: block; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        th { color: var(--gold); font-weight: 500; }
        .btn-small { padding: 0.3rem 0.7rem; font-size: 0.7rem; margin: 0 0.2rem; border-radius: 4px; cursor: pointer; }
        .btn-edit { background: var(--gold); color: black; border: none; }
        .btn-delete { background: transparent; border: 1px solid #e05555; color: #e05555; }
        .btn-add { background: var(--gold); color: black; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; }
        .form-modal { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--dark-2); padding: 2rem; border-radius: 12px; z-index: 1000; width: 90%; max-width: 500px; display: none; border: 1px solid rgba(201,168,76,0.3); }
        .form-modal input, .form-modal textarea, .form-modal select { width: 100%; padding: 0.5rem; margin: 0.5rem 0; background: var(--dark-3); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 4px; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; display: none; }
        .status-select { padding: 0.3rem; border-radius: 4px; background: var(--dark-3); color: white; border: 1px solid var(--gold); }
        .message-text { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        @media (max-width: 768px) { th, td { font-size: 0.75rem; } .stats-grid { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <div class="admin-name">Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></div>
    </div>
    
    <!-- إحصائيات -->
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-number"><?= $stats['products'] ?></div><div class="stat-label">Products</div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['orders'] ?></div><div class="stat-label">Orders</div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['users'] ?></div><div class="stat-label">Users</div></div>
        <div class="stat-card"><div class="stat-number"><?= $stats['messages'] ?></div><div class="stat-label">Messages</div></div>
    </div>
    
    <!-- تبويبات -->
    <div class="admin-tabs">
        <button class="tab-btn active" data-tab="products">Products</button>
        <button class="tab-btn" data-tab="orders">Orders</button>
        <button class="tab-btn" data-tab="users">Users</button>
        <button class="tab-btn" data-tab="messages">Messages</button>
    </div>
    
    <!-- تبويب المنتجات -->
    <div id="productsTab" class="tab-pane active">
        <div style="margin-bottom: 1rem;">
            <button class="btn-add" onclick="openProductModal()">+ Add New Product</button>
        </div>
        <table>
            <thead>
                <tr><th>ID</th><th>Brand</th><th>Name</th><th>Price</th><th>Image</th><th>Actions</th>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id']) ?></td>
                    <td><?= htmlspecialchars($p['brand']) ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td>$<?= number_format($p['price']) ?></td>
                    <td><img src="<?= htmlspecialchars($p['image']) ?>" width="40" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\'%3E%3Crect width=\'40\' height=\'40\' fill=\'%23222\'/%3E%3C/svg%3E'"></td>
                    <td>
                        <button class="btn-small btn-edit" onclick="editProduct('<?= $p['id'] ?>')">Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="delete_product" value="1">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-small btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- تبويب الطلبات -->
    <div id="ordersTab" class="tab-pane">
        <table>
            <thead>
                <tr><th>Order ID</th><th>User</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['id']) ?></td>
                    <td><?= htmlspecialchars($o['user_email']) ?></td>
                    <td><?= date('Y-m-d', strtotime($o['created_at'])) ?></td>
                    <td>$<?= number_format($o['total']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="update_order_status" value="1">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="status-select" onchange="this.form.submit()">
                                <option value="processing" <?= $o['status']=='processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $o['status']=='shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $o['status']=='delivered' ? 'selected' : '' ?>>Delivered</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this order?');">
                            <input type="hidden" name="delete_order" value="1">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <button type="submit" class="btn-small btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- تبويب المستخدمين -->
    <div id="usersTab" class="tab-pane">
        <table>
            <thead>
                <tr><th>ID</th><th>Email</th><th>Full Name</th><th>Registered</th><th>Actions</th>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this user and all their data?');">
                            <input type="hidden" name="delete_user" value="1">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn-small btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- تبويب الرسائل -->
    <div id="messagesTab" class="tab-pane">
        <table>
            <thead>
                <tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Actions</th>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['name']) ?></td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td class="message-text" title="<?= htmlspecialchars($m['message']) ?>"><?= htmlspecialchars(substr($m['message'], 0, 50)) ?>...</td>
                    <td><?= date('Y-m-d', strtotime($m['created_at'])) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this message?');">
                            <input type="hidden" name="delete_message" value="1">
                            <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn-small btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- مودال إضافة/تعديل منتج -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
<div id="productModal" class="form-modal">
    <h2 id="modalTitle">Add Product</h2>
    <form method="POST" id="productForm">
        <input type="hidden" name="add_product" id="formAction" value="add_product">
        <input type="hidden" name="id" id="productId">
        <input type="text" name="brand" id="productBrand" placeholder="Brand" required>
        <input type="text" name="name" id="productName" placeholder="Name" required>
        <input type="text" name="model" id="productModel" placeholder="Model">
        <input type="number" step="0.01" name="price" id="productPrice" placeholder="Price (USD)" required>
        <input type="text" name="image" id="productImage" placeholder="Image path (e.g., images/r1.jpg)">
        <textarea name="description" id="productDesc" placeholder="Description" rows="3"></textarea>
        <select name="theme" id="productTheme">
            <option value="classic">Classic</option>
            <option value="blue">Blue</option>
            <option value="champagne">Champagne</option>
        </select>
        <div style="margin-top: 1rem;">
            <button type="submit" class="btn-add">Save</button>
            <button type="button" class="btn-small btn-delete" onclick="closeModal()">Cancel</button>
        </div>
    </form>
</div>

<script>
    // التبديل بين التبويبات
    const tabs = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.tab-pane');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab;
            tabs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            panes.forEach(pane => pane.classList.remove('active'));
            document.getElementById(tabId + 'Tab').classList.add('active');
        });
    });
    
    // فتح مودال الإضافة
    function openProductModal() {
        document.getElementById('modalTitle').innerText = 'Add Product';
        document.getElementById('formAction').value = 'add_product';
        document.getElementById('productId').value = '';
        document.getElementById('productBrand').value = '';
        document.getElementById('productName').value = '';
        document.getElementById('productModel').value = '';
        document.getElementById('productPrice').value = '';
        document.getElementById('productImage').value = '';
        document.getElementById('productDesc').value = '';
        document.getElementById('productTheme').value = 'classic';
        document.getElementById('modalOverlay').style.display = 'block';
        document.getElementById('productModal').style.display = 'block';
    }
    
    // تعديل منتج (تعبئة النموذج وتغيير الإجراء)
    function editProduct(id) {
        // جلب البيانات عبر AJAX أو تخزينها في مصفوفة من PHP
        // استخدم البيانات المخزنة في HTML (يمكن إضافة data attributes)
        // للتبسيط، سنستخدم fetch لجلب البيانات من ملف إضافي، أو نمررها هنا عبر PHP المضمن.
        // بما أننا في صفحة PHP، يمكننا تخزين المنتجات في JavaScript.
        const products = <?= json_encode($products) ?>;
        const product = products.find(p => p.id === id);
        if (product) {
            document.getElementById('modalTitle').innerText = 'Edit Product';
            document.getElementById('formAction').value = 'edit_product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productBrand').value = product.brand;
            document.getElementById('productName').value = product.name;
            document.getElementById('productModel').value = product.model;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productImage').value = product.image;
            document.getElementById('productDesc').value = product.description;
            document.getElementById('productTheme').value = product.theme;
            document.getElementById('modalOverlay').style.display = 'block';
            document.getElementById('productModal').style.display = 'block';
        } else {
            alert('Product not found');
        }
    }
    
    function closeModal() {
        document.getElementById('modalOverlay').style.display = 'none';
        document.getElementById('productModal').style.display = 'none';
    }
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>