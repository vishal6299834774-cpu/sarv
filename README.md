# E-Commerce Digital Product Selling Website

A comprehensive PHP-based e-commerce platform for selling digital products with responsive design, admin dashboard, and payment gateway integration.

## Features

### User-Side Features
- **Authentication System**
  - User registration and login
  - Password reset functionality
  - User profile management
  - Remember me functionality

- **Product Management**
  - Product listing with filters and search
  - Product detail pages with related products
  - Featured products section
  - Category-based browsing

- **Shopping Cart & Checkout**
  - Add to cart functionality
  - Cart management (update quantities, remove items)
  - Multi-step checkout process
  - Coupon/discount code support
  - Order confirmation and tracking

- **Responsive Design**
  - Mobile-first approach
  - Dark/Light mode toggle
  - Bottom navigation for mobile
  - Professional desktop layout

### Admin-Side Features
- **Product Management**
  - Add/Edit/Delete products
  - Upload digital files and screenshots
  - Category management
  - Product status control

- **Order Management**
  - View all orders with details
  - Order status management
  - Refund processing
  - Sales reports and analytics

- **User Management**
  - View all registered users
  - Block/unblock users
  - View purchase history

- **System Settings**
  - Payment gateway configuration
  - Tax settings
  - Website branding
  - Email notifications

## Technology Stack

- **Frontend**: PHP, HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+, MySQL
- **UI Framework**: Bootstrap 5 with custom CSS
- **Icons**: Font Awesome 6
- **Payment Gateways**: Razorpay, Stripe, PayPal (configurable)

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- PHP extensions: PDO, MySQLi, GD, cURL, JSON

### Setup Instructions

1. **Clone/Download the Project**
   ```bash
   git clone <repository-url>
   cd ecommerce-website
   ```

2. **Database Setup**
   - Create a new MySQL database: `ecommerce_db`
   - Import the `database_setup.sql` file:
     ```sql
     mysql -u username -p ecommerce_db < database_setup.sql
     ```

3. **Configure Database Connection**
   - Edit `config/database.php`:
     ```php
     private $host = 'localhost';
     private $db_name = 'ecommerce_db';
     private $username = 'your_db_username';
     private $password = 'your_db_password';
     ```

4. **Set Up File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 config/database.php
   ```

5. **Configure Web Server**
   - Point your web server document root to the project directory
   - Ensure `.htaccess` is enabled for Apache

6. **Access the Website**
   - Open your browser and navigate to `http://localhost/ecommerce-website`
   - Default admin login:
     - Email: `admin@example.com`
     - Password: `admin123`

## Directory Structure

```
ecommerce-website/
├── admin/                  # Admin dashboard files
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Image files
├── config/                # Configuration files
│   └── database.php       # Database connection
├── includes/              # Reusable components
│   ├── functions.php      # Helper functions
│   ├── header.php         # Header component
│   └── footer.php         # Footer component
├── uploads/               # User uploaded files
├── database_setup.sql     # Database schema
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Registration page
├── products.php           # Product listing
├── product.php            # Product details
├── cart.php               # Shopping cart
├── checkout.php           # Checkout process
├── profile.php            # User profile
└── README.md              # This file
```

## Configuration

### Payment Gateways
Configure payment gateway settings in the admin dashboard:
1. Login as admin
2. Go to Settings > Payment Gateways
3. Enter your API keys for Razorpay, Stripe, or PayPal

### Email Settings
1. Go to Settings > Email Configuration
2. Configure SMTP settings for order notifications
3. Set up email templates

### Tax Settings
1. Go to Settings > Tax Configuration
2. Set tax rates (GST/VAT) for different regions
3. Configure tax rules

## Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection with form tokens
- Secure file upload handling
- Session management
- Rate limiting for login attempts

## Mobile Responsiveness

The website is fully responsive with:
- Mobile-first design approach
- Touch-friendly interface
- Bottom navigation for mobile users
- Adaptive layouts for tablets
- Optimized performance for mobile devices

## Customization

### Adding New Themes
1. Modify CSS variables in `assets/css/style.css`
2. Update color schemes and typography
3. Test across different devices

### Adding New Payment Gateways
1. Create new payment gateway class in `includes/payments/`
2. Implement required methods
3. Add configuration options to admin panel

### Extending Functionality
The modular structure allows easy extension:
- Add new pages following the existing pattern
- Use helper functions from `includes/functions.php`
- Follow the established naming conventions

## Support

For support and questions:
1. Check the FAQ section on the website
2. Contact support through the contact form
3. Review the documentation

## License

This project is licensed under the MIT License. See LICENSE file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Version History

- **v1.0.0** - Initial release with core e-commerce functionality
- **v1.1.0** - Added admin dashboard and payment gateway integration
- **v1.2.0** - Enhanced mobile responsiveness and dark mode support

---

Built with ❤️ using PHP, MySQL, and Bootstrap 5
