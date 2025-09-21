<?php
// my_bookings.php
session_start();
$pageTitle = "My Bookings - Billboard Solutions";
include 'db_connect.php';
include 'header.php';

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$user_email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_id = $user['user_id'];

$result = $conn->query("SELECT b.booking_id, bi.location, b.status, b.start_date, b.end_date, b.created_at 
                       FROM bookings b 
                       JOIN billboards bi ON b.billboard_id = bi.billboard_id 
                       WHERE b.user_id = $user_id 
                       ORDER BY b.created_at DESC");
?>

<style>
    .bookings-container {
        margin: 20px auto;
        max-width: 1200px;
        padding: 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .bookings-container h2 {
        color: var(--primary-color);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--secondary-color);
    }

    .bookings-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .bookings-table th, 
    .bookings-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .bookings-table th {
        background-color: var(--primary-color);
        color: white;
        font-weight: 600;
    }

    .bookings-table tr:hover {
        background-color: #f5f5f5;
    }

    .status-pending {
        color: #e67e22;
        font-weight: 600;
    }

    .status-confirmed {
        color: #27ae60;
        font-weight: 600;
    }

    .status-cancelled {
        color: #e74c3c;
        font-weight: 600;
    }

    .no-bookings {
        text-align: center;
        padding: 40px;
        color: #7f8c8d;
        font-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .bookings-table {
            display: block;
            overflow-x: auto;
        }
        
        .bookings-container {
            padding: 15px;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h2>User Dashboard</h2>
        <a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="my_bookings.php" class="active"><i class="fas fa-calendar-check"></i> My Bookings</a>
        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="bookings-container">
            <h2><i class="fas fa-calendar-check"></i> My Bookings</h2>
            
            <?php if ($result->num_rows > 0): ?>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Billboard Location</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Booking Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $booking['booking_id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['location']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($booking['start_date'])); ?></td>
                            <td><?php echo date('M j, Y', strtotime($booking['end_date'])); ?></td>
                            <td class="status-<?php echo strtolower($booking['status']); ?>">
                                <?php echo $booking['status']; ?>
                            </td>
                            <td><?php echo date('M j, Y g:i a', strtotime($booking['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <p>You don't have any bookings yet.</p>
                    <a href="view_billboards.php" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-ad"></i> View Available Billboards
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>