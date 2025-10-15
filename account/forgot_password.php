<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';

$message = "";
$error = "";

// 📋 المعالجة عند إرسال البريد الإلكتروني
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "⚠️ يرجى إدخال بريدك الإلكتروني.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ البريد الإلكتروني غير صالح.";
    } else {
        // التحقق من وجود البريد في قاعدة البيانات
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            // مبدئيًا فقط نعرض رسالة النجاح (بدون إرسال فعلي)
            $message = "📩 تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.";
        } else {
            $error = "❌ هذا البريد غير مسجل لدينا.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🔑 نسيت كلمة المرور</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ✅ استدعاء الستايل الأصلي -->
  <link rel="stylesheet" href="../assets/style.css">

  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: #111;
      color: #fff;
      font-family: "Cairo", sans-serif;
    }

    .forgot-box {
      background: rgba(0,0,0,0.8);
      padding: 35px 30px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(255,215,0,0.2);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .forgot-box h2 {
      color: gold;
      margin-bottom: 25px;
    }

    .input-field {
      position: relative;
      margin: 12px 0;
      width: 100%;
    }

    .input-field input {
      width: 100%;
      height: 50px;
      padding: 10px;
      border: none;
      border-radius: 8px;
      text-align: center;
      background: #222;
      color: #fff;
      font-size: 16px;
      box-sizing: border-box;
      transition: 0.3s;
    }

    .input-field input:focus {
      border: 2px solid gold;
      outline: none;
    }

    .btn {
      width: 70%;
      margin-top: 18px;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: gold;
      color: #000;
      font-weight: bold;
      cursor: pointer;
      font-size: 17px;
      transition: 0.3s;
    }

    .btn:hover {
      background: #ffca2c;
    }

    .msg {
      margin: 10px 0;
    }

    a {
      color: gold;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="forgot-box">
    <h2>🔑 نسيت كلمة المرور</h2>

    <?php if ($error): ?>
      <p class="msg" style="color:red;"><?= $error; ?></p>
    <?php elseif ($message): ?>
      <p class="msg" style="color:lightgreen;"><?= $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="input-field">
        <input type="email" name="email" placeholder="📧 أدخل بريدك الإلكتروني" required>
      </div>

      <button type="submit" class="btn">📩 إرسال رابط الاستعادة</button>

      <p class="msg">
        <a href="login.php">⬅️ العودة إلى تسجيل الدخول</a>
      </p>
    </form>
  </div>
</body>
</html>
