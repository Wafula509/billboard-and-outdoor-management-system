<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['html' => '']);
    exit();
}

$user_email = $_SESSION['user_email'];

// Fetch user ID
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_id = $user['user_id'];

// Fetch notifications including admin replies
$query = "SELECT n.notification_id, n.message, n.status, n.created_at, 
                 a.admin_name AS sender
          FROM notifications n
          LEFT JOIN admins a ON n.admin_id = a.admin_id
          WHERE n.user_id = ?
          ORDER BY n.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$html = '';

while ($notification = $result->fetch_assoc()) {
    $class = ($notification['status'] == 'Unread') ? 'unread' : '';
    $replyHtml = (!empty($notification['sender'])) ? "<div class='reply'><strong>Reply from " . htmlspecialchars($notification['sender']) . ":</strong> " . htmlspecialchars($notification['message']) . "</div>" : '';

    $html .= "<div class='notification $class'>
                <p>" . htmlspecialchars($notification['message']) . "</p>
                <span>" . $notification['created_at'] . "</span>
                $replyHtml
                <a class='mark-read' href='mark_read.php?id=" . $notification['notification_id'] . "'>Mark as Read</a>
              </div>";
}

echo json_encode(['html' => $html]);
?>
