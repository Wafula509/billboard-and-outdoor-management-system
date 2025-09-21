<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_logged_in']) || !isset($_GET['id'])) {
    header("Location: notifications.php");
    exit();
}

$notification_id = intval($_GET['id']);

// Update notification status to 'Read'
$stmt = $conn->prepare("UPDATE notifications SET status = 'Read' WHERE notification_id = ?");
$stmt->bind_param("i", $notification_id);
$stmt->execute();

header("Location: notifications.php");
?>
