<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

// Get record ID and validate
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    setFlashMessage('danger', 'Invalid record ID.');
    header('Location: record_milk.php');
    exit();
}

// Fetch record details with farmer information
try {
    $stmt = $pdo->prepare("
        SELECT mr.*, 
               CONCAT(u.first_name, ' ', u.last_name) as farmer_name,
               f.phone,
               f.location
        FROM milk_records mr 
        JOIN farmers f ON mr.farmer_id = f.id 
        JOIN users u ON f.user_id = u.id 
        WHERE mr.id = ?
    ");
    $stmt->execute([$id]);
    $record = $stmt->fetch();
    
    if (!$record) {
        setFlashMessage('danger', 'Record not found.');
        header('Location: record_milk.php');
        exit();
    }
} catch (PDOException $e) {
    setFlashMessage('danger', 'Error fetching record: ' . $e->getMessage());
    header('Location: record_milk.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Milk Record - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Milk Record Details</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="record_milk.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Records
                        </a>
                    </div>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Record Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Farmer Details</h6>
                                        <p class="mb-1">
                                            <strong>Name:</strong> 
                                            <?= htmlspecialchars($record['farmer_name']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Phone:</strong> 
                                            <?= htmlspecialchars($record['phone']) ?>
                                        </p>
                                        <p class="mb-3">
                                            <strong>Location:</strong> 
                                            <?= htmlspecialchars($record['location']) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Record Details</h6>
                                        <p class="mb-1">
                                            <strong>Date:</strong> 
                                            <?= date('M d, Y H:i', strtotime($record['created_at'])) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Quantity:</strong> 
                                            <?= number_format($record['quantity_liters'], 2) ?> Liters
                                        </p>
                                        <p class="mb-1">
                                            <strong>Price/Liter:</strong> 
                                            Ksh <?= number_format($record['price_per_liter'], 2) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Total Amount:</strong> 
                                            Ksh <?= number_format($record['quantity_liters'] * $record['price_per_liter'], 2) ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if ($record['image_url']): ?>
                                    <div class="mt-4">
                                        <h6 class="text-muted mb-2">Record Image</h6>
                                        <img src="<?= htmlspecialchars($record['image_url']) ?>" 
                                             alt="Milk Record Image" 
                                             class="img-fluid rounded"
                                             style="max-height: 300px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Quick Stats
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get farmer's average production
                                $avg_stmt = $pdo->prepare("
                                    SELECT AVG(quantity_liters) as avg_quantity
                                    FROM milk_records
                                    WHERE farmer_id = ?
                                    AND DATE(created_at) >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                                ");
                                $avg_stmt->execute([$record['farmer_id']]);
                                $avg_data = $avg_stmt->fetch();
                                
                                // Get farmer's total production today
                                $today_stmt = $pdo->prepare("
                                    SELECT SUM(quantity_liters) as total_quantity
                                    FROM milk_records
                                    WHERE farmer_id = ?
                                    AND DATE(created_at) = CURRENT_DATE
                                ");
                                $today_stmt->execute([$record['farmer_id']]);
                                $today_data = $today_stmt->fetch();
                                ?>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">30-Day Average Production</h6>
                                    <h3><?= number_format($avg_data['avg_quantity'], 2) ?> L</h3>
                                </div>
                                
                                <div class="mb-3">
                                    <h6 class="text-muted">Today's Total Production</h6>
                                    <h3><?= number_format($today_data['total_quantity'], 2) ?> L</h3>
                                </div>
                                
                                <div>
                                    <h6 class="text-muted">Comparison to Average</h6>
                                    <?php
                                    $diff = $record['quantity_liters'] - $avg_data['avg_quantity'];
                                    $diff_percent = $avg_data['avg_quantity'] > 0 ? 
                                        ($diff / $avg_data['avg_quantity']) * 100 : 0;
                                    $icon_class = $diff >= 0 ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger';
                                    ?>
                                    <p class="mb-0">
                                        <i class="fas <?= $icon_class ?> me-2"></i>
                                        <?= abs(number_format($diff_percent, 1)) ?>% 
                                        <?= $diff >= 0 ? 'above' : 'below' ?> average
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
</body>
</html>
