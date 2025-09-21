<?php
session_start();
$pageTitle = "Forgot Password - Billboard Solutions";
include 'db_connect.php';
include 'header.php';

// Import PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer using absolute paths
require 'vendor/autoload.php';

$message = '';
$message_type = ''; // 'success' or 'error'

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address";
        $message_type = 'error';
    } else {
        // Check if email exists in the database using prepared statement
        $query = "SELECT * FROM users WHERE email=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $token = bin2hex(random_bytes(50)); // Generate a secure token
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

            // Store token in the database using prepared statement
            $update = "UPDATE users SET reset_token=?, reset_expiry=? WHERE email=?";
            $stmt = $conn->prepare($update);
            $stmt->bind_param("sss", $token, $expiry, $email);
            
            if ($stmt->execute()) {
                // Send the reset link via email
                $mail = new PHPMailer(true);

                try {
                    // SMTP settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'walukhuwafula@gmail.com'; // Your email
                    $mail->Password   = 'xodk pauh aysx agne'; // Your generated App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    // Email content
                    $mail->setFrom('walukhuwafula@gmail.com', 'Billboard Solutions');
                    $mail->addAddress($email);
                    $mail->Subject = 'Password Reset Request';
                    $mail->isHTML(true);
                    
                    $reset_link = "http://localhost/BILLBOARDS SYSTEM/reset_password.php?token=$token";


                    $mail->Body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .button { 
                                display: inline-block; 
                                padding: 10px 20px; 
                                background-color: #4CAF50; 
                                color: white; 
                                text-decoration: none; 
                                border-radius: 5px; 
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h2>Password Reset Request</h2>
                            <p>We received a request to reset your password. Click the button below to reset it:</p>
                            <p><a href='$reset_link' class='button'>Reset Password</a></p>
                            <p>If you didn't request this, you can safely ignore this email.</p>
                            <p><small>This link will expire in 1 hour.</small></p>
                        </div>
                    </body>
                    </html>";

                    if ($mail->send()) {
                        $message = "Password reset link has been sent to your email address.";
                        $message_type = 'success';
                        $_SESSION['reset_email'] = $email; // Store email in session for verification
                    } else {
                        throw new Exception('Mail send failed');
                    }
                } catch (Exception $e) {
                    $message = "Error sending email. Please try again later.";
                    $message_type = 'error';
                    error_log("Mailer Error: " . $e->getMessage());
                }
            } else {
                $message = "Error generating reset token. Please try again.";
                $message_type = 'error';
                error_log("Database Error: " . $conn->error);
            }
        } else {
            $message = "No account found with that email address.";
            $message_type = 'error';
        }
    }
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

    .login-link {
        text-align: center;
        margin-top: 20px;
    }

    .login-link a {
        color: var(--secondary-color);
        text-decoration: none;
    }

    .login-link a:hover {
        text-decoration: underline;
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
            <h2><i class="fas fa-key"></i> Forgot Password</h2>
            <p>Enter your email to receive a password reset link</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="password-reset-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <button type="submit" name="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <div class="login-link">
            <a href="user_login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>