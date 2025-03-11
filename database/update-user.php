<?php
$data = $_POST;
$user_id= (int) $data['user_id'];
$first_name= $data['f_name'];
$last_name= $data['l_name'];
$email= $data['email'];
//$now = date('Y-m-d H:i:s');



try {
    $sql = "UPDATE Users SET email =?, last_name =?, first_name =?, updated_at=? WHERE id =? ";

    include('connection.php');
    $conn->prepare($sql)->execute([$email, $last_name, $first_name, date('Y-m-d H:i:s'), $user_id]);

       // $conn->exec($command);

       echo json_encode([
            'success'=> true,
            'message'=> $first_name .' '. $last_name . ' '.  ' successfully updated.'
        ]);
    
    } catch (PDOException $e) {
       echo json_encode([
            'success'=> false,
            'message'=> 'Error processing your request!'
        ]); 
    }
//var_dump($user_id);
?>