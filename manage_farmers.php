<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

// Handle farmer deletion
if (isset($_POST['delete_farmer'])) {
    $farmer_id = sanitize($_POST['farmer_id']);
    
    try {
        // Get user_id first
        $stmt = $pdo->prepare("SELECT user_id FROM farmers WHERE id = ?");
        $stmt->execute([$farmer_id]);
        $user_id = $stmt->fetchColumn();
        
        if (!$user_id) {
            throw new Exception('Farmer not found');
        }
        
        // Delete user record which will cascade delete everything else
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        setFlashMessage('success', 'Farmer deleted successfully');
        header('Location: manage_farmers.php');
        exit();
    } catch (Exception $e) {
        setFlashMessage('danger', 'Error deleting farmer: ' . $e->getMessage());
        header('Location: manage_farmers.php');
        exit();
    }
}

// Get all farmers with their details
$stmt = $pdo->prepare("
    SELECT 
        f.id,
        f.phone,
        f.location,
        u.first_name,
        u.last_name,
        u.email,
        u.created_at,
        (SELECT COUNT(*) FROM milk_records WHERE farmer_id = f.id) as record_count
    FROM farmers f
    JOIN users u ON f.user_id = u.id
    ORDER BY u.first_name, u.last_name");
$stmt->execute();
$farmers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Farmers - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Manage Farmers</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add_farmer.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Add New Farmer
                        </a>
                    </div>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <?php if (empty($farmers)): ?>
                            <div class="text-center text-muted my-5">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p class="lead">No farmers registered yet</p>
                                <a href="add_farmer.php" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Add First Farmer
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Location</th>
                                            <th>Records</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($farmers as $farmer): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($farmer['first_name']) . ' ' . htmlspecialchars($farmer['last_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($farmer['email']) ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($farmer['phone']) ?></td>
                                                <td><?= htmlspecialchars($farmer['location']) ?></td>
                                                <td><?= $farmer['record_count'] ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="edit_farmer.php?id=<?= $farmer['id'] ?>" 
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this farmer? This will delete all their records and cannot be undone.');">
                                                            <input type="hidden" name="farmer_id" value="<?= $farmer['id'] ?>">
                                                            <button type="submit" name="delete_farmer" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
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
</body>
</html>
