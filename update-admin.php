<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->connect();

// Update admin email and password
$new_email = 'vishal6299834774@gmail.com';
$new_password = 'vishal6299837447';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $db->prepare("UPDATE users SET email = ?, password = ? WHERE email = 'admin@example.com'");

if ($stmt->execute([$new_email, $hashed_password])) {
    echo "✅ Admin credentials updated successfully!<br>";
    echo "New Email: " . $new_email . "<br>";
    echo "New Password: " . $new_password . "<br><br>";
    echo "You can now login with these credentials.<br>";
    echo '<a href="login.php">Go to Login Page</a>';
} else {
    echo "❌ Failed to update admin credentials.";
}
?>
