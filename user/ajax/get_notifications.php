<?php
session_start();
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';

$user_id = $_SESSION['user_id'] ?? null;
$data = ['count' => 0, 'notifications' => []];

if ($user_id) {
    $sql = "SELECT title, message, type, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as created_at 
            FROM notifications 
            WHERE user_id = $user_id OR user_id IS NULL 
            ORDER BY id DESC 
            LIMIT 5";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
        $data['notifications'] = $rows;
        $data['count'] = count($rows);
    }
}

echo json_encode($data);

