<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once("navbar_admin.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../account/login.php");
    exit;
}

$msg = "";
$contest_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ✅ جلب المسابقة المحددة
$contest = $conn->query("SELECT * FROM contests WHERE id=$contest_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $winning_numbers = mysqli_real_escape_string($conn, $_POST['winning_numbers']);

    // تحديث المسابقة
    $conn->query("UPDATE contests SET winning_numbers='$winning_numbers', status='finished' WHERE id=$contest_id");

    // جلب المشاركين
    $entries = $conn->query("SELECT id, user_id, numbers, bet_amount FROM user_entries WHERE contest_id=$contest_id");
    $winners = 0; $total_prizes = 0;

    while ($entry = $entries->fetch_assoc()) {
        $result = "lose"; $win_amount = 0;

        if ($entry['numbers'] === $winning_numbers) {
            $result = "win";
            $gross = $entry['bet_amount'] * 400;
            $net = $gross - ($gross * 0.10); // خصم 10%
            $win_amount = $net;

            $conn->query("UPDATE users SET balance = balance + $net WHERE id={$entry['user_id']}");
            $winners++; $total_prizes += $net;
        }

        $conn->query("UPDATE user_entries SET result='$result', win_amount=$win_amount WHERE id={$entry['id']}");
    }

    $msg = "📢 Result announced for contest #$contest_id.<br>
            🏆 Winners: $winners<br>
            💰 Total prizes: $" . number_format($total_prizes, 2);
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>📢 إعلان نتيجة المسابقة</title>
<link rel="stylesheet" href="../assets/navbar.css">
<style>
body {font-family:"Cairo",sans-serif;background:#111;color:#fff;}
.container {max-width:700px;margin:60px auto;padding:25px;background:rgba(0,0,0,0.8);border-radius:12px;}
h2{text-align:center;color:gold;}
label{color:gold;font-weight:bold;margin-top:10px;display:block;}
input,button{width:100%;padding:10px;margin:5px 0;border:none;border-radius:8px;background:#222;color:#fff;}
button{background:gold;color:#000;font-weight:bold;cursor:pointer;}
button:hover{background:#ffca2c;}
.msg{text-align:center;color:lightgreen;margin-bottom:10px;}
</style>
</head>
<body>

<div class="container">
  <h2>📢 إعلان نتيجة المسابقة</h2>
  <?php if ($msg): ?><p class="msg"><?= $msg ?></p><?php endif; ?>

  <?php if ($contest): ?>
  <form method="POST">
    <label>🏷️ Contest Title:</label>
    <input type="text" value="<?= htmlspecialchars($contest['title']) ?>" readonly>

    <label>🌍 Country:</label>
    <input type="text" value="<?= htmlspecialchars($contest['country']) ?>" readonly>

    <label>🔢 Winning Numbers (3 digits):</label>
    <input type="text" name="winning_numbers" maxlength="3" placeholder="e.g., 444" required>

    <button type="submit">📢 Publish Result</button>
  </form>
  <?php else: ?>
    <p style="text-align:center;">❌ Contest not found.</p>
  <?php endif; ?>
</div>

</body>
</html>
