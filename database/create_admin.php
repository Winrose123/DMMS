<?php
require_once 'connection.php';

// Create admin user if it doesn't exist
$email = 'admin@dairy.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);

try {
    // Check if admin exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if (!$check->fetch()) {
        // Create admin user
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['System', 'Admin', $email, $password, 'admin']);
        echo "Admin user created successfully!\n";
        echo "Email: admin@dairy.com\n";
        echo "Password: admin123\n";
    } else {
        echo "Admin user already exists.\n";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
