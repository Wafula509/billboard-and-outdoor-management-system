<?php
// manage_bookings.php
session_start();
include 'db_connect.php';
include 'email_functions.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $booking_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Booking deleted successfully!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error deleting booking: ' . $conn->error];
    }
    header("Location: manage_bookings.php");
    exit();
}

// Handle status update with email notification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    // First get booking details including user email
    $stmt = $conn->prepare("SELECT b.*, u.email, u.full_name, bi.location 
                           FROM bookings b
                           JOIN users u ON b.user_id = u.user_id
                           JOIN billboards bi ON b.billboard_id = bi.billboard_id
                           WHERE b.booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    // Update the status
    $updateStmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
    $updateStmt->bind_param("si", $status, $booking_id);
    
    if ($updateStmt->execute()) {
        // Send email notification based on status
        $user_email = $booking['email'];
        $user_name = $booking['full_name'];
        $location = $booking['location'];
        $start_date = date('M j, Y', strtotime($booking['start_date']));
        $end_date = date('M j, Y', strtotime($booking['end_date']));
        
        if ($status == 'Approved') {
            $subject = "Your Booking #$booking_id Has Been Approved";
            $body = "
                <h2>Booking Approved</h2>
                <p>Hello $user_name,</p>
                <p>We're pleased to inform you that your booking for <strong>$location</strong> has been approved!</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li><strong>Booking ID:</strong> #$booking_id</li>
                    <li><strong>Dates:</strong> $start_date to $end_date</li>
                    <li><strong>Status:</strong> Approved</li>
                </ul>
                <p>You can now proceed with your advertising campaign as scheduled.</p>
                <p>Thank you for choosing our service!</p>
            ";
        } elseif ($status == 'Rejected') {
            $subject = "Your Booking #$booking_id Has Been Rejected";
            $body = "
                <h2>Booking Rejected</h2>
                <p>Hello $user_name,</p>
                <p>We regret to inform you that your booking for <strong>$location</strong> has been rejected.</p>
                <p><strong>Booking Details:</strong></p>
                <ul>
                    <li><strong>Booking ID:</strong> #$booking_id</li>
                    <li><strong>Dates:</strong> $start_date to $end_date</li>
                    <li><strong>Status:</strong> Rejected</li>
                </ul>
                <p>If you believe this is an error or have any questions, please contact our support team.</p>
                <p>We apologize for any inconvenience caused.</p>
            ";
        }
        
        // Send the email
        if (isset($subject) && isset($body)) {
            sendEmail($user_email, $subject, $body);
            
            // Also create a notification in the database
            $notificationMsg = "Booking #$booking_id status updated to $status";
            $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $notifyStmt->bind_param("is", $booking['user_id'], $notificationMsg);
            $notifyStmt->execute();
        }
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Booking status updated successfully!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error updating booking status: ' . $conn->error];
    }
    header("Location: manage_bookings.php");
    exit();
}

$result = $conn->query("SELECT b.booking_id, u.full_name as user_name, bi.location, b.status, 
                        b.start_date, b.end_date, b.created_at, bi.price
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.user_id 
                        JOIN billboards bi ON b.billboard_id = bi.billboard_id 
                        ORDER BY b.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Bookings</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3a5a40;
      --danger: #ef233c;
      --success: #4cc9f0;
      --warning: #ff9f1c;
      --dark: #2b2d42;
      --light: #f8f9fa;
      --sidebar: #344e41;
      --sidebar-hover: #588157;
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background: #f0f2f5;
      color: #333;
      line-height: 1.6;
    }
    
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      bottom: 0;
      width: 250px;
      background: var(--sidebar);
      color: white;
      padding: 25px 0;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      z-index: 1000;
    }
    
    .sidebar h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 1.5rem;
      color: white;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar a {
      display: flex;
      align-items: center;
      color: #bdc3c7;
      text-decoration: none;
      padding: 12px 25px;
      margin: 5px 0;
      transition: all 0.3s;
      border-left: 3px solid transparent;
    }
    
    .sidebar a i {
      margin-right: 10px;
      font-size: 1.1rem;
    }
    
    .sidebar a:hover, .sidebar a.active {
      color: white;
      background: var(--sidebar-hover);
      border-left: 3px solid var(--success);
    }
    
    .content {
      margin-left: 250px;
      padding: 30px;
    }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    
    .header h2 {
      color: var(--dark);
      font-weight: 600;
    }
    
    .card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      padding: 25px;
      margin-bottom: 30px;
      overflow-x: auto;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    
    th {
      background: var(--primary);
      color: white;
      font-weight: 500;
    }
    
    tr:nth-child(even) {
      background: #f9f9f9;
    }
    
    tr:hover {
      background: #f1f1f1;
    }
    
    .action-buttons {
      display: flex;
      gap: 10px;
    }
    
    .btn {
      padding: 8px 15px;
      border-radius: 5px;
      font-size: 0.9rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s;
      border: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    
    .btn i {
      margin-right: 5px;
    }
    
    .btn-edit {
      background: var(--primary);
      color: white;
    }
    
    .btn-edit:hover {
      background: #3a56e8;
    }
    
    .btn-delete {
      background: var(--danger);
      color: white;
    }
    
    .btn-delete:hover {
      background: #d90429;
    }
    
    .btn-status {
      background: var(--warning);
      color: white;
    }
    
    .btn-status:hover {
      background: #e08c00;
    }
    
    /* Status badges */
    .status-badge {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
      text-transform: capitalize;
    }
    
    .status-pending {
      background: #fff3cd;
      color: #856404;
    }
    
    .status-approved {
      background: #d4edda;
      color: #155724;
    }
    
    .status-rejected {
      background: #f8d7da;
      color: #721c24;
    }
    
    .status-completed {
      background: #cce5ff;
      color: #004085;
    }
    
    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1050;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      overflow: auto;
    }
    
    .modal-content {
      background: white;
      margin: 10% auto;
      width: 500px;
      max-width: 90%;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      position: relative;
      animation: modalopen 0.3s;
    }
    
    @keyframes modalopen {
      from {opacity: 0; transform: translateY(-50px);}
      to {opacity: 1; transform: translateY(0);}
    }
    
    .modal-header {
      padding: 20px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-header h3 {
      margin: 0;
      color: var(--dark);
    }
    
    .close-btn {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #999;
    }
    
    .modal-body {
      padding: 20px;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }
    
    .form-control {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
      transition: border 0.3s;
    }
    
    .form-control:focus {
      outline: none;
      border-color: var(--primary);
    }
    
    select.form-control {
      height: 40px;
    }
    
    .modal-footer {
      padding: 20px;
      border-top: 1px solid #eee;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    .btn-save {
      background: var(--success);
      color: white;
    }
    
    .btn-save:hover {
      background: #3aa8d8;
    }
    
    .btn-cancel {
      background: #6c757d;
      color: white;
    }
    
    .btn-cancel:hover {
      background: #5a6268;
    }
    
    /* Alert Messages */
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      animation: fadeIn 0.5s;
    }
    
    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }
    
    .alert-success {
      background: #28a745;
    }
    
    .alert-error {
      background: #dc3545;
    }
    
    .close-alert {
      background: none;
      border: none;
      color: white;
      font-size: 1.2rem;
      cursor: pointer;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        width: 70px;
        overflow: hidden;
      }
      
      .sidebar h2, .sidebar a span {
        display: none;
      }
      
      .sidebar a {
        justify-content: center;
        padding: 15px 0;
      }
      
      .sidebar a i {
        margin-right: 0;
        font-size: 1.3rem;
      }
      
      .content {
        margin-left: 70px;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
    <a href="manage_users.php"><i class="fas fa-users"></i> <span>Manage Users</span></a>
    <a href="manage_billboards.php"><i class="fas fa-bullhorn"></i> <span>Billboards</span></a>
    <a href="manage_bookings.php" class="active"><i class="fas fa-calendar-check"></i> <span>Bookings</span></a>
    <a href="manage_payments.php"><i class="fas fa-credit-card"></i> <span>Payments</span></a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
  </div>

  <div class="content">
    <div class="header">
      <h2>Manage Bookings</h2>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>">
        <?php echo $_SESSION['message']['text']; ?>
        <button class="close-alert">&times;</button>
      </div>
      <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <div class="card">
      <table>
        <thead>
          <tr>
            <th>Booking ID</th>
            <th>User</th>
            <th>Billboard</th>
            <th>Price</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($booking = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $booking['booking_id']; ?></td>
            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
            <td><?php echo htmlspecialchars($booking['location']); ?></td>
            <td>$<?php echo number_format($booking['price'], 2); ?></td>
            <td><?php echo date('M d, Y', strtotime($booking['start_date'])); ?></td>
            <td><?php echo date('M d, Y', strtotime($booking['end_date'])); ?></td>
            <td>
              <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                <?php echo $booking['status']; ?>
              </span>
            </td>
            <td><?php echo date('M d, Y h:i A', strtotime($booking['created_at'])); ?></td>
            <td>
              <div class="action-buttons">
                <button class="btn btn-status status-btn" 
                  data-id="<?php echo $booking['booking_id']; ?>"
                  data-status="<?php echo $booking['status']; ?>">
                  <i class="fas fa-sync-alt"></i> Status
                </button>
                <button class="btn btn-edit edit-btn" 
                  data-id="<?php echo $booking['booking_id']; ?>">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-delete delete-btn" 
                  data-id="<?php echo $booking['booking_id']; ?>"
                  data-user="<?php echo htmlspecialchars($booking['user_name']); ?>">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Status Update Modal -->
  <div id="statusModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Update Booking Status</h3>
        <button class="close-btn">&times;</button>
      </div>
      <form id="statusForm" method="post" action="manage_bookings.php">
        <input type="hidden" name="update_status" value="1">
        <input type="hidden" name="booking_id" id="status_booking_id">
        <div class="modal-body">
          <div class="form-group">
            <label for="status">Select Status</label>
            <select class="form-control" id="status" name="status" required>
              <option value="Pending">Pending</option>
              <option value="Approved">Approved</option>
              <option value="Rejected">Rejected</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel">Cancel</button>
          <button type="submit" class="btn btn-save">Update Status</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Confirm Deletion</h3>
        <button class="close-btn">&times;</button>
      </div>
      <form id="deleteForm" method="post" action="manage_bookings.php">
        <div class="modal-body">
          <input type="hidden" name="delete_id" id="delete_booking_id">
          <p>Are you sure you want to delete booking for <strong id="delete_booking_user"></strong>?</p>
          <p>This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel">Cancel</button>
          <button type="submit" class="btn btn-delete">Delete Booking</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      // Close alert message
      $('.close-alert').click(function() {
        $(this).parent().fadeOut();
      });
      
      // Open Status Modal
      $('.status-btn').click(function() {
        const bookingId = $(this).data('id');
        const currentStatus = $(this).data('status');
        
        $('#status_booking_id').val(bookingId);
        $('#status').val(currentStatus);
        $('#statusModal').show();
      });
      
      // Open Delete Modal
      $('.delete-btn').click(function() {
        const bookingId = $(this).data('id');
        const userName = $(this).data('user');
        
        $('#delete_booking_id').val(bookingId);
        $('#delete_booking_user').text(userName);
        
        $('#deleteModal').show();
      });
      
      // Close modals
      $('.close-btn, .btn-cancel').click(function() {
        $('.modal').hide();
      });
      
      // Close modal when clicking outside
      $(window).click(function(e) {
        if ($(e.target).hasClass('modal')) {
          $('.modal').hide();
        }
      });
    });
  </script>
</body>
</html>// Handle booking confirmation with payment prompt
document.querySelector('.confirm-booking-btn').addEventListener('click', function() {
    const bookingId = this.dataset.bookingId;
    
    fetch('update_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            booking_id: bookingId,
            status: 'Confirmed'
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.payment_required) {
            showPaymentModal(data.payment_details);
        } else if (data.success) {
            showAlert('Success', data.message);
        } else {
            showAlert('Error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error', 'An error occurred');
    });
});

// Show payment modal
function showPaymentModal(paymentDetails) {
    const modal = document.createElement('div');
    modal.className = 'payment-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3>Complete Payment</h3>
            <p>Please send KSh ${paymentDetails.amount.toFixed(2)} to:</p>
            <p><strong>Paybill: 0791844103</strong></p>
            <p>From your registered number: ${paymentDetails.user_phone}</p>
            
            <div class="form-group">
                <label>Enter M-Pesa Transaction Code:</label>
                <input type="text" id="mpesa-code" placeholder="e.g. MGS4567XHJK">
            </div>
            
            <div class="modal-actions">
                <button class="btn-cancel">Cancel</button>
                <button class="btn-confirm">I've Paid</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Handle confirmation
    modal.querySelector('.btn-confirm').addEventListener('click', function() {
        const mpesaCode = document.getElementById('mpesa-code').value;
        if (!mpesaCode) {
            alert('Please enter your M-Pesa code');
            return;
        }
        verifyPayment(paymentDetails.payment_id, mpesaCode);
        document.body.removeChild(modal);
    });
    
    // Handle cancel
    modal.querySelector('.btn-cancel').addEventListener('click', function() {
        document.body.removeChild(modal);
    });
}

// Verify payment
function verifyPayment(paymentId, mpesaCode) {
    fetch('verify_payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            payment_id: paymentId,
            mpesa_code: mpesaCode
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Success', 'Payment verified! Your booking is now confirmed.');
            // Refresh booking status
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showAlert('Error', data.message || 'Payment verification failed');
        }
    });
}

// Helper function to show alerts
function showAlert(title, message) {
    alert(`${title}\n\n${message}`);
}