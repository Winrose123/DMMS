<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireFarmer();

$farmer_id = $_SESSION['farmer_id'];
$success_message = '';
$error_message = '';

// Get available balance
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $notes = sanitize($_POST['notes']);

    if ($amount <= 0) {
        $error_message = "Please enter a valid amount greater than 0";
    } elseif ($amount > $available_balance) {
        $error_message = "Withdrawal amount exceeds available balance";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO withdrawals (farmer_id, amount, notes)
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$farmer_id, $amount, $notes])) {
            $success_message = "Withdrawal request submitted successfully";
        } else {
            $error_message = "Failed to submit withdrawal request";
        }
    }
}

// Get withdrawal history
$history = $pdo->prepare("
    SELECT * FROM withdrawals 
    WHERE farmer_id = ? 
    ORDER BY requested_at DESC 
    LIMIT 10
");
$history->execute([$farmer_id]);
$withdrawal_history = $history->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Funds - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Withdraw Funds</h1>
                </div>

                <?php if($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-wallet me-2"></i>Available Balance
                                </h5>
                            </div>
                            <div class="card-body">
                                <h2 class="display-4">Ksh <?= number_format($available_balance, 2) ?></h2>
                                <p class="text-muted">Total Income: Ksh <?= number_format($balance_data['total_income'], 2) ?></p>
                                <p class="text-muted">Total Expenses: Ksh <?= number_format($balance_data['total_expenses'], 2) ?></p>
                                <p class="text-muted">Total Withdrawals: Ksh <?= number_format($balance_data['total_withdrawals'], 2) ?></p>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-money-bill-wave me-2"></i>Request Withdrawal
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount (Ksh)</label>
                                        <input type="number" step="0.01" min="0" max="<?= $available_balance ?>" 
                                            class="form-control" id="amount" name="amount" required>
                                        <div class="form-text">Maximum withdrawal: Ksh <?= number_format($available_balance, 2) ?></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>Withdrawal History
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($withdrawal_history)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No withdrawal history</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach($withdrawal_history as $withdrawal): ?>
                                                    <tr>
                                                        <td><?= date('Y-m-d', strtotime($withdrawal['requested_at'])) ?></td>
                                                        <td>Ksh <?= number_format($withdrawal['amount'], 2) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= 
                                                                $withdrawal['status'] === 'approved' ? 'success' : 
                                                                ($withdrawal['status'] === 'pending' ? 'warning' : 'danger') 
                                                            ?>">
                                                                <?= ucfirst($withdrawal['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= htmlspecialchars($withdrawal['notes'] ?: '-') ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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
