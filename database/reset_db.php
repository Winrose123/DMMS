<?php
try {
    // Create a temporary connection without database selection
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop and recreate the database
    $pdo->exec("DROP DATABASE IF EXISTS dmms");
    $pdo->exec("CREATE DATABASE dmms");
    $pdo->exec("USE dmms");

    // Read and execute schema.sql
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);

    // Read and execute initial_data.sql
    $initial_data = file_get_contents(__DIR__ . '/initial_data.sql');
    $pdo->exec($initial_data);

    echo "Database reset successful!<br>";
    echo "You can now log in with:<br>";
    echo "Admin: admin@dmms.com / admin123<br>";
    echo "Farmer: farmer@dmms.com / farmer123<br>";

} catch (PDOException $e) {
    die("Reset failed: " . $e->getMessage());
}
?>
