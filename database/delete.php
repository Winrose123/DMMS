<?php
header('Content-Type: application/json'); // Ensure JSON response
session_start();

try {
    include('connection.php');
    
    // Validate POST data
    $data = $_POST;
    $id = isset($data['id']) ? (int)$data['id'] : null;
    $table = isset($data['table']) ? $data['table'] : null;

    if (!$id || !$table) {
        throw new Exception("Missing ID or table name.");
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM `$table` WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
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