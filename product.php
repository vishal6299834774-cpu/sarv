<?php
require_once 'includes/functions.php';

$product_id = $_GET['id'] ?? 0;
$product = get_product($product_id);

if (!$product) {
    redirect('products.php');
}

$related_products = get_related_products($product_id, $product['category_id']);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - <?php echo get_setting('site_name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Product Detail Section -->
    <section class="py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
                </ol>
            </nav>

            <div class="row">
                <!-- Product Image -->
                <div class="col-lg-6 mb-4">
                    <?php if ($product['screenshot']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($product['screenshot']); ?>" 
                             alt="<?php echo htmlspecialchars($product['title']); ?>" 
                             class="img-fluid rounded shadow">
                    <?php else: ?>
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                            <i class="fas fa-image fa-4x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6">
                    <h1 class="mb-3"><?php echo htmlspecialchars($product['title']); ?></h1>
                    
                    <?php if ($product['category_name']): ?>
                        <div class="mb-3">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="product-price mb-4">
                        <?php echo format_price($product['price']); ?>
                    </div>

                    <div class="product-description mb-4">
                        <h5>Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>

                    <div class="product-actions mb-4">
                        <?php if (is_logged_in()): ?>
                            <form action="cart.php" method="POST" class="d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <button type="submit" class="btn btn-primary btn-lg me-2">
                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                </button>
                            </form>
                            <a href="checkout.php?product=<?php echo $product['id']; ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-bolt me-2"></i>Buy Now
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="product-features">
                        <h5>Product Features</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Instant Download</li>
                            <li><i class="fas fa-check text-success me-2"></i>Secure Payment</li>
                            <li><i class="fas fa-check text-success me-2"></i>24/7 Support</li>
                            <li><i class="fas fa-check text-success me-2"></i>Regular Updates</li>
                        </ul>
                    </div>

                    <div class="share-buttons mt-4">
                        <h5>Share this product</h5>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-info btn-sm">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="#" class="btn btn-outline-danger btn-sm">
                                <i class="fab fa-pinterest"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <section class="py-5 bg-light">
            <div class="container">
                <h3 class="text-center mb-4">Related Products</h3>
                <div class="product-grid">
                    <?php foreach ($related_products as $related): ?>
                        <div class="product-card">
                            <?php if ($related['screenshot']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($related['screenshot']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['title']); ?>" 
                                     class="product-image">
                            <?php else: ?>
                                <div class="product-image d-flex align-center justify-center">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-content">
                                <h3 class="product-title"><?php echo htmlspecialchars($related['title']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars(substr($related['description'], 0, 100)); ?>...</p>
                                <div class="product-price"><?php echo format_price($related['price']); ?></div>
                                <div class="product-actions">
                                    <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-primary flex-1">View Details</a>
                                    <?php if (is_logged_in()): ?>
                                        <form action="cart.php" method="POST" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $related['id']; ?>">
                                            <input type="hidden" name="action" value="add">
                                            <button type="submit" class="btn btn-outline">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add to cart with animation
        document.querySelector('form[action="cart.php"]')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
            button.disabled = true;
            
            fetch('cart.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.text())
            .then(data => {
                button.innerHTML = '<i class="fas fa-check me-2"></i>Added!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    // Update cart count in header
                    location.reload();
                }, 1500);
            })
            .catch(error => {
                button.innerHTML = originalText;
                button.disabled = false;
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
