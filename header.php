<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Billboard Management System'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #f4a261;
            --accent-color: #e76f51;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --transition-speed: 0.3s;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            padding-top: 70px;
            background-color: #f5f5f5;
        }
        
        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo-img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            transition: color var(--transition-speed);
        }
        
        .logo-text:hover {
            color: var(--secondary-color);
        }
        
        .nav-links {
            display: flex;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1rem;
            font-weight: 500;
            transition: color var(--transition-speed);
            position: relative;
            white-space: nowrap;
        }
        
        .nav-links a:hover {
            color: var(--secondary-color);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--secondary-color);
            transition: width var(--transition-speed);
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        /* Auth Buttons */
        .auth-buttons {
            display: flex;
            gap: 10px;
            margin-left: 20px;
        }
        
        .auth-btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-speed);
            text-decoration: none;
            display: inline-block;
        }
        
        .signin-btn {
            background-color: transparent;
            border: 1px solid white;
            color: white;
        }
        
        .signin-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .signup-btn {
            background-color: var(--secondary-color);
            border: 1px solid var(--secondary-color);
            color: var(--primary-color);
        }
        
        .signup-btn:hover {
            background-color: #e68900;
            border-color: #e68900;
        }
        
        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            position: relative;
            cursor: pointer;
            margin-left: 20px;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
            border: 2px solid var(--secondary-color);
            transition: border-color var(--transition-speed);
        }
        
        .user-profile:hover .user-avatar {
            border-color: var(--accent-color);
        }
        
        .username {
            font-weight: 500;
            margin-right: 5px;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            min-width: 200px;
            border-radius: 5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1001;
            overflow: hidden;
        }
        
        .user-profile:hover .dropdown-menu {
            display: block;
            animation: fadeIn var(--transition-speed);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--dark-color);
            text-decoration: none;
            transition: all var(--transition-speed);
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }
        
        .dropdown-menu a:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
            padding-left: 20px;
        }
        
        .dropdown-menu a i {
            margin-right: 8px;
            color: var(--secondary-color);
            width: 20px;
            text-align: center;
        }
        
        .logout-btn {
            color: var(--danger-color) !important;
        }
        
        .logout-btn i {
            color: var(--danger-color) !important;
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform var(--transition-speed);
        }
        
        .mobile-menu-btn:hover {
            transform: scale(1.1);
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .nav-links a {
                margin: 0 10px;
            }
            
            .username {
                max-width: 100px;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
                flex-wrap: wrap;
            }
            
            .mobile-menu-btn {
                display: block;
                order: 1;
            }
            
            .logo-container {
                order: 0;
                flex: 1;
            }
            
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                padding: 20px 0;
                order: 2;
                background-color: var(--primary-color);
                margin-top: 15px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .nav-links a {
                margin: 10px 0;
                padding: 10px 0;
                width: 100%;
            }
            
            .nav-links a::after {
                bottom: 0;
            }
            
            .auth-buttons {
                margin: 15px 0 0 0;
                width: 100%;
                justify-content: flex-start;
            }
            
            .auth-btn {
                width: 100%;
                text-align: left;
                padding: 10px 15px;
            }
            
            .user-profile {
                margin: 15px 0 0 0;
                width: 100%;
                justify-content: flex-start;
            }
            
            .dropdown-menu {
                position: static;
                width: 100%;
                box-shadow: none;
                border-radius: 0;
                margin-top: 10px;
                animation: none;
                display: none;
            }
            
            .user-profile:hover .dropdown-menu,
            .user-profile:focus-within .dropdown-menu {
                display: block;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo-container">
            <img src="images/logo.jpg" alt="Company Logo" class="logo-img">
            <a href="index.php" class="logo-text">Billboard Solutions</a>
        </div>
        
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="nav-links" id="navLinks">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
            <a href="services.php"><i class="fas fa-cogs"></i> Services</a>
            <a href="user_dashboard.php"><i class="fas fa-ad"></i> Dashboards</a>
            <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
            
            <?php if (isset($_SESSION['admin_logged_in'])): ?>
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Panel</a>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_logged_in'])): ?>
                <div class="user-profile" tabindex="0">
                    <img src="<?php echo !empty($_SESSION['user_avatar']) ? htmlspecialchars($_SESSION['user_avatar']) : 'images/default-avatar.jpg'; ?>" 
                         alt="User Avatar" class="user-avatar">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                    <div class="dropdown-menu">
                        <a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        <a href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    
                    <a href="signup.php" class="auth-btn signup-btn"><i class="fas fa-user-plus"></i> Sign Up</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('navLinks').classList.toggle('active');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function() {
            const navLinks = document.getElementById('navLinks');
            if (navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
            }
        });
        
        // Keyboard accessibility for dropdown
        const userProfile = document.querySelector('.user-profile');
        if (userProfile) {
            userProfile.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    this.querySelector('.dropdown-menu').style.display = 
                        this.querySelector('.dropdown-menu').style.display === 'block' ? 'none' : 'block';
                    e.preventDefault();
                }
            });
        }
        
        // Close dropdown when clicking outside (for desktop)
        document.addEventListener('click', function(e) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                if (!dropdown.parentElement.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        });
    </script>