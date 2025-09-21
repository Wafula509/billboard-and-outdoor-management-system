<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_SESSION['admin_id']; 
    $message_id = $_POST['message_id'];
    $reply = $_POST['reply'];

    $stmt = $conn->prepare("INSERT INTO message_replies (message_id, admin_id, reply) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $message_id, $admin_id, $reply);

    if ($stmt->execute()) {
        header("Location: manage_messages.php?success=Reply Sent");
    } else {
        header("Location: manage_messages.php?error=Failed to send reply");
    }
}
?>
