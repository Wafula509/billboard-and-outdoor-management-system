<?php
session_start();
$pageTitle = "User Dashboard - Billboard Solutions";
include 'header.php';
include 'db_connect.php';
include 'email_functions.php';

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$user_email = $_SESSION['user_email'];

// Fetch logged-in user details
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_id = $user['user_id'];

// Handle booking form submission
$bookingSuccess = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['billboard_id'], $_POST['start_date'], $_POST['end_date'])) {
        $billboard_id = $_POST['billboard_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Check if dates are valid
        if (strtotime($end_date) <= strtotime($start_date)) {
            $bookingError = "End date must be after start date";
        } else {
            // Check availability
            $checkStmt = $conn->prepare("SELECT * FROM bookings WHERE billboard_id = ? AND 
                ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?))");
            $checkStmt->bind_param("issss", $billboard_id, $end_date, $start_date, $start_date, $end_date);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows == 0) {
                // Insert booking record
                $stmt = $conn->prepare("INSERT INTO bookings (user_id, billboard_id, start_date, end_date, status) 
                    VALUES (?, ?, ?, ?, 'Pending Payment')");
                $stmt->bind_param("iiss", $user_id, $billboard_id, $start_date, $end_date);

                if ($stmt->execute()) {
                    $booking_id = $conn->insert_id;
                    
                    // Get billboard details for the email
                    $billboardStmt = $conn->prepare("SELECT location, price_per_day FROM billboards WHERE billboard_id = ?");
                    $billboardStmt->bind_param("i", $billboard_id);
                    $billboardStmt->execute();
                    $billboard = $billboardStmt->get_result()->fetch_assoc();
                    
                    // Calculate total cost
                    $total_cost = calculateTotalCost($start_date, $end_date, $billboard['price_per_day']);

                    function calculateTotalCost($start_date, $end_date, $price_per_day) {
                        $start = strtotime($start_date);
                        $end = strtotime($end_date);
                        $days = ($end - $start) / (60 * 60 * 24) + 1; // Include the start date
                        return $days * $price_per_day;
                    }
                    
                    // Create email content
                    $subject = "Your Billboard Booking Confirmation #$booking_id";
                    $body = "
                        <h2>Booking Confirmation</h2>
                        <p>Hello ".htmlspecialchars($user['full_name']).",</p>
                        <p>Your booking request has been received with the following details:</p>
                        <ul>
                            <li><strong>Booking ID:</strong> #$booking_id</li>
                            <li><strong>Billboard:</strong> ".htmlspecialchars($billboard['location'])."</li>
                            <li><strong>Start Date:</strong> ".date('M j, Y', strtotime($start_date))."</li>
                            <li><strong>End Date:</strong> ".date('M j, Y', strtotime($end_date))."</li>
                            <li><strong>Price per day:</strong> KES ".number_format($billboard['price_per_day'], 2)."</li>
                            <li><strong>Total Estimated Cost:</strong> KES ".number_format($total_cost, 2)."</li>
                        </ul>
                        <p><strong>Status:</strong> Pending Payment</p>
                        <p>Please complete your payment to secure this booking.</p>
                        <p>Thank you for choosing Billboard Solutions!</p>
                    ";
                    
                    // Send email
                    sendEmail($user_email, $subject, $body);
                    
                    // Create a notification in the database
                    $notificationMsg = "Booking #$booking_id created for ".htmlspecialchars($billboard['location'])." (Pending Payment)";
                    $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $notifyStmt->bind_param("is", $user_id, $notificationMsg);
                    $notifyStmt->execute();
                    
                    $bookingSuccess = true;
                    $_SESSION['success_message'] = "Booking successful! Please check your email for confirmation.";
                    header("Location: user_dashboard.php");
                    exit();
                }
            } else {
                $bookingError = "This billboard is already booked for the selected dates";
            }
        }
    }
}

// Fetch dashboard statistics
$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$bookingsCount = $stmt2->get_result()->fetch_assoc()['total'];

$stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND status='Unread'");
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$notificationsCount = $stmt3->get_result()->fetch_assoc()['total'];

$stmt4 = $conn->prepare("SELECT COUNT(*) as total FROM billboards WHERE availability='Available'");
$stmt4->execute();
$billboardsCount = $stmt4->get_result()->fetch_assoc()['total'];

$pendingPaymentsStmt = $conn->prepare("SELECT COUNT(*) as total FROM payments p JOIN bookings b ON p.booking_id=b.booking_id WHERE b.user_id = ? AND p.payment_status='Pending'");
$pendingPaymentsStmt->bind_param("i", $user_id);
$pendingPaymentsStmt->execute();
$pendingPayments = $pendingPaymentsStmt->get_result()->fetch_assoc()['total'];

// Fetch notifications
$notifyStmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$notifyStmt->bind_param("i", $user_id);
$notifyStmt->execute();
$notifications = $notifyStmt->get_result();

// Fetch all available billboards
$billboardsStmt = $conn->query("SELECT * FROM billboards WHERE availability='Available'");
$billboards = $billboardsStmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #f4a261;
            --accent-color: #e76f51;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --transition-speed: 0.3s;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        .sidebar {
            width: 220px;
            background: var(--primary-color);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            z-index: 1;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 1.3rem;
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: var(--light-color);
            padding: 12px 20px;
            margin: 5px 0;
            text-decoration: none;
            transition: all var(--transition-speed);
            border-left: 3px solid transparent;
        }

        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1);
            border-left: 3px solid var(--secondary-color);
        }

        .main-content {
            flex: 1;
            margin-left: 220px;
            padding: 30px;
            background-color: #f5f5f5;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform var(--transition-speed);
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-card p {
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .notifications-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .notifications-card h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .notifications-card h2 i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .notification-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-message {
            margin-bottom: 5px;
        }

        .notification-date {
            color: #7f8c8d;
            font-size: 0.8rem;
        }

        .billboards-section h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .billboards-section h2 i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .billboards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .billboard-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all var(--transition-speed);
        }

        .billboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .billboard-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .billboard-details {
            padding: 15px;
        }

        .billboard-title {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .billboard-info {
            display: flex;
            align-items: center;
            margin: 8px 0;
            font-size: 0.9rem;
            color: var(--dark-color);
        }

        .billboard-info i {
            width: 20px;
            color: var(--secondary-color);
            margin-right: 8px;
        }

        .billboard-price {
            font-weight: bold;
            color: var(--success-color);
            font-size: 1.1rem;
            margin: 15px 0;
        }

        .book-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background var(--transition-speed);
            text-align: center;
            text-decoration: none;
        }

        .book-btn:hover {
            background: var(--accent-color);
        }

        .booking-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .booking-form {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .booking-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .booking-form-header h3 {
            color: var(--primary-color);
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .submit-btn {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background var(--transition-speed);
        }

        .submit-btn:hover {
            background: #218838;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px 0;
            }

            .sidebar a {
                padding: 10px 15px;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .billboards-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .booking-form {
                padding: 20px;
                margin: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <h2>User Dashboard</h2>
            <a href="user_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
            <a href="notifications.php"><i class="fas fa-bell"></i> Notifications 
                <?php if ($notificationsCount > 0): ?>
                    <span style="margin-left: auto; background: var(--accent-color); color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                        <?php echo $notificationsCount; ?>
                    </span>
                <?php endif; ?>
            </a>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h1>
                <div>
                    <a href="profile.php" class="btn btn-outline"><i class="fas fa-user-circle"></i> My Profile</a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_SESSION['success_message']); 
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($bookingError)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($bookingError); ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3><?php echo $bookingsCount; ?></h3>
                    <p>Total Bookings</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3><?php echo $pendingPayments; ?></h3>
                    <p>Pending Payments</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-ad"></i>
                    <h3><?php echo $billboardsCount; ?></h3>
                    <p>Available Billboards</p>
                </div>
            </div>

            <!-- Notifications Section -->
            <div class="notifications-card">
                <h2><i class="fas fa-bell"></i> Recent Notifications</h2>
                <?php if ($notifications->num_rows > 0): ?>
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <div class="notification-item">
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </div>
                            <div class="notification-date">
                                <?php echo date('M j, Y g:i a', strtotime($notification['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <a href="notifications.php" style="display: inline-block; margin-top: 15px; color: var(--secondary-color);">View All Notifications</a>
                <?php else: ?>
                    <p>No new notifications</p>
                <?php endif; ?>
            </div>

            <!-- Available Billboards Section -->
            <div class="billboards-section">
                <h2><i class="fas fa-ad"></i> Available Billboards</h2>
                <?php if (count($billboards) > 0): ?>
                    <div class="billboards-grid">
                        <?php foreach ($billboards as $billboard): 
                            // Determine the image path with fallback to default
                            $imagePath = 'images/default-billboard.jpg';
                            if (!empty($billboard['image'])) {
                                $dbImage = $billboard['image'];
                                // Check if it's just a filename (no path)
                                if (strpos($dbImage, '/') === false && strpos($dbImage, '\\') === false) {
                                    $potentialPath = 'images/' . $dbImage;
                                    $imagePath = $potentialPath;
                                } else {
                                    $imagePath = $dbImage;
                                }
                            }
                        ?>
                            <div class="billboard-card">
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                     alt="Billboard at <?php echo htmlspecialchars($billboard['location']); ?>"
                                     class="billboard-image"
                                     onerror="this.src='images/default-billboard.jpg'">
                                <div class="billboard-details">
                                    <h3 class="billboard-title"><?php echo htmlspecialchars($billboard['location']); ?></h3>
                                    <div class="billboard-info">
                                        <i class="fas fa-ruler-combined"></i>
                                        <span><?php echo htmlspecialchars($billboard['size']); ?></span>
                                    </div>
                                    <div class="billboard-info">
                                        <i class="fas fa-tag"></i>
                                        <span><?php echo htmlspecialchars($billboard['type'] ?? 'Standard'); ?></span>
                                    </div>
                                    <div class="billboard-price">
                                        <i class="fas fa-money-bill-wave"></i>
                                        KES <?php echo number_format($billboard['price_per_day'], 2); ?> / day
                                    </div>
                                    <button class="book-btn" onclick="openBookingForm(<?php echo $billboard['billboard_id']; ?>)">
                                        <i class="fas fa-calendar-plus"></i> Book Now
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No available billboards at the moment. Please check back later.</p>
                <?php endif; ?>
            </div>

            <!-- Booking Modal -->
            <div class="booking-modal" id="bookingModal">
                <div class="booking-form">
                    <div class="booking-form-header">
                        <h3>Book Billboard</h3>
                        <button class="close-btn" onclick="closeBookingForm()">&times;</button>
                    </div>
                    <form method="POST" id="bookingForm">
                        <input type="hidden" name="billboard_id" id="formBillboardId">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" required>
                        </div>
                        <button type="submit" class="submit-btn">Confirm Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openBookingForm(billboardId) {
            document.getElementById('formBillboardId').value = billboardId;
            document.getElementById('bookingModal').style.display = 'flex';
            
            // Set minimum dates
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').min = today;
            document.getElementById('end_date').min = today;
        }

        function closeBookingForm() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        // Update end date min when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });

        // Close modal when clicking outside
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBookingForm();
            }
        });
    </script>
</body>
</html>