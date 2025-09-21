<?php
// admin_dashboard.php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}
$admin_email = $_SESSION['admin_email'];

// Fetch admin details
$stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// Summary queries
$totalUsers      = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalAdmins     = $conn->query("SELECT COUNT(*) as total FROM admins")->fetch_assoc()['total'];
$totalBillboards = $conn->query("SELECT COUNT(*) as total FROM billboards")->fetch_assoc()['total'];
$pendingBookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status='Pending'")->fetch_assoc()['total'];
$totalPayments   = $conn->query("SELECT COUNT(*) as total FROM payments WHERE payment_status='Completed'")->fetch_assoc()['total'];
$totalMessages   = $conn->query("SELECT COUNT(*) as total FROM contact_messages")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <style>
    body { margin: 0; font-family: Arial, sans-serif; background:rgb(136, 205, 223); }
    .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 200px; background:rgb(42, 74, 105); padding: 20px; color: #ecf0f1; overflow-y: auto; }
    .sidebar h2 { text-align: center; margin-bottom: 30px; }
    .sidebar a { display: block; color: #bdc3c7; text-decoration: none; margin: 15px 0; font-size: 16px; }
    .sidebar a:hover { color: #ecf0f1; }
    .sidebar a i { margin-right: 10px; }
    .header { margin-left: 250px; background: #fff; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
    .header .admin-info { font-size: 18px; color: #2c3e50; }
    .header .logout { color: #e74c3c; text-decoration: none; font-size: 16px; }
    .content { margin-left: 250px; padding: 20px; }
    .cards { display: flex; flex-wrap: wrap; gap: 20px; }
    .card { background: #fff; flex: 1; min-width: 200px; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); text-align: center; }
    .card i { font-size: 40px; color: #2c3e50; margin-bottom: 10px; }
    .card h3 { margin: 10px 0; font-size: 20px; color: #2c3e50; }
    .card p { font-size: 16px; color: #7f8c8d; }
    .table-container { margin-top: 30px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    .table-container table { width: 100%; border-collapse: collapse; }
    .table-container th, .table-container td { padding: 12px; border: 1px solid #ecf0f1; text-align: left; }
    .table-container th { background: #2c3e50; color: #ecf0f1; }
    @media (max-width: 768px) { .sidebar { width: 200px; } .header, .content { margin-left: 200px; } }
  </style>
</head>
<body>
  <!-- Sidebar Navigation -->
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
  <!-- Header -->
  <div class="header">
    <div class="admin-info">
      <i class="fas fa-user-shield"></i> Admin: <?php echo htmlspecialchars($admin['full_name']); ?>
    </div>
    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
  <!-- Main Content -->
  <div class="content">
    <h2>Dashboard Overview</h2>
    <div class="cards">
      <div class="card">
        <i class="fas fa-users"></i>
        <h3>Total Users</h3>
        <p><?php echo $totalUsers; ?></p>
      </div>
      <div class="card">
        <i class="fas fa-user-shield"></i>
        <h3>Total Admins</h3>
        <p><?php echo $totalAdmins; ?></p>
      </div>
      <div class="card">
        <i class="fas fa-bullhorn"></i>
        <h3>Billboards</h3>
        <p><?php echo $totalBillboards; ?></p>
      </div>
      <div class="card">
        <i class="fas fa-calendar-check"></i>
        <h3>Pending Bookings</h3>
        <p><?php echo $pendingBookings; ?></p>
      </div>
      <div class="card">
        <i class="fas fa-credit-card"></i>
        <h3>Completed Payments</h3>
        <p><?php echo $totalPayments; ?></p>
      </div>
      <div class="card">
        <i class="fas fa-envelope"></i>
        <h3>Messages</h3>
        <p><?php echo $totalMessages; ?></p>
      </div>
    </div>
    <!-- Recent Bookings Table -->
    <div class="table-container">
      <h3>Recent Bookings</h3>
      <table>
        <tr>
          <th>Booking ID</th>
          <th>User</th>
          <th>Billboard</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
        <?php
        $resultRecent = $conn->query("SELECT b.booking_id, u.full_name as user_name, bi.location, b.status, b.created_at 
                                       FROM bookings b 
                                       JOIN users u ON b.user_id = u.user_id 
                                       JOIN billboards bi ON b.billboard_id = bi.billboard_id 
                                       ORDER BY b.created_at DESC LIMIT 5");
        while($row = $resultRecent->fetch_assoc()):
        ?>
        <tr>
          <td><?php echo $row['booking_id']; ?></td>
          <td><?php echo htmlspecialchars($row['user_name']); ?></td>
          <td><?php echo htmlspecialchars($row['location']); ?></td>
          <td><?php echo $row['status']; ?></td>
          <td><?php echo $row['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </div>
</body>
</html>
