<?php
session_start();
$pageTitle = "My Profile - Billboard Solutions";
include 'header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$user_email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #f4a261;
        --accent-color: #e76f51;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --success-color: #28a745;
        --transition-speed: 0.3s;
    }

    .profile-container {
        max-width: 800px;
        margin: 30px auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .profile-header {
        background: var(--primary-color);
        color: white;
        padding: 25px;
        text-align: center;
        position: relative;
    }

    .profile-header h1 {
        margin: 0;
        font-size: 1.8rem;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        margin: 0 auto 15px;
        display: block;
        background: var(--light-color);
    }

    .profile-body {
        padding: 30px;
    }

    .profile-section {
        margin-bottom: 30px;
    }

    .profile-section h2 {
        color: var(--primary-color);
        border-bottom: 2px solid var(--secondary-color);
        padding-bottom: 8px;
        margin-bottom: 20px;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
    }

    .profile-section h2 i {
        margin-right: 10px;
        color: var(--secondary-color);
    }

    .profile-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .info-item {
        margin-bottom: 15px;
    }

    .info-item strong {
        display: block;
        color: var(--primary-color);
        margin-bottom: 5px;
        font-size: 0.9rem;
    }

    .info-item p {
        font-size: 1.1rem;
        color: var(--dark-color);
        margin: 0;
        padding: 10px;
        background: var(--light-color);
        border-radius: 5px;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .profile-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        transition: all var(--transition-speed);
    }

    .btn-edit {
        background: var(--secondary-color);
        color: white;
    }

    .btn-edit:hover {
        background: var(--accent-color);
        transform: translateY(-2px);
    }

    .btn-change-password {
        background: var(--primary-color);
        color: white;
    }

    .btn-change-password:hover {
        background: #1a252f;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: var(--danger-color);
        color: white;
    }

    .btn-delete:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .profile-container {
            margin: 15px;
        }
        
        .profile-info {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .profile-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-header">
        <?php if (!empty($user['profile_pic'])): ?>
            <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="profile-avatar">
        <?php else: ?>
            <div class="profile-avatar">
                <i class="fas fa-user" style="font-size: 50px; line-height: 92px; color: var(--primary-color);"></i>
            </div>
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
    </div>
    
    <div class="profile-body">
        <div class="profile-section">
            <h2><i class="fas fa-info-circle"></i> Personal Information</h2>
            <div class="profile-info">
                <div class="info-item">
                    <strong>Full Name</strong>
                    <p><?php echo htmlspecialchars($user['full_name']); ?></p>
                </div>
                <div class="info-item">
                    <strong>Email Address</strong>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="info-item">
                    <strong>Phone Number</strong>
                    <p><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?></p>
                </div>
                <div class="info-item">
                    <strong>Member Since</strong>
                    <p><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>
        </div>
        
        <div class="profile-section">
            <h2><i class="fas fa-ad"></i> Account Statistics</h2>
            <div class="profile-info">
                <div class="info-item">
                    <strong>Total Bookings</strong>
                    <p>
                        <?php 
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
                        $stmt->bind_param("i", $user['user_id']);
                        $stmt->execute();
                        echo $stmt->get_result()->fetch_row()[0];
                        ?>
                    </p>
                </div>
                <div class="info-item">
                    <strong>Active Bookings</strong>
                    <p>
                        <?php 
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND end_date >= CURDATE()");
                        $stmt->bind_param("i", $user['user_id']);
                        $stmt->execute();
                        echo $stmt->get_result()->fetch_row()[0];
                        ?>
                    </p>
                </div>
                <div class="info-item">
                    <strong>Pending Payments</strong>
                    <p>
                        <?php 
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM payments p JOIN bookings b ON p.booking_id=b.booking_id WHERE b.user_id = ? AND p.payment_status='Pending'");
                        $stmt->bind_param("i", $user['user_id']);
                        $stmt->execute();
                        echo $stmt->get_result()->fetch_row()[0];
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="edit_profile.php" class="profile-btn btn-edit">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="change_password.php" class="profile-btn btn-change-password">
                <i class="fas fa-lock"></i> Change Password
            </a>
            <a href="delete_account.php" class="profile-btn btn-delete" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
                <i class="fas fa-trash-alt"></i> Delete Account
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>