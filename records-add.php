<?php
//start the session
session_start();
if(!isset($_SESSION['user'])) header('location: login.php');

$_SESSION['table'] = 'milk_records';
$_SESSION['redirect_to'] = 'records-add.php';

$user = $_SESSION['user'];
//$users = include('database/show-users.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Add record - Dairy Milk Management System</title>
   <?php include('partials/app-header-scripts.php');?>
</head>
<body>
   <div id="dashboardMainContainer" id="dashboardMainContainer">
    <?php include('partials/app-sidebar.php') ?>
 <!--sidebar-->
    <div class="dashboard_content_container">
    <?php include('partials/app-topnav.php') ?> 
        <div class="dashboard_content">
        <div class="dashboard_content_main">
            <div class="row">
                <div class="column column-12">
                    <h1 class="section-header"><i class="fa fa-plus"></i> Create record</h1>
          

<div class="userAddFormContainer">
<form action="database/add.php" method="POST" class="appForm" enctype="multipart/form-data" >
<div class="appFormInputContainer">
<label for="first_name">Farmer id</label>
<input type="text" class="appFormInput" id="farmer_id" name="farmer_id"/>
</div>
<div class="appFormInputContainer">
<label for="first_name">Quantity in Liters</label>
<input type="text" class="appFormInput" id="quantity_liters" name="quantity_liters"/>
</div>
<div class="appFormInputContainer">
<label for="last_name">Price per liter</label>
<input type="text" class="appFormInput" id="price_per_liter" name="price_per_liter"/>
</div>
<div class="appFormInputContainer">
<label for="last_name">Record Image</label>
<input type="file"  name="image"/>
</div>

<button type="submit" class="appBtn"> <i class="fa fa-plus"></i>Add Record</button>

</form>
<?php
if(isset($_SESSION['response'])){
$response_message = $_SESSION['response'] ['message'] ;
$is_success = $_SESSION['response'] ['success'] ;
?>
<div class="responseMessage">
<p class=" responseMessage <?=$is_success ? 'responseMessage_success' : 'responseMessage_error' ?>" >
<?= $response_message?>
</p>

</div>
<?php unset($_SESSION['response']); }?>
</div>


                </div>
           
            </div>
            </div>
        </div>
    </div>
   </div> 
   <?php include('partials/app-scripts.php');?>
</body>
</html>