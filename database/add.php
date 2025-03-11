<?php
session_start();

//Capture the table mappings
include('table_columns.php');

//var_dump($_POST);
//var_dump($_FILES);
//die;

//Capture the table name.
$table_name = $_SESSION ['table'];
$columns = $table_columns_mapping[$table_name];

//Loop through the columns
$db_arr = [];
$user = $_SESSION['user'];
foreach ($columns as $column) {
    if (in_array($column, ['created_at'])) { 
        $value = date('Y-m-d H:i:s'); // Use PHP timestamp
    } 
    else if ($column === 'farmer_id') {
        if (!isset($_POST['farmer_id']) || empty($_POST['farmer_id'])) {
            $value = 1; // Default farmer_id (Ensure this exists in the farmers table)
        } else {
            $value = $_POST['farmer_id'];
        }
    }  
    else if ($column == 'password') { 
        $value = password_hash($_POST[$column], PASSWORD_DEFAULT);
    }
    else if ($column == 'image'){
        //upload or move a file to our directory
        $target_dir = "../uploads/records/";
        $file_data = $_FILES[$column];

        $file_name = $file_data['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_name = 'record-' . time() . '.' . $file_ext;
        //var_dump($file_ext);
        //die;

        $check = getimagesize($file_data['tmp_name']);

         //Move the file
         if($check){
            if( move_uploaded_file($file_data['tmp_name'], $target_dir . $file_name )){
                // Save the filename to the database.
                $value = $file_name;

            }
          
        }else{
            // Do not move the file.s

        }
 
    }
    else {
        $value = isset($_POST[$column]) ? $_POST[$column] : ''; 
    }
    $db_arr[$column] = $value;
}

$table_properties = implode(" , ", array_keys($db_arr));
$table_placeholders = ':' . implode(", :", array_keys($db_arr));


//Adding the record
try {
$sql = "INSERT INTO $table_name ($table_properties) 
VALUES ($table_placeholders)";

include('connection.php');

$stmt = $conn->prepare($sql);
$stmt->execute($db_arr);


$response =[
        'success'=> true,
        'message'=> 'Successfully added to the system.'
    ];

} catch (PDOException $e) {
    $response =[
        'success'=> false,
        'message'=> $e->getMessage() 
    ];  
}
$_SESSION['response']= $response;
header('location: ../'. $_SESSION['redirect_to']);

?>