<?php
require_once 'includes/functions.php';

// Redirect if not logged in
if (!is_logged_in()) {
    redirect('login.php');
}

$order_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $db->prepare("
    SELECT o.*, oi.product_id, oi.price, oi.quantity, p.title 
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN products p ON oi.product_id = p.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($order_items)) {
    redirect('profile.php');
}

$order = [
    'id' => $order_items[0]['id'],
    'order_number' => $order_items[0]['order_number'],
    'total_amount' => $order_items[0]['total_amount'],
    'status' => $order_items[0]['status'],
    'created_at' => $order_items[0]['created_at']
];
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - <?php echo get_setting('site_name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Order Success Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h1 class="mb-3">Order Placed Successfully!</h1>
                        <p class="lead text-muted">Thank you for your purchase. Your order has been received and is being processed.</p>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Order Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                                    <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-warning"><?php echo ucfirst($order['status']); ?></span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total Amount:</strong> <?php echo format_price($order['total_amount']); ?></p>
                                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method'] ?? 'Online'); ?></p>
                                </div>
                            </div>

                            <h6 class="mb-3">Products Ordered</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                                <td><?php echo format_price($item['price']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo format_price($item['price'] * $item['quantity']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>What happens next?
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                        <h6>Order Confirmation</h6>
                                        <p class="small text-muted">You'll receive an email confirmation with your order details</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-cogs fa-3x text-info mb-3"></i>
                                        <h6>Processing</h6>
                                        <p class="small text-muted">We're processing your order and preparing your digital products</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-download fa-3x text-success mb-3"></i>
                                        <h6>Download Ready</h6>
                                        <p class="small text-muted">You'll receive another email when your products are ready for download</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="products.php" class="btn btn-outline-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                        <a href="profile.php" class="btn btn-primary">
                            <i class="fas fa-user me-2"></i>View My Orders
                        </a>
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
