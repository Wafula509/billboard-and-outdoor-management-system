<?php
session_start();
$messages_file = "messages.txt";

if (file_exists($messages_file)) {
    $messages = file($messages_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($messages as $msg) {
        list($sender, $message) = explode("|", $msg, 2);
        $senderClass = ($sender === 'admin') ? "admin" : "user";
        echo "<div class='message $senderClass'>";
        echo "<i class='icon " . ($senderClass === 'admin' ? 'fas fa-user-shield' : 'fas fa-user') . "'></i> ";
        echo htmlspecialchars($message);
        echo "</div>";
    }
}
?>
