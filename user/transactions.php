<?php
// ✅ Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once(__DIR__ . '/navbar_user.php');

// 🚪 Allow only logged in user
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: /account/login.php", true, 303);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 🗑️ Handle delete (single or multiple)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ " . __t('security_validation_failed'));
    }

    if (!empty($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
        $ids = array_map('intval', $_POST['delete_ids']);
        $id_list = implode(',', $ids);

        $conn->query("DELETE FROM transactions WHERE user_id = $user_id AND id IN ($id_list)");
        header("Location: transactions.php?deleted=1");
        exit;
    }
}

// ✅ Fetch transactions
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>💰 <?= __t('my_transactions') ?> | Lotto Grande</title>
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
  max-width: 850px;
  margin: 110px auto 40px;
  padding: 30px;
  background: rgba(0,0,0,0.85);
  border-radius: 15px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.8);
}
h2 {
  color: #ffd700;
  text-align: center;
  margin-bottom: 15px;
}
.success {
  text-align: center;
  color: #00ff99;
  font-weight: bold;
  margin-bottom: 10px;
}
table {
  width: 100%;
  border-collapse: collapse;
  color: #fff;
  margin-top: 15px;
}
th, td {
  padding: 10px;
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
.deposit { color: #00ff7f; font-weight: bold; }
.withdraw { color: #ff4c4c; font-weight: bold; }
.bet { color: #ffa500; font-weight: bold; }
.prize { color: #00bfff; font-weight: bold; }
.manual_edit { color: #fff; font-style: italic; }
.no-data {
  text-align: center;
  color: #ccc;
  padding: 15px;
}
.delete-btn {
  background: #ff4444;
  border: none;
  color: #fff;
  padding: 5px 10px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
}
.delete-btn:hover {
  background: #ff0000;
}
.action-bar {
  text-align: right;
  margin-bottom: 10px;
}
.action-bar button {
  background: #ff4444;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  color: #fff;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}
.action-bar button:hover {
  background: #ff0000;
}
</style>
<script>
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.row-check');
    checkboxes.forEach(ch => ch.checked = source.checked);
}
function confirmDelete() {
  return confirm("⚠️ <?= __t('confirm_delete_selected_records') ?>");
}
</script>
</head>
<body>

<div class="container">
<h2>💰 <?= __t('my_transactions') ?></h2>

<?php if (isset($_GET['deleted'])): ?>
<p class="success">🗑️ <?= __t('selected_records_deleted_successfully') ?></p>
<?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
  <form method="POST" onsubmit="return confirmDelete();">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <div class="action-bar">
      <button type="submit" name="delete_selected">🗑️ <?= __t('delete_selected') ?></button>
    </div>

    <table>
      <tr>
        <th><input type="checkbox" onclick="toggleAll(this)"></th>
        <th>ID</th>
        <th><?= __t('type') ?></th>
        <th><?= __t('amount') ?></th>
        <th><?= __t('balance_after') ?></th>
        <th><?= __t('date') ?></th>
        <th><?= __t('action') ?></th>
      </tr>

      <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><input type="checkbox" name="delete_ids[]" value="<?= $row['id'] ?>" class="row-check"></td>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td class="<?= strtolower($row['type']) ?>"><?= ucfirst(__t($row['type'])) ?></td>
        <td>$<?= htmlspecialchars($row['amount']) ?></td>
        <td><?= htmlspecialchars($row['balance_after']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
          <button type="submit" name="delete_selected" value="1" class="delete-btn"
            onclick="return confirm('⚠️ <?= __t('confirm_delete_this_record') ?>'); 
                     this.form.querySelectorAll('.row-check').forEach(ch => ch.checked = false);
                     this.form.querySelector('input[value=<?= $row['id'] ?>]').checked = true;">
            🗑️ <?= __t('delete') ?>
          </button>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </form>
  <?php else: ?>
    <div class="no-data">🚫 <?= __t('no_transactions_found') ?></div>
  <?php endif; ?>
</div>

<?php require_once(__DIR__ . '/footer_user.php'); ?>
</body>
</html>
