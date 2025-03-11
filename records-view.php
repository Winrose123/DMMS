<?php
//start the session
session_start();
if(!isset($_SESSION['user'])) header('location: login.php');
$_SESSION['table'] = 'milk_records';
$milk_records = include('database/show.php');

//var_dump($milk_records);
//die;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> View records - Dairy Milk Management System</title>
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
                <h1 class="section-header"><i class="fa fa-list"></i> List of Records</h1>
                <div class="section_content">
<div class="users">
<table>
<thead>
    <tr>
    <th>#</th>
    <th>Image</th>
    <th>Farmer Name</th>
    <th>Quantity in Liters</th>
    <th>Price per liter</th>
    <th>Created At</th>
    <th>Action</th>
    </tr>
</thead>
<tbody>
    <?php foreach($milk_records as $index => $milk_record){?>
        <tr>
        <td><?= $index + 1?></td>
        <td class="firstName">
            <img class="recordImages" src=" uploads/records/<?= $milk_record['image'] ?>" alt=""/>
        </td>
        <td>
            <?php
            $recordid = $milk_record['farmer_id'];
            $stmt = $conn->prepare("SELECT * FROM farmers WHERE id = $recordid ");
            $stmt->execute();
            $row = $stmt->Fetch(PDO::FETCH_ASSOC);
            
            $farmer_id_name = $row['name'];

            echo $recordid,' ', ':', ' ', $farmer_id_name;
            
            ?>
           
        </td>
        <td class="email"><?= $milk_record['quantity_liters'] ?></td>
        <td class="email"><?= $milk_record['price_per_liter'] ?></td>
        <td><?= date('M d, Y @ h:i:s A', strtotime($milk_record['created_at'])) ?></td>

        <td>
            <a href="" class="updateRecord" data-recordid="<?= $milk_record['id']?>"><i class="fa fa-pencil"></i>Edit</a> |
            <a href="" class="deleteRecord" data-name="<?= $milk_record['farmer_id']?>" data-recordid="<?=$milk_record['id']?>" ?><i class="fa fa-trash"></i>Delete</a>
        </td>
    </tr>
        <?php }  ?>
   
</tbody>
</table>
<p class="usercount"><?= count($milk_records)?> Records </p>
</div>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>
   </div> 
   <?php include('partials/app-scripts.php');?>
   <script>
function script() {
    this.registerEvents = function() {
        document.addEventListener('click', function(e) {
            const targetElement = e.target;
            const classList = targetElement.classList;

            if (classList.contains('deleteRecord')) {
                e.preventDefault();
                const recordId = targetElement.dataset.recordid;
                const recordName = targetElement.dataset.name;

                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: 'Confirm Deletion',
                    message: `Are you sure you want to delete the record for Farmer ID ${recordName}?`,
                    btnOKLabel: 'Delete',
                    btnCancelLabel: 'Cancel',
                    callback: function(isDelete) {
                        if (isDelete) {
                            $.ajax({
                                method: 'POST',
                                url: 'database/delete.php',
                                data: {
                                    id: recordId,
                                    table: 'milk_records'
                                },
                                dataType: 'json',
                                success: function(data) {
                                    const message = data.success ? 
                                        'Record deleted successfully.' : 
                                        data.message || 'Failed to delete record.';
                                    BootstrapDialog.alert({
                                        type: data.success ? 
                                            BootstrapDialog.TYPE_SUCCESS : 
                                            BootstrapDialog.TYPE_DANGER,
                                        message: message,
                                        callback: function() {
                                            if (data.success) location.reload();
                                        }
                                    });
                                },
                                error: function(xhr) {
                                    BootstrapDialog.alert({
                                        type: BootstrapDialog.TYPE_DANGER,
                                        message: 'Server Error: ' + xhr.responseText
                                    });
                                }
                            });
                        }
                    }
                });
            }
        });
    };

    this.initialize = function() {
        this.registerEvents();
    };
}

const scriptInstance = new script();
scriptInstance.initialize();
</script>

</body>
</html>