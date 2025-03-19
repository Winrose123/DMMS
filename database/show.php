<?php
include('connection.php');

$table_name = $_SESSION['table'];

// For milk records, join with farmers and users tables to get complete information
if ($table_name === 'milk_records') {
    $stmt = $pdo->prepare("
        SELECT 
            mr.*,
            f.id as farmer_id,
            CONCAT(u.first_name, ' ', u.last_name) as farmer_name
        FROM milk_records mr
        JOIN farmers f ON mr.farmer_id = f.id
        JOIN users u ON f.user_id = u.id
        ORDER BY mr.created_at DESC
    ");
} else {
    $stmt = $pdo->prepare("SELECT * FROM $table_name ORDER BY created_at DESC");
}

$stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);

return $stmt->fetchAll();
?>