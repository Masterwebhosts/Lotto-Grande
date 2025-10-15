<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
$error = $success = "";

// Redirect if already logged in
if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') header("Location: ../admin/dashboard.php");
    else header("Location: ../user/dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm']);

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = "⚠️ Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Invalid email address.";
    } elseif ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $error = "⚠️ Username or email already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, balance) VALUES (?, ?, ?, 'user', 1000)");
            $stmt->bind_param("sss", $username, $email, $hashed);
            if ($stmt->execute()) $success = "✅ Account created successfully! You can now log in.";
            else $error = "❌ Error creating account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📝 Register</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../assets/style.css">
<style>
body{display:flex;justify-content:center;align-items:center;height:100vh;background:#111;color:#fff;font-family:"Cairo",sans-serif;}
.register-box{background:rgba(0,0,0,0.8);padding:30px;border-radius:15px;box-shadow:0 0 20px rgba(255,215,0,0.2);width:100%;max-width:400px;text-align:center;}
.register-box h2{color:gold;margin-bottom:20px;}
.register-box input{width:100%;padding:10px;margin:8px 0;border:none;border-radius:8px;text-align:center;background:#222;color:#fff;font-size:16px;}
.register-box input:focus{border:2px solid gold;outline:none;}
.btn{width:60%;margin-top:15px;padding:12px;border:none;border-radius:8px;background:gold;color:#000;font-weight:bold;cursor:pointer;font-size:16px;}
.btn:hover{background:#ffca2c;}
.msg{margin:10px 0;}
a{color:gold;text-decoration:none;}a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="register-box">
<h2>📝 Create Account</h2>
<?php if($error):?><p class="msg" style="color:red;"><?= $error;?></p><?php elseif($success):?><p class="msg" style="color:lightgreen;"><?= $success;?></p><?php endif;?>
<form method="POST">
<input type="text" name="username" placeholder="👤 Username" required>
<input type="email" name="email" placeholder="📧 Email" required>
<input type="password" name="password" placeholder="🔑 Password" required>
<input type="password" name="confirm" placeholder="🔁 Confirm password" required>
<button type="submit" class="btn">Register</button>
<p class="msg">Already have an account? <a href="login.php">Login here</a></p>
</form>
</div>
</body>
</html>
