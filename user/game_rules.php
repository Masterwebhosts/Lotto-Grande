<?php
// ✅ Secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once("navbar_user.php");

// 🚪 User authentication
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../account/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 🗑️ Delete entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_entry'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ Invalid CSRF token");
    }

    $entry_id = intval($_POST['entry_id']);
    $stmt = $conn->prepare("DELETE FROM user_entries WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $entry_id, $user_id);
    $stmt->execute();

    header("Location: results.php?deleted=1");
    exit;
}

// 🎯 Fetch results
$query = "
    SELECT 
        ue.id AS entry_id,
        c.country,
        c.type,
        c.result_time,
        c.winning_numbers,
        ue.numbers AS user_numbers,
        ue.bet_amount,
        ue.win_amount,
        ue.result,
        ue.created_at
    FROM user_entries ue
    INNER JOIN contests c ON ue.contest_id = c.id
    WHERE ue.user_id = ?
    ORDER BY c.result_time DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>🏆 <?= __t('my_contest_results') ?> | Lotto Grande</title>
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
  max-width: 1100px;
  margin: 110px auto 50px;
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
.success {
  text-align: center;
  color: #00ff9c;
  font-weight: bold;
  margin-bottom: 15px;
}
table {
  width: 100%;
  border-collapse: collapse;
  background: #1a1a1a;
  border-radius: 12px;
  overflow: hidden;
}
th, td {
  padding: 12px 10px;
  text-align: center;
  border-bottom: 1px solid #333;
}
th {
  background: rgba(255,215,0,0.15);
  color: #ffd700;
}
tr:hover {
  background: rgba(255,215,0,0.08);
}
.win { color: #00ff88; font-weight: bold; }
.lose { color: #ff4d4d; font-weight: bold; }
.no-results {
  text-align: center;
  padding: 25px;
  color: #aaa;
}
button.delete-btn {
  background: #ff4444;
  border: none;
  color: #fff;
  padding: 6px 10px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
}
button.delete-btn:hover {
  background: #ff0000;
  transform: scale(1.05);
}
</style>
<script>
function confirmDelete() {
  return confirm("⚠️ <?= __t('are_you_sure_delete_record') ?>");
}
</script>
</head>
<body>

<div class="container">
  <h2>🏆 <?= __t('my_contest_results') ?></h2>

  <?php if (isset($_GET['deleted'])): ?>
    <p class="success">🗑️ <?= __t('record_deleted_successfully') ?></p>
  <?php endif; ?>

  <table>
    <tr>
      <th><?= __t('country') ?></th>
      <th><?= __t('type') ?></th>
      <th><?= __t('your_numbers') ?></th>
      <th><?= __t('winning_numbers') ?></th>
      <th><?= __t('bet') ?></th>
      <th><?= __t('prize') ?></th>
      <th><?= __t('result') ?></th>
      <th><?= __t('date') ?></th>
      <th><?= __t('action') ?></th>
    </tr>

    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['country']) ?></td>
          <td><?= htmlspecialchars(ucfirst($row['type'])) ?></td>
          <td><?= htmlspecialchars($row['user_numbers']) ?></td>
          <td><?= htmlspecialchars($row['winning_numbers'] ?: '—') ?></td>
          <td>$<?= number_format($row['bet_amount'], 2) ?></td>
          <td>$<?= number_format($row['win_amount'], 2) ?></td>
          <td class="<?= $row['result'] === 'win' ? 'win' : 'lose' ?>">
            <?= $row['result'] === 'win' ? '✅ ' . __t('win') : '❌ ' . __t('lose') ?>
          </td>
          <td><?= $row['created_at'] ?></td>
          <td>
            <form method="POST" onsubmit="return confirmDelete();">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
              <input type="hidden" name="entry_id" value="<?= $row['entry_id'] ?>">
              <button type="submit" name="delete_entry" class="delete-btn">🗑️ <?= __t('delete') ?></button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="9" class="no-results">🚫 <?= __t('no_contest_results_found_yet') ?></td></tr>
    <?php endif; ?>
  </table>
</div>

<?php require_once(__DIR__ . '/footer_user.php'); ?>

</body>
</html>
