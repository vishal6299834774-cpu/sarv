<?php
require_once 'includes/functions.php';

$category_id = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$products = get_products($category_id, $search, $limit, $offset);
$categories = get_categories();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
$count_params = [];
if ($category_id) {
    $count_sql .= " AND category_id = ?";
    $count_params[] = $category_id;
}
if ($search) {
    $count_sql .= " AND (title LIKE ? OR description LIKE ?)";
    $count_params[] = "%$search%";
    $count_params[] = "%$search%";
}
$stmt = $db->prepare($count_sql);
$stmt->execute($count_params);
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?php echo get_setting('site_name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Products Section -->
    <section class="products-section py-4">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="">
                                <!-- Search -->
                                <div class="mb-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Search products...">
                                </div>
                                
                                <!-- Categories -->
                                <div class="mb-3">
                                    <label class="form-label">Categories</label>
                                    <select class="form-select" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Apply Filters
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Products</h2>
                        <div class="text-muted">
                            Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products
                        </div>
                    </div>

                    <?php if (empty($products)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h4>No products found</h4>
                            <p class="text-muted">Try adjusting your filters or search terms</p>
                        </div>
                    <?php else: ?>
                        <div class="product-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <?php if ($product['screenshot']): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($product['screenshot']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                             class="product-image">
                                    <?php else: ?>
                                        <div class="product-image d-flex align-center justify-center">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="product-content">
                                        <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                        <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                        <div class="product-price"><?php echo format_price($product['price']); ?></div>
                                        <div class="product-actions">
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary flex-1">View Details</a>
                                            <?php if (is_logged_in()): ?>
                                                <form action="cart.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
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

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav class="mt-5">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $category_id; ?>&search=<?php echo urlencode($search); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
