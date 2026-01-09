<?php
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
    redirect('login.php');
}

// Get cart items
$stmt = $db->prepare("
    SELECT c.id as cart_id, c.quantity, p.id, p.title, p.price, p.screenshot 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? AND p.status = 'active'
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    redirect('cart.php');
}

$cart_total = get_cart_total();
$tax_rate = 0.1; // 10% tax
$tax_amount = $cart_total * $tax_rate;
$total_amount = $cart_total + $tax_amount;

// Handle direct product purchase
$direct_product = null;
if (isset($_GET['product'])) {
    $product_id = intval($_GET['product']);
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$product_id]);
    $direct_product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($direct_product) {
        $cart_items = [[$direct_product]];
        $cart_total = $direct_product['price'];
        $tax_amount = $cart_total * $tax_rate;
        $total_amount = $cart_total + $tax_amount;
    }
}

$error = '';
$success = '';

// Handle coupon application
$discount = 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_coupon'])) {
    $coupon_code = clean_input($_POST['coupon_code']);
    
    if (!empty($coupon_code)) {
        $stmt = $db->prepare("
            SELECT * FROM coupons 
            WHERE code = ? AND status = 'active' 
            AND (expiry_date IS NULL OR expiry_date >= CURDATE())
            AND (usage_limit IS NULL OR used_count < usage_limit)
        ");
        $stmt->execute([$coupon_code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($coupon) {
            if ($coupon['min_amount'] > 0 && $cart_total < $coupon['min_amount']) {
                $error = "Minimum order amount of " . format_price($coupon['min_amount']) . " required for this coupon.";
            } else {
                if ($coupon['discount_type'] === 'flat') {
                    $discount = $coupon['discount_value'];
                } else {
                    $discount = $cart_total * ($coupon['discount_value'] / 100);
                }
                
                $total_amount = $cart_total + $tax_amount - $discount;
                $success = "Coupon applied successfully! You saved " . format_price($discount);
            }
        } else {
            $error = "Invalid or expired coupon code.";
        }
    }
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $payment_method = clean_input($_POST['payment_method']);
    
    // Create order
    $order_number = generate_order_number();
    $stmt = $db->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, status, payment_method) 
        VALUES (?, ?, ?, 'pending', ?)
    ");
    
    if ($stmt->execute([$_SESSION['user_id'], $order_number, $total_amount, $payment_method])) {
        $order_id = $db->lastInsertId();
        
        // Add order items
        foreach ($cart_items as $item) {
            $product = is_array($item[0]) ? $item[0] : $item;
            $stmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, price, quantity) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $product['id'], $product['price'], $product['quantity'] ?? 1]);
        }
        
        // Clear cart (if not direct purchase)
        if (!$direct_product) {
            $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
        
        // Send order confirmation email
        $stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $subject = 'Order Confirmation - ' . get_setting('site_name');
        $message = "
            <h2>Order Confirmation</h2>
            <p>Thank you for your order!</p>
            <p><strong>Order Number:</strong> $order_number</p>
            <p><strong>Total Amount:</strong> " . format_price($total_amount) . "</p>
            <p>Your order is being processed. You will receive another email when your order is ready for download.</p>
            <p>Best regards,<br>" . get_setting('site_name') . " Team</p>
        ";
        send_email($user['email'], $subject, $message);
        
        redirect('order-success.php?id=' . $order_id);
    } else {
        $error = "Failed to place order. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo get_setting('site_name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Checkout Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="mb-4">
                <i class="fas fa-credit-card me-2"></i>Checkout
            </h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <form method="POST" action="">
                        <!-- Billing Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Billing Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="billing_name" 
                                               value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="billing_email" 
                                               value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="billing_phone" required>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="razorpay" value="razorpay" checked>
                                    <label class="form-check-label" for="razorpay">
                                        <i class="fab fa-cc-stripe me-2"></i>Razorpay
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="stripe" value="stripe">
                                    <label class="form-check-label" for="stripe">
                                        <i class="fab fa-stripe me-2"></i>Stripe
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="paypal" value="paypal">
                                    <label class="form-check-label" for="paypal">
                                        <i class="fab fa-paypal me-2"></i>PayPal
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-success btn-lg">
                            <i class="fas fa-lock me-2"></i>Place Order
                        </button>
                    </form>
                </div>

                <div class="col-lg-4">
                    <!-- Order Summary -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3">Products</h6>
                            <?php foreach ($cart_items as $item): ?>
                                <?php $product = is_array($item[0]) ? $item[0] : $item; ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <small><?php echo htmlspecialchars($product['title']); ?></small>
                                    <small><?php echo format_price($product['price']); ?></small>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?php echo format_price($cart_total); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10%):</span>
                                <span><?php echo format_price($tax_amount); ?></span>
                            </div>
                            <?php if ($discount > 0): ?>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-<?php echo format_price($discount); ?></span>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong><?php echo format_price($total_amount); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Security Info -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="fas fa-shield-alt me-2"></i>Secure Checkout
                            </h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-2"></i>SSL Encrypted</li>
                                <li><i class="fas fa-check text-success me-2"></i>PCI Compliant</li>
                                <li><i class="fas fa-check text-success me-2"></i>Safe & Secure</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
