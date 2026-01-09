<?php
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
    redirect('login.php');
}

$message = '';
$error = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if ($action === 'add' && $product_id > 0) {
        // Check if product exists and is active
        $stmt = $db->prepare("SELECT id FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        
        if ($stmt->fetch()) {
            // Check if already in cart
            $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Update quantity
                $stmt = $db->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
                $stmt->execute([$existing['id']]);
                $message = 'Product quantity updated in cart!';
            } else {
                // Add to cart
                $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
                $stmt->execute([$_SESSION['user_id'], $product_id]);
                $message = 'Product added to cart!';
            }
        } else {
            $error = 'Product not found';
        }
    } elseif ($action === 'update') {
        $quantities = $_POST['quantities'] ?? [];
        
        foreach ($quantities as $cart_id => $quantity) {
            $quantity = intval($quantity);
            if ($quantity > 0) {
                $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
            } else {
                // Remove if quantity is 0
                $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $_SESSION['user_id']]);
            }
        }
        $message = 'Cart updated successfully!';
    } elseif ($action === 'remove') {
        $cart_id = intval($_POST['cart_id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
        $message = 'Product removed from cart!';
    }
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

$cart_total = get_cart_total();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo get_setting('site_name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Cart Section -->
    <section class="cart-page">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="mb-4">
                        <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                        <?php if (!empty($cart_items)): ?>
                            <span class="badge bg-primary"><?php echo count($cart_items); ?> items</span>
                        <?php endif; ?>
                    </h2>

                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($cart_items)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                            <h4>Your cart is empty</h4>
                            <p class="text-muted">Looks like you haven't added any products yet</p>
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" id="cartForm">
                            <input type="hidden" name="action" value="update">
                            
                            <div class="table-responsive">
                                <table class="table cart-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($item['screenshot']): ?>
                                                            <img src="uploads/<?php echo htmlspecialchars($item['screenshot']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                                 class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                        <?php else: ?>
                                                            <div class="me-3 bg-light d-flex align-items-center justify-content-center" 
                                                                 style="width: 60px; height: 60px; border-radius: 8px;">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                            <small class="text-muted">Digital Product</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo format_price($item['price']); ?></td>
                                                <td>
                                                    <input type="number" name="quantities[<?php echo $item['cart_id']; ?>]" 
                                                           value="<?php echo $item['quantity']; ?>" 
                                                           min="1" max="99" class="form-control form-control-sm" 
                                                           style="width: 80px;" onchange="updateCart()">
                                                </td>
                                                <td><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                                <td>
                                                    <form method="POST" action="" class="d-inline">
                                                        <input type="hidden" name="action" value="remove">
                                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('Remove this item from cart?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="products.php" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync me-2"></i>Update Cart
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <?php if (!empty($cart_items)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span><?php echo format_price($cart_total); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax:</span>
                                    <span><?php echo format_price($cart_total * 0.1); ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong><?php echo format_price($cart_total * 1.1); ?></strong>
                                </div>
                                
                                <a href="checkout.php" class="btn btn-success w-100 btn-lg">
                                    <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                </a>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>Secure checkout powered by SSL encryption
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <h6 class="mb-3">Have a coupon?</h6>
                                <form method="POST" action="checkout.php">
                                    <div class="input-group">
                                        <input type="text" name="coupon_code" class="form-control" 
                                               placeholder="Enter coupon code">
                                        <button type="submit" name="apply_coupon" class="btn btn-outline-primary">
                                            Apply
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateCart() {
            // Auto-submit form when quantity changes
            document.getElementById('cartForm').submit();
        }
        
        // Prevent form submission on Enter key in quantity inputs
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    updateCart();
                }
            });
        });
    </script>
</body>
</html>
