<?php
header('Content-Type: application/json'); // Ensure JSON response
session_start();

require_once 'connection.php';
require_once 'auth.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit();
}

try {
    // Validate POST data
    $data = $_POST;
    $id = isset($data['id']) ? (int)$data['id'] : null;
    $table = isset($data['table']) ? sanitize($data['table']) : null;

    if (!$id || !$table) {
        throw new Exception("Missing ID or table name.");
    }

    // Validate table name to prevent SQL injection
    $allowed_tables = ['milk_records', 'farmers', 'expenses'];
    if (!in_array($table, $allowed_tables)) {
        throw new Exception("Invalid table name.");
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Delete associated image if it exists (for milk records)
        if ($table === 'milk_records') {
            $image_query = $pdo->prepare("SELECT image_url FROM milk_records WHERE id = :id");
            $image_query->execute(['id' => $id]);
            $image = $image_query->fetchColumn();
            
            if ($image && file_exists($image)) {
                unlink($image);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Record deleted successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No record found with the given ID.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>