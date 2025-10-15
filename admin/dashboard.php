<?php
// ✅ Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/navbar_admin.php');

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';

// 🛑 Admin protection
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /account/login.php", true, 303);
    exit;
}

$msg = "";

// ✅ Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int) $_POST['user_id'];
    $amount  = isset($_POST['amount']) ? (int) $_POST['amount'] : 0;
    $action  = $_POST['action'];

    if ($action === "delete") {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $msg = "🗑️ User deleted successfully.";
    } else {
        $stmt = $conn->prepare("SELECT username, balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $user = $res->fetch_assoc();
            $username   = $user['username'];
            $oldBalance = (int) $user['balance'];

            if ($action === "add") {
                $newBalance = $oldBalance + $amount;
                $type = "deposit";
                $msg  = "✅ Added $amount to $username. New balance: $newBalance";
            } elseif ($action === "edit") {
                $newBalance = $amount;
                $type = "manual_edit";
                $msg  = "✏️ Edited $username balance. New balance: $newBalance";
            }

            $stmt2 = $conn->prepare("UPDATE users SET balance=? WHERE id=?");
            $stmt2->bind_param("ii", $newBalance, $user_id);
            $stmt2->execute();

            $stmt3 = $conn->prepare("INSERT INTO transactions (user_id, type, amount, balance_after) VALUES (?,?,?,?)");
            $stmt3->bind_param("isii", $user_id, $type, $amount, $newBalance);
            $stmt3->execute();
        } else {
            $msg = "❌ User not found.";
        }
    }
}

// ✅ Fetch all users
$users = $conn->query("SELECT id, username, email, balance, created_at FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>⚙️ Manage Users | Lotto Grande Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  margin: 100px auto 50px;
  padding: 30px;
  background: rgba(0,0,0,0.85);
  border-radius: 15px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.8);
}
h1 {
  color: #ffd700;
  text-align: center;
  margin-bottom: 25px;
}
.msg {
  text-align: center;
  font-weight: bold;
  margin-bottom: 20px;
  color: lightgreen;
}
table {
  width: 100%;
  border-collapse: collapse;
  color: #fff;
  font-size: 15px;
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
input[type="number"] {
  width: 80px;
  padding: 5px;
  text-align: center;
  border-radius: 6px;
  border: none;
}
form {
  display: flex;
  gap: 6px;
  justify-content: center;
  align-items: center;
}
.btn {
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}
.btn-add {
  background: #00c851;
  color: #fff;
}
.btn-add:hover { background: #009432; }
.btn-edit {
  background: #007bff;
  color: #fff;
}
.btn-edit:hover { background: #0056b3; }
.btn-delete {
  background: #dc3545;
  color: #fff;
}
.btn-delete:hover { background: #b02a37; }
</style>
</head>
<body>


<div class="container">
  <h1>👥 Manage User Balances</h1>
  <?php if (!empty($msg)): ?>
    <p class="msg"><?= htmlspecialchars($msg) ?></p>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Username</th>
        <th>Email</th>
        <th>Balance</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $users->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td>$<?= htmlspecialchars($row['balance']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
          <form method="post">
            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
            <input type="number" name="amount" min="1" required>
            <button type="submit" name="action" value="add" class="btn btn-add">➕</button>
            <button type="submit" name="action" value="edit" class="btn btn-edit">✏️</button>
            <button type="submit" name="action" value="delete" class="btn btn-delete"
              onclick="return confirm('⚠️ Are you sure you want to delete this user?');">🗑️</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

</body>
</html>
