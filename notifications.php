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
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; text-align: center; padding: 20px; }
        .chat-container { width: 80%; max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .chat-box { height: 300px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; background: #fff; margin-bottom: 10px; }
        .message { padding: 5px; margin: 5px 0; border-radius: 5px; }
        .admin { background: #3498db; color: white; text-align: right; }
        .user { background: #e74c3c; color: white; text-align: left; }
        input, button { padding: 10px; margin: 5px; width: 90%; }
    </style>
</head>
<body>

    <div class="chat-container">
        <h2>User Chat</h2>
        <div class="chat-box" id="chatBox"></div>
        <input type="text" id="userMessage" placeholder="Type your message..." />
        <button onclick="sendMessage('user')">Send</button>
    </div>

    <script>
        function loadChat() {
            let messages = JSON.parse(localStorage.getItem("chatMessages")) || [];
            let chatBox = document.getElementById("chatBox");
            chatBox.innerHTML = "";
            messages.forEach(msg => {
                let div = document.createElement("div");
                div.classList.add("message", msg.sender);
                div.innerText = msg.text;
                chatBox.appendChild(div);
            });
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function sendMessage(sender) {
            let input = document.getElementById("userMessage");
            let text = input.value.trim();
            if (text) {
                let messages = JSON.parse(localStorage.getItem("chatMessages")) || [];
                messages.push({ sender: sender, text: "User: " + text });
                localStorage.setItem("chatMessages", JSON.stringify(messages));
                input.value = "";
                loadChat();
            }
        }

        loadChat();
        setInterval(loadChat, 3000);
    </script>

</body>
</html>
