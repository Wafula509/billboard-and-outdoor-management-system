<?php
// admin_signup.php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    
    // Hash the password using a secure algorithm
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert the new admin into the admins table
    $stmt = $conn->prepare("INSERT INTO admins (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $phone, $password_hash);
    
    if ($stmt->execute()) {
        // Redirect to the admin login page after successful signup
        header("Location: admin_login.html");
        exit();
    } else {
        // Display error message (you may want to handle this more gracefully)
        echo "Signup failed. Please try again.";
    }
} else {
    header("Location: admin_signup.html");
    exit();
}
?>
