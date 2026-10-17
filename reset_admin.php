<?php
require_once 'config/db.php';

// تحديث كلمة مرور المدير
$new_password = 'admin123';
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

// أولاً: تأكد من وجود المستخدم admin@aurum.com
$check = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@aurum.com'");
$check->execute();
if ($check->rowCount() > 0) {
    // تحديث كلمة المرور
    $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@aurum.com'");
    $update->execute([$hashed]);
    echo "✅ تم تحديث كلمة مرور المدير إلى: <strong>admin123</strong><br>";
    echo "يمكنك الآن <a href='signin.php'>تسجيل الدخول</a>.";
} else {
    // إدراج المدير إذا لم يكن موجوداً
    $insert = $pdo->prepare("INSERT INTO users (email, password, full_name, is_admin) VALUES (?, ?, ?, 1)");
    $insert->execute(['admin@aurum.com', $hashed, 'Administrator']);
    echo "✅ تم إنشاء حساب المدير.<br>";
    echo "البريد: admin@aurum.com<br>";
    echo "كلمة المرور: admin123<br>";
    echo "<a href='signin.php'>تسجيل الدخول الآن</a>";
}
?>