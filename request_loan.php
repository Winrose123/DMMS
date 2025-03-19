<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireFarmer();

$farmer_id = $_SESSION['farmer_id'];

// Check if farmer has any pending loans
$pending_loan = $pdo->prepare("SELECT * FROM loans WHERE farmer_id = ? AND status = 'pending'");
$pending_loan->execute([$farmer_id]);
$has_pending = $pending_loan->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$has_pending) {
    $amount = sanitize($_POST['amount']);
    
    // Get farmer's qualification metrics
    $qualification_check = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT DATE(created_at)) as days_supplied,
            COALESCE(AVG(quantity_liters), 0) as avg_daily_liters,
            COALESCE(SUM(quantity_liters * price_per_liter), 0) as total_income
        FROM milk_records 
        WHERE farmer_id = ? 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $qualification_check->execute([$farmer_id]);
    $qualifications = $qualification_check->fetch();

    // Define minimum requirements
    $min_days_supplied = 5; // Must have supplied milk for at least 5 days in the last 30 days
    $min_daily_liters = 5;   // Must average at least 5 liters per day
    $max_loan_amount = $qualifications['total_income'] * 0.5; // Can borrow up to 50% of monthly income

    $errors = [];
    if ($qualifications['days_supplied'] < $min_days_supplied) {
        $errors[] = "You must supply milk for at least {$min_days_supplied} days in the last 30 days. You have supplied for {$qualifications['days_supplied']} days.";
    }
    if ($qualifications['avg_daily_liters'] < $min_daily_liters) {
        $errors[] = "Your average daily supply must be at least {$min_daily_liters} liters. Your average is " . number_format($qualifications['avg_daily_liters'], 2) . " liters.";
    }
    if ($amount > $max_loan_amount) {
        $errors[] = "Maximum loan amount is 50% of your monthly income (Ksh " . number_format($max_loan_amount, 2) . "). You requested Ksh " . number_format($amount, 2) . ".";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO loans (farmer_id, amount) VALUES (?, ?)");
            $stmt->execute([$farmer_id, $amount]);
            setFlashMessage('success', 'Loan request submitted successfully!');
            header('Location: request_loan.php');
            exit();
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error submitting loan request: ' . $e->getMessage());
        }
    } else {
        setFlashMessage('danger', 'Loan request denied:<br>' . implode('<br>', $errors));
    }
}

// Get farmer's milk production history for last 30 days
$milk_stats = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT DATE(created_at)) as days_supplied,
        COALESCE(SUM(quantity_liters), 0) as total_liters,
        COALESCE(AVG(quantity_liters), 0) as avg_daily_liters,
        COALESCE(SUM(quantity_liters * price_per_liter), 0) as total_income,
        COALESCE(AVG(price_per_liter), 0) as avg_price
    FROM milk_records 
    WHERE farmer_id = ? 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$milk_stats->execute([$farmer_id]);
$stats = $milk_stats->fetch();

// Get loan history
$loan_history = $pdo->prepare("SELECT * FROM loans WHERE farmer_id = ? ORDER BY requested_at DESC");
$loan_history->execute([$farmer_id]);
$loans = $loan_history->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Loan - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Request Loan</h1>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Production Statistics -->
                    <div class="col-md-4 mb-4">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>30 Days Statistics
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Days Supplied</small>
                                    <h3><?= number_format((float)$stats['days_supplied']) ?> days</h3>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Total Production</small>
                                    <h3><?= number_format((float)$stats['total_liters'], 2) ?> L</h3>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Average Daily</small>
                                    <h3><?= number_format((float)$stats['avg_daily_liters'], 2) ?> L</h3>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Average Price</small>
                                    <h3>Ksh <?= number_format((float)$stats['avg_price'], 2) ?></h3>
                                </div>
                                <div>
                                    <small class="text-muted">Total Income</small>
                                    <h3>Ksh <?= number_format((float)$stats['total_income'], 2) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Request Form -->
                    <div class="col-md-4 mb-4">
                        <?php if($has_pending): ?>
                            <div class="card border-warning h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-clock me-2"></i>Pending Request
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Amount Requested</small>
                                        <h3>Ksh <?= number_format((float)$has_pending['amount'], 2) ?></h3>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted">Request Date</small>
                                        <h3><?= date('M j, Y', strtotime($has_pending['requested_at'])) ?></h3>
                                    </div>
                                    <div>
                                        <small class="text-muted">Status</small>
                                        <h3>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-hourglass-half me-2"></i>Pending Review
                                            </span>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card border-success h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-hand-holding-usd me-2"></i>Request New Loan
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-4">
                                            <label for="amount" class="form-label">Loan Amount (Ksh)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Ksh</span>
                                                <input type="number" step="0.01" min="1000" class="form-control" 
                                                    id="amount" name="amount" required>
                                            </div>
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Loan Requirements:
                                                <ul class="mt-2 small">
                                                    <li>Must have supplied milk for at least 5 days in the last 30 days</li>
                                                    <li>Average daily supply must be at least 5 liters</li>
                                                    <li>Maximum loan amount is 50% of your monthly income</li>
                                                    <li>Minimum loan amount: Ksh 1,000</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Request
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Loan History -->
                    <div class="col-md-4 mb-4">
                        <div class="card border-info h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>Loan History
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if(empty($loans)): ?>
                                    <p class="text-center text-muted mt-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No loan history available
                                    </p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($loans as $loan): ?>
                                                    <tr>
                                                        <td><?= date('M j, Y', strtotime($loan['requested_at'])) ?></td>
                                                        <td>Ksh <?= number_format((float)$loan['amount'], 2) ?></td>
                                                        <td>
                                                            <?php if($loan['status'] === 'pending'): ?>
                                                                <span class="badge bg-warning">Pending</span>
                                                            <?php elseif($loan['status'] === 'approved'): ?>
                                                                <span class="badge bg-success">Approved</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Rejected</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
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
