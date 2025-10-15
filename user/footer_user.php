<!-- ======= Footer ======= -->
<footer class="site-footer">
  <div class="footer-container">
    <p>© <?= date('Y') ?> Lotto Grande 🎲 All rights reserved.</p>
    <div class="footer-links">
      <a href="/user/dashboard.php">🏠 Home</a>
      <a href="/user/contests.php">🎯 Contests</a>
      <a href="/user/results.php">🏆 Results</a>
      <a href="/user/edit_profile.php">👤 My Account</a>
      <a href="/user/game_rules.php">💡 Game Rules</a>
    </div>
  </div>
</footer>

<!-- Toast Container -->
<div id="toast-container"></div>

<style>
/* === Footer Style === */
.site-footer {
  background: rgba(0,0,0,0.85);
  color: #ffd700;
  text-align: center;
  padding: 20px 10px;
  border-top: 2px solid rgba(255,215,0,0.2);
  box-shadow: 0 -4px 15px rgba(0,0,0,0.6);
  font-family: "Cairo", sans-serif;
}
.footer-container {
  max-width: 900px;
  margin: 0 auto;
}
.footer-links {
  margin-top: 10px;
}
.footer-links a {
  color: #ffd700;
  text-decoration: none;
  margin: 0 10px;
  font-weight: bold;
  transition: 0.3s;
}
.footer-links a:hover {
  color: #fff;
  text-shadow: 0 0 8px #ffd700;
}

/* === Toast Style === */
#toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 2000;
}
.toast {
  background: rgba(0,0,0,0.85);
  color: #fff;
  padding: 12px 20px;
  margin-top: 10px;
  border-radius: 8px;
  font-size: 0.95rem;
  min-width: 220px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.5);
  animation: slideIn 0.4s ease;
}
.toast.success { border-left: 5px solid #28a745; }
.toast.error { border-left: 5px solid #dc3545; }
.toast.info { border-left: 5px solid #17a2b8; }
.toast.warning { border-left: 5px solid #ffc107; }

@keyframes slideIn {
  from { opacity: 0; transform: translateX(100%); }
  to { opacity: 1; transform: translateX(0); }
}
</style>

<script>
function showToast(message, type="info") {
  const container = document.getElementById("toast-container");
  const toast = document.createElement("div");
  toast.className = "toast " + type;
  toast.innerHTML = message;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 500);
  }, 3000);
}
</script>
