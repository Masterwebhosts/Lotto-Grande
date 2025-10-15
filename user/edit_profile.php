<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once(__DIR__ . '/navbar_user.php');

// 🚪 السماح فقط للمستخدمين المسجلين
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: /account/login.php", true, 303);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ توليد رمز CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 🔄 عند الحفظ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ فشل التحقق الأمني (CSRF).");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($email)) {
        $error = "❌ يرجى ملء جميع الحقول المطلوبة.";
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "📧 البريد الإلكتروني غير صالح.";
        } else {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
                $stmt->bind_param("sssi", $username, $email, $hashed, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
                $stmt->bind_param("ssi", $username, $email, $user_id);
            }

            if ($stmt->execute()) {
                $success = "✅ تم تحديث بياناتك بنجاح!";
            } else {
                $error = "⚠️ حدث خطأ أثناء التحديث.";
            }
        }
    }
}

// 📄 جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>✏️ تعديل الملف الشخصي | Lotto Grande</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="../assets/navbar.css">
<style>
body {
  font-family: "Cairo", sans-serif;
  color: #fff;
  margin: 0;
  background: linear-gradient(135deg,#f7b733,#fc4a1a,#0d47a1);
  background-size: 400% 400%;
  animation: gradientBG 12s ease infinite;
}
@keyframes gradientBG {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.container {
  max-width: 550px;
  margin: 110px auto 40px;
  padding: 35px;
  background: rgba(0,0,0,0.85);
  border-radius: 15px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.8);
  text-align: center;
}
h2 {
  color: #ffd700;
  margin-bottom: 25px;
  font-size: 24px;
}
label {
  display: block;
  text-align: right;
  margin-bottom: 5px;
  color: #ffd700;
  font-weight: bold;
}
input[type="text"],
input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 10px;
  border: none;
  background: #111;
  color: #fff;
  font-size: 15px;
}
button {
  width: 100%;
  background: #ffd700;
  color: #000;
  border: none;
  padding: 12px;
  border-radius: 10px;
  font-weight: bold;
  font-size: 16px;
  cursor: pointer;
  transition: 0.3s;
}
button:hover {
  background: #ffca2c;
  transform: scale(1.03);
}
.success {
  color: #00ff9c;
  background: rgba(0, 255, 156, 0.1);
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 15px;
}
.error {
  color: #ff4d4d;
  background: rgba(255, 77, 77, 0.1);
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 15px;
}
.note {
  color: #ccc;
  font-size: 13px;
  margin-top: 10px;
}
</style>
</head>
<body>

<div class="container">
  <h2>✏️ تعديل الملف الشخصي</h2>

  <?php if (!empty($success)): ?>
    <div class="success"><?= $success ?></div>
  <?php elseif (!empty($error)): ?>
    <div class="error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <label>👤 اسم المستخدم</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label>📧 البريد الإلكتروني</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

    <label>🔒 كلمة المرور الجديدة (اختياري)</label>
    <input type="password" name="password" placeholder="اتركها فارغة إذا لا تريد التغيير">

    <button type="submit" name="update_profile">💾 حفظ التعديلات</button>
  </form>

  <p class="note">⚙️ يمكنك تعديل معلوماتك بأمان، كلمة المرور مشفرة بالكامل 🔐</p>
</div>

</body>
</html>

