<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';

// 🧠 Fetch notifications
$user_id = $_SESSION['user_id'] ?? 0;
$notifications = [];
if ($user_id > 0) {
    $query = $conn->prepare("
        SELECT title, message, type, created_at
        FROM notifications
        WHERE user_id = ? OR user_id IS NULL
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $query->bind_param("i", $user_id);
    $query->execute();
    $res = $query->get_result();
    $notifications = $res->fetch_all(MYSQLI_ASSOC);
}
?>

<style>
.navbar {
    background: #000;
    color: #ffd700;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: "Cairo", sans-serif;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(255, 215, 0, 0.15);
}

.navbar .left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.navbar .left a {
    color: #ffd700;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}

.navbar .left a:hover {
    color: #fff;
}

/* 🔔 Notifications */
.notification-wrapper {
    position: relative;
    display: inline-block;
    margin-right: 15px;
}

.notification-icon {
    cursor: pointer;
    font-size: 22px;
    color: gold;
    transition: transform 0.2s;
}

.notification-icon:hover {
    transform: scale(1.15);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -10px;
    background: red;
    color: white;
    font-size: 12px;
    border-radius: 50%;
    padding: 2px 6px;
    font-weight: bold;
}

.notification-dropdown {
    position: absolute;
    right: 0;
    top: 40px;
    background: #111;
    border: 1px solid #444;
    border-radius: 8px;
    width: 300px;
    max-height: 320px;
    overflow-y: auto;
    display: none;
    box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
}

.notification-item {
    padding: 10px;
    border-bottom: 1px solid #333;
    color: #ddd;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item strong {
    color: gold;
    display: block;
    margin-bottom: 5px;
}

.notification-item small {
    color: #777;
    font-size: 12px;
}

.logout-btn {
    background: gold;
    color: black;
    padding: 8px 14px;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
    transition: 0.3s;
}

.logout-btn:hover {
    background: #ffcc00;
    color: #000;
}

/* 📱 Responsive Menu */
.menu-toggle {
    display: none;
    flex-direction: column;
    cursor: pointer;
    gap: 5px;
}

.menu-toggle span {
    background: gold;
    height: 3px;
    width: 25px;
    border-radius: 2px;
    transition: 0.3s;
}

/* Responsive Design */
@media (max-width: 768px) {
    .navbar {
        flex-wrap: wrap;
    }

    .navbar .left {
        display: none;
        flex-direction: column;
        width: 100%;
        background: #111;
        padding: 10px 0;
        border-top: 1px solid #333;
        text-align: center;
    }

    .navbar .left.show {
        display: flex;
    }

    .menu-toggle {
        display: flex;
    }

    .right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .logout-btn {
        padding: 6px 10px;
        font-size: 14px;
    }
}
</style>

<div class="navbar">
    <div class="menu-toggle" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="left" id="menuLinks">
        <a href="/user/dashboard.php">🏠 Dashboard</a>
        <a href="/user/contests.php">🎮 Contests</a>
        <a href="/user/results.php">🏆 Results</a>
        <a href="/user/transactions.php">💰 Transactions</a>
        <a href="/user/game_rules.php">📘 Game Rules</a>
    </div>

    <div class="right">
        <div class="notification-wrapper">
            <div class="notification-icon" onclick="toggleNotifications()">🔔</div>
            <?php if (count($notifications) > 0): ?>
                <span class="notification-badge"><?= count($notifications) ?></span>
            <?php endif; ?>
            <div class="notification-dropdown" id="notificationDropdown">
                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $note): ?>
                        <div class="notification-item">
                            <strong><?= htmlspecialchars($note['title']) ?></strong>
                            <span><?= htmlspecialchars($note['message']) ?></span><br>
                            <small><?= htmlspecialchars($note['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="notification-item">🔕 No new notifications</div>
                <?php endif; ?>
            </div>
        </div>
        <a href="/account/logout.php" class="logout-btn">🚪 Logout</a>
    </div>
</div>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
}

window.onclick = function(event) {
    const notif = document.getElementById('notificationDropdown');
    if (!event.target.closest('.notification-wrapper')) notif.style.display = 'none';
}

function toggleMenu() {
    const menu = document.getElementById('menuLinks');
    menu.classList.toggle('show');
}
</script>
