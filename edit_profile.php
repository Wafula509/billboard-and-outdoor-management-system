<?php
// edit_profile.php - Where users shine and profiles glow
session_start();
$pageTitle = "Edit Profile - Billboard Solutions";
include 'db_connect.php';
include 'header.php';

// Check if user's logged in, or redirect away
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: user_login.php");
    exit();
}

$user_email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    
    // Validate inputs with care
    if (empty($full_name) || empty($phone)) {
        $error = "Fields can't be empty, fill them right";
    } elseif (!preg_match('/^[0-9\+\-\s]{10,15}$/', $phone)) {
        $error = "Phone number format isn't right";
    } else {
        $stmtUpdate = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?");
        $stmtUpdate->bind_param("ssi", $full_name, $phone, $user['user_id']);
        
        if($stmtUpdate->execute()) {
            $_SESSION['success_message'] = "Profile updated bright and clear!";
            header("Location: profile.php");
            exit();
        } else {
            $error = "Update failed, please try again";
        }
    }
}
?>

<style>
    .profile-container {
        max-width: 600px;
        margin: 30px auto;
        padding: 30px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        animation: fadeIn 0.5s ease-out;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .profile-header h2 {
        color: var(--secondary-color);
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .edit-form {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .form-group {
        margin-bottom: 15px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--light-color);
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        background: rgba(255,255,255,0.05);
        color: white;
        font-size: 1rem;
        transition: all var(--transition-speed);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 3px rgba(244, 162, 97, 0.2);
        background: rgba(255,255,255,0.1);
    }

    .submit-btn {
        background: var(--secondary-color);
        color: white;
        border: none;
        padding: 14px;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-speed);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .submit-btn:hover {
        background: var(--accent-color);
        transform: translateY(-2px);
    }

    .error-message {
        color: var(--danger-color);
        text-align: center;
        margin-bottom: 15px;
        font-weight: 500;
        animation: shake 0.5s;
    }

    .success-message {
        color: var(--success-color);
        text-align: center;
        margin-bottom: 15px;
        font-weight: 500;
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }

    .input-hint {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.6);
        margin-top: 5px;
    }

    @media (max-width: 768px) {
        .profile-container {
            margin: 20px;
            padding: 20px;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <h2>User Dashboard</h2>
        <a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
        <a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-container">
            <div class="profile-header">
                <h2><i class="fas fa-user-edit"></i> Edit Your Profile</h2>
                <p>Make your changes, take your time<br>Update your details, make them shine</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="edit-form">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>" 
                           pattern="[0-9\+\-\s]{10,15}" required>
                    <span class="input-hint">Format: 0712345678 or +254712345678</span>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>