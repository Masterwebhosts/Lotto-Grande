<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once("navbar_admin.php");

// 🔒 حماية الأدمن
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../account/login.php");
    exit;
}

// ✅ التحقق من وجود معرف المسابقة
if (!isset($_GET['id'])) {
    die("❌ Invalid contest ID");
}

$contest_id = (int)$_GET['id'];
$msg = "";

// ✅ جلب بيانات المسابقة
$stmt = $conn->prepare("SELECT * FROM contests WHERE id = ?");
$stmt->bind_param("i", $contest_id);
$stmt->execute();
$contest = $stmt->get_result()->fetch_assoc();

if (!$contest) {
    die("❌ Contest not found.");
}

// ✅ عند تعديل المسابقة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $result_time = $_POST['result_time'];
    $ticket_price = floatval($_POST['ticket_price']);
    $prize_multiplier = intval($_POST['prize_multiplier']);
    $status = $_POST['status'];

    $update = $conn->prepare("UPDATE contests 
                              SET country=?, result_time=?, ticket_price=?, prize_multiplier=?, status=? 
                              WHERE id=?");
    $update->bind_param("ssdssi", $country, $result_time, $ticket_price, $prize_multiplier, $status, $contest_id);
    $update->execute();

    $msg = "✅ Contest updated successfully!";
    // تحديث البيانات للعرض بعد التعديل
    $stmt->execute();
    $contest = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>✏️ Edit Contest</title>
<link rel="stylesheet" href="../assets/navbar.css">
<link rel="stylesheet" href="../assets/admin.css">
<style>
body {
  font-family: "Cairo", sans-serif;
  background: #111;
  color: #fff;
}
.container {
  max-width: 600px;
  margin: 60px auto;
  padding: 25px;
  background: rgba(0,0,0,0.8);
  border-radius: 12px;
  box-shadow: 0 0 20px rgba(255,215,0,0.2);
}
h2 { text-align: center; color: gold; margin-bottom: 20px; }
label { display: block; margin: 10px 0 5px; color: #ffd700; }
input, select {
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 8px;
  background: #222;
  color: #fff;
}
button {
  background: gold;
  color: black;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  width: 100%;
  margin-top: 15px;
}
button:hover { background: #ffca2c; }
.msg { text-align: center; color: lightgreen; margin-bottom: 10px; }
</style>
</head>
<body>
<div class="container">
  <h2>✏️ Edit Contest</h2>

  <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

  <form method="POST">
    <label>🌍 Country</label>
    <input type="text" name="country" value="<?= htmlspecialchars($contest['country']); ?>" required>

    <label>📅 Result Time</label>
    <input type="datetime-local" name="result_time" value="<?= date('Y-m-d\TH:i', strtotime($contest['result_time'])); ?>" required>

    <label>💵 Ticket Price ($)</label>
    <input type="number" step="0.01" name="ticket_price" value="<?= $contest['ticket_price']; ?>" required>

    <label>🏆 Prize Multiplier</label>
    <input type="number" name="prize_multiplier" value="<?= $contest['prize_multiplier']; ?>" required>

    <label>📊 Status</label>
    <select name="status">
      <option value="upcoming" <?= $contest['status'] == 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
      <option value="active" <?= $contest['status'] == 'active' ? 'selected' : '' ?>>Active</option>
      <option value="finished" <?= $contest['status'] == 'finished' ? 'selected' : '' ?>>Finished</option>
    </select>

    <button type="submit">💾 Save Changes</button>
  </form>

  <p style="text-align:center; margin-top:10px;">
    <a href="contests.php" style="color:gold;">⬅️ Back to Contests</a>
  </p>
</div>
</body>
</html>
