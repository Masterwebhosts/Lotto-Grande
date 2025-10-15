<?php
// ✅ Secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once(__DIR__ . '/navbar_user.php');

// 🚪 Ensure logged in user
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: /account/login.php", true, 303);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch results safely
$stmt = $conn->prepare("
    SELECT 
        ue.id AS entry_id,
        ue.numbers AS user_numbers,
        ue.win_amount AS prize,
        ue.result AS ticket_result,
        ue.created_at AS played_at,
        c.country,
        c.type,
        c.winning_numbers,
        c.result_time,
        c.status
    FROM user_entries ue
    INNER JOIN contests c ON ue.contest_id = c.id
    WHERE ue.user_id = ?
    ORDER BY c.result_time DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>🏆 <?= __t('competition_results') ?> | Lotto Grande</title>
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
  0%{background-position:0% 50%;}
  50%{background-position:100% 50%;}
  100%{background-position:0% 50%;}
}
.container {
  max-width: 950px;
  margin: 110px auto 40px;
  padding: 30px;
  background: rgba(0,0,0,0.85);
  border-radius: 15px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.8);
}
h2 {
  color: #ffd700;
  text-align: center;
  margin-bottom: 25px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  color: #fff;
}
th, td {
  padding: 12px;
  text-align: center;
  border-bottom: 1px solid #333;
}
th {
  background: rgba(255,215,0,0.15);
  color: #ffd700;
}
tr:nth-child(even) {
  background-color: rgba(255,255,255,0.05);
}
.win {
  color: lightgreen;
  font-weight: bold;
}
.lose {
  color: #ff4c4c;
  font-weight: bold;
}
.no-results {
  text-align: center;
  color: #ccc;
  font-size: 16px;
  padding: 20px;
}
</style>
</head>
<body>

<div class="container">
  <h2>🏆 <?= __t('your_competition_results') ?></h2>

  <?php if ($result->num_rows > 0): ?>
  <table>
    <tr>
      <th><?= __t('country') ?></th>
      <th><?= __t('type') ?></th>
      <th><?= __t('played_at') ?></th>
      <th><?= __t('result_time') ?></th>
      <th><?= __t('your_numbers') ?></th>
      <th><?= __t('winning_numbers') ?></th>
      <th><?= __t('status') ?></th>
      <th><?= __t('result') ?></th>
      <th><?= __t('prize') ?></th>
    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>
      <?php
        $status = ucfirst($row['status']);
        $result_txt = "—";
        $prize_txt = "—";

        if ($row['status'] === 'finished') {
            if ($row['ticket_result'] === 'win') {
                $result_txt = "<span class='win'>✅ " . __t('win') . "</span>";
                $prize_txt = "$" . number_format($row['prize'], 2);
            } elseif ($row['ticket_result'] === 'lose') {
                $result_txt = "<span class='lose'>❌ " . __t('lose') . "</span>";
                $prize_txt = "0";
            } else {
                $result_txt = __t('pending');
                $prize_txt = "—";
            }
        } else {
            $result_txt = "⏳ " . __t('upcoming');
            $prize_txt = "—";
        }
      ?>
      <tr>
        <td><?= htmlspecialchars($row['country']) ?></td>
        <td><?= htmlspecialchars(ucfirst(__t($row['type']))) ?></td>
        <td><?= htmlspecialchars($row['played_at']) ?></td>
        <td><?= htmlspecialchars($row['result_time']) ?></td>
        <td><?= htmlspecialchars($row['user_numbers']) ?></td>
        <td><?= htmlspecialchars($row['winning_numbers'] ?? '—') ?></td>
        <td><?= htmlspecialchars($status) ?></td>
        <td><?= $result_txt ?></td>
        <td><?= $prize_txt ?></td>
      </tr>
    <?php endwhile; ?>

  </table>
  <?php else: ?>
    <div class="no-results">🚫 <?= __t('you_have_no_results_yet') ?></div>
  <?php endif; ?>
</div>

<?php require_once(__DIR__ . '/footer_user.php'); ?>
</body>
</html>
