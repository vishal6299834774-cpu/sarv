<!-- Header -->
<header class="header">
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-store"></i> <?php echo get_setting('site_name'); ?>
            </a>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="faq.php">FAQ</a></li>
                
                <?php if (is_logged_in()): ?>
                    <li><a href="cart.php">
                        <i class="fas fa-shopping-cart"></i> 
                        Cart <span class="badge bg-primary"><?php echo get_cart_count(); ?></span>
                    </a></li>
                    <li><a href="profile.php">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                    </a></li>
                    <?php if (is_admin()): ?>
                        <li><a href="admin/">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Sign Up</a></li>
                <?php endif; ?>
                
                <li>
                    <button class="btn btn-sm btn-outline" onclick="toggleTheme()">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                </li>
            </ul>
            
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
</header>
