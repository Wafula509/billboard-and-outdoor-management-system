<?php
session_start();
$pageTitle = "Contact Us - Billboard Solutions";
include 'db_connect.php';
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);

    $query = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $name, $email, $message);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Your message has been sent successfully!";
    } else {
        $_SESSION['error_message'] = "Error sending message. Please try again!";
    }
    header("Location: contact.php");
    exit();
}
?>

<style>
    .contact-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
        animation: fadeIn 0.5s ease-out;
    }

    .contact-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .contact-header h2 {
        color: var(--secondary-color);
        font-size: 2.2rem;
        margin-bottom: 10px;
    }

    .contact-header p {
        color: #ddd;
        font-size: 1.1rem;
    }

    .contact-form {
        display: grid;
        grid-template-columns: 1fr;
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
        transition: all var(--transition-speed);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 3px rgba(244, 162, 97, 0.2);
        background: rgba(255,255,255,0.1);
    }

    textarea.form-control {
        min-height: 150px;
        resize: vertical;
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

    .back-link {
        display: inline-block;
        margin-top: 20px;
        color: var(--light-color);
        text-decoration: none;
        transition: color var(--transition-speed);
    }

    .back-link:hover {
        color: var(--secondary-color);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .contact-container {
            margin: 20px;
            padding: 20px;
        }
        
        .contact-header h2 {
            font-size: 1.8rem;
        }
    }
</style>

<div class="main-content">
    <div class="contact-container">
        <div class="contact-header">
            <h2><i class="fas fa-envelope"></i> Contact Us</h2>
            <p>Have questions? Send us a message and we'll get back to you soon.</p>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success_message']); 
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error_message']); 
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="contact-form">
            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="message">Your Message</label>
                <textarea id="message" name="message" class="form-control" required></textarea>
            </div>
            
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </form>

        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>