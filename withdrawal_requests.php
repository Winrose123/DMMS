<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

// Handle withdrawal approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_id'], $_POST['action'])) {
    $withdrawal_id = (int)$_POST['withdrawal_id'];
    $action = $_POST['action'];
    $notes = isset($_POST['notes']) ? sanitize($_POST['notes']) : '';
    
    if ($action === 'approve' || $action === 'reject') {
        try {
            $pdo->beginTransaction();
            
            // Update withdrawal status
            $stmt = $pdo->prepare("
                UPDATE withdrawals 
                SET status = ?, processed_at = CURRENT_TIMESTAMP, notes = ?
                WHERE id = ? AND status = 'pending'
            ");
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $stmt->execute([$status, $notes, $withdrawal_id]);
            
            $pdo->commit();
            setFlashMessage('success', 'Withdrawal request ' . $status . ' successfully.');
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlashMessage('danger', 'Error processing withdrawal: ' . $e->getMessage());
        }
    }
    
    header('Location: withdrawal_requests.php');
    exit();
}

// Get all withdrawal requests with farmer details
$withdrawals = $pdo->query("
    SELECT w.*, 
           CONCAT(u.first_name, ' ', u.last_name) as farmer_name,
           f.phone
    FROM withdrawals w
    JOIN farmers f ON w.farmer_id = f.id
    JOIN users u ON f.user_id = u.id
    ORDER BY 
        CASE w.status 
            WHEN 'pending' THEN 1
            WHEN 'approved' THEN 2
            WHEN 'rejected' THEN 3
        END,
        w.requested_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Requests - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Withdrawal Requests</h1>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Requested</th>
                                        <th>Farmer</th>
                                        <th>Phone</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Processed</th>
                                        <th>Notes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($withdrawals)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                No withdrawal requests found
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($withdrawals as $withdrawal): ?>
                                            <tr>
                                                <td><?= date('M d, Y H:i', strtotime($withdrawal['requested_at'])) ?></td>
                                                <td><?= htmlspecialchars($withdrawal['farmer_name']) ?></td>
                                                <td><?= htmlspecialchars($withdrawal['phone']) ?></td>
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
                                                    <?= $withdrawal['processed_at'] ? 
                                                        date('M d, Y H:i', strtotime($withdrawal['processed_at'])) : 
                                                        '<span class="text-muted">-</span>' ?>
                                                </td>
                                                <td>
                                                    <?= $withdrawal['notes'] ? 
                                                        htmlspecialchars($withdrawal['notes']) : 
                                                        '<span class="text-muted">-</span>' ?>
                                                </td>
                                                <td>
                                                    <?php if ($withdrawal['status'] === 'pending'): ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success me-1" 
                                                                onclick="showActionModal('approve', <?= $withdrawal['id'] ?>)">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger" 
                                                                onclick="showActionModal('reject', <?= $withdrawal['id'] ?>)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
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

    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="actionForm" method="POST">
                    <input type="hidden" name="withdrawal_id" id="withdrawal_id">
                    <input type="hidden" name="action" id="action">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Process Withdrawal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                placeholder="Enter any additional notes..."></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="submitBtn">Process</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
    
    <script>
        let actionModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            actionModal = new bootstrap.Modal(document.getElementById('actionModal'));
        });
        
        function showActionModal(action, withdrawalId) {
            const form = document.getElementById('actionForm');
            const title = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtn');
            
            document.getElementById('withdrawal_id').value = withdrawalId;
            document.getElementById('action').value = action;
            document.getElementById('notes').value = '';
            
            if (action === 'approve') {
                title.textContent = 'Approve Withdrawal';
                submitBtn.textContent = 'Approve';
                submitBtn.className = 'btn btn-success';
            } else {
                title.textContent = 'Reject Withdrawal';
                submitBtn.textContent = 'Reject';
                submitBtn.className = 'btn btn-danger';
            }
            
            actionModal.show();
        }
    </script>
</body>
</html>
