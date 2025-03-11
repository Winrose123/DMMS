<?php
require_once 'database/connection.php';
require_once 'database/auth.php';

requireFarmer();

$farmer_id = $_SESSION['farmer_id'];

// Get milk statistics for current month
$milk_stats = $pdo->prepare("
    SELECT 
        SUM(quantity_liters) AS total_liters,
        AVG(price_per_liter) AS avg_price,
        SUM(quantity_liters * price_per_liter) AS total_income
    FROM milk_records 
    WHERE farmer_id = ? 
    AND MONTH(created_at) = MONTH(CURRENT_DATE)
    AND YEAR(created_at) = YEAR(CURRENT_DATE)");
$milk_stats->execute([$farmer_id]);
$stats = $milk_stats->fetch();

// Get expenses for current month
$expenses = $pdo->prepare("
    SELECT SUM(amount) AS total 
    FROM expenses 
    WHERE farmer_id = ? 
    AND MONTH(date) = MONTH(CURRENT_DATE)
    AND YEAR(date) = YEAR(CURRENT_DATE)");
$expenses->execute([$farmer_id]);
$total_expenses = $expenses->fetch()['total'] ?? 0;

// Get loan status
$loan = $pdo->prepare("SELECT * FROM loans WHERE farmer_id = ? ORDER BY requested_at DESC LIMIT 1");
$loan->execute([$farmer_id]);
$loan_status = $loan->fetch();

// Get recent milk records
$records = $pdo->prepare("
    SELECT * 
    FROM milk_records 
    WHERE farmer_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5");
$records->execute([$farmer_id]);
$recent_records = $records->fetchAll();

// Get monthly production data for the last 6 months
$monthly_production = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(quantity_liters) as total_liters,
        SUM(quantity_liters * price_per_liter) as total_income
    FROM milk_records 
    WHERE farmer_id = ? 
    AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC");
$monthly_production->execute([$farmer_id]);
$production_data = $monthly_production->fetchAll();

// Get expense categories for the current month
$expense_categories = $pdo->prepare("
    SELECT 
        description as category,
        SUM(amount) as total
    FROM expenses 
    WHERE farmer_id = ? 
    AND MONTH(date) = MONTH(CURRENT_DATE)
    AND YEAR(date) = YEAR(CURRENT_DATE)
    GROUP BY description");
$expense_categories->execute([$farmer_id]);
$expense_data = $expense_categories->fetchAll();

// Calculate available balance
$balance_query = $pdo->prepare("
    SELECT 
        COALESCE(SUM(mr.quantity_liters * mr.price_per_liter), 0) as total_income,
        COALESCE(SUM(e.amount), 0) as total_expenses,
        COALESCE(SUM(CASE WHEN w.status = 'approved' THEN w.amount ELSE 0 END), 0) as total_withdrawals
    FROM farmers f
    LEFT JOIN milk_records mr ON f.id = mr.farmer_id
    LEFT JOIN expenses e ON f.id = e.farmer_id
    LEFT JOIN withdrawals w ON f.id = w.farmer_id
    WHERE f.id = ?
");
$balance_query->execute([$farmer_id]);
$balance_data = $balance_query->fetch();

$available_balance = $balance_data['total_income'] - $balance_data['total_expenses'] - $balance_data['total_withdrawals'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - DMMS</title>
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
                            <a href="withdraw.php" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-wallet me-1"></i>Withdraw Funds
                            </a>
                            <a href="request_loan.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-hand-holding-usd me-1"></i>Request Loan
                            </a>
                            <a href="add_expense.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-receipt me-1"></i>Add Expense
                            </a>
                            <a href="production_history.php" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-history me-1"></i>View History
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Monthly Statistics -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card border-primary h-100">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <i class="fas fa-milk fa-fw me-2"></i>Monthly Production
                                </h5>
                                <p class="card-text display-6"><?= number_format($stats['total_liters'] ?? 0, 2) ?> L</p>
                                <p class="card-text text-muted">
                                    Avg. Price: Ksh <?= number_format($stats['avg_price'] ?? 0, 2) ?>/L
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-success h-100">
                            <div class="card-body">
                                <h5 class="card-title text-success">
                                    <i class="fas fa-money-bill-wave fa-fw me-2"></i>Monthly Income
                                </h5>
                                <p class="card-text display-6">Ksh <?= number_format($stats['total_income'] ?? 0, 2) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-danger h-100">
                            <div class="card-body">
                                <h5 class="card-title text-danger">
                                    <i class="fas fa-receipt fa-fw me-2"></i>Monthly Expenses
                                </h5>
                                <p class="card-text display-6">Ksh <?= number_format($total_expenses, 2) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card border-info h-100">
                            <div class="card-body">
                                <h5 class="card-title text-info">
                                    <i class="fas fa-wallet fa-fw me-2"></i>Available Balance
                                </h5>
                                <p class="card-text display-6">Ksh <?= number_format($available_balance, 2) ?></p>
                                <a href="withdraw.php" class="btn btn-sm btn-outline-info mt-2">
                                    <i class="fas fa-money-bill-wave me-1"></i>Withdraw
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphical Reports -->
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line fa-fw me-2"></i>Monthly Production & Income Trends
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
                                    <i class="fas fa-chart-pie fa-fw me-2"></i>Expense Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="expenseChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Records -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history fa-fw me-2"></i>Recent Milk Records
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Quantity (L)</th>
                                        <th>Price/L</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($recent_records)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No records found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($recent_records as $record): ?>
                                            <tr>
                                                <td><?= date('Y-m-d', strtotime($record['created_at'])) ?></td>
                                                <td><?= number_format($record['quantity_liters'], 2) ?></td>
                                                <td>Ksh <?= number_format($record['price_per_liter'], 2) ?></td>
                                                <td>Ksh <?= number_format($record['quantity_liters'] * $record['price_per_liter'], 2) ?></td>
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
    // Production & Income Chart
    const productionCtx = document.getElementById('productionChart').getContext('2d');
    new Chart(productionCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($production_data, 'month')) ?>,
            datasets: [{
                label: 'Production (Liters)',
                data: <?= json_encode(array_column($production_data, 'total_liters')) ?>,
                borderColor: '#2563eb',
                backgroundColor: '#2563eb20',
                yAxisID: 'y',
                fill: true
            }, {
                label: 'Income (Ksh)',
                data: <?= json_encode(array_column($production_data, 'total_income')) ?>,
                borderColor: '#16a34a',
                backgroundColor: '#16a34a20',
                yAxisID: 'y1',
                fill: true
            }]
        },
        options: {
            responsive: true,
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
                        text: 'Income (Ksh)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });

    // Expense Distribution Chart
    const expenseCtx = document.getElementById('expenseChart').getContext('2d');
    new Chart(expenseCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($expense_data, 'category')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($expense_data, 'total')) ?>,
                backgroundColor: [
                    '#2563eb', '#16a34a', '#dc2626', '#ca8a04', '#7c3aed',
                    '#be185d', '#0891b2', '#854d0e', '#a21caf', '#115e59'
                ]
            }]
        },
        options: {
            responsive: true,
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
