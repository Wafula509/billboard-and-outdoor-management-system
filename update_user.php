<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['user_id']) && !empty($_POST['full_name']) && !empty($_POST['email']) && !empty($_POST['phone'])) {
        $user_id = intval($_POST['user_id']);
        $full_name = trim($_POST['full_name']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = preg_replace("/[^0-9]/", "", $_POST['phone']); // Remove non-numeric characters

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("<script>alert('Invalid email format.'); window.history.back();</script>");
        }

        if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
            die("<script>alert('Invalid phone number. Please enter a valid 10-15 digit phone number.'); window.history.back();</script>");
        }

        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=? WHERE user_id=?");
        if ($stmt) {
            $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
            if ($stmt->execute()) {
                echo "<script>alert('User updated successfully!'); window.location.href='manage_users.php';</script>";
            } else {
                echo "<script>alert('Error updating user: " . $stmt->error . "'); window.history.back();</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Error preparing statement: " . $conn->error . "'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}

$conn->close();
?>
