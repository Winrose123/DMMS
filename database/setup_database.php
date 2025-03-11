<?php
require_once 'connection.php';

try {
    // Read and execute schema.sql
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);
    echo "Database schema created successfully!\n";

    // Read and execute sample_data.sql
    $sample_data = file_get_contents(__DIR__ . '/sample_data.sql');
    $pdo->exec($sample_data);
    echo "Sample data imported successfully!\n";

    // Create admin user
    $email = 'admin@dairy.com';
    $password = password_hash('admin123', PASSWORD_DEFAULT);

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

    echo "\nDatabase setup completed successfully!\n";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
