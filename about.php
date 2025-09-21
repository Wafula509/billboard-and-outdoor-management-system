<?php
session_start();
$pageTitle = "About Us - Billboard Solutions";
include 'header.php';
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

    .about-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/about-bg.jpg') no-repeat center center/cover;
        color: white;
        text-align: center;
        padding: 100px 20px;
        margin-bottom: 50px;
    }

    .about-hero h1 {
        font-size: 3rem;
        margin-bottom: 20px;
        color: var(--secondary-color);
    }

    .about-hero p {
        font-size: 1.2rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .about-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .about-section {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 60px;
        gap: 40px;
    }

    .about-content {
        flex: 1;
        min-width: 300px;
    }

    .about-image {
        flex: 1;
        min-width: 300px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .about-image img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform var(--transition-speed);
    }

    .about-image:hover img {
        transform: scale(1.03);
    }

    .about-section h2 {
        color: var(--primary-color);
        margin-bottom: 20px;
        font-size: 2rem;
        position: relative;
        display: inline-block;
    }

    .about-section h2::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -10px;
        width: 50px;
        height: 3px;
        background: var(--secondary-color);
    }

    .about-section p {
        margin-bottom: 20px;
        line-height: 1.6;
        color: var(--text-color);
    }

    .mission-vision {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }

    .mission-card, .vision-card {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform var(--transition-speed);
    }

    .mission-card:hover, .vision-card:hover {
        transform: translateY(-10px);
    }

    .mission-card h3, .vision-card h3 {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-size: 1.5rem;
    }

    .mission-card i, .vision-card i {
        font-size: 2.5rem;
        color: var(--secondary-color);
        margin-bottom: 20px;
    }

    .team-section {
        margin-bottom: 60px;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .team-member {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform var(--transition-speed);
        text-align: center;
    }

    .team-member:hover {
        transform: translateY(-10px);
    }

    .team-member img {
        width: 100%;
        height: 250px;
        object-fit: cover;
    }

    .team-info {
        padding: 20px;
    }

    .team-info h4 {
        color: var(--primary-color);
        margin-bottom: 5px;
    }

    .team-info p {
        color: var(--secondary-color);
        font-weight: 600;
        margin-bottom: 15px;
    }

    .team-social {
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .team-social a {
        color: var(--primary-color);
        transition: color var(--transition-speed);
    }

    .team-social a:hover {
        color: var(--accent-color);
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 80px 20px;
        }
        
        .about-hero h1 {
            font-size: 2.5rem;
        }
        
        .about-section {
            flex-direction: column;
        }
        
        .about-image {
            order: -1;
        }
    }

    @media (max-width: 480px) {
        .about-hero h1 {
            font-size: 2rem;
        }
        
        .about-section h2 {
            font-size: 1.8rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="about-hero">
    <h1>About Billboard Solutions</h1>
    <p>Pioneers in outdoor advertising with over 15 months of experience in connecting brands with their audiences</p>
</section>

<div class="about-container">
    <!-- Company Story Section -->
    <section class="about-section">
        <div class="about-content">
            <h2>Our Story</h2>
            <p>Founded in 2025, Billboard Solutions began with a single billboard in Nairobi's central business district. Today, we operate over 200 premium billboard locations across East Africa, serving multinational corporations, local businesses, and government agencies.</p>
            <p>Our journey has been marked by innovation, from introducing digital billboards in 2025 to implementing data-driven audience measurement tools in 2025. We're proud to have helped thousands of brands amplify their messages and reach their target audiences effectively.</p>
            <p>What sets us apart is our commitment to quality locations, innovative formats, and exceptional customer service. We don't just rent space - we create impactful advertising solutions that deliver results.</p>
        </div>
        <div class="about-image">
            <img src="images/office..jfif" alt="Billboard Solutions office">
        </div>
    </section>

    <!-- Mission and Vision Section -->
    <section class="mission-vision">
        <div class="mission-card">
            <i class="fas fa-bullseye"></i>
            <h3>Our Mission</h3>
            <p>To provide innovative outdoor advertising solutions that deliver measurable results for our clients while maintaining the highest standards of quality, integrity, and environmental responsibility.</p>
        </div>
        <div class="vision-card">
            <i class="fas fa-eye"></i>
            <h3>Our Vision</h3>
            <p>To be East Africa's leading outdoor advertising company by continuously innovating, expanding our premium locations, and leveraging technology to maximize the impact of every campaign.</p>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <h2>Meet Our Leadership Team</h2>
        <p>Our success is driven by a team of passionate professionals with diverse expertise in advertising, technology, and business development.</p>
        
        <div class="team-grid">
            <div class="team-member">
                <img src="images/team1.jpeg" alt="Wanjala M, CEO">
                <div class="team-info">
                    <h4>Wanjala M</h4>
                    <p>Founder & CEO</p>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="team-member">
                <img src="images/team2.jfif" alt="Mercy anyango, COO">
                <div class="team-info">
                    <h4>Mercy anyango</h4>
                    <p>Chief Operations Officer</p>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="team-member">
                <img src="images/team3.jfif" alt="Dr KSibet, CMO">
                <div class="team-info">
                    <h4>Dr Kibet</h4>
                    <p>Chief Marketing Officer</p>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="team-member">
                <img src="images/team4.jfif" alt="DR Langat, CTO">
                <div class="team-info">
                    <h4>DR Langat</h4>
                    <p>Chief Technology Officer</p>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievements Section -->
    <section class="about-section">
        <div class="about-image">
            <img src="images/awards.jfif" alt="Our achievements">
        </div>
        <div class="about-content">
            <h2>Our Achievements</h2>
            <p>Over the years, we've been recognized for our excellence in outdoor advertising and commitment to innovation:</p>
            <ul style="list-style-type: none; padding-left: 0;">
                <li><i class="fas fa-trophy" style="color: var(--secondary-color); margin-right: 10px;"></i> Best Outdoor Advertising Company 2022</li>
                <li><i class="fas fa-trophy" style="color: var(--secondary-color); margin-right: 10px;"></i> Innovation in Advertising Award 2021</li>
                <li><i class="fas fa-trophy" style="color: var(--secondary-color); margin-right: 10px;"></i> Environmental Sustainability Award 2020</li>
                <li><i class="fas fa-trophy" style="color: var(--secondary-color); margin-right: 10px;"></i> Top 100 SMEs in Kenya 2019</li>
            </ul>
            <p>These accolades reflect our dedication to pushing boundaries and delivering exceptional value to our clients.</p>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>