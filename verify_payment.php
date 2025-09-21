<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_id = $_POST['payment_id'];
    $mpesa_code = $_POST['mpesa_code'];

    // In a real implementation, you would verify with M-Pesa API here
    // For now, we'll just simulate successful verification
    
    // Update payment record
    $stmt = $conn->prepare("UPDATE payments 
                          SET payment_status = 'Completed', mpesa_code = ? 
                          WHERE payment_id = ?");
    $stmt->bind_param("si", $mpesa_code, $payment_id);
    
    if ($stmt->execute()) {
        // Get booking ID to update status
        $stmt = $conn->prepare("SELECT booking_id FROM payments WHERE payment_id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $booking_id = $stmt->get_result()->fetch_assoc()['booking_id'];
        
        // Update booking status
        $stmt = $conn->prepare("UPDATE bookings SET status = 'Confirmed' WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Create notification
        $message = "Your payment has been confirmed. Booking is now active.";
        $stmt = $conn->prepare("INSERT INTO notifications 
                              (user_id, message, status) 
                              SELECT user_id, ?, 'Unread' 
                              FROM bookings WHERE booking_id = ?");
        $stmt->bind_param("si", $message, $booking_id);
        $stmt->execute();
        
        echo json_encode(["success" => true, "message" => "Payment verified"]);
    } else {
        echo json_encode(["success" => false, "message" => "Verification failed"]);
    }
}
?>