<?php
require_once 'database/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Access Denied</h1>
                </div>

                <div class="alert alert-danger">
                    <h4 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Access Denied
                    </h4>
                    <p>You do not have permission to access this page.</p>
                    <hr>
                    <p class="mb-0">
                        Please contact your administrator if you believe this is an error.
                        <br>
                        <a href="logout.php" class="alert-link">Click here to logout</a> and try again with a different account.
                    </p>
                </div>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
</body>
</html>
