<?php
// user_dashboard.php - Billboard Booking Dashboard
date_default_timezone_set('Africa/Nairobi'); // Set Nairobi timezone
session_start();
require 'db_connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d H:i:s'); // Current Nairobi time

// Secure database query with prepared statement
$stmt = $conn->prepare("SELECT b.*, bi.image, bi.location, bi.price 
                       FROM bookings b
                       JOIN billboards bi ON b.billboard_id = bi.billboard_id
                       WHERE b.user_id = ? AND b.status = 'Pending'
                       ORDER BY b.booking_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Payment processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $billboard_id = filter_input(INPUT_POST, 'billboard_id', FILTER_VALIDATE_INT);
    $transaction_id = htmlspecialchars(trim($_POST['transaction_id']));
    
    if (!$billboard_id || empty($transaction_id)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid input provided'];
        header("Location: user_dashboard.php");
        exit();
    }

    // Update booking status
    $update_stmt = $conn->prepare("UPDATE bookings 
                                  SET status = 'Confirmed', 
                                      transaction_id = ?,
                                      confirmation_date = ?
                                  WHERE billboard_id = ? 
                                  AND user_id = ? 
                                  AND status = 'Pending'");
    $update_stmt->bind_param("ssii", $transaction_id, $current_date, $billboard_id, $user_id);
    
    if ($update_stmt->execute()) {
        // Get booking details with proper error handling
        $detail_stmt = $conn->prepare("SELECT b.*, u.email, u.full_name, bi.location, bi.price 
                                     FROM bookings b
                                     JOIN users u ON b.user_id = u.user_id
                                     JOIN billboards bi ON b.billboard_id = bi.billboard_id
                                     WHERE b.billboard_id = ? AND b.user_id = ?");
        $detail_stmt->bind_param("ii", $billboard_id, $user_id);
        
        if ($detail_stmt->execute()) {
            $booking = $detail_stmt->get_result()->fetch_assoc();
            
            // Send confirmation email
            $mail = new PHPMailer(true);
            try {
                // SMTP Configuration (should be in config file)
                $mail->isSMTP();
                $mail->Host       = 'smtp.yourdomain.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'your@email.com';
                $mail->Password   = 'yourpassword';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';
                
                // Recipients
                $mail->setFrom('noreply@billboard.com', 'Billboard Booking System');
                $mail->addAddress($booking['email'], $booking['full_name']);
                
                // Email content with improved formatting
                $formatted_price = number_format($booking['price'], 2);
                $formatted_date = date('jS F Y', strtotime($booking['start_date'])) . ' to ' . 
                                  date('jS F Y', strtotime($booking['end_date']));
                
                $mail->isHTML(true);
                $mail->Subject = 'Billboard Booking Confirmation #' . $transaction_id;
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #0066cc;'>Booking Confirmed</h2>
                        <p>Dear {$booking['full_name']},</p>
                        <p>Your payment has been processed successfully and your booking is now confirmed.</p>
                        
                        <h3 style='color: #0066cc;'>Booking Summary</h3>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px; border: 1px solid #ddd;'><strong>Location:</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>{$booking['location']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px; border: 1px solid #ddd;'><strong>Period:</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>{$formatted_date}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px; border: 1px solid #ddd;'><strong>Amount Paid:</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>KES {$formatted_price}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px; border: 1px solid #ddd;'><strong>Transaction ID:</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>{$transaction_id}</td>
                            </tr>
                        </table>
                        
                        <p style='margin-top: 20px;'>For any inquiries, contact us at support@billboard.com or call +254 791 844 103</p>
                        <p>Thank you for your business!</p>
                    </div>
                ";
                
                $mail->send();
                $_SESSION['message'] = [
                    'type' => 'success', 
                    'text' => 'Booking confirmed! A confirmation has been sent to your email.'
                ];
            } catch (Exception $e) {
                error_log("Email sending failed: " . $e->getMessage());
                $_SESSION['message'] = [
                    'type' => 'warning', 
                    'text' => 'Booking confirmed, but the confirmation email failed to send. Please contact support.'
                ];
            }
        }
        header("Location: my_bookings.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | Billboard Booking System</title>
    <style>
        /* Add your CSS styles here */
        .booking-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .success-message {
            color: green;
            padding: 10px;
            background-color: #e6ffe6;
        }
        .error-message {
            color: red;
            padding: 10px;
            background-color: #ffebeb;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['message'])) : ?>
        <div class="<?= $_SESSION['message']['type'] ?>-message">
            <?= $_SESSION['message']['text'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <h1>My Pending Bookings</h1>
    
    <?php if ($result->num_rows > 0) : ?>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <div class="booking-card">
                <h3><?= htmlspecialchars($row['location']) ?></h3>
                <img src="<?= htmlspecialchars($row['image']) ?>" alt="Billboard Image" width="200">
                <p>Price: KES <?= number_format($row['price'], 2) ?></p>
                <p>Dates: <?= date('jS M Y', strtotime($row['start_date'])) ?> - <?= date('jS M Y', strtotime($row['end_date'])) ?></p>
                
                <form method="POST">
                    <input type="hidden" name="billboard_id" value="<?= $row['billboard_id'] ?>">
                    <label for="transaction_id">M-Pesa Transaction Code:</label>
                    <input type="text" name="transaction_id" required pattern="[A-Z0-9]{10}" 
                           title="10 character transaction code" placeholder="e.g. OA2345X678">
                    <button type="submit" name="confirm_booking">Confirm Payment</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else : ?>
        <p>You have no pending bookings.</p>
    <?php endif; ?>
</body>
</html>