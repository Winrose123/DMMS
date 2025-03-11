<?php
//start the session
session_start();
if(!isset($_SESSION['user'])) header('location: login.php');
$_SESSION['table'] = 'users';

//$user = $_SESSION['user'];
$_SESSION['table'] = 'users';
$users = include('database/show.php');
//var_dump($users);
//die;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> View users - Dairy Milk Management System</title>
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
                <h1 class="section-header"><i class="fa fa-list"></i> List of users</h1>
                <div class="section_content">
<div class="users">
<table>
<thead>
    <tr>
    <th>#</th>
    <th>First Name</th>
    <th>Last Name</th>
    <th>Email</th>
    <th>Created At</th>
    <th>Updated At</th>
    <th>Action</th>
    </tr>
</thead>
<tbody>
    <?php foreach($users as $index => $user){?>
        <tr>
        <td><?= $index + 1?></td>
        <td class="firstName"><?= $user['first_name'] ?></td>
        <td class="lastName"><?= $user['last_name'] ?></td>
        <td class="email"><?= $user['email'] ?></td>
        <td><?= date('M d, Y @ h:i:s A', strtotime($user['created_at'])) ?></td>
        <td><?= date('M d, Y @ h:i:s A', strtotime($user['updated_at'])) ?></td>

        <td>
            <a href="" class="updateUser" data-userid="<?= $user['id']?>"><i class="fa fa-pencil"></i>Edit</a> |
            <a href="" class="deleteUser" data-userid="<?= $user['id']?>" data-fname="<?=$user['first_name']
            ?>" data-lname="<?=$user['last_name']?>"><i class="fa fa-trash"></i>Delete</a>
        </td>
    </tr>
        <?php }  ?>
   
</tbody>
</table>
<p class="usercount"><?= count($users)?> Users </p>
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
function script(){

    this.initialize = function(){
        this.registerEvents();
    };

    this.registerEvents = function(){
        document.addEventListener('click', function(e){
            targetElement = e.target;
            classList = targetElement.classList;

            if(classList.contains('deleteUser')){
                e.preventDefault();
                userId = targetElement.dataset.userid;
                fname = targetElement.dataset.fname;
                lname = targetElement.dataset.lname;
                fullname = fname + ' ' + lname;

                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: 'Confirm Deletion',
                    message: 'Are you sure you want to delete <strong>' + fullname + '? </strong>',
                    btnOKLabel: 'Delete',
                    btnCancelLabel: 'Cancel',
                    callback: function (isDelete) {
                        if (isDelete) { 
                            $.ajax({
                                method: 'POST',
                                url: 'database/delete-user.php',
                                data: {
                                    user_id: userId,
                                    f_name: fname,
                                    l_name: lname
                                },
                                dataType: "json",
                                success: function (data) {
                                    if (data.success) {
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_SUCCESS,
                                            message: data.message,
                                            callback: function () {
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_DANGER,
                                            message: data.message
                                        });
                                    }
                                },
                                error: function () {
                                    BootstrapDialog.alert({
                                        type: BootstrapDialog.TYPE_DANGER,
                                        message: "An error occurred while processing your request."
                                    });
                                }
                            });
                        }
                    }
                });
            }

            if(classList.contains('updateUser')){
                e.preventDefault();

                //Get data
                firstName = targetElement.closest('tr').querySelector('td.firstName').innerHTML;
                lastName = targetElement.closest('tr').querySelector('td.lastName').innerHTML;
                email = targetElement.closest('tr').querySelector('td.email').innerHTML;
                userId = targetElement.dataset.userid;

                BootstrapDialog.confirm({
                    title: 'Update ' + firstName + ' ' + lastName,
                    message:  '<form >\
                    <div class="form-group">\
                    <label for="firstName">First Name</label>\
                    <input type="text" class="form-control" id="firstName" value="' +firstName+ '">\
                    </div>\
                    <div class="form-group">\
                    <label for="lastName">Last Name</label>\
                    <input type="text" class="form-control" id="lastName" value="' +lastName+ '">\
                    </div>\
                    <div class="form-group">\
                    <label for="email">Email Address</label>\
                    <input type="email" class="form-control" id="emailUpdate" value="' +email+ '">\
                    </div>\
                    </form>',
                    callback: function(isUpdate){  // ✅ Fixed typo: Changed `isUdate` to `isUpdate`
                        if(isUpdate){ 
                            $.ajax({
                                method: 'POST',
                                url:'database/update-user.php',
                                data: {
                                    user_id: userId,
                                    f_name: document.getElementById('firstName').value,
                                    l_name: document.getElementById('lastName').value,
                                    email: document.getElementById('emailUpdate').value
                                },
                                dataType: "json",
                                success: function(data){
                                    if (data.success) {
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_SUCCESS,
                                            message: data.message,
                                            callback: function () {
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        BootstrapDialog.alert({
                                            type: BootstrapDialog.TYPE_DANGER,
                                            message: data.message
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            }
        });
    };
}

var script = new script();
script.initialize();
</script>

</body>
</html>