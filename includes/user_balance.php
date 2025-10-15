<?php
// ✅ بدء الجلسة إن لم تكن بدأت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ استدعاء الاتصال بقاعدة البيانات
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';

// ✅ التأكد من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: /account/login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// ✅ جلب بيانات المستخدم (الاسم + الرصيد)
$stmt = $conn->prepare("SELECT username, balance FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if ($user) {
        $username = htmlspecialchars($user['username']);
        $balance = number_format($user['balance'], 2);
    } else {
        // في حال لم يتم العثور على المستخدم
        $username = "Unknown";
        $balance = "0.00";
    }
} else {
    // فشل في الاتصال بقاعدة البيانات
    $username = "Error";
    $balance = "0.00";
}
?>
