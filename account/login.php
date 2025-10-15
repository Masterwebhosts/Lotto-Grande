<?php
// ✅ Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';

$error = "";

// 🚪 Check if user already logged in properly
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'user') {
        header("Location: ../user/dashboard.php");
        exit;
    }
}

// ❗ Only clear invalid sessions on GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}


// 📋 Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "⚠️ Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // ✅ Save session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                $_SESSION['balance'] = $user['balance'];

                // ✅ Redirect by role
                if ($_SESSION['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                    exit;
                } else {
                    header("Location: ../user/dashboard.php");
                    exit;
                }
            } else {
                $error = "❌ Incorrect password.";
            }
        } else {
            $error = "❌ No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🔐 Login | Lotto Grande</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
    .login-box {
      background: rgba(0,0,0,0.85);
      padding: 35px 30px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(255,215,0,0.2);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    .login-box h2 {
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
      padding: 10px 40px 10px 10px;
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
    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: gold;
      font-size: 20px;
      user-select: none;
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
    .btn:hover { background: #ffca2c; }
    .msg { margin: 10px 0; }
    a { color: gold; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .forgot-link {
      margin-top: 12px;
      display: block;
      color: #ccc;
      font-size: 15px;
    }
    .forgot-link:hover { color: gold; }
  </style>
</head>

<body>
  <div class="login-box">
    <h2>🔐 Login</h2>


    <?php if ($error): ?>
      <p class="msg" style="color:red;"><?= htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="input-field">
        <input type="email" name="email" placeholder="📧 Email address" required>
      </div>
      <div class="input-field">
        <input type="password" id="password" name="password" placeholder="🔑 Password" required>
        <span class="toggle-password" onclick="togglePassword('password', this)">👁️</span>
      </div>
      <button type="submit" class="btn">Login</button>

      <a href="forgot_password.php" class="forgot-link">Forgot your password?</a>
      <p class="msg">Don't have an account? <a href="register.php">Create one</a></p>
    </form>
  </div>

  <script>
    function togglePassword(id, icon) {
      const input = document.getElementById(id);
      if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙈";
      } else {
        input.type = "password";
        icon.textContent = "👁️";
      }
    }
  </script>
</body>
</html>
