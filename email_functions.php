<?php
// email_functions.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

class EmailService {
    private $mailer;
    private $config;
    private $debugMode = false;

    public function __construct() {
        $this->loadConfig();
        $this->initializeMailer();
    }

    private function loadConfig() {
        // Load configuration from a separate file or environment variables
        $this->config = [
            'host' => 'smtp.gmail.com',
            'username' => 'walukhuwafula@gmail.com',
            'password' => 'xodk pauh aysx agne',
            'port' => 587,
            'encryption' => 'tls',
            'from_email' => 'no-reply@billboardsolutions.com',
            'from_name' => 'Billboard Solutions',
            'reply_to' => 'support@billboardsolutions.com',
            'reply_name' => 'Support Team'
        ];
    }

    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = $this->config['encryption'];
        $this->mailer->Port = $this->config['port'];
        
        // Debug output (only enable in development)
        if ($this->debugMode) {
            $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
        }

        // Default from address
        $this->mailer->setFrom(
            $this->config['from_email'], 
            $this->config['from_name']
        );
        $this->mailer->addReplyTo(
            $this->config['reply_to'], 
            $this->config['reply_name']
        );
    }

    public function sendEmail($to, $subject, $body, $attachments = []) {
        try {
            // Clear all addresses and attachments for a new email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->clearReplyTos();
            
            // Add recipient
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    $this->mailer->addAddress($email, $name);
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Add attachments if any
            foreach ($attachments as $attachment) {
                $this->mailer->addAttachment(
                    $attachment['path'], 
                    $attachment['name'] ?? ''
                );
            }

            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->wrapEmailTemplate($body);
            $this->mailer->AltBody = strip_tags($body);

            // Send the email
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function wrapEmailTemplate($content) {
        // Create a consistent email template with header and footer
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Billboard Solutions</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
                .button { 
                    display: inline-block; padding: 10px 20px; 
                    background-color: #4361ee; color: white; 
                    text-decoration: none; border-radius: 5px; 
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Billboard Solutions</h1>
                </div>
                <div class="content">
                    ' . $content . '
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' Billboard Solutions. All rights reserved.</p>
                    <p>If you didn\'t request this email, please ignore it.</p>
                </div>
            </div>
        </body>
        </html>
        ';
    }

    public function sendBookingConfirmation($userEmail, $userName, $bookingDetails) {
        $subject = "Booking Confirmation #" . $bookingDetails['id'];
        $body = "
            <h2>Hello $userName,</h2>
            <p>Thank you for your booking with Billboard Solutions!</p>
            
            <h3>Booking Details:</h3>
            <p><strong>Booking ID:</strong> #" . $bookingDetails['id'] . "</p>
            <p><strong>Billboard Location:</strong> " . $bookingDetails['location'] . "</p>
            <p><strong>Dates:</strong> " . $bookingDetails['start_date'] . " to " . $bookingDetails['end_date'] . "</p>
            <p><strong>Total Cost:</strong> KES " . number_format($bookingDetails['total_cost'], 2) . "</p>
            <p><strong>Status:</strong> " . $bookingDetails['status'] . "</p>
            
            <p>If you have any questions about your booking, please reply to this email.</p>
        ";

        return $this->sendEmail($userEmail, $subject, $body);
    }

    public function sendStatusUpdate($userEmail, $userName, $bookingDetails) {
        $subject = "Booking #" . $bookingDetails['id'] . " Status Update";
        
        if ($bookingDetails['status'] == 'Approved') {
            $statusMessage = "<p>We're pleased to inform you that your booking has been <strong>approved</strong>!</p>";
        } else {
            $statusMessage = "<p>Your booking status has been updated to <strong>" . $bookingDetails['status'] . "</strong>.</p>";
        }

        $body = "
            <h2>Hello $userName,</h2>
            $statusMessage
            
            <h3>Booking Details:</h3>
            <p><strong>Booking ID:</strong> #" . $bookingDetails['id'] . "</p>
            <p><strong>Billboard Location:</strong> " . $bookingDetails['location'] . "</p>
            <p><strong>Dates:</strong> " . $bookingDetails['start_date'] . " to " . $bookingDetails['end_date'] . "</p>
            
            <p>Thank you for choosing Billboard Solutions.</p>
        ";

        return $this->sendEmail($userEmail, $subject, $body);
    }
}

// Helper function for backward compatibility
function sendEmail($to, $subject, $body) {
    $emailService = new EmailService();
    return $emailService->sendEmail($to, $subject, $body);
}

// Helper function to calculate total cost
function calculateTotalCost($start_date, $end_date, $price_per_day) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $days = $interval->days + 1; // +1 to include both start and end dates
    return $days * $price_per_day;
}