<?php
// manage_users.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $user_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'User deleted successfully!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error deleting user: ' . $conn->error];
    }
    header("Location: manage_users.php");
    exit();
}

// Handle edit action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $user_id = $_POST['edit_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'User updated successfully!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error updating user: ' . $conn->error];
    }
    header("Location: manage_users.php");
    exit();
}

$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f0d12;
      --danger: #ef233c;
      --success: #4cc9f0;
      --dark: #2b2d42;
      --light: #f8f9fa;
      --sidebar: #3f2c56;
      --sidebar-hover: #4d3869;
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
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
    <a href="manage_users.php" class="active"><i class="fas fa-users"></i> <span>Manage Users</span></a>
    <a href="manage_billboards.php"><i class="fas fa-bullhorn"></i> <span>Billboards</span></a>
    <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> <span>Bookings</span></a>
    <a href="manage_payments.php"><i class="fas fa-credit-card"></i> <span>Payments</span></a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
  </div>

  <div class="content">
    <div class="header">
      <h2>Manage Users</h2>
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
            <th>User ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($user = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $user['user_id']; ?></td>
            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['phone']); ?></td>
            <td><?php echo date('M d, Y h:i A', strtotime($user['created_at'])); ?></td>
            <td>
              <div class="action-buttons">
                <button class="btn btn-edit edit-btn" 
                  data-id="<?php echo $user['user_id']; ?>"
                  data-name="<?php echo htmlspecialchars($user['full_name']); ?>"
                  data-email="<?php echo htmlspecialchars($user['email']); ?>"
                  data-phone="<?php echo htmlspecialchars($user['phone']); ?>">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-delete delete-btn" 
                  data-id="<?php echo $user['user_id']; ?>"
                  data-name="<?php echo htmlspecialchars($user['full_name']); ?>">
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

  <!-- Edit User Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Edit User</h3>
        <button class="close-btn">&times;</button>
      </div>
      <form id="editForm" method="post">
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="edit_user_id">
          <div class="form-group">
            <label for="edit_full_name">Full Name</label>
            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
          </div>
          <div class="form-group">
            <label for="edit_email">Email</label>
            <input type="email" class="form-control" id="edit_email" name="email" required>
          </div>
          <div class="form-group">
            <label for="edit_phone">Phone</label>
            <input type="text" class="form-control" id="edit_phone" name="phone" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel">Cancel</button>
          <button type="submit" class="btn btn-save">Save Changes</button>
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
      <form id="deleteForm" method="post">
        <div class="modal-body">
          <input type="hidden" name="delete_id" id="delete_user_id">
          <p>Are you sure you want to delete user: <strong id="delete_user_name"></strong>?</p>
          <p>This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel">Cancel</button>
          <button type="submit" class="btn btn-delete">Delete User</button>
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
      
      // Open Edit Modal
      $('.edit-btn').click(function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        const userEmail = $(this).data('email');
        const userPhone = $(this).data('phone');
        
        $('#edit_user_id').val(userId);
        $('#edit_full_name').val(userName);
        $('#edit_email').val(userEmail);
        $('#edit_phone').val(userPhone);
        
        $('#editModal').show();
      });
      
      // Open Delete Modal
      $('.delete-btn').click(function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        $('#delete_user_id').val(userId);
        $('#delete_user_name').text(userName);
        
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
      
      // Submit Edit Form
      $('#editForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
          type: 'POST',
          url: 'manage_users.php',
          data: $(this).serialize(),
          success: function(response) {
            location.reload();
          },
          error: function(xhr, status, error) {
            alert('Error: ' + error);
          }
        });
      });
      
      // Submit Delete Form
      $('#deleteForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
          type: 'POST',
          url: 'manage_users.php',
          data: $(this).serialize(),
          success: function(response) {
            location.reload();
          },
          error: function(xhr, status, error) {
            alert('Error: ' + error);
          }
        });
      });
    });
  </script>
</body>
</html>