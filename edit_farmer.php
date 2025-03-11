<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: manage_farmers.php');
    exit();
}

$farmer_id = sanitize($_GET['id']);

// Get farmer details
$stmt = $pdo->prepare("
    SELECT f.*, u.first_name, u.last_name, u.email 
    FROM farmers f
    JOIN users u ON f.user_id = u.id
    WHERE f.id = ?");
$stmt->execute([$farmer_id]);
$farmer = $stmt->fetch();

if (!$farmer) {
    setFlashMessage('danger', 'Farmer not found');
    header('Location: manage_farmers.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = sanitize($_POST['location']);
    $phone = sanitize($_POST['phone']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'] ?? null;
    
    try {
        $pdo->beginTransaction();
        
        // Check if email already exists for other users
        $check = $pdo->prepare("
            SELECT id 
            FROM users 
            WHERE email = ? 
            AND id != ?");
        $check->execute([$email, $farmer['user_id']]);
        if ($check->rowCount() > 0) {
            throw new Exception('Email already exists');
        }
        
        // Update user account
        if ($password) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, password = ?
                WHERE id = ?");
            $stmt->execute([
                $first_name,
                $last_name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $farmer['user_id']
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?
                WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $email, $farmer['user_id']]);
        }
        
        // Update farmer profile
        $stmt = $pdo->prepare("
            UPDATE farmers 
            SET location = ?, phone = ?
            WHERE id = ?");
        $stmt->execute([$location, $phone, $farmer_id]);
        
        $pdo->commit();
        setFlashMessage('success', 'Farmer details updated successfully');
        header('Location: manage_farmers.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlashMessage('danger', 'Error updating farmer details: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Farmer - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Edit Farmer</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="manage_farmers.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-edit me-2"></i>Edit Farmer Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <!-- Farmer Profile -->
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location/Address</label>
                                <input type="text" class="form-control" id="location" name="location" required
                                    value="<?= htmlspecialchars($farmer['location'] ?? '') ?>"
                                    placeholder="Enter farmer's location or address">
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" required
                                    value="<?= htmlspecialchars($farmer['phone'] ?? '') ?>"
                                    placeholder="Enter phone number (e.g., +254712345678)">
                            </div>

                            <hr class="my-4">

                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                    value="<?= htmlspecialchars($farmer['first_name']) ?>"
                                    placeholder="Enter first name">
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                    value="<?= htmlspecialchars($farmer['last_name']) ?>"
                                    placeholder="Enter last name">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    value="<?= htmlspecialchars($farmer['email']) ?>"
                                    placeholder="Enter email address">
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Leave blank to keep current password">
                                <small class="text-muted">Only fill this if you want to change the password</small>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <a href="manage_farmers.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
</body>
</html>
