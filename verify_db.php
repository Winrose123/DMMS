<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database/connection.php';

try {
    // Drop all tables and recreate them
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop existing tables in correct order
    $tables = ['withdrawals', 'loans', 'expenses', 'milk_records', 'farmers', 'users'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Read and execute schema.sql
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($schema);
    
    // Create admin user with consistent password hash
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'User', 'admin@dmms.com', $hash, 'admin']);
    
    // Create farmer user with same password for testing
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['John', 'Doe', 'farmer@dmms.com', $hash, 'farmer']);
    $farmer_user_id = $pdo->lastInsertId();
    
    // Create farmer profile
    $stmt = $pdo->prepare("INSERT INTO farmers (user_id, phone, location) VALUES (?, ?, ?)");
    $stmt->execute([$farmer_user_id, '+254712345678', 'Nairobi']);
    
    // Get the farmer ID for sample data
    $farmer_id = $pdo->lastInsertId();
    
    // Add sample milk records
    $stmt = $pdo->prepare("
        INSERT INTO milk_records (farmer_id, quantity_liters, price_per_liter) 
        VALUES (?, ?, ?)"
    );
    
    // Add 3 sample records
    $stmt->execute([$farmer_id, 35.5, 60.00]);
    $stmt->execute([$farmer_id, 42.0, 60.00]);
    $stmt->execute([$farmer_id, 38.5, 60.00]);
    
    // Add sample loan requests
    $stmt = $pdo->prepare("
        INSERT INTO loans (farmer_id, amount, status) 
        VALUES (?, ?, ?)"
    );
    
    // Add 3 sample loans with different statuses
    $stmt->execute([$farmer_id, 25000.00, 'pending']);
    $stmt->execute([$farmer_id, 15000.00, 'approved']);
    $stmt->execute([$farmer_id, 10000.00, 'rejected']);
    
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>";
    echo "<h2 style='color: #27ae60;'>✅ Database Initialized Successfully!</h2>";
    echo "<p style='color: #333; margin: 20px 0;'>You can now log in with either of these accounts:</p>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: #2c3e50; margin-bottom: 10px;'>Admin Account</h3>";
    echo "<p style='margin: 5px 0;'><strong>Email:</strong> admin@dmms.com</p>";
    echo "<p style='margin: 5px 0;'><strong>Password:</strong> admin123</p>";
    echo "</div>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3 style='color: #2c3e50; margin-bottom: 10px;'>Farmer Account</h3>";
    echo "<p style='margin: 5px 0;'><strong>Email:</strong> farmer@dmms.com</p>";
    echo "<p style='margin: 5px 0;'><strong>Password:</strong> admin123</p>";
    echo "</div>";
    
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='login.php' style='display: inline-block; background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
    echo "</div>";
    echo "</div>";
    
} catch (PDOException $e) {
    die("<div style='color: red; margin: 20px;'>Database initialization failed: " . $e->getMessage() . "</div>");
}
?>
