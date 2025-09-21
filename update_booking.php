<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    $admin_id = $_SESSION['admin_id'];

    // Fetch booking details with user and billboard info
    $stmt = $conn->prepare("SELECT b.*, u.phone_number, u.full_name, bi.price 
                           FROM bookings b
                           JOIN users u ON b.user_id = u.user_id
                           JOIN billboards bi ON b.billboard_id = bi.billboard_id
                           WHERE b.booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        $user_id = $booking['user_id'];
        $phone_number = $booking['phone_number'];
        $user_name = $booking['full_name'];
        $amount = $booking['price'];

        if ($new_status == 'Confirmed') {
            // Create payment record
            $payment_number = "0791844103"; // Your payment number
            $transaction_id = "MPESA" . time(); // Generate temporary transaction ID
            
            $stmt = $conn->prepare("INSERT INTO payments 
                                  (booking_id, amount, payment_method, transaction_id, payment_status) 
                                  VALUES (?, ?, 'M-Pesa', ?, 'Pending')");
            $stmt->bind_param("ids", $booking_id, $amount, $transaction_id);
            $stmt->execute();
            $payment_id = $conn->insert_id;

            // Return payment instructions
            echo json_encode([
                "success" => true, 
                "payment_required" => true,
                "message" => "Please complete payment to confirm booking",
                "payment_details" => [
                    "payment_id" => $payment_id,
                    "amount" => $amount,
                    "payment_number" => $payment_number,
                    "user_phone" => $phone_number
                ]
            ]);
            exit();
        }

        // For non-confirmation status updates
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        
        if ($stmt->execute()) {
            $message = "Your booking (ID: $booking_id) has been $new_status.";
            $stmt = $conn->prepare("INSERT INTO notifications 
                                   (user_id, admin_id, message, status) 
                                   VALUES (?, ?, ?, 'Unread')");
            $stmt->bind_param("iis", $user_id, $admin_id, $message);
            $stmt->execute();

            echo json_encode(["success" => true, "message" => "Booking updated"]);
        } else {
            echo json_encode(["success" => false, "message" => "Update failed"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Booking not found"]);
    }
}
?>