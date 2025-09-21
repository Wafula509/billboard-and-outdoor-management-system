<?php
// admin_login.php
session_start();
include 'db_connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $query = "SELECT * FROM admins WHERE email = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password_hash'])) {
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_email'] = $email;
      header("Location: admin_dashboard.php");
      exit();
    } else {
      echo "Invalid password. <a href='admin_login.html'>Try again</a>";
    }
  } else {
    echo "Admin not found. <a href='admin_login.html'>Try again</a>";
  }
}
?>
