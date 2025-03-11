<?php
require_once 'database/connection.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

try {
    // Test database connection
    $test = $pdo->query("SELECT 1")->fetch();
    echo "✅ Database connection successful<br>";
    
    // Check if users table exists and has records
    $users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($users) . " users in database<br><br>";
    
    echo "<strong>User Details:</strong><br>";
    foreach ($users as $user) {
        echo "ID: " . $user['id'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
        echo "Password Hash Length: " . strlen($user['password']) . "<br><br>";
        
        // Test password verification
        $test_password = 'admin123';
        $verify_result = password_verify($test_password, $user['password']);
        echo "Password 'admin123' verification result: " . ($verify_result ? "✅ Valid" : "❌ Invalid") . "<br><br>";
    }
    
    // Check farmers table
    $farmers = $pdo->query("SELECT * FROM farmers")->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Found " . count($farmers) . " farmers in database<br><br>";
    
    echo "<strong>Farmer Details:</strong><br>";
    foreach ($farmers as $farmer) {
        echo "ID: " . $farmer['id'] . "<br>";
        echo "User ID: " . $farmer['user_id'] . "<br>";
        echo "Phone: " . $farmer['phone'] . "<br>";
        echo "Location: " . $farmer['location'] . "<br><br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage();
    error_log("Database error in verify_login.php: " . $e->getMessage());
}
?>
