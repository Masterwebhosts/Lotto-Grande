<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once("navbar_user.php");

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: /account/login.php", true, 303);
    exit;
}

$user_id = $_SESSION['user_id'];
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$balance_q = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$balance_q->bind_param("i", $user_id);
$balance_q->execute();
$balance_data = $balance_q->get_result()->fetch_assoc();
$current_balance = $balance_data['balance'] ?? 0;
$base_price = 1.00;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_contest'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF error");

    $contest_id = intval($_POST['contest_id']);
    $numbers = trim($_POST['numbers']);
    $type = $_POST['type'] ?? 'grande';
    $multiplier = floatval($_POST['multiplier'] ?? 1);

    if (!preg_match('/^\d{3}$/', $numbers)) die("❌ Enter 3 digits");

    $total_price = $base_price * $multiplier;
    $user_q = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $user_q->bind_param("i", $user_id);
    $user_q->execute();
    $user = $user_q->get_result()->fetch_assoc();

    if ($user['balance'] < $total_price) die(__t('insufficient_balance'));

    $new_balance = $user['balance'] - $total_price;
    $update = $conn->prepare("UPDATE users SET balance=? WHERE id=?");
    $update->bind_param("di", $new_balance, $user_id);
    $update->execute();

    $stmt = $conn->prepare("INSERT INTO user_entries (user_id, contest_id, numbers, bet_amount, type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisds", $user_id, $contest_id, $numbers, $total_price, $type);
    $stmt->execute();

    $t = $conn->prepare("INSERT INTO transactions (user_id, type, amount, balance_after) VALUES (?, 'bet', ?, ?)");
    $t->bind_param("idd", $user_id, $total_price, $new_balance);
    $t->execute();

    header("Location: contests.php?joined=1&balance=" . $new_balance);
    exit;
}

$now = date("Y-m-d H:i:s");
$result = $conn->query("SELECT * FROM contests WHERE result_time > '$now' ORDER BY start_time ASC");
if (isset($_GET['balance'])) $current_balance = floatval($_GET['balance']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lotto Challenges</title>
<style>
body {
  font-family: "Cairo", sans-serif;
  color: #fff; margin:0;
  background: linear-gradient(135deg,#f7b733,#fc4a1a,#0d47a1);
  background-size:400% 400%;
  animation: gradientBG 12s ease infinite;
}
@keyframes gradientBG {0%{background-position:0%50%}50%{background-position:100%50%}100%{background-position:0%50%}}
.container {
  max-width:900px;margin:110px auto 40px;padding:30px;
  background:rgba(0,0,0,0.85);border-radius:15px;box-shadow:0 12px 35px rgba(0,0,0,0.8);
}
.balance-box{text-align:center;background:rgba(255,255,255,0.1);padding:12px;border-radius:12px;font-weight:bold;color:#00ff9c;margin-bottom:15px}
h2{color:#ffd700;text-align:center;margin-bottom:25px;}
.price-tag{color:#00ff9c;font-size:18px;margin-left:10px;}
table{width:100%;border-collapse:collapse;color:#fff}
th,td{padding:12px;text-align:center;border-bottom:1px solid #333}
th{background:rgba(255,215,0,0.15);color:#ffd700}
input[type="text"],select{padding:8px;border-radius:8px;border:none;text-align:center;background:#111;color:#fff;font-weight:bold}
button{background:#ffd700;color:#000;border:none;padding:8px 16px;border-radius:8px;cursor:pointer;font-weight:bold;transition:0.3s}
button:hover{background:#ffca2c}
.details-box{display:none;margin-top:6px;padding:6px 8px;border-radius:10px;background:rgba(255,255,255,0.1);color:#ffd700;font-size:13px;opacity:0;transition:opacity 0.5s ease-in-out;}
.details-box.show{display:block;opacity:1;}
.success-message{display:flex;align-items:center;justify-content:center;background:rgba(0,255,100,0.2);border:1px solid #00ff6a;color:#00ff6a;font-weight:bold;border-radius:10px;padding:10px;margin-bottom:15px;animation:fadeIn 0.5s ease;}
.success-message span{font-size:22px;margin-right:8px;}
@keyframes fadeIn{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:translateY(0)}}
.fade-out{opacity:0;transition:opacity 1s ease;}
</style>
</head>
<body>
<div class="container">
  <div class="balance-box">💰 <?=__t('current_balance')?>: <?=number_format($current_balance,2)?> $</div>

  <h2>
    🎯 Lotto Challenges 
    <span class="price-tag">💵 Ticket Price: <?=number_format($base_price,2)?> $</span>
  </h2>

  <?php if (isset($_GET['joined'])): ?>
  <div class="success-message" id="successMsg">
    <span>✅</span> The withdrawal has been added successfully.
  </div>
  <?php endif; ?>

  <?php if ($result->num_rows>0): ?>
  <table>
    <tr>
      <th><?=__t('country')?></th>
      <th><?=__t('game_type')?></th>
      <th><?=__t('result_time')?></th>
      <th><?=__t('join')?></th>
    </tr>
    <?php while($row=$result->fetch_assoc()): ?>
    <tr>
      <td><?=htmlspecialchars($row['country'])?></td>
      <td colspan="3">
        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
          <input type="hidden" name="contest_id" value="<?=$row['id']?>">

          <div style="display:flex;flex-wrap:wrap;gap:5px;justify-content:center;align-items:center;">
            <select name="type" required onchange="showDetails(this)">
              <option value="grande">🎰 Lotto Grande</option>
              <option value="rumble">⚔️ Lotto Rumble</option>
              <option value="strap">🎟️ Lotto Strap</option>
            </select>
            <span>⏰ <?=htmlspecialchars($row['result_time'])?></span>
            <input type="text" name="numbers" maxlength="3" placeholder="123" required>
            <select name="multiplier" required>
              <option value="1">×1</option>
              <option value="2">×2</option>
              <option value="5">×5</option>
              <option value="10">×10</option>
            </select>
            <button type="submit" name="join_contest">🎟️ <?=__t('join')?> ($1.00)</button>
          </div>
          <div class="details-box"></div>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>

  <script>
  function showDetails(select){
    const form=select.closest('form');
    const box=form.querySelector('.details-box');
    let text='';
    switch(select.value){
      case 'grande': text='🎰 Lotto Grande — Pick 3 numbers and match 2 to win 40× jackpot!'; break;
      case 'rumble': text='⚔️ Lotto Rumble — Match all 3 numbers in any order to win 60× jackpot!'; break;
      case 'strap': text='🎟️ Lotto Strap — Match 3 numbers in exact sequence to win 400× jackpot!'; break;
    }
    box.textContent=text;
    box.classList.remove('show');
    void box.offsetWidth;
    box.classList.add('show');
  }

  window.addEventListener('DOMContentLoaded', ()=>{
    const msg=document.getElementById('successMsg');
    if(msg){
      setTimeout(()=>{
        msg.classList.add('fade-out');
        setTimeout(()=>msg.remove(),1000);
      },3000);
    }
  });
  </script>
  <?php else: ?>
  <p style="text-align:center;color:#ccc;">🚫 <?=__t('no_active_contests')?></p>
  <?php endif; ?>
</div>
<?php require_once(__DIR__ . '/footer_user.php'); ?>
</body>
</html>
