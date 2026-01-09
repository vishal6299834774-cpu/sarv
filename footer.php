<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p><?php echo get_setting('site_name'); ?> is your trusted marketplace for high-quality digital products.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="categories.php">Categories</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="support.php">Support</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="refund.php">Refund Policy</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div class="social-links">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo get_setting('site_name'); ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Mobile Bottom Navigation -->
<nav class="bottom-nav">
    <ul>
        <li>
            <a href="index.php">
                <i class="fas fa-home icon"></i>
                <span>Home</span>
            </a>
        </li>
        <li>
            <a href="products.php">
                <i class="fas fa-th-large icon"></i>
                <span>Products</span>
            </a>
        </li>
        <li>
            <a href="cart.php">
                <i class="fas fa-shopping-cart icon"></i>
                <span>Cart</span>
            </a>
        </li>
        <li>
            <a href="profile.php">
                <i class="fas fa-user icon"></i>
                <span>Profile</span>
            </a>
        </li>
    </ul>
</nav>

<script>
function toggleTheme() {
    const html = document.documentElement;
    const themeIcon = document.getElementById('themeIcon');
    
    if (html.getAttribute('data-theme') === 'light') {
        html.setAttribute('data-theme', 'dark');
        themeIcon.className = 'fas fa-sun';
        localStorage.setItem('theme', 'dark');
    } else {
        html.setAttribute('data-theme', 'light');
        themeIcon.className = 'fas fa-moon';
        localStorage.setItem('theme', 'light');
    }
}

function toggleMobileMenu() {
    const navLinks = document.getElementById('navLinks');
    navLinks.classList.toggle('active');
}

// Load saved theme
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    const themeIcon = document.getElementById('themeIcon');
    if (themeIcon) {
        themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Set active nav item
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-links a, .bottom-nav a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath.split('/').pop()) {
            link.classList.add('active');
        }
    });
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const navLinks = document.getElementById('navLinks');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    
    if (navLinks && mobileToggle && !navLinks.contains(event.target) && !mobileToggle.contains(event.target)) {
        navLinks.classList.remove('active');
    }
});
</script>
