<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';

// 🔒 حماية الأدمن
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../account/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("❌ Invalid contest ID");
}

$contest_id = (int)$_GET['id'];

// 🧨 تنفيذ الحذف
$stmt = $conn->prepare("DELETE FROM contests WHERE id = ?");
$stmt->bind_param("i", $contest_id);

if ($stmt->execute()) {
    header("Location: contests.php?deleted=1");
    exit;
} else {
    die("❌ Failed to delete contest: " . $conn->error);
}
?>
