<?php
require_once 'database/connection.php';

header('Content-Type: application/json');

if (!isset($_GET['email'])) {
    echo json_encode(['error' => 'Email not provided']);
    exit;
}

$email = strtolower(trim($_GET['email']));

try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE LOWER(email) = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'role' => $result ? $result['role'] : null
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
