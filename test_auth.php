<?php
require_once 'database/connection.php';

// Test database connection
try {
    $test_query = $pdo->query("SELECT 1");
    echo "Database connection successful<br>";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Test user creation with a simple password
$test_password = 'test123';
$hashed_password = password_hash($test_password, PASSWORD_DEFAULT);

// Create test user
try {
    // First, clean up any existing test user
    $pdo->exec("DELETE FROM users WHERE email = 'test@dmms.com'");
    
    // Create new test user
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Test', 'User', 'test@dmms.com', $hashed_password, 'admin']);
    echo "Test user created successfully<br>";
    
    // Try to retrieve and verify the user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['test@dmms.com']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User retrieval successful<br>";
        if (password_verify($test_password, $user['password'])) {
            echo "Password verification successful<br>";
        } else {
            echo "Password verification failed<br>";
        }
    } else {
        echo "User retrieval failed<br>";
    }
    
} catch (PDOException $e) {
    die("Test failed: " . $e->getMessage());
}

// Display all users in the database
try {
    $users = $pdo->query("SELECT id, email, role FROM users")->fetchAll();
    echo "<br>All users in database:<br>";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Email: {$user['email']}, Role: {$user['role']}<br>";
    }
} catch (PDOException $e) {
    echo "Error listing users: " . $e->getMessage();
}
?>
