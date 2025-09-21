<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message_id = $_POST['message_id'];
    $reply_message = $_POST['reply_message'];
    $email = $_POST['email'];

    // Update the message with a reply
    $stmt = $conn->prepare("UPDATE contact_messages SET reply = ?, replied_at = NOW() WHERE message_id = ?");
    $stmt->bind_param("si", $reply_message, $message_id);
    if ($stmt->execute()) {
        // Insert notification for the user
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, status, created_at) VALUES 
                                ((SELECT user_id FROM users WHERE email = ?), ?, 'Unread', NOW())");
        $notif_message = "Admin replied to your message: " . $reply_message;
        $stmt->bind_param("ss", $email, $notif_message);
        $stmt->execute();
    }

    header("Location: manage_messages.php");
    exit();
}
?>
