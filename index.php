<?php
session_start();
$pageTitle = "Billboard Management - Home";
include 'header.php';
?>

<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #f4a261;
        --accent-color: #e76f51;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --success-color: #28a745;
        --transition-speed: 0.3s;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background: url('images/f4.jpg') no-repeat center center/cover;
        background-attachment: fixed;
        color: white;
        text-align: center;
        padding-top: 70px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
    }

    .main-content {
        flex: 1;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }

    .container {
        width: 90%;
        max-width: 500px;
        padding: 30px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        animation: fadeIn 1s ease-out;
        margin: 20px 0;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .container h2 {
        margin-bottom: 20px;
        color: var(--secondary-color);
        font-size: 2rem;
    }

    .container p {
        margin-bottom: 25px;
        font-size: 1.1rem;
        line-height: 1.5;
    }

    .btn-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        width: 100%;
    }

    .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 14px;
        font-size: 1rem;
        font-weight: 600;
        color: white;
        background: var(--secondary-color);
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all var(--transition-speed);
        text-decoration: none;
    }

    .btn:hover {
        background: var(--accent-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn i {
        margin-right: 10px;
        font-size: 1.1rem;
    }

    .btn-signin {
        background: var(--primary-color);
    }

    .btn-signup {
        background: var(--success-color);
    }

    .marquee-container {
        width: 100%;
        background: rgba(0, 0, 0, 0.8);
        padding: 12px 0;
        position: relative;
        overflow: hidden;
    }

    .marquee {
        font-size: 1.1rem;
        color: white;
        text-align: center;
        font-weight: 500;
        padding: 5px 0;
        width: 100%;
    }

    /* Modern alternative to marquee */
    .scrolling-text {
        display: inline-block;
        white-space: nowrap;
        animation: scrollText 20s linear infinite;
    }

    @keyframes scrollText {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }

    @media (max-width: 768px) {
        .container {
            padding: 25px;
            margin: 15px 0;
        }
        
        .container h2 {
            font-size: 1.8rem;
        }
        
        .btn {
            padding: 12px;
            font-size: 0.95rem;
        }
        
        .marquee, .scrolling-text {
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 20px;
            width: 95%;
        }
        
        .container h2 {
            font-size: 1.6rem;
            margin-bottom: 15px;
        }
        
        .container p {
            font-size: 1rem;
            margin-bottom: 20px;
        }
        
        .btn-container {
            gap: 10px;
        }
    }
</style>

<div class="main-content">
    <div class="container">
        <h2><i class="fas fa-billboard"></i> Welcome to Billboard Solutions</h2>
        <p><i class="fas fa-map-marked-alt"></i> Manage your billboards with ease and maximize your advertising potential</p>
        
        <div class="btn-container">
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                <!-- Dashboard for logged in users -->
                <a href="user_dashboard.php" class="btn" aria-label="Go to Dashboard">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
                <a href="services.php" class="btn" aria-label="Explore Our Services">
                    <i class="fas fa-cogs"></i> Explore Our Services
                </a>
                <a href="view_billboards.php" class="btn" aria-label="View Available Billboards">
                    <i class="fas fa-ad"></i> View Billboards
                </a>
            <?php else: ?>
                <!-- Options for guests -->
                <a href="view_billboards.php" class="btn" aria-label="View Available Billboards">
                    <i class="fas fa-ad"></i> View Billboards
                </a>
                <a href="services.php" class="btn" aria-label="Explore Our Services">
                    <i class="fas fa-cogs"></i> Explore Services
                </a>
                
                </a>
                <a href="signup.php" class="btn btn-signup" aria-label="Create Account">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="marquee-container">
    <div class="marquee">
        <!-- Modern alternative to marquee with better accessibility -->
        <div class="scrolling-text" aria-label="Company announcement">
            BILLBOARDS SOLUTIONS LTD - Enhancing visibility, maximizing reach. Billboards are a key tool in effective outdoor advertising, ensuring that your message reaches a broad audience. The importance of billboards in advertising is undeniable!
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>