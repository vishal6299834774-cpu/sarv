<?php
session_start();

// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Helper functions
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'super_admin');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function get_setting($key) {
    global $db;
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['setting_value'] : '';
}

function format_price($price) {
    $currency = get_setting('currency');
    $symbol = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : '₹');
    return $symbol . number_format($price, 2);
}

function generate_order_number() {
    return 'ORD' . time() . rand(1000, 9999);
}

function send_email($to, $subject, $message) {
    $headers = "From: " . get_setting('email_from') . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

// Cart functions
function get_cart_count() {
    if (!is_logged_in()) return 0;
    global $db;
    $stmt = $db->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] ? $result['count'] : 0;
}

function get_cart_total() {
    if (!is_logged_in()) return 0;
    global $db;
    $stmt = $db->prepare("SELECT SUM(c.quantity * p.price) as total FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ? $result['total'] : 0;
}

// Product functions
function get_featured_products($limit = 8) {
    global $db;
    $limit = (int)$limit;
    $stmt = $db->prepare("SELECT * FROM products WHERE status = 'active' AND featured = 1 ORDER BY created_at DESC LIMIT $limit");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_products($category_id = null, $search = '', $limit = 12, $offset = 0) {
    global $db;
    $limit = (int)$limit;
    $offset = (int)$offset;
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'active'";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_categories() {
    global $db;
    $stmt = $db->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_product($id) {
    global $db;
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_related_products($product_id, $category_id, $limit = 4) {
    global $db;
    $limit = (int)$limit;
    $stmt = $db->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' ORDER BY RAND() LIMIT $limit");
    $stmt->execute([$category_id, $product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
