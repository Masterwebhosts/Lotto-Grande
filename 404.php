<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>404 - Lost in the Lotto?</title>
  <style>
    body {
      background: #111;
      color: #fff;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
      flex-direction: column;
      overflow: hidden;
    }
    h1 {
      font-size: 80px;
      margin: 0;
      color: gold;
      text-shadow: 0 0 10px #ffd700, 0 0 20px #ffae00;
      animation: bounce 1.5s infinite;
    }
    p {
      font-size: 20px;
      margin: 20px 0;
    }
    .btn {
      padding: 12px 24px;
      background: gold;
      color: #111;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s ease;
    }
    .btn:hover {
      background: darkgoldenrod;
      color: #fff;
    }
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-15px); }
    }
    .girl-img {
      max-width: 280px;
      margin: 20px auto;
    }
  </style>
</head>
<body>
  <div>
    <h1>404 😵</h1>
    <!-- ✅ صورة بنت متحركة (GIF) -->
    <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExN3BrdHZrbjF2ZjF1bnB3dTljOHdyb3pyd3h2NzNqaWFtdG1kYnlkcyZlcD12MV9naWZzX3NlYXJjaCZjdD1n/qgQUggAC3Pfv687qPC/giphy.gif" 
         alt="Lost Girl Animation" class="girl-img">
    <p>Oops! She’s still looking for this page... <br> الصفحة اللي بدك إياها راحت تتمشى 🎲</p>
    <a href="/index.php" class="btn">⬅️ رجوع للصفحة الرئيسية</a>
  </div>
</body>
</html>
