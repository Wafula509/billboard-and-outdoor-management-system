<?php
// manage_billboards.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $billboard_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM billboards WHERE billboard_id = ?");
    $stmt->bind_param("i", $billboard_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Billboard deleted successfully!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error deleting billboard: ' . $conn->error];
    }
    header("Location: manage_billboards.php");
    exit();
}

$result = $conn->query("SELECT * FROM billboards ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Billboards</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3a5a40;
      --danger: #ef233c;
      --success: #4cc9f0;
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
    
    .btn-add {
      background: var(--success);
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      margin-bottom: 20px;
      display: inline-block;
    }
    
    .btn-add:hover {
      background: #3aa8d8;
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
      width: 600px;
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
    
    .billboard-img {
      max-width: 100px;
      max-height: 60px;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
    <a href="manage_users.php"><i class="fas fa-users"></i> <span>Manage Users</span></a>
    <a href="manage_billboards.php" class="active"><i class="fas fa-bullhorn"></i> <span>Billboards</span></a>
    <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> <span>Bookings</span></a>
    <a href="manage_payments.php"><i class="fas fa-credit-card"></i> <span>Payments</span></a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
  </div>

  <div class="content">
    <div class="header">
      <h2>Manage Billboards</h2>
      <a href="add_billboard.php" class="btn btn-add"><i class="fas fa-plus"></i> Add Billboard</a>
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
            <th>ID</th>
            <th>Location</th>
            <th>Size</th>
            <th>Availability</th>
            <th>Price</th>
            <th>Image</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($billboard = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $billboard['billboard_id']; ?></td>
            <td><?php echo htmlspecialchars($billboard['location']); ?></td>
            <td><?php echo htmlspecialchars($billboard['size']); ?></td>
            <td><?php echo $billboard['availability'] ? 'Available' : 'Booked'; ?></td>
            <td>$<?php echo number_format($billboard['price'], 2); ?></td>
            <td>
              <?php if($billboard['image']): ?>
                <img src="<?php echo htmlspecialchars($billboard['image']); ?>" class="billboard-img" alt="Billboard">
              <?php else: ?>
                No Image
              <?php endif; ?>
            </td>
            <td><?php echo date('M d, Y h:i A', strtotime($billboard['created_at'])); ?></td>
            <td>
              <div class="action-buttons">
                <button class="btn btn-edit edit-btn" 
                  data-id="<?php echo $billboard['billboard_id']; ?>"
                  data-location="<?php echo htmlspecialchars($billboard['location']); ?>"
                  data-size="<?php echo htmlspecialchars($billboard['size']); ?>"
                  data-availability="<?php echo $billboard['availability']; ?>"
                  data-price="<?php echo $billboard['price']; ?>"
                  data-image="<?php echo htmlspecialchars($billboard['image']); ?>">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-delete delete-btn" 
                  data-id="<?php echo $billboard['billboard_id']; ?>"
                  data-location="<?php echo htmlspecialchars($billboard['location']); ?>">
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

  <!-- Edit Billboard Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Edit Billboard</h3>
        <button class="close-btn">&times;</button>
      </div>
      <form id="editForm" method="post" enctype="multipart/form-data" action="update_billboard.php">
        <div class="modal-body">
          <input type="hidden" name="billboard_id" id="edit_billboard_id">
          <div class="form-group">
            <label for="edit_location">Location</label>
            <input type="text" class="form-control" id="edit_location" name="location" required>
          </div>
          <div class="form-group">
            <label for="edit_size">Size</label>
            <input type="text" class="form-control" id="edit_size" name="size" required>
          </div>
          <div class="form-group">
            <label for="edit_availability">Availability</label>
            <select class="form-control" id="edit_availability" name="availability" required>
              <option value="1">Available</option>
              <option value="0">Booked</option>
            </select>
          </div>
          <div class="form-group">
            <label for="edit_price">Price</label>
            <input type="number" step="0.01" class="form-control" id="edit_price" name="price" required>
          </div>
          <div class="form-group">
            <label for="edit_image">Image</label>
            <input type="file" class="form-control" id="edit_image" name="image">
            <small>Leave blank to keep current image</small>
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
      <form id="deleteForm" method="post" action="manage_billboards.php">
        <div class="modal-body">
          <input type="hidden" name="delete_id" id="delete_billboard_id">
          <p>Are you sure you want to delete billboard at: <strong id="delete_billboard_location"></strong>?</p>
          <p>This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-cancel">Cancel</button>
          <button type="submit" class="btn btn-delete">Delete Billboard</button>
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
        const billboardId = $(this).data('id');
        const location = $(this).data('location');
        const size = $(this).data('size');
        const availability = $(this).data('availability');
        const price = $(this).data('price');
        
        $('#edit_billboard_id').val(billboardId);
        $('#edit_location').val(location);
        $('#edit_size').val(size);
        $('#edit_availability').val(availability ? '1' : '0');
        $('#edit_price').val(price);
        
        $('#editModal').show();
      });
      
      // Open Delete Modal
      $('.delete-btn').click(function() {
        const billboardId = $(this).data('id');
        const location = $(this).data('location');
        
        $('#delete_billboard_id').val(billboardId);
        $('#delete_billboard_location').text(location);
        
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
</html>
