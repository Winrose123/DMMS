<?php
require_once 'database/connection.php';
require_once 'database/auth.php';

requireAdmin();

// Get total farmers count
$farmers_count = $pdo->query("SELECT COUNT(*) FROM farmers")->fetchColumn();

// Get today's milk collection
$today_milk = $pdo->query("
    SELECT SUM(quantity_liters) as total_liters, 
           SUM(quantity_liters * price_per_liter) as total_value,
           COUNT(DISTINCT farmer_id) as farmers_count
    FROM milk_records 
    WHERE DATE(created_at) = CURRENT_DATE")->fetch();

// Get pending loan requests
$pending_loans = $pdo->query("
    SELECT COUNT(*) as count, SUM(amount) as total_amount 
    FROM loans 
    WHERE status = 'pending'")->fetch();

// Get pending withdrawals
$pending_withdrawals = $pdo->query("
    SELECT COUNT(*) as count, SUM(amount) as total_amount 
    FROM withdrawals 
    WHERE status = 'pending'")->fetch();

// Get monthly statistics
$monthly_stats = $pdo->query("
    SELECT 
        SUM(quantity_liters) as total_liters,
        SUM(quantity_liters * price_per_liter) as total_value,
        COUNT(DISTINCT farmer_id) as active_farmers
    FROM milk_records 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE)
    AND YEAR(created_at) = YEAR(CURRENT_DATE)")->fetch();

// Get recent milk records with proper joins
$recent_records = $pdo->query("
    SELECT mr.*, CONCAT(u.first_name, ' ', u.last_name) as farmer_name 
    FROM milk_records mr
    JOIN farmers f ON mr.farmer_id = f.id
    JOIN users u ON f.user_id = u.id
    ORDER BY mr.created_at DESC 
    LIMIT 5")->fetchAll();

// Get recent withdrawal requests
$recent_withdrawals = $pdo->query("
    SELECT w.*, CONCAT(u.first_name, ' ', u.last_name) as farmer_name 
    FROM withdrawals w
    JOIN farmers f ON w.farmer_id = f.id
    JOIN users u ON f.user_id = u.id
    ORDER BY w.requested_at DESC 
    LIMIT 5")->fetchAll();

// Get monthly production data for chart
$monthly_production = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(quantity_liters) as total_liters,
        SUM(quantity_liters * price_per_liter) as total_value,
        COUNT(DISTINCT farmer_id) as farmers_count
    FROM milk_records 
    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC")->fetchAll();

// Get farmer distribution data - Fixed with derived table
$farmer_distribution = $pdo->query("
    SELECT 
        activity_level,
        COUNT(*) as farmer_count
    FROM (
        SELECT 
            f.id,
            CASE 
                WHEN COUNT(mr.id) = 0 THEN 'Inactive'
                WHEN COUNT(mr.id) < 10 THEN 'Low Activity'
                WHEN COUNT(mr.id) < 20 THEN 'Moderate'
                ELSE 'High Activity'
            END as activity_level
        FROM farmers f
        LEFT JOIN milk_records mr ON f.id = mr.farmer_id 
            AND MONTH(mr.created_at) = MONTH(CURRENT_DATE)
            AND YEAR(mr.created_at) = YEAR(CURRENT_DATE)
        GROUP BY f.id
    ) activity_stats
    GROUP BY activity_level
    ORDER BY 
        CASE activity_level
            WHEN 'Inactive' THEN 1
            WHEN 'Low Activity' THEN 2
            WHEN 'Moderate' THEN 3
            WHEN 'High Activity' THEN 4
        END")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="record_milk.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>Record Milk
                            </a>
                            <a href="manage_farmers.php" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-users me-1"></i>Manage Farmers
                            </a>
                            <a href="loan_requests.php" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-hand-holding-usd me-1"></i>Loan Requests
                            </a>
                            <a href="withdrawal_requests.php" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-money-check-alt me-1"></i>Withdrawals
                            </a>
                            <a href="reports.php" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-chart-bar me-1"></i>Reports
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card border-primary h-100">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <i class="fas fa-users fa-fw me-2"></i>Total Farmers
                                </h5>
                                <p class="card-text display-6"><?= number_format($farmers_count) ?></p>
                                <p class="card-text text-muted">
                                    <?= number_format($monthly_stats['active_farmers'] ?? 0) ?> active this month
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-success h-100">
                            <div class="card-body">
                                <h5 class="card-title text-success">
                                    <i class="fas fa-milk fa-fw me-2"></i>Today's Collection
                                </h5>
                                <p class="card-text display-6"><?= number_format($today_milk['total_liters'] ?? 0, 1) ?> L</p>
                                <p class="card-text text-muted">
                                    From <?= number_format($today_milk['farmers_count'] ?? 0) ?> farmers
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-info h-100">
                            <div class="card-body">
                                <h5 class="card-title text-info">
                                    <i class="fas fa-money-bill-wave fa-fw me-2"></i>Monthly Revenue
                                </h5>
                                <p class="card-text display-6">Ksh <?= number_format($monthly_stats['total_value'] ?? 0) ?></p>
                                <p class="card-text text-muted">
                                    <?= number_format($monthly_stats['total_liters'] ?? 0, 1) ?> L collected
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-warning h-100">
                            <div class="card-body">
                                <h5 class="card-title text-warning">
                                    <i class="fas fa-hand-holding-usd fa-fw me-2"></i>Pending Loans
                                </h5>
                                <p class="card-text display-6"><?= number_format($pending_loans['count'] ?? 0) ?></p>
                                <p class="card-text text-muted">
                                    Ksh <?= number_format($pending_loans['total_amount'] ?? 0) ?> total
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card border-danger h-100">
                            <div class="card-body">
                                <h5 class="card-title text-danger">
                                    <i class="fas fa-money-check-alt fa-fw me-2"></i>Pending Withdrawals
                                </h5>
                                <p class="card-text display-6"><?= number_format($pending_withdrawals['count'] ?? 0) ?></p>
                                <p class="card-text text-muted">
                                    Ksh <?= number_format($pending_withdrawals['total_amount'] ?? 0) ?> total
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line fa-fw me-2"></i>Monthly Production Trends
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="productionChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-pie fa-fw me-2"></i>Farmer Activity Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="farmerChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Records -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history fa-fw me-2"></i>Recent Milk Records
                        </h5>
                        <a href="record_milk.php" class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Farmer</th>
                                        <th>Quantity (L)</th>
                                        <th>Price/L</th>
                                        <th>Total</th>
                                        <th>Image</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_records)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                No records found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_records as $record): ?>
                                            <tr>
                                                <td><?= date('M d, Y H:i', strtotime($record['created_at'])) ?></td>
                                                <td><?= htmlspecialchars($record['farmer_name']) ?></td>
                                                <td><?= number_format($record['quantity_liters'], 2) ?></td>
                                                <td>Ksh <?= number_format($record['price_per_liter'], 2) ?></td>
                                                <td>Ksh <?= number_format($record['quantity_liters'] * $record['price_per_liter'], 2) ?></td>
                                                <td>
                                                    <?php if ($record['image_url']): ?>
                                                        <a href="<?= $record['image_url'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-image"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Withdrawals -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-money-check-alt fa-fw me-2"></i>Recent Withdrawal Requests
                        </h5>
                        <a href="withdrawal_requests.php" class="btn btn-sm btn-outline-danger">
                            View All
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Requested</th>
                                        <th>Farmer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_withdrawals)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                No withdrawal requests found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_withdrawals as $withdrawal): ?>
                                            <tr>
                                                <td><?= date('M d, Y H:i', strtotime($withdrawal['requested_at'])) ?></td>
                                                <td><?= htmlspecialchars($withdrawal['farmer_name']) ?></td>
                                                <td>Ksh <?= number_format($withdrawal['amount'], 2) ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = [
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger'
                                                    ][$withdrawal['status']];
                                                    ?>
                                                    <span class="badge bg-<?= $status_class ?>">
                                                        <?= ucfirst($withdrawal['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($withdrawal['status'] === 'pending'): ?>
                                                        <a href="withdrawal_requests.php" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> Review
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
    
    <script>
        // Production Chart
        const productionCtx = document.getElementById('productionChart').getContext('2d');
        new Chart(productionCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(function($item) { 
                    return date('M Y', strtotime($item['month'] . '-01')); 
                }, $monthly_production)) ?>,
                datasets: [{
                    label: 'Total Liters',
                    data: <?= json_encode(array_map(function($item) { 
                        return $item['total_liters']; 
                    }, $monthly_production)) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: '#3b82f680',
                    fill: true
                }, {
                    label: 'Active Farmers',
                    data: <?= json_encode(array_map(function($item) { 
                        return $item['farmers_count']; 
                    }, $monthly_production)) ?>,
                    borderColor: '#059669',
                    backgroundColor: '#34d39980',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Farmer Distribution Chart
        const farmerCtx = document.getElementById('farmerChart').getContext('2d');
        new Chart(farmerCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_map(function($item) { 
                    return $item['activity_level']; 
                }, $farmer_distribution)) ?>,
                datasets: [{
                    data: <?= json_encode(array_map(function($item) { 
                        return $item['farmer_count']; 
                    }, $farmer_distribution)) ?>,
                    backgroundColor: [
                        '#dc2626',  // Inactive - Red
                        '#f59e0b',  // Low Activity - Orange
                        '#2563eb',  // Moderate - Blue
                        '#059669'   // High Activity - Green
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>