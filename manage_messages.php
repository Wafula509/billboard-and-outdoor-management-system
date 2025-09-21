<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: user_login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Chat</title>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            text-align: center;
            padding: 20px;
        }
        .chat-container {
            width: 80%;
            max-width: 600px;
            margin: auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .chat-box {
            height: 350px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            background: #fff;
            margin-bottom: 10px;
            border-radius: 10px;
        }
        .message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 20px;
            display: flex;
            align-items: center;
            font-size: 16px;
        }
        .admin {
            background: #3498db;
            color: white;
            text-align: right;
            justify-content: flex-end;
        }
        .user {
            background: #e74c3c;
            color: white;
            text-align: left;
            justify-content: flex-start;
        }
        .icon {
            margin: 0 10px;
        }
        input, button {
            padding: 10px;
            margin: 5px;
            width: 90%;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background: #27ae60;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: #218c53;
        }
    </style>
</head>
<body>

    <div class="chat-container">
        <h2>User Chat <i class="fas fa-comments"></i></h2>
        <div class="chat-box" id="chatBox"></div>
        <input type="text" id="userMessage" placeholder="Type your message..." />
        <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i> Send</button>
    </div>

    <script>
        function loadChat() {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", "load_messages.php", true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById("chatBox").innerHTML = xhr.responseText;
                    document.getElementById("chatBox").scrollTop = document.getElementById("chatBox").scrollHeight;
                }
            };
            xhr.send();
        }

        function sendMessage() {
            let text = document.getElementById("userMessage").value.trim();
            if (text) {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "send_message.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        document.getElementById("userMessage").value = "";
                        loadChat();
                    }
                };
                xhr.send("message=" + encodeURIComponent(text));
            }
        }

        loadChat();
        setInterval(loadChat, 3000);
    </script>

</body>
</html>
