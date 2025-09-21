<?php
// manage_payments.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Search functionality
$search = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_term = "%$search%";
    $stmt = $conn->prepare("SELECT p.payment_id, b.booking_id, p.amount, p.payment_method, 
                           p.payment_status, p.transaction_id, p.created_at
                           FROM payments p
                           JOIN bookings b ON p.booking_id = b.booking_id
                           WHERE p.payment_id LIKE ? OR b.booking_id LIKE ? OR p.transaction_id LIKE ?
                           ORDER BY p.created_at DESC");
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
} else {
    $stmt = $conn->prepare("SELECT p.payment_id, b.booking_id, p.amount, p.payment_method, 
                           p.payment_status, p.transaction_id, p.created_at
                           FROM payments p
                           JOIN bookings b ON p.booking_id = b.booking_id
                           ORDER BY p.created_at DESC");
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Payments - Admin Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #2980b9;
      --secondary: #2c3e50;
      --light: #ecf0f1;
      --dark: #34495e;
      --success: #27ae60;
      --warning: #f39c12;
      --danger: #e74c3c;
    }
    
    body { 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
      background: #f8f9fa; 
      margin: 0; 
      padding: 0; 
      color: #333;
    }
    
    .sidebar { 
      position: fixed; 
      left: 0; 
      top: 0; 
      bottom: 0; 
      width: 220px; 
      background: var(--secondary); 
      color: var(--light); 
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      z-index: 1000;
    }
    
    .sidebar h2 { 
      text-align: center; 
      margin: 20px 0; 
      color: white;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar a { 
      display: flex; 
      align-items: center;
      color: var(--light); 
      text-decoration: none; 
      padding: 12px 20px; 
      transition: all 0.3s;
    }
    
    .sidebar a:hover, 
    .sidebar a.active { 
      background: rgba(255,255,255,0.1); 
      color: white;
    }
    
    .sidebar i { 
      margin-right: 10px; 
      width: 20px;
    }
    
    .content { 
      margin-left: 220px; 
      padding: 20px; 
    }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    
    .search-box {
      display: flex;
      margin-bottom: 20px;
    }
    
    .search-box input {
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 4px 0 0 4px;
      width: 300px;
    }
    
    .search-box button {
      padding: 10px 15px;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 0 4px 4px 0;
      cursor: pointer;
    }
    
    table { 
      width: 100%; 
      border-collapse: collapse; 
      background: white;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    th, td { 
      padding: 12px 15px; 
      text-align: left; 
      border-bottom: 1px solid #eee;
    }
    
    th { 
      background: var(--primary); 
      color: white;
      position: sticky;
      top: 0;
    }
    
    tr:hover {
      background: rgba(0,0,0,0.02);
    }
    
    .status {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }
    
    .status-completed {
      background: #d4edda;
      color: #155724;
    }
    
    .status-pending {
      background: #fff3cd;
      color: #856404;
    }
    
    .status-failed {
      background: #f8d7da;
      color: #721c24;
    }
    
    .action-buttons {
      display: flex;
      gap: 5px;
    }
    
    .action-buttons a { 
      display: inline-flex;
      align-items: center;
      text-decoration: none; 
      padding: 6px 12px; 
      border-radius: 4px; 
      font-size: 0.85rem;
      transition: all 0.2s;
    }
    
    .action-buttons a i {
      margin-right: 5px;
    }
    
    .btn-edit { 
      background: var(--primary); 
      color: white;
    }
    
    .btn-edit:hover {
      background: #2472a4;
    }
    
    .btn-delete { 
      background: var(--danger); 
      color: white;
    }
    
    .btn-delete:hover {
      background: #c0392b;
    }
    
    .no-results {
      text-align: center;
      padding: 20px;
      color: #666;
    }
    
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        position: relative;
        height: auto;
      }
      .content {
        margin-left: 0;
      }
      .search-box input {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
    <a href="manage_billboards.php"><i class="fas fa-bullhorn"></i> Billboards</a>
    <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a>
    <a href="manage_payments.php" class="active"><i class="fas fa-credit-card"></i> Payments</a>
    <a href="manage_messages.php"><i class="fas fa-envelope"></i> Messages</a>
    <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
  
  <div class="content">
    <div class="header">
      <h2>Manage Payments</h2>
      <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search payments..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit"><i class="fas fa-search"></i></button>
      </form>
    </div>
    
    <table>
      <thead>
        <tr>
          <th>Payment ID</th>
          <th>Booking ID</th>
          <th>Amount</th>
          <th>Method</th>
          <th>Status</th>
          <th>Transaction ID</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($payment = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
              <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
              <td>$<?php echo number_format($payment['amount'], 2); ?></td>
              <td><?php echo htmlspecialchars(ucfirst($payment['payment_method'])); ?></td>
              <td>
                <span class="status status-<?php echo strtolower($payment['payment_status']); ?>">
                  <?php echo htmlspecialchars($payment['payment_status']); ?>
                </span>
              </td>
              <td><?php echo $payment['transaction_id'] ? htmlspecialchars($payment['transaction_id']) : 'N/A'; ?></td>
              <td><?php echo date('M j, Y H:i', strtotime($payment['created_at'])); ?></td>
              <td>
                <div class="action-buttons">
                  <a href="edit_payment.php?id=<?php echo $payment['payment_id']; ?>" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="delete_payment.php?id=<?php echo $payment['payment_id']; ?>" 
                     class="btn-delete" 
                     onclick="return confirm('Are you sure you want to delete this payment?')">
                    <i class="fas fa-trash"></i> Delete
                  </a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="no-results">No payments found</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>