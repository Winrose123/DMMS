<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

// Handle loan status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loan_id'], $_POST['action'])) {
    $loan_id = sanitize($_POST['loan_id']);
    $action = sanitize($_POST['action']);
    
    if ($action === 'approve' || $action === 'reject') {
        try {
            $stmt = $pdo->prepare("UPDATE loans SET status = ? WHERE id = ?");
            $stmt->execute([$action . 'd', $loan_id]);
            setFlashMessage('success', 'Loan request ' . $action . 'd successfully!');
        } catch (PDOException $e) {
            setFlashMessage('danger', 'Error updating loan status: ' . $e->getMessage());
        }
    }
    header('Location: loan_requests.php');
    exit();
}

// Get all loan requests with farmer details
$loans = $pdo->query("
    SELECT 
        l.*,
        CONCAT(u.first_name, ' ', u.last_name) as farmer_name,
        f.phone,
        f.location,
        (
            SELECT COALESCE(SUM(quantity_liters * price_per_liter), 0)
            FROM milk_records mr 
            WHERE mr.farmer_id = f.id 
            AND mr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ) as monthly_income,
        (
            SELECT COALESCE(AVG(quantity_liters), 0)
            FROM milk_records mr 
            WHERE mr.farmer_id = f.id 
            AND mr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ) as avg_daily_supply
    FROM loans l
    JOIN farmers f ON l.farmer_id = f.id
    JOIN users u ON f.user_id = u.id
    ORDER BY l.requested_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Requests - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Loan Requests</h1>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($loans)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h3 class="text-muted">No loan requests found</h3>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Farmer</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Amount</th>
                                    <th>Monthly Income</th>
                                    <th>Avg. Daily Supply</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($loans as $loan): ?>
                                    <tr>
                                        <td><?= date('M j, Y', strtotime($loan['requested_at'])) ?></td>
                                        <td><?= htmlspecialchars($loan['farmer_name']) ?></td>
                                        <td><?= htmlspecialchars($loan['phone']) ?></td>
                                        <td><?= htmlspecialchars($loan['location']) ?></td>
                                        <td>Ksh <?= number_format((float)$loan['amount'], 2) ?></td>
                                        <td>Ksh <?= number_format((float)$loan['monthly_income'], 2) ?></td>
                                        <td><?= number_format((float)$loan['avg_daily_supply'], 2) ?> L</td>
                                        <td>
                                            <?php if($loan['status'] === 'pending'): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                            <?php elseif($loan['status'] === 'approved'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Approved
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i>Rejected
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($loan['status'] === 'pending'): ?>
                                                <div class="btn-group">
                                                    <form method="POST" class="d-inline-block me-1">
                                                        <input type="hidden" name="loan_id" value="<?= $loan['id'] ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-sm btn-success" 
                                                                onclick="return confirm('Are you sure you want to approve this loan request?')">
                                                            <i class="fas fa-check me-1"></i>Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline-block">
                                                        <input type="hidden" name="loan_id" value="<?= $loan['id'] ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Are you sure you want to reject this loan request?')">
                                                            <i class="fas fa-times me-1"></i>Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No actions available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
</body>
</html>
