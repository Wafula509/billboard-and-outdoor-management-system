<?php
session_start();
$pageTitle = "Reset Password - Billboard Solutions";
include 'db_connect.php';
include 'header.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    // Validate token using prepared statement
    $query = "SELECT * FROM users WHERE reset_token=? AND reset_expiry > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (isset($_POST['reset'])) {
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            
            // Validate password
            if (empty($password) || empty($confirm_password)) {
                $error = "Please enter both password fields";
            } elseif ($password !== $confirm_password) {
                $error = "Passwords do not match";
            } elseif (strlen($password) < 8) {
                $error = "Password must be at least 8 characters";
            } else {
                // Update password in database using prepared statement
                $new_password = password_hash($password, PASSWORD_DEFAULT);
                $update = "UPDATE users SET password=?, reset_token=NULL, reset_expiry=NULL WHERE reset_token=?";
                $stmt = $conn->prepare($update);
                $stmt->bind_param("ss", $new_password, $token);
                
                if ($stmt->execute()) {
                    $success = "Password has been reset successfully!";
                    $_SESSION['success_message'] = $success;
                    header("Location: user_login.php");
                    exit();
                } else {
                    $error = "Error updating password. Please try again.";
                }
            }
        }
    } else {
        $error = "Invalid or expired reset link.";
    }
} else {
    $error = "No token provided!";
}
?>

<style>
    .password-reset-container {
        max-width: 500px;
        margin: 40px auto;
        padding: 30px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        animation: fadeIn 0.5s ease-out;
    }

    .password-reset-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .password-reset-header h2 {
        color: var(--secondary-color);
        font-size: 2rem;
    }

    .password-reset-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        margin-bottom: 15px;
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
    }

    .form-control:focus {
        outline: none;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 3px rgba(244, 162, 97, 0.2);
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
    }

    .submit-btn:hover {
        background: var(--accent-color);
        transform: translateY(-2px);
    }

    .message {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
    }

    .message-success {
        background-color: #d4edda;
        color: #155724;
    }

    .message-error {
        background-color: #f8d7da;
        color: #721c24;
    }

    .password-strength {
        margin-top: 5px;
        font-size: 0.8rem;
        color: #aaa;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .password-reset-container {
            margin: 20px;
            padding: 20px;
        }
    }
</style>

<div class="main-content">
    <div class="password-reset-container">
        <div class="password-reset-header">
            <h2><i class="fas fa-key"></i> Reset Password</h2>
            <p>Enter your new password below</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message message-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($error) || (isset($user) && $result->num_rows == 1)): ?>
            <form method="POST" class="password-reset-form">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="8">
                    <div class="password-strength">Minimum 8 characters</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8">
                </div>
                
                <button type="submit" name="reset" class="submit-btn">
                    <i class="fas fa-save"></i> Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>