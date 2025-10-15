<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';
require_once("navbar_admin.php");


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../account/login.php");
    exit;
}

// Total deposits
$res_deposits = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount),0) AS total FROM transactions WHERE type='deposit'"));
$total_deposits = $res_deposits['total'];

// Total withdrawals
$res_withdrawals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount),0) AS total FROM transactions WHERE type='withdraw'"));
$total_withdrawals = $res_withdrawals['total'];

// Profit
$profit = $total_deposits - $total_withdrawals;

// Transactions by day (last 7 days)
$query = "SELECT DATE(created_at) as day, 
                 SUM(CASE WHEN type='deposit' THEN amount ELSE 0 END) as deposits,
                 SUM(CASE WHEN type='withdraw' THEN amount ELSE 0 END) as withdrawals
          FROM transactions
          GROUP BY DATE(created_at)
          ORDER BY day DESC
          LIMIT 7";
$result = mysqli_query($conn, $query);

$days = [];
$deposits_data = [];
$withdraws_data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $days[] = $row['day'];
    $deposits_data[] = $row['deposits'];
    $withdraws_data[] = $row['withdrawals'];
}
$days = array_reverse($days);
$deposits_data = array_reverse($deposits_data);
$withdraws_data = array_reverse($withdraws_data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/navbar.css">
    <link rel="stylesheet" href="../assets/admin.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
        .card { background: #222; color: #fff; padding: 20px; border-radius: 12px; text-align: center; }
        .card h3 { margin: 0; font-size: 20px; color: #ffd700; }
        .card p { font-size: 24px; margin: 10px 0 0; }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Financial Reports</h2>

    <div class="dashboard">
        <div class="card">
            <h3>Total Deposits</h3>
            <p><?php echo $total_deposits; ?> 💰</p>
        </div>
        <div class="card">
            <h3>Total Withdrawals</h3>
            <p><?php echo $total_withdrawals; ?> 💸</p>
        </div>
        <div class="card">
            <h3>System Profit</h3>
            <p><?php echo $profit; ?> ⚡</p>
        </div>
    </div>

    <hr>
    <h3>Transactions (Last 7 Days)</h3>
    <canvas id="reportChart" width="600" height="300"></canvas>
</div>

<script>
const ctx = document.getElementById('reportChart').getContext('2d');
const reportChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($days); ?>,
        datasets: [
            {
                label: 'Deposits',
                data: <?php echo json_encode($deposits_data); ?>,
                borderColor: 'green',
                fill: false
            },
            {
                label: 'Withdrawals',
                data: <?php echo json_encode($withdraws_data); ?>,
                borderColor: 'red',
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        }
    }
});
</script>
</body>
</html>
