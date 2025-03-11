<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireFarmer();

$farmer_id = $_SESSION['farmer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = sanitize($_POST['description']);
    $amount = sanitize($_POST['amount']);
    $date = sanitize($_POST['date']);
    $category = sanitize($_POST['category']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO expenses (farmer_id, description, amount, date, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$farmer_id, $description, $amount, $date, $category]);
        setFlashMessage('success', 'Expense recorded successfully!');
        header('Location: add_expense.php');
        exit();
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Error recording expense: ' . $e->getMessage());
    }
}

// Get monthly expense summary
$monthly_expenses = $pdo->prepare("
    SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        COUNT(*) as count,
        MIN(amount) as min_amount,
        MAX(amount) as max_amount,
        AVG(amount) as avg_amount,
        SUM(amount) as total
    FROM expenses 
    WHERE farmer_id = ?
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6");
$monthly_expenses->execute([$farmer_id]);
$expense_summary = $monthly_expenses->fetchAll();

// Get recent expenses
$recent_expenses = $pdo->prepare("
    SELECT * FROM expenses 
    WHERE farmer_id = ? 
    ORDER BY date DESC, id DESC 
    LIMIT 10");
$recent_expenses->execute([$farmer_id]);
$expenses = $recent_expenses->fetchAll();

// Calculate total expenses for current month
$current_month = $pdo->prepare("
    SELECT SUM(amount) as total 
    FROM expenses 
    WHERE farmer_id = ? 
    AND MONTH(date) = MONTH(CURRENT_DATE)
    AND YEAR(date) = YEAR(CURRENT_DATE)");
$current_month->execute([$farmer_id]);
$current_month_total = $current_month->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Expense - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Record Expense</h1>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Current Month Summary -->
                    <div class="col-md-4 mb-4">
                        <div class="card border-info h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i>Current Month
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <h6 class="text-muted mb-3"><?= date('F Y') ?></h6>
                                    <h2 class="display-4 mb-3">
                                        Ksh <?= number_format($current_month_total, 2) ?>
                                    </h2>
                                    <p class="text-muted">Total Expenses This Month</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add New Expense -->
                    <div class="col-md-8 mb-4">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-plus-circle me-2"></i>Add New Expense
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-12">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" 
                                            rows="2" required placeholder="Enter expense description"></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="amount" class="form-label">Amount (Ksh)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Ksh</span>
                                            <input type="number" step="0.01" min="0" class="form-control" 
                                                id="amount" name="amount" required 
                                                placeholder="Enter amount">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="date" class="form-control" id="date" name="date" 
                                            value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <option value="feed">Feed</option>
                                            <option value="medicine">Medicine</option>
                                            <option value="equipment">Equipment</option>
                                            <option value="labor">Labor</option>
                                            <option value="transport">Transport</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Record Expense
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Summary -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-success h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Monthly Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if(empty($expense_summary)): ?>
                                    <p class="text-center text-muted mt-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No expense history available
                                    </p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Count</th>
                                                    <th>Average</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($expense_summary as $month): ?>
                                                    <tr>
                                                        <td><?= date('F Y', strtotime($month['month'] . '-01')) ?></td>
                                                        <td><?= number_format($month['count']) ?></td>
                                                        <td>Ksh <?= number_format($month['avg_amount'], 2) ?></td>
                                                        <td>Ksh <?= number_format($month['total'], 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Expenses -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-warning h-100">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>Recent Expenses
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if(empty($expenses)): ?>
                                    <p class="text-center text-muted mt-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No recent expenses
                                    </p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Description</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($expenses as $expense): ?>
                                                    <tr>
                                                        <td><?= date('M j, Y', strtotime($expense['date'])) ?></td>
                                                        <td><?= htmlspecialchars($expense['description']) ?></td>
                                                        <td>Ksh <?= number_format($expense['amount'], 2) ?></td>
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
