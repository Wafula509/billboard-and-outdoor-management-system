<?php
// receipt.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: user_login.php"); // Changed from .html to .php
    exit();
}

if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    die("Invalid booking specified.");
}
$booking_id = intval($_GET['booking_id']);

try {
    // Fetch booking, billboard, payment, and user details
    $stmt = $conn->prepare("SELECT b.booking_id, b.start_date, b.end_date, b.status, 
                           bi.location, bi.size, bi.price, p.amount, p.payment_method, 
                           p.transaction_id, p.created_at as payment_date, u.full_name 
                           FROM bookings b 
                           JOIN billboards bi ON b.billboard_id = bi.billboard_id 
                           JOIN payments p ON b.booking_id = p.booking_id 
                           JOIN users u ON b.user_id = u.user_id 
                           WHERE b.booking_id = ? AND b.user_id = ?");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $receipt = $stmt->get_result()->fetch_assoc();
    
    if (!$receipt) {
        die("Receipt not found or you don't have permission to view it.");
    }
} catch (Exception $e) {
    die("Error retrieving receipt: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt - Booking #<?php echo htmlspecialchars($receipt['booking_id']); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #2980b9;
      --secondary: #2c3e50;
      --success: #27ae60;
      --light: #f8f9fa;
      --dark: #343a40;
    }
    
    body { 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
      background: var(--light); 
      padding: 20px;
      color: var(--dark);
    }
    
    .receipt-container { 
      max-width: 700px; 
      margin: 0 auto; 
      border: 1px solid #ddd; 
      padding: 30px; 
      background: white;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      border-radius: 8px;
    }
    
    .receipt-header { 
      text-align: center; 
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #eee;
    }
    
    .receipt-header h2 { 
      color: var(--primary);
      margin-bottom: 5px;
    }
    
    .receipt-id {
      font-size: 1.1rem;
      color: #666;
    }
    
    .receipt-details { 
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin: 30px 0;
    }
    
    .detail-group {
      margin-bottom: 15px;
    }
    
    .detail-group h3 {
      color: var(--primary);
      margin-bottom: 10px;
      font-size: 1.1rem;
      border-bottom: 1px dashed #ddd;
      padding-bottom: 5px;
    }
    
    .detail-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
    }
    
    .detail-label {
      font-weight: 600;
      color: #555;
    }
    
    .detail-value {
      text-align: right;
    }
    
    .total-amount {
      font-size: 1.3rem;
      font-weight: bold;
      color: var(--success);
      text-align: right;
      margin-top: 20px;
      padding-top: 10px;
      border-top: 2px solid #eee;
    }
    
    .action-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 30px;
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s;
    }
    
    .btn-print {
      background: var(--primary);
      color: white;
    }
    
    .btn-print:hover {
      background: #2472a4;
    }
    
    .btn-back {
      background: var(--secondary);
      color: white;
    }
    
    .btn-back:hover {
      background: #1a252f;
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    @media print {
      body { background: white; }
      .receipt-container { box-shadow: none; border: none; }
      .action-buttons { display: none; }
    }
    
    @media (max-width: 768px) {
      .receipt-details {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="receipt-container">
    <div class="receipt-header">
      <h2><i class="fas fa-receipt"></i> Booking Receipt</h2>
      <div class="receipt-id">Transaction #<?php echo htmlspecialchars($receipt['transaction_id']); ?></div>
    </div>
    
    <div class="receipt-details">
      <div class="detail-group">
        <h3>Booking Information</h3>
        <div class="detail-item">
          <span class="detail-label">Booking ID:</span>
          <span class="detail-value"><?php echo htmlspecialchars($receipt['booking_id']); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Customer Name:</span>
          <span class="detail-value"><?php echo htmlspecialchars($receipt['full_name']); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Booking Dates:</span>
          <span class="detail-value">
            <?php echo date('M j, Y', strtotime($receipt['start_date'])); ?> - 
            <?php echo date('M j, Y', strtotime($receipt['end_date'])); ?>
          </span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Status:</span>
          <span class="detail-value"><?php echo htmlspecialchars(ucfirst($receipt['status'])); ?></span>
        </div>
      </div>
      
      <div class="detail-group">
        <h3>Billboard Details</h3>
        <div class="detail-item">
          <span class="detail-label">Location:</span>
          <span class="detail-value"><?php echo htmlspecialchars($receipt['location']); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Size:</span>
          <span class="detail-value"><?php echo htmlspecialchars($receipt['size']); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Daily Rate:</span>
          <span class="detail-value">$<?php echo number_format($receipt['price'], 2); ?></span>
        </div>
      </div>
      
      <div class="detail-group">
        <h3>Payment Information</h3>
        <div class="detail-item">
          <span class="detail-label">Payment Method:</span>
          <span class="detail-value"><?php echo htmlspecialchars(ucfirst($receipt['payment_method'])); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Transaction ID:</span>
          <span class="detail-value"><?php echo htmlspecialchars($receipt['transaction_id']); ?></span>
        </div>
        <div class="detail-item">
          <span class="detail-label">Payment Date:</span>
          <span class="detail-value"><?php echo date('M j, Y H:i', strtotime($receipt['payment_date'])); ?></span>
        </div>
      </div>
    </div>
    
    <div class="total-amount">
      Total Paid: $<?php echo number_format($receipt['amount'], 2); ?>
    </div>
    
    <div class="action-buttons">
      <a href="javascript:window.print()" class="btn btn-print">
        <i class="fas fa-print"></i> Print Receipt
      </a>
      <a href="user_dashboard.php" class="btn btn-back">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>
  </div>
</body>
</html>
