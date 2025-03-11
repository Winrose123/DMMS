<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

// Get date range filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Milk Production Stats
$milk_stats = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT farmer_id) as total_farmers,
        COUNT(*) as total_records,
        SUM(quantity_liters) as total_liters,
        SUM(quantity_liters * price_per_liter) as total_revenue,
        AVG(quantity_liters) as avg_liters_per_record,
        AVG(price_per_liter) as avg_price_per_liter
    FROM milk_records
    WHERE DATE(created_at) BETWEEN ? AND ?");
$milk_stats->execute([$start_date, $end_date]);
$milk_stats = $milk_stats->fetch();

// Daily Production Trend
$daily_trend = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        SUM(quantity_liters) as total_liters,
        SUM(quantity_liters * price_per_liter) as total_revenue,
        COUNT(DISTINCT farmer_id) as farmer_count
    FROM milk_records
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date");
$daily_trend->execute([$start_date, $end_date]);
$daily_trend = $daily_trend->fetchAll();

// Top Farmers
$top_farmers = $pdo->prepare("
    SELECT 
        f.id as farmer_id,
        CONCAT(u.first_name, ' ', u.last_name) as farmer_name,
        COUNT(mr.id) as record_count,
        SUM(mr.quantity_liters) as total_liters,
        SUM(mr.quantity_liters * mr.price_per_liter) as total_revenue,
        AVG(mr.quantity_liters) as avg_liters
    FROM farmers f
    JOIN users u ON f.user_id = u.id
    LEFT JOIN milk_records mr ON f.id = mr.farmer_id
    WHERE DATE(mr.created_at) BETWEEN ? AND ?
    GROUP BY f.id, u.first_name, u.last_name
    ORDER BY total_revenue DESC
    LIMIT 5");
$top_farmers->execute([$start_date, $end_date]);
$top_farmers = $top_farmers->fetchAll();

// Expense Summary
$expense_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_expenses,
        SUM(amount) as total_amount,
        COUNT(DISTINCT farmer_id) as farmer_count,
        AVG(amount) as avg_amount
    FROM expenses
    WHERE DATE(date) BETWEEN ? AND ?");
$expense_stats->execute([$start_date, $end_date]);
$expense_stats = $expense_stats->fetch();

// Expense Categories
$expense_categories = $pdo->prepare("
    SELECT 
        category,
        COUNT(*) as expense_count,
        SUM(amount) as total_amount
    FROM expenses
    WHERE DATE(date) BETWEEN ? AND ?
    GROUP BY category
    ORDER BY total_amount DESC");
$expense_categories->execute([$start_date, $end_date]);
$expense_categories = $expense_categories->fetchAll();

// If no expenses yet, provide default categories
if (empty($expense_categories)) {
    $expense_categories = [
        ['category' => 'feed', 'expense_count' => 0, 'total_amount' => 0],
        ['category' => 'medicine', 'expense_count' => 0, 'total_amount' => 0],
        ['category' => 'equipment', 'expense_count' => 0, 'total_amount' => 0],
        ['category' => 'labor', 'expense_count' => 0, 'total_amount' => 0],
        ['category' => 'transport', 'expense_count' => 0, 'total_amount' => 0],
        ['category' => 'other', 'expense_count' => 0, 'total_amount' => 0]
    ];
}

// Loan Statistics
$loan_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_loans,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_loans,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_loans,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_loans,
        SUM(amount) as total_amount,
        SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved_amount,
        COUNT(DISTINCT farmer_id) as farmer_count
    FROM loans
    WHERE DATE(requested_at) BETWEEN ? AND ?");
$loan_stats->execute([$start_date, $end_date]);
$loan_stats = $loan_stats->fetch();

// Initialize stats to 0 if no data
$milk_stats = array_map(function($value) {
    return $value ?? 0;
}, $milk_stats);

$expense_stats = array_map(function($value) {
    return $value ?? 0;
}, $expense_stats);

$loan_stats = array_map(function($value) {
    return $value ?? 0;
}, $loan_stats);

// Prepare data for charts
$chart_dates = [];
$chart_liters = [];
$chart_revenue = [];
$chart_farmers = [];

foreach ($daily_trend as $day) {
    $chart_dates[] = date('M j', strtotime($day['date']));
    $chart_liters[] = round($day['total_liters'], 2);
    $chart_revenue[] = round($day['total_revenue'], 2);
    $chart_farmers[] = $day['farmer_count'];
}

// Prepare expense category data for chart
$expense_labels = [];
$expense_amounts = [];
$expense_counts = [];
foreach ($expense_categories as $category) {
    $expense_labels[] = ucfirst($category['category']);
    $expense_amounts[] = round($category['total_amount'], 2);
    $expense_counts[] = $category['expense_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }
        .card-header {
            background-color: rgba(0, 0, 0, 0.03);
            padding: 1rem;
        }
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Reports & Analytics</h1>
                    
                    <!-- Date Range Filter -->
                    <form class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label for="start_date" class="col-form-label">From</label>
                        </div>
                        <div class="col-auto">
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                value="<?= htmlspecialchars($start_date) ?>">
                        </div>
                        <div class="col-auto">
                            <label for="end_date" class="col-form-label">To</label>
                        </div>
                        <div class="col-auto">
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Milk Production Overview -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card border-primary stats-card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total Production</h6>
                                <h2 class="card-title mb-0">
                                    <?= number_format($milk_stats['total_liters'], 1) ?> L
                                </h2>
                                <small class="text-muted">
                                    <?= number_format($milk_stats['total_records']) ?> records from
                                    <?= number_format($milk_stats['total_farmers']) ?> farmers
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-success stats-card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total Revenue</h6>
                                <h2 class="card-title mb-0">
                                    Ksh <?= number_format($milk_stats['total_revenue'], 2) ?>
                                </h2>
                                <small class="text-muted">
                                    Avg: Ksh <?= number_format($milk_stats['avg_price_per_liter'], 2) ?>/L
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-info stats-card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Total Expenses</h6>
                                <h2 class="card-title mb-0">
                                    Ksh <?= number_format($expense_stats['total_amount'], 2) ?>
                                </h2>
                                <small class="text-muted">
                                    <?= number_format($expense_stats['total_expenses']) ?> expenses from
                                    <?= number_format($expense_stats['farmer_count']) ?> farmers
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-warning stats-card h-100">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Loan Requests</h6>
                                <h2 class="card-title mb-0">
                                    Ksh <?= number_format($loan_stats['total_amount'], 2) ?>
                                </h2>
                                <small class="text-muted">
                                    <?= number_format($loan_stats['total_loans']) ?> requests from
                                    <?= number_format($loan_stats['farmer_count']) ?> farmers
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <!-- Daily Production Trend -->
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Daily Production Trend</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="productionTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Categories -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Expense Distribution</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="expenseCategoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Farmers Table -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top Performing Farmers</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Farmer</th>
                                                <th>Records</th>
                                                <th>Total Production</th>
                                                <th>Average Production</th>
                                                <th>Total Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($top_farmers as $farmer): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($farmer['farmer_name']) ?></td>
                                                    <td><?= number_format($farmer['record_count']) ?></td>
                                                    <td><?= number_format($farmer['total_liters'], 1) ?> L</td>
                                                    <td><?= number_format($farmer['avg_liters'], 1) ?> L</td>
                                                    <td>Ksh <?= number_format($farmer['total_revenue'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Statistics -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Loan Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 text-center">
                                            <h6 class="text-muted">Total Requests</h6>
                                            <h3><?= number_format($loan_stats['total_loans']) ?></h3>
                                            <p class="mb-0">Ksh <?= number_format($loan_stats['total_amount'], 2) ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 text-center bg-success bg-opacity-10">
                                            <h6 class="text-muted">Approved</h6>
                                            <h3><?= number_format($loan_stats['approved_loans']) ?></h3>
                                            <p class="mb-0">Ksh <?= number_format($loan_stats['approved_amount'], 2) ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 text-center bg-warning bg-opacity-10">
                                            <h6 class="text-muted">Pending</h6>
                                            <h3><?= number_format($loan_stats['pending_loans']) ?></h3>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border rounded p-3 text-center bg-danger bg-opacity-10">
                                            <h6 class="text-muted">Rejected</h6>
                                            <h3><?= number_format($loan_stats['rejected_loans']) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
    
    <script>
        // Production Trend Chart
        const productionCtx = document.getElementById('productionTrendChart').getContext('2d');
        new Chart(productionCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_dates) ?>,
                datasets: [{
                    label: 'Production (L)',
                    data: <?= json_encode($chart_liters) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    yAxisID: 'y'
                }, {
                    label: 'Revenue (Ksh)',
                    data: <?= json_encode($chart_revenue) ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Production (Liters)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenue (Ksh)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Expense Category Chart
        const expenseCtx = document.getElementById('expenseCategoryChart').getContext('2d');
        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($expense_labels) ?>,
                datasets: [{
                    data: <?= json_encode($expense_amounts) ?>,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    </script>
</body>
</html>
