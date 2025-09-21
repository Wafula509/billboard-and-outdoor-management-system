<?php
// footer.php
?>
<!-- Footer Section -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                <li><a href="services.php"><i class="fas fa-cogs"></i> Services</a></li>
                <li><a href="view_billboards.php"><i class="fas fa-ad"></i> Billboards</a></li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Contact Info</h3>
            <ul>
                <li><i class="fas fa-map-marker-alt"></i> 123 Billboard St, Nairobi, Kenya</li>
                <li><i class="fas fa-phone"></i> +254 700 123456</li>
                <li><i class="fas fa-envelope"></i> info@billboardsolutions.co.ke</li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Connect With Us</h3>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <div class="newsletter">
                <p>Subscribe to our newsletter</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Billboard Solutions LTD. All Rights Reserved.</p>
        <div class="legal-links">
            <a href="privacy.php">Privacy Policy</a>
            <a href="terms.php">Terms of Service</a>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" aria-label="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/main.js"></script>

<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #f4a261;
        --accent-color: #e76f51;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --footer-bg: #2c3e50;
        --footer-text: #f8f9fa;
        --transition-speed: 0.3s;
    }

    /* Footer Styles */
    .site-footer {
        background-color: var(--footer-bg);
        color: var(--footer-text);
        padding: 40px 0 20px;
        font-size: 0.95rem;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .footer-section h3 {
        color: var(--secondary-color);
        margin-bottom: 20px;
        font-size: 1.2rem;
        position: relative;
        padding-bottom: 10px;
    }

    .footer-section h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 2px;
        background: var(--accent-color);
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
    }

    .footer-section li {
        margin-bottom: 12px;
    }

    .footer-section a, .footer-section li {
        color: var(--footer-text);
        transition: color var(--transition-speed);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .footer-section a:hover {
        color: var(--secondary-color);
        text-decoration: none;
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .social-links a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        color: var(--footer-text);
        transition: all var(--transition-speed);
    }

    .social-links a:hover {
        background: var(--secondary-color);
        transform: translateY(-3px);
    }

    .newsletter {
        margin-top: 20px;
    }

    .newsletter p {
        margin-bottom: 10px;
    }

    .newsletter-form {
        display: flex;
    }

    .newsletter-form input {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 4px 0 0 4px;
    }

    .newsletter-form button {
        background: var(--secondary-color);
        color: white;
        border: none;
        padding: 0 15px;
        border-radius: 0 4px 4px 0;
        cursor: pointer;
        transition: background var(--transition-speed);
    }

    .newsletter-form button:hover {
        background: var(--accent-color);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        margin-top: 30px;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .legal-links {
        margin-top: 10px;
    }

    .legal-links a {
        color: var(--footer-text);
        margin: 0 10px;
        font-size: 0.9rem;
    }

    .legal-links a:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    /* Back to Top Button */
    #backToTop {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        transition: all var(--transition-speed);
        z-index: 999;
    }

    #backToTop:hover {
        background: var(--accent-color);
        transform: translateY(-3px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .footer-container {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .footer-section {
            margin-bottom: 20px;
        }

        #backToTop {
            width: 40px;
            height: 40px;
            bottom: 20px;
            right: 20px;
        }
    }
</style>

<script>
    // Back to Top Button
    window.addEventListener('scroll', function() {
        var backToTop = document.getElementById('backToTop');
        if (window.pageYOffset > 300) {
            backToTop.style.display = 'flex';
        } else {
            backToTop.style.display = 'none';
        }
    });

    document.getElementById('backToTop').addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Current year for copyright
    document.querySelector('.footer-bottom p').innerHTML = `&copy; ${new Date().getFullYear()} Billboard Solutions LTD. All Rights Reserved.`;
</script>
</body>
</html>