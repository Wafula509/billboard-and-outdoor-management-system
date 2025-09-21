<?php
// user_dashboard.php - Where bookings shine and dreams take flight
session_start();
include 'db_connect.php';
require 'vendor/autoload.php'; // PHPMailer makes emails right

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check authentication first, before we proceed
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT b.*, bi.image, bi.location, bi.price 
                       FROM bookings b
                       JOIN billboards bi ON b.billboard_id = bi.billboard_id
                       WHERE b.user_id = $user_id AND b.status = 'Pending'
                       ORDER BY b.booking_date DESC");

// When payment comes in, we'll make it right
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    $billboard_id = $_POST['billboard_id'];
    $transaction_id = $_POST['transaction_id'];
    
    // Update the status, make it confirmed
    $stmt = $conn->prepare("UPDATE bookings SET status = 'Confirmed', transaction_id = ? 
                           WHERE billboard_id = ? AND user_id = ? AND status = 'Pending'");
    $stmt->bind_param("sii", $transaction_id, $billboard_id, $user_id);
    
    if ($stmt->execute()) {
        // Fetch booking details, prepare to share
        $stmt = $conn->prepare("SELECT b.*, u.email, u.full_name, bi.location, bi.price 
                              FROM bookings b
                              JOIN users u ON b.user_id = u.user_id
                              JOIN billboards bi ON b.billboard_id = bi.billboard_id
                              WHERE b.billboard_id = ? AND b.user_id = ?");
        $stmt->bind_param("ii", $billboard_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $booking = $result->fetch_assoc();
        
        // Send the email, make it sweet
        $mail = new PHPMailer(true);
        try {
            // Server settings we must configure
            $mail->isSMTP();
            $mail->Host       = 'smtp.yourdomain.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your@email.com';
            $mail->Password   = 'yourpassword';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Who to send to, who will read
            $mail->setFrom('noreply@billboard.com', 'Billboard Booking System');
            $mail->addAddress($booking['email'], $booking['full_name']);
            
            // Craft the message, make it neat
            $mail->isHTML(true);
            $mail->Subject = 'Your Billboard Booking Complete';
            $mail->Body    = "
                <h2>Booking Confirmation</h2>
                <p>Dear {$booking['full_name']},</p>
                <p>Your payment's in and your spot's secure,</p>
                <p>Your ad will shine, of that we're sure!</p>
                
                <h3>Booking Details:</h3>
                <p><strong>Billboard Location:</strong> {$booking['location']}</p>
                <p><strong>Booking Period:</strong> {$booking['start_date']} to {$booking['end_date']}</p>
                <p><strong>Total Amount:</strong> KES {$booking['price']}</p>
                <p><strong>Transaction ID:</strong> $transaction_id</p>
                <p><strong>Payment Method:</strong> M-Pesa (0791844103)</p>
                
                <p>Thank you for choosing us today,</p>
                <p>Your message will soon be on display!</p>
            ";
            
            $mail->send();
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Booking confirmed, all is well! Check your email for details to tell!'];
        } catch (Exception $e) {
            $_SESSION['message'] = ['type' => 'error', 'text' => "Booking went through, this much is true, but the email failed to reach you!"];
        }
        header("Location: my_bookings.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<!-- Rest of your HTML remains unchanged -->