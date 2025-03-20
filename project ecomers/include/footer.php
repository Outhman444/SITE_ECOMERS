<style>

    /* Footer Styles */
.footer {
    background-color: var(--dark-color);
    color: var(--light-color);
    padding: 40px 0 20px;
    border-radius: 15px;
}

.footer-content {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    gap: 30px;
}

.footer-section {
    flex: 1;
    min-width: 200px;
    margin-bottom: 20px;
}

.footer-section h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: var(--primary-color);
}

.footer-section p {
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 10px;
}

.footer-section ul li a {
    color: var(--light-color);
    text-decoration: none;
    transition: var(--transition);
}

.footer-section ul li a:hover {
    color: var(--accent-color);
}

.social-links {
    display: flex;
    gap: 10px;
}

.social-icon {
    color: var(--light-color);
    font-size: 1.2rem;
    transition: var(--transition);
}

.social-icon:hover {
    color: var(--accent-color);
    transform: translateY(-3px);
}

.footer-bottom {
    text-align: center;
    padding: 20px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: 20px;
}

.footer-bottom p {
    font-size: 0.9rem;
    margin: 0;
}




</style>
<footer class="footer">
        <div class="footer-content">
            <div class="footer-section about">
                <h3>About Us</h3>
                <p>We are a creative team dedicated to building amazing products and experiences for our users.</p>
                <div class="social-links">
                    <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
          
            <div class="footer-section contact">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Main Street, City, Country</p>
                <p><i class="fas fa-phone"></i> +1 234 567 890</p>
                <p><i class="fas fa-envelope"></i> info@example.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Your Company. All rights reserved.</p>
        </div>
    </footer>