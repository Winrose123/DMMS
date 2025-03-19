<?php
require_once 'database/connection.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION = array();
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get farmer details if user is a farmer
$farmer = null;
if ($_SESSION['role'] === 'farmer') {
    $stmt = $pdo->prepare("SELECT * FROM farmers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $farmer = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update user details
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = ?, last_name = ?, email = ?
            WHERE id = ?");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_SESSION['user_id']
        ]);

        // Update farmer details if applicable
        if ($_SESSION['role'] === 'farmer' && $farmer) {
            $stmt = $pdo->prepare("
                UPDATE farmers 
                SET name = ?, contact_info = ?
                WHERE user_id = ?");
            $stmt->execute([
                $_POST['farm_name'],
                $_POST['contact_info'],
                $_SESSION['user_id']
            ]);
        }

        // Update password if provided
        if (!empty($_POST['new_password'])) {
            if (empty($_POST['current_password'])) {
                throw new Exception("Current password is required to change password");
            }
            
            // Verify current password
            if (!password_verify($_POST['current_password'], $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            // Update password
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
        }

        $pdo->commit();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Profile updated successfully'];
        header('Location: profile.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
    <style>
        .profile-section {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border: 1px solid rgba(0,0,0,.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .profile-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            border-radius: 15px 15px 0 0;
            border-bottom: 1px solid #dee2e6;
        }
        .profile-header h5 {
            color: #2c3e50;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .profile-content {
            padding: 2rem;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 3px solid #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            font-size: 3rem;
            color: #6c757d;
        }
        .btn-save {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s ease;
        }
        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
                    <h1 class="h2 text-primary">My Profile</h1>
                </div>

                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="profile-avatar">
                        <?= strtoupper(substr($user['first_name'] ?? '', 0, 1)) ?>
                    </div>

                    <!-- Personal Information -->
                    <div class="profile-section">
                        <div class="profile-header">
                            <h5><i class="fas fa-user me-2"></i>Personal Information</h5>
                        </div>
                        <div class="profile-content">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                        value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                        value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                            value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($_SESSION['role'] === 'farmer' && $farmer): ?>
                    <!-- Farm Information -->
                    <div class="profile-section">
                        <div class="profile-header">
                            <h5><i class="fas fa-tractor me-2"></i>Farm Information</h5>
                        </div>
                        <div class="profile-content">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="farm_name" class="form-label">Farm Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-home"></i></span>
                                        <input type="text" class="form-control" id="farm_name" name="farm_name"
                                            value="<?= htmlspecialchars($farmer['name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="contact_info" class="form-label">Contact Information</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <textarea class="form-control" id="contact_info" name="contact_info" rows="3"
                                            required><?= htmlspecialchars($farmer['contact_info'] ?? '') ?></textarea>
                                    </div>
                                    <div class="form-text"><i class="fas fa-info-circle me-1"></i>Include phone number and location details</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Change Password -->
                    <div class="profile-section">
                        <div class="profile-header">
                            <h5><i class="fas fa-lock me-2"></i>Change Password</h5>
                        </div>
                        <div class="profile-content">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                    <div class="form-text"><i class="fas fa-info-circle me-1"></i>Required only if changing password</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                    </div>
                                    <div class="form-text"><i class="fas fa-info-circle me-1"></i>Leave blank to keep current password</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 mb-5">
                        <button type="submit" class="btn btn-primary btn-save">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
