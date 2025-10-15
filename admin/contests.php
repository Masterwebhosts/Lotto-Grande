
<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once("navbar_admin.php");

// ✅ تحقق من صلاحيات الأدمن
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../account/login.php");
    exit;
}

$msg = "";

// ✅ عند إنشاء مسابقة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_contest'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $result_time = $_POST['result_time'];
    $admin_id = $_SESSION['user_id'];

    $ticket_price = 1.00; // ثابت لكل المسابقات

    $stmt = $conn->prepare("INSERT INTO contests (title, country, start_time, result_time, ticket_price, prize_multiplier, created_by, status)
                            VALUES (?, ?, NOW(), ?, ?, 1, ?, 'upcoming')");
    $stmt->bind_param("sssdi", $title, $country, $result_time, $ticket_price, $admin_id);
    $stmt->execute();

    $msg = "✅ Contest created successfully!";
}

// ✅ جلب كل المسابقات
$result = $conn->query("SELECT * FROM contests ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🎮 Contest Management</title>
<link rel="stylesheet" href="../assets/navbar.css">
<link rel="stylesheet" href="../assets/admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
body { font-family: "Cairo", sans-serif; background: #111; color: #fff; }
.container { max-width: 1100px; margin: 60px auto; padding: 25px; }
h2 { text-align: center; color: gold; margin-bottom: 25px; }
.msg { text-align: center; color: lightgreen; margin-bottom: 10px; }
form { background: rgba(0,0,0,0.8); padding: 20px; border-radius: 12px; box-shadow: 0 0 10px rgba(255,215,0,0.2); }
label { color: gold; display: block; margin-top: 10px; }
input, button {
  width: 100%; padding: 10px; border: none; border-radius: 6px;
  background: #222; color: #fff; margin-top: 5px;
}
button {
  background: gold; color: #000; font-weight: bold; cursor: pointer;
}
button:hover { background: #ffca2c; }
table { width: 100%; border-collapse: collapse; margin-top: 25px; }
th, td { border: 1px solid #444; padding: 10px; text-align: center; }
th { background: #222; color: gold; }
tr:nth-child(even) { background: #1a1a1a; }
.actions button {
  width: auto; padding: 6px 12px; margin: 2px; border-radius: 6px;
  cursor: pointer; font-weight: bold;
}
.btn-edit { background: royalblue; color: white; }
.btn-delete { background: crimson; color: white; }
.btn-announce { background: seagreen; color: white; }
.btn-edit:hover { background: navy; }
.btn-delete:hover { background: darkred; }
.btn-announce:hover { background: darkgreen; }
</style>
</head>
<body>

<div class="container">
  <h2>🎯 Contest Management</h2>
  <?php if ($msg): ?><p class="msg"><?= $msg; ?></p><?php endif; ?>

  <!-- 🆕 إنشاء مسابقة جديدة -->
  <form method="POST">
    <h3 style="color:#ffd700;">➕ Create New Contest</h3>
    <label>🏷️ Contest Title</label>
    <input type="text" name="title" placeholder="e.g., Turkey Daily Draw" required>

    <label>🌍 Country</label>
    <input type="text" name="country" value="Türkiye" required>

    <label>🗓️ Result Date & Time</label>
    <input type="text" id="datetimePicker" name="result_time" placeholder="Select date and time" required>

    <button type="submit" name="create_contest">💾 Create Contest</button>
  </form>

  <!-- 📋 جدول المسابقات -->
  <table>
    <tr>
      <th>ID</th>
      <th>Title</th>
      <th>Country</th>
      <th>Start</th>
      <th>Result Time</th>
      <th>Price</th>
      <th>Status</th>
      <th>Winning Numbers</th>
      <th>Actions</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id']; ?></td>
      <td><?= htmlspecialchars($row['title'] ?? '—'); ?></td>
      <td><?= htmlspecialchars($row['country']); ?></td>
      <td><?= $row['start_time']; ?></td>
      <td><?= $row['result_time']; ?></td>
      <td>💵 1.00 $</td>
      <td><?= $row['status']; ?></td>
      <td><?= $row['winning_numbers'] ?? '—'; ?></td>
      <td class="actions">
        <button class="btn-edit" onclick="window.location='edit_contest.php?id=<?= $row['id']; ?>'">✏️</button>
        <button class="btn-delete" onclick="if(confirm('Delete this contest?')) window.location='delete_contest.php?id=<?= $row['id']; ?>'">🗑️</button>
        <?php if ($row['status'] !== 'finished'): ?>
          <button class="btn-announce" onclick="window.location='announce_result.php?id=<?= $row['id']; ?>'">📢</button>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr("#datetimePicker", {
  enableTime: true,
  time_24hr: false,
  dateFormat: "Y-m-d h:i K",
  minDate: "today",
  defaultDate: new Date(),
  minuteIncrement: 5,
  locale: "en"
});
</script>
</body>
</html>
