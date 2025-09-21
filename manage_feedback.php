<?php
// manage_feedback.php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}
$result = $conn->query("SELECT f.feedback_id, u.full_name as user_name, f.message, f.rating, f.created_at 
                         FROM feedback f 
                         JOIN users u ON f.user_id = u.user_id 
                         ORDER BY f.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Feedback</title>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    body { font-family: Arial, sans-serif; background:rgb(201, 145, 238); margin: 0; padding: 0; }
    .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 200px; background:rgb(14, 70, 126); padding: 20px; color: #ecf0f1; }
    .sidebar h2 { text-align: center; margin-bottom: 30px; }
    .sidebar a { display: block; color: #bdc3c7; text-decoration: none; margin: 10px 0; }
    .sidebar a:hover { color: #ecf0f1; }
    .content { margin-left: 250px; padding: 20px; }
    table { width: 100%; border-collapse: collapse; }
    table, th, td { border: 1px solid #bdc3c7; }
    th, td { padding: 10px; text-align: left; }
    th { background: #2c3e50; color: #ecf0f1; }
    .action-buttons a { text-decoration: none; padding: 5px 10px; margin-right: 5px; background: #e74c3c; color: white; border-radius: 3px; }
    .action-buttons a.edit { background: #3498db; }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
    <a href="manage_billboards.php"><i class="fas fa-bullhorn"></i> Billboards</a>
    <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a>
    <a href="manage_payments.php"><i class="fas fa-credit-card"></i> Payments</a>
    <a href="manage_messages.php"><i class="fas fa-envelope"></i> Messages</a>
    <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
  <div class="content">
    <h2>Manage Feedback</h2>
    <table>
      <tr>
        <th>Feedback ID</th>
        <th>User</th>
        <th>Message</th>
        <th>Rating</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
      <?php while($feedback = $result->fetch_assoc()): ?>
      <tr>
        <td><?php echo $feedback['feedback_id']; ?></td>
        <td><?php echo htmlspecialchars($feedback['user_name']); ?></td>
        <td><?php echo htmlspecialchars($feedback['message']); ?></td>
        <td><?php echo $feedback['rating']; ?></td>
        <td><?php echo $feedback['created_at']; ?></td>
        <td class="action-buttons">
          <a class="delete" href="delete_feedback.php?id=<?php echo $feedback['feedback_id']; ?>" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
