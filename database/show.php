<?php
include('connection.php');

$table_name = $_SESSION['table'];

//var_dump($table_name);
//die;

$stmt = $conn->prepare("SELECT * FROM $table_name ORDER BY created_at DESC");
$stmt->execute();
$stmt->setFetchMode(PDO::FETCH_ASSOC);

return $stmt->fetchAll();
//var_dump($stmt->fetchAll());
//die;
?>