<?php  
// ✅ جلسة آمنة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🚫 الوصول المسموح فقط للمسؤول
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /account/login.php", true, 303);
    exit;
}
?>

<nav class="navbar" dir="ltr">
  <div class="navbar-container">
    <div class="navbar-logo">
      🎯 <span>Lotto Grande Admin</span>
    </div>

    <!-- زر القائمة في الجوال -->
    <button class="menu-toggle" id="menu-toggle">☰</button>

    <ul class="navbar-links" id="navbar-links">
      <li><a href="/admin/dashboard.php">🏠 Dashboard</a></li>
      <li><a href="/admin/contests.php">🎟️ Contests</a></li>
      <li><a href="/admin/results.php">🏆 Results</a></li>
      <li><a href="/admin/notify.php">🔔 Notifications</a></li>
    </ul>

    <div class="navbar-right">
      <div class="dropdown">
        <button class="dropbtn">
          👑 <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?> ▼
        </button>
        <div class="dropdown-content">
          <a href="/admin/dashboard.php">Dashboard</a>
          <a href="/account/logout.php">🚪 Logout</a>
        </div>
      </div>
    </div>
  </div>
</nav>

<style>
/* === الأساسيات === */
.navbar {
  background: rgba(0,0,0,0.9);
  position: fixed;
  top: 0;
  width: 100%;
  display: flex;
  justify-content: center;
  padding: 12px 0;
  box-shadow: 0 2px 10px rgba(0,0,0,0.6);
  z-index: 1000;
  font-family: "Cairo", sans-serif;
}
.navbar-container {
  width: 95%;
  max-width: 1300px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.navbar-logo {
  font-weight: bold;
  font-size: 20px;
  color: #ffd700;
}

/* === روابط القائمة === */
.navbar-links {
  list-style: none;
  display: flex;
  gap: 18px;
  margin: 0;
  padding: 0;
}
.navbar-links a {
  color: #fff;
  text-decoration: none;
  font-weight: bold;
  transition: 0.3s;
}
.navbar-links a:hover {
  color: #ffd700;
}

/* === الزاوية اليمنى === */
.navbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

/* === القائمة المنسدلة === */
.dropdown {
  position: relative;
  display: inline-block;
}
.dropbtn {
  background: none;
  border: none;
  color: #ffd700;
  font-weight: bold;
  cursor: pointer;
  font-size: 16px;
}
.dropdown-content {
  display: none;
  position: absolute;
  right: 0;
  background-color: #222;
  min-width: 160px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.4);
  border-radius: 8px;
  z-index: 1;
}
.dropdown-content a {
  color: #fff;
  padding: 10px 16px;
  text-decoration: none;
  display: block;
}
.dropdown-content a:hover {
  background-color: #444;
}
.dropdown:hover .dropdown-content {
  display: block;
}

/* === زر القائمة للجوال === */
.menu-toggle {
  display: none;
  background: none;
  border: none;
  color: #ffd700;
  font-size: 26px;
  cursor: pointer;
}

/* === تجاوب الشاشات الصغيرة === */
@media (max-width: 768px) {
  .navbar-container {
    flex-wrap: wrap;
  }
  .menu-toggle {
    display: block;
  }
  .navbar-links {
    flex-direction: column;
    width: 100%;
    background-color: rgba(0,0,0,0.95);
    display: none;
    margin-top: 10px;
    border-radius: 8px;
  }
  .navbar-links.show {
    display: flex;
  }
  .navbar-links li {
    text-align: center;
    padding: 10px 0;
  }
  .navbar-right {
    margin-top: 10px;
  }
}
</style>

<script>
// ✅ تفعيل القائمة المنسدلة للجوال
document.getElementById('menu-toggle').addEventListener('click', function() {
  document.getElementById('navbar-links').classList.toggle('show');
});
</script>

