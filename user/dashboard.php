<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php'; // يحتوي بالفعل على الترجمة
require_once(__DIR__ . '/navbar_user.php');

// 🚪 تحقق من تسجيل الدخول
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: /account/login.php", true, 303);
    exit;
}

$user_id = $_SESSION['user_id'];

// 🧭 بيانات المستخدم
$user_stmt = $conn->prepare("SELECT username, email, balance FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// 📊 الإحصائيات
$stats = ['entries' => 0, 'wins' => 0, 'loses' => 0, 'transactions' => 0];

$entries_q = $conn->prepare("SELECT COUNT(*) as total, SUM(result='win') as wins, SUM(result='lose') as loses FROM user_entries WHERE user_id = ?");
$entries_q->bind_param("i", $user_id);
$entries_q->execute();
$res = $entries_q->get_result()->fetch_assoc();
if ($res) {
    $stats['entries'] = $res['total'];
    $stats['wins'] = $res['wins'];
    $stats['loses'] = $res['loses'];
}

$trans_q = $conn->prepare("SELECT COUNT(*) as t FROM transactions WHERE user_id = ?");
$trans_q->bind_param("i", $user_id);
$trans_q->execute();
$stats['transactions'] = $trans_q->get_result()->fetch_assoc()['t'] ?? 0;

// 🎯 المسابقات النشطة
$now = date("Y-m-d H:i:s");
$contests = $conn->query("SELECT * FROM contests WHERE result_time > '$now' ORDER BY start_time ASC LIMIT 4");

// 💸 آخر 5 معاملات
$transactions = $conn->query("SELECT * FROM transactions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// 🔔 آخر 3 إشعارات
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id IS NULL OR user_id = $user_id ORDER BY created_at DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>📊 <?= __t('dashboard') ?> | Lotto Grande</title>
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
  max-width: 1200px;
  margin: 110px auto 50px;
  padding: 20px;
}
.section {
  background: rgba(0,0,0,0.85);
  border-radius: 15px;
  padding: 25px;
  margin-bottom: 30px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.8);
}
.section h2 {
  color: #ffd700;
  border-bottom: 2px solid #ffd70033;
  padding-bottom: 10px;
  margin-bottom: 20px;
  text-align: center;
}
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 15px;
}
.card {
  background: rgba(255,255,255,0.08);
  padding: 15px;
  border-radius: 12px;
  text-align: center;
  transition: 0.3s;
  box-shadow: 0 4px 10px rgba(0,0,0,0.4);
}
.card:hover {
  background: rgba(255,255,255,0.15);
  transform: scale(1.03);
}
.card h3 { color: #ffd700; margin-bottom: 8px; }
.card p { font-size: 20px; font-weight: bold; color: #00ff9c; }

.table {
  width: 100%;
  border-collapse: collapse;
  color: #fff;
}
.table th, .table td {
  padding: 10px;
  text-align: center;
  border-bottom: 1px solid #333;
}
.table th {
  background: rgba(255,215,0,0.15);
  color: #ffd700;
}
.table td { color: #eee; }

.btn {
  background: #ffd700;
  color: #000;
  text-decoration: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: bold;
  transition: 0.3s;
  display: inline-block;
}
.btn:hover {
  background: #ffca2c;
  transform: scale(1.05);
}
.no-data {
  color: #ccc;
  text-align: center;
  padding: 15px;
}
</style>
</head>
<body>

<div class="container">
<!-- 👤 User Information -->
<div class="section">
<h2>👤 <?= __t('account_information') ?></h2>
<p><strong><?= __t('name') ?></strong> <?= htmlspecialchars($user['username']) ?></p>
<p><strong><?= __t('email') ?></strong> <?= htmlspecialchars($user['email']) ?></p>
<p><strong>💰 <?= __t('current_balance') ?></strong> <span style="color:#00ff9c;"><?= number_format($user['balance'], 2) ?> $</span></p>
<a href="edit_profile.php" class="btn">✏️ <?= __t('edit_profile') ?></a>
</div>

<!-- 📊 Statistics -->
<div class="section">
<h2>📊 <?= __t('statistics') ?></h2>
<div class="grid">
<div class="card"><h3>🎟️ <?= __t('entries') ?></h3><p><?= $stats['entries'] ?></p></div>
<div class="card"><h3>🏆 <?= __t('wins') ?></h3><p><?= $stats['wins'] ?></p></div>
<div class="card"><h3>❌ <?= __t('losses') ?></h3><p><?= $stats['loses'] ?></p></div>
<div class="card"><h3>💸 <?= __t('transactions') ?></h3><p><?= $stats['transactions'] ?></p></div>
</div>
</div>

<!-- 🎯 Active Contests -->
<div class="section">
<h2>🎯 <?= __t('active_contests') ?></h2>
<div class="grid">
<?php if ($contests->num_rows > 0): ?> 
<?php while ($row = $contests->fetch_assoc()): ?> 
<div class="card"> 
<h3><?= htmlspecialchars($row['country']) ?></h3> 
<p>🎮 <?= $row['type'] === 'speed' ? __t('fast_lotto') : __t('regular_lotto') ?></p> 
<p>💰 <?= number_format($row['ticket_price'],2) ?> $</p> 
<p>🕒 <?= htmlspecialchars($row['result_time']) ?></p> 
<a href="contests.php" class="btn">🎟️ <?= __t('join_now') ?></a> 
</div> 
<?php endwhile; ?>
<?php else: ?>
<p class="no-data">🚫 <?= __t('no_active_contests') ?></p>
<?php endif; ?>
</div>
</div>

<!-- 💸 Latest Transactions -->
<div class="section">
<h2>💸 <?= __t('latest_transactions') ?></h2>
<?php if ($transactions->num_rows > 0): ?>
<table class="table">
<tr><th><?= __t('type') ?></th><th><?= __t('amount') ?></th><th><?= __t('balance_after') ?></th><th><?= __t('date') ?></th></tr>
<?php while ($t = $transactions->fetch_assoc()): ?>
<tr>
<td><?= ucfirst($t['type']) ?></td>
<td>$<?= number_format($t['amount'], 2) ?></td>
<td><?= $t['balance_after'] ?></td>
<td><?= $t['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p class="no-data">🚫 <?= __t('no_transactions_yet') ?></p>
<?php endif; ?>
</div>

<!-- 🔔 Notifications -->
<div class="section">
<h2>🔔 <?= __t('latest_notifications') ?></h2>
<?php if ($notifications->num_rows > 0): ?>
<?php while ($n = $notifications->fetch_assoc()): ?>
<div style="background:rgba(255,255,255,0.07);padding:10px;border-radius:8px;margin-bottom:10px;">
<h4 style="color:#ffd700;margin:0;"><?= htmlspecialchars($n['title']) ?></h4>
<p style="margin:5px 0;"><?= htmlspecialchars($n['message']) ?></p>
<small style="color:#aaa;">📅 <?= $n['created_at'] ?></small>
</div>
<?php endwhile; ?>
<?php else: ?>
<p class="no-data">🔕 <?= __t('there_are_currently_no_notifications') ?></p>
<?php endif; ?>
</div>

</div>
<?php require_once(__DIR__ . '/footer_user.php'); ?>

</body>
</html>
