<?php
session_start();
$pageTitle = "Our Services - Billboard Solutions";
include 'header.php';
include 'db_connect.php';

// Fetch latest customer feedback (limit to 5)
$feedbacks = $conn->query("SELECT u.full_name, f.message, f.rating, f.created_at 
                           FROM feedback f 
                           JOIN users u ON f.user_id = u.user_id 
                           ORDER BY f.created_at DESC LIMIT 5");

// Handle feedback form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $message = $conn->real_escape_string($_POST['message']);
        $rating = (int)$_POST['rating'];

        $conn->query("INSERT INTO feedback (user_id, message, rating) VALUES ('$user_id', '$message', '$rating')");
        $_SESSION['feedback_success'] = "Feedback submitted successfully";
        header("Location: services.php");
        exit();
    } else {
        $_SESSION['feedback_error'] = "Please login to submit feedback";
        header("Location: user_login.php");
        exit();
    }
}

// Services data
$services = [
    [
        "icon" => "fas fa-tv",
        "title" => "Digital Billboard Advertising",
        "description" => "High-resolution LED billboards that display dynamic and engaging advertisements in real-time, ideal for brands looking for maximum exposure.",
        "image" => "images/digital_billboard.jpg"
    ],
    [
        "icon" => "fas fa-bullhorn",
        "title" => "Traditional Billboard Advertising",
        "description" => "Classic printed billboards in high-traffic areas, perfect for long-term brand visibility and awareness campaigns.",
        "image" => "images/traditional_billboard.jpg"
    ],
    [
        "icon" => "fas fa-truck-moving",
        "title" => "Mobile Billboard Advertising",
        "description" => "Moving billboard trucks that take your advertisement directly to your target audience in different locations.",
        "image" => "images/mobile_billboard.jpg"
    ],
    [
        "icon" => "fas fa-cube",
        "title" => "3D Billboard Advertising",
        "description" => "Innovative 3D billboards that create an eye-catching experience, making your brand stand out in a unique way.",
        "image" => "images/3d_billboard.jpg"
    ],
    [
        "icon" => "fas fa-chart-line",
        "title" => "AI-Powered Billboard Analytics",
        "description" => "Smart billboards with AI-driven analytics that provide insights into audience engagement and effectiveness of your ads.",
        "image" => "images/ai_billboard.jpg"
    ],
    [
        "icon" => "fas fa-lightbulb",
        "title" => "Creative Design Services",
        "description" => "Our expert design team creates compelling visuals that maximize the impact of your billboard advertising.",
        "image" => "images/design_service.jpg"
    ]
];
?>

<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #f4a261;
        --accent-color: #e76f51;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --text-color: #333;
        --transition-speed: 0.3s;
    }

    .services-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/services-bg.jpg') no-repeat center center/cover;
        color: white;
        text-align: center;
        padding: 100px 20px;
        margin-bottom: 50px;
    }

    .services-hero h1 {
        font-size: 3rem;
        margin-bottom: 20px;
        color: var(--secondary-color);
    }

    .services-hero p {
        font-size: 1.2rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .services-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px 50px;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }

    .service-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all var(--transition-speed);
    }

    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .service-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .service-content {
        padding: 25px;
    }

    .service-icon {
        font-size: 2.5rem;
        color: var(--secondary-color);
        margin-bottom: 15px;
    }

    .service-card h3 {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-size: 1.4rem;
    }

    .service-card p {
        color: var(--text-color);
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .feedback-section {
        background: var(--light-color);
        padding: 40px;
        border-radius: 10px;
        margin-bottom: 50px;
    }

    .feedback-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .feedback-list {
        max-height: 500px;
        overflow-y: auto;
        padding-right: 15px;
    }

    .feedback-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .feedback-user {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .feedback-user strong {
        color: var(--primary-color);
        margin-right: 10px;
    }

    .rating {
        color: #f39c12;
        font-size: 1.1rem;
    }

    .feedback-message {
        color: var(--text-color);
        line-height: 1.6;
        margin-bottom: 10px;
    }

    .feedback-date {
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .feedback-form {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--primary-color);
        font-weight: 600;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
        transition: border-color var(--transition-speed);
    }

    .form-control:focus {
        border-color: var(--secondary-color);
        outline: none;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .btn {
        display: inline-block;
        background: var(--secondary-color);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-speed);
    }

    .btn:hover {
        background: var(--accent-color);
        transform: translateY(-2px);
    }

    .btn-block {
        display: block;
        width: 100%;
    }

    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
    }

    .view-all {
        display: inline-block;
        margin-top: 20px;
        color: var(--secondary-color);
        font-weight: 600;
        text-decoration: none;
        transition: color var(--transition-speed);
    }

    .view-all:hover {
        color: var(--accent-color);
        text-decoration: underline;
    }

    @media (max-width: 992px) {
        .feedback-grid {
            grid-template-columns: 1fr;
        }
        
        .feedback-list {
            max-height: none;
        }
    }

    @media (max-width: 768px) {
        .services-hero {
            padding: 80px 20px;
        }
        
        .services-hero h1 {
            font-size: 2.5rem;
        }
        
        .services-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .services-hero h1 {
            font-size: 2rem;
        }
        
        .feedback-section {
            padding: 25px;
        }
    }
</style>

<!-- Hero Section -->
<section class="services-hero">
    <h1>Our Advertising Solutions</h1>
    <p>Maximize your brand's visibility with our innovative billboard advertising services tailored to your marketing needs</p>
</section>

<div class="services-container">
    <!-- Services Grid -->
    <h2>Our Billboard Services</h2>
    <div class="services-grid">
        <?php foreach ($services as $service): ?>
            <div class="service-card">
                <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>">
                <div class="service-content">
                    <div class="service-icon">
                        <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Feedback Section -->
    <div class="feedback-section">
        <h2>Customer Feedback</h2>
        
        <?php if (isset($_SESSION['feedback_success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['feedback_success']; unset($_SESSION['feedback_success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="feedback-grid">
            <div class="feedback-list">
                <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                    <div class="feedback-card">
                        <div class="feedback-user">
                            <strong><?php echo htmlspecialchars($feedback['full_name']); ?></strong>
                            <span class="rating"><?php echo str_repeat("★", $feedback['rating']); ?></span>
                        </div>
                        <p class="feedback-message"><?php echo htmlspecialchars($feedback['message']); ?></p>
                        <small class="feedback-date"><?php echo date('M j, Y', strtotime($feedback['created_at'])); ?></small>
                    </div>
                <?php endwhile; ?>
                <a href="manage_feedback.php" class="view-all">View All Feedback →</a>
            </div>
            
            <div class="feedback-form">
                <h3>Share Your Experience</h3>
                <form action="services.php" method="post">
                    <div class="form-group">
                        <label for="message">Your Feedback</label>
                        <textarea class="form-control" name="message" id="message" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="rating">Rating</label>
                        <select class="form-control" name="rating" id="rating" required>
                            <option value="">Select a rating</option>
                            <option value="5">★★★★★ Excellent</option>
                            <option value="4">★★★★ Very Good</option>
                            <option value="3">★★★ Average</option>
                            <option value="2">★★ Poor</option>
                            <option value="1">★ Very Bad</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="submit_feedback" class="btn btn-block">Submit Feedback</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>