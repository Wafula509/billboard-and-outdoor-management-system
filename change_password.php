<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["admin_id"])) {
    echo "<script>alert('Access denied! Please log in as admin.'); window.location='login.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_SESSION["admin_id"];
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Fetch the current password from database
    $query = "SELECT password_hash FROM admins WHERE admin_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    $stmt->fetch();
    $stmt->close();

    // Verify current password
    if (!password_verify($current_password, $stored_password)) {
        echo "<script>alert('Current password is incorrect!');</script>";
    } elseif ($new_password !== $confirm_password) {
        echo "<script>alert('New passwords do not match!');</script>";
    } else {
        // Update password in the database
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE admins SET password_hash = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $new_hashed_password, $admin_id);
        if ($stmt->execute()) {
            echo "<script>alert('Password changed successfully!'); window.location='admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error updating password!');</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>
<body>
    <h2>Change Admin Password</h2>
    <form method="POST">
        <input type="password" name="current_password" placeholder="Current Password" required><br>
        <input type="password" name="new_password" placeholder="New Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required><br>
        <button type="submit">Change Password</button>
    </form>
    <br>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
