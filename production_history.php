<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireFarmer();

$farmer_id = $_SESSION['farmer_id'];

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Get milk records for date range
$milk_records = $pdo->prepare("
    SELECT * 
    FROM milk_records 
    WHERE farmer_id = ? 
    AND DATE(created_at) BETWEEN ? AND ?
    ORDER BY created_at DESC");
$milk_records->execute([$farmer_id, $start_date, $end_date]);
$records = $milk_records->fetchAll();

// Calculate statistics
$total_liters = 0;
$total_income = 0;
$avg_price = 0;
$record_count = count($records);

foreach($records as $record) {
    $total_liters += $record['quantity_liters'];
    $total_income += $record['quantity_liters'] * $record['price_per_liter'];
}
$avg_price = $record_count > 0 ? $total_income / $total_liters : 0;

// Get date coverage
$date_coverage = $pdo->prepare("
    SELECT 
        MIN(DATE(created_at)) as first_record,
        MAX(DATE(created_at)) as last_record,
        COUNT(DISTINCT DATE(created_at)) as total_days
    FROM milk_records 
    WHERE farmer_id = ?");
$date_coverage->execute([$farmer_id]);
$coverage = $date_coverage->fetch();

// Handle null dates in coverage
$first_record = $coverage['first_record'] ? date('M j, Y', strtotime($coverage['first_record'])) : 'No records yet';
$last_record = $coverage['last_record'] ? date('M j, Y', strtotime($coverage['last_record'])) : 'No records yet';
$total_days = $coverage['total_days'] ?? 0;

// Get daily summary
$daily_summary = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        SUM(quantity_liters) as total_liters,
        AVG(price_per_liter) as avg_price,
        SUM(quantity_liters * price_per_liter) as total_income
    FROM milk_records 
    WHERE farmer_id = ? 
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC");
$daily_summary->execute([$farmer_id, $start_date, $end_date]);
$daily_records = $daily_summary->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production History - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Production History</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <form class="row g-3 align-items-center">
                            <div class="col-auto">
                                <div class="input-group">
                                    <span class="input-group-text">From</span>
                                    <input type="text" class="form-control" id="start_date" name="start_date" 
                                        value="<?= htmlspecialchars($start_date) ?>" required>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="input-group">
                                    <span class="input-group-text">To</span>
                                    <input type="text" class="form-control" id="end_date" name="end_date" 
                                        value="<?= htmlspecialchars($end_date) ?>" required>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tint me-2"></i>Total Production
                                </h5>
                            </div>
                            <div class="card-body">
                                <h3 class="card-text"><?= number_format($total_liters, 2) ?> Liters</h3>
                                <p class="text-muted">Total milk recorded</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card border-success h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-money-bill-wave me-2"></i>Total Income
                                </h5>
                            </div>
                            <div class="card-body">
                                <h3 class="card-text">KES <?= number_format($total_income, 2) ?></h3>
                                <p class="text-muted">Revenue from milk sales</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card border-info h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>Average Price
                                </h5>
                            </div>
                            <div class="card-body">
                                <h3 class="card-text">KES <?= number_format($avg_price, 2) ?></h3>
                                <p class="text-muted">Per liter</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-danger h-100">
                            <div class="card-header bg-danger text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-check me-2"></i>Coverage
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text">First Record: <?= $first_record ?></p>
                                <p class="card-text">Last Record: <?= $last_record ?></p>
                                <p class="card-text">Total Days: <?= $total_days ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Summary -->
                <div class="card border-success mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-day me-2"></i>Daily Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($daily_records)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No records found for the selected date range.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Total Liters</th>
                                            <th>Average Price</th>
                                            <th>Total Income</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($daily_records as $record): ?>
                                            <tr>
                                                <td><?= date('M j, Y', strtotime($record['date'])) ?></td>
                                                <td><?= number_format($record['total_liters'], 2) ?> L</td>
                                                <td>KES <?= number_format($record['avg_price'], 2) ?></td>
                                                <td>KES <?= number_format($record['total_income'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#start_date", {
            dateFormat: "Y-m-d"
        });
        flatpickr("#end_date", {
            dateFormat: "Y-m-d"
        });
    </script>
</body>
</html>
