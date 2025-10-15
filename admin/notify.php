<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once("navbar_admin.php");

// 🔒 Admin Access Only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../account/login.php");
    exit;
}

$success = $error = "";

// 🗑️ Delete notification
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM notifications WHERE id = $delete_id")) {
        $success = "🗑️ Notification deleted successfully!";
    } else {
        $error = "❌ Failed to delete notification.";
    }
}

// 📨 Send Notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : "NULL";

    $sql = "INSERT INTO notifications (user_id, title, message, type) VALUES ($user_id, '$title', '$message', '$type')";
    if (mysqli_query($conn, $sql)) {
        $success = "✅ Notification sent successfully!";
    } else {
        $error = "❌ Failed to send notification.";
    }
}

// 📋 Fetch all notifications
$result = $conn->query("
    SELECT n.*, u.username 
    FROM notifications n 
    LEFT JOIN users u ON n.user_id = u.id 
    ORDER BY n.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📢 Send Notification | Lotto Grande Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: "Cairo", sans-serif;
      background: linear-gradient(135deg, #f7b733, #fc4a1a, #0d47a1);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
      color: #fff;
      margin: 0;
    }
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    .container {
      max-width: 900px;
      margin: 100px auto;
      padding: 30px;
      background: rgba(0,0,0,0.85);
      border-radius: 15px;
      box-shadow: 0 12px 35px rgba(0,0,0,0.8);
    }
    h2 {
      color: #ffd700;
      text-align: center;
      margin-bottom: 20px;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    label {
      font-weight: bold;
      color: #ffd700;
      margin-bottom: 5px;
    }
    input[type="text"],
    input[type="number"],
    textarea,
    select {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: #222;
      color: #fff;
      font-size: 15px;
    }
    textarea {
      min-height: 120px;
      resize: vertical;
    }
    button {
      background: #ffd700;
      color: #000;
      font-weight: bold;
      padding: 12px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover {
      background: #ffca2c;
    }
    .msg-success {
      color: #90ee90;
      text-align: center;
      margin-bottom: 10px;
      font-weight: bold;
    }
    .msg-error {
      color: #ff7b7b;
      text-align: center;
      margin-bottom: 10px;
      font-weight: bold;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
    }
    th, td {
      border: 1px solid #444;
      padding: 10px;
      text-align: center;
    }
    th {
      background: #222;
      color: #ffd700;
    }
    tr:nth-child(even) {
      background: rgba(255,255,255,0.05);
    }
    .btn-delete {
      background: crimson;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 6px 10px;
      cursor: pointer;
      font-weight: bold;
      transition: 0.3s;
    }
    .btn-delete:hover {
      background: darkred;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>📢 Send Notification</h2>

    <?php if ($success): ?><p class="msg-success"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p class="msg-error"><?= $error ?></p><?php endif; ?>

    <form method="POST">
      <div>
        <label>Title:</label>
        <input type="text" name="title" placeholder="Enter notification title" required>
      </div>

      <div>
        <label>Message:</label>
        <textarea name="message" placeholder="Write your message here..." required></textarea>
      </div>

      <div>
        <label>Type:</label>
        <select name="type" required>
          <option value="info">ℹ️ Info</option>
          <option value="success">✅ Success</option>
          <option value="warning">⚠️ Warning</option>
          <option value="error">❌ Error</option>
        </select>
      </div>

      <div>
        <label>User ID (optional):</label>
        <input type="number" name="user_id" placeholder="Leave empty for all users">
      </div>

      <button type="submit">Send Notification</button>
    </form>

    <!-- 🗂️ Notifications List -->
    <h2 style="margin-top:40px;">📋 Existing Notifications</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Title</th>
        <th>Message</th>
        <th>Type</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username'] ?? 'All Users') ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['message']) ?></td>
            <td><?= ucfirst($row['type']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
              <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this notification?')">
                <button type="button" class="btn-delete">🗑️ Delete</button>
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7">No notifications found.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</body>
</html>
