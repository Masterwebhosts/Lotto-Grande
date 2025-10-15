<?php
session_start();
$loggedIn = isset($_SESSION['user']);
$isAdmin = $loggedIn && $_SESSION['user']['role'] === 'admin';
$isUser  = $loggedIn && $_SESSION['user']['role'] === 'user';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <title>Lotto Grande</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#2196f3">
  <link rel="manifest" href="/manifest.json">
  <script src="/js/app.js"></script>

  <style>
    body {
      font-family: "Cairo", sans-serif;
      margin: 0; padding: 0; color: #fff;
      background: linear-gradient(135deg,#f7b733,#fc4a1a,#0d47a1);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
    }
    @keyframes gradientBG {
      0%{background-position:0% 50%}
      50%{background-position:100% 50%}
      100%{background-position:0% 50%}
    }
    .container{max-width:1100px;margin:auto;padding:40px 20px}
    .text-center{text-align:center}
    h1{font-size:2.8rem;margin-bottom:15px;text-shadow:2px 2px 5px rgba(0,0,0,.5)}
    h1 span{color:#ffd700;font-weight:bold}
    .lead{font-size:1.25rem;max-width:650px;margin:auto;margin-bottom:25px;color:#f8f9fa}
    .btn{display:inline-block;text-decoration:none;padding:12px 28px;margin:10px 5px;
      border-radius:50px;font-size:1.1rem;font-weight:bold;transition:.3s;
      box-shadow:0 4px 12px rgba(0,0,0,.2)}
    .btn-primary{background:#ffd700;color:#000}
    .btn-primary:hover{background:#ffca2c}
    .btn-outline{background:transparent;color:#fff;border:2px solid #fff}
    .btn-outline:hover{background:#fff;color:#0d47a1}
    .btn-success{background:#28a745;color:#fff}
    .btn-success:hover{background:#218838}
    .row{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:40px}
    .card{background:linear-gradient(145deg,#0d1b2a,#000);border-radius:15px;padding:25px;
      box-shadow:0 12px 35px rgba(0,0,0,.8);transition:transform .3s,box-shadow .3s;text-align:center}
    .card:hover{transform:translateY(-8px);box-shadow:0 16px 50px rgba(0,0,0,.9)}
    .card h4{color:#ffd700;margin-bottom:12px;font-size:1.3rem;font-weight:bold}
    .card p{color:#f1f1f1;line-height:1.6}
    @media(max-width:600px){h1{font-size:2rem}.lead{font-size:1.1rem}}
  </style>
</head>

<body>
  <div class="container">
    <div class="text-center">
      <h1>🎉 Welcome to <span>Lotto Grande</span></h1>
      <p class="lead">Pick your lucky numbers, join the draws, and win amazing prizes!</p>

      <?php if ($loggedIn): ?>
        <a href="<?= $isAdmin ? '/admin/dashboard.php' : '/user/dashboard.php' ?>" class="btn btn-success">
          🚀 Go to Dashboard
        </a>
      <?php else: ?>
        <a href="/account/register.php" class="btn btn-primary">📝 Create Account</a>
        <a href="/account/login.php" class="btn btn-outline">🔐 Login</a>
      <?php endif; ?>
    </div>

    <div class="row">
      <div class="card">
        <h4>🎰 Lotto Grande</h4>
        <p>The most popular challenge! Pick three numbers and match two to win ×40 jackpot!</p>
      </div>
      <div class="card">
        <h4>⚔️ Lotto Rumble</h4>
        <p>Challenge for professionals! Match three in any order to win ×60!</p>
      </div>
      <div class="card">
        <h4>🎟️ Lotto Strap</h4>
        <p>The toughest challenge! Pick 3 numbers in exact sequence for ×400 jackpot!</p>
      </div>
    </div>
  </div>
 <script>
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;

  // إنشاء بطاقة التثبيت المتحركة
  const banner = document.createElement('div');
  banner.innerHTML = `
    <div id="install-banner" style="
      position: fixed;
      bottom: -150px;
      left: 50%;
      transform: translateX(-50%);
      width: 90%;
      max-width: 360px;
      background: linear-gradient(145deg, #ffd700, #ffca2c);
      color: #000;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
      padding: 18px;
      text-align: center;
      font-family: 'Cairo', sans-serif;
      transition: all 0.6s ease;
      z-index: 99999;
    ">
      <h3 style="margin:0; font-weight:bold;">📲 Install Lotto Grande</h3>
      <p style="margin:8px 0 12px; font-size:0.95rem;">Get quick access & play instantly!</p>
      <button id="install-btn" style="
        background:#0d47a1;
        color:#fff;
        font-weight:bold;
        border:none;
        padding:10px 20px;
        border-radius:50px;
        cursor:pointer;
        transition:0.3s;
      ">Install Now</button>
      <button id="dismiss-btn" style="
        background:transparent;
        color:#000;
        border:none;
        margin-left:10px;
        font-size:0.9rem;
        cursor:pointer;
      ">Later</button>
    </div>
  `;

  document.body.appendChild(banner);

  // تحريك البطاقة إلى الأعلى بعد ظهورها
  setTimeout(() => {
    banner.querySelector('#install-banner').style.bottom = '20px';
  }, 800);

  // زر التثبيت
  const installBtn = document.getElementById('install-btn');
  installBtn.addEventListener('click', async () => {
    banner.querySelector('#install-banner').style.bottom = '-150px';
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    if (outcome === 'accepted') {
      console.log('✅ User installed the app');
    } else {
      console.log('❌ User dismissed install');
    }
    deferredPrompt = null;
  });

  // زر "لاحقًا"
  const dismissBtn = document.getElementById('dismiss-btn');
  dismissBtn.addEventListener('click', () => {
    banner.querySelector('#install-banner').style.bottom = '-150px';
    setTimeout(() => banner.remove(), 700);
  });
});
</script>
 
  
</body>
</html>
