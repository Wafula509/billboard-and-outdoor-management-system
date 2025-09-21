<?php
// delete_booking.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}

if (!isset($_GET['id'])) {
    die("Error: Booking ID is missing.");
}

$booking_id = $_GET['id'];

// Get user details for notification
$userStmt = $conn->prepare("SELECT u.full_name, bi.location, p.amount, b.user_id 
                            FROM bookings b 
                            JOIN users u ON b.user_id = u.user_id 
                            JOIN billboards bi ON b.billboard_id = bi.billboard_id 
                            JOIN payments p ON b.booking_id = p.booking_id 
                            WHERE b.booking_id = ?");
$userStmt->bind_param("i", $booking_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

if ($user) {
    $username = $user['full_name'];
    $billboard = $user['location'];
    $amount = $user['amount'];
    $message = "Dear $username, your booking for billboard at $billboard has been **deleted**. Amount Paid: KES $amount.";

    // Insert notification
    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
    $notifStmt->bind_param("is", $user['user_id'], $message);
    $notifStmt->execute();
}

// Delete booking
$deleteStmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
$deleteStmt->bind_param("i", $booking_id);
$deleteStmt->execute();

header("Location: manage_bookings.php?success=Booking Deleted Successfully!");
exit();
?>
