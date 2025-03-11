<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $contact_info = sanitize($_POST['contact_info']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = strtolower(sanitize($_POST['email'])); // Normalize email to lowercase
    $password = $_POST['password'];
    
    try {
        $pdo->beginTransaction();
        
        // Check if email already exists (case-insensitive)
        $check = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            throw new Exception('This email address is already registered. Please use a different email.');
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        // Validate phone number format (simple validation for demonstration)
        if (!preg_match('/^\+?\d{10,15}$/', $contact_info)) {
            throw new Exception('Please enter a valid phone number (10-15 digits, can start with +).');
        }
        
        // Create user account
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password, role) 
            VALUES (?, ?, ?, ?, 'farmer')");
        $stmt->execute([
            $first_name, 
            $last_name, 
            $email, 
            password_hash($password, PASSWORD_DEFAULT)
        ]);
        $user_id = $pdo->lastInsertId();
        
        // Create farmer profile
        $stmt = $pdo->prepare("
            INSERT INTO farmers (user_id, phone, location) 
            VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $contact_info, $name]);
        
        $pdo->commit();
        setFlashMessage('success', 'Farmer account created successfully');
        header('Location: manage_farmers.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlashMessage('danger', 'Error creating farmer account: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Farmer - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Add New Farmer</h1>
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
                            <i class="fas fa-user-plus me-2"></i>Farmer Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <!-- Farmer Profile -->
                            <div class="col-md-6">
                                <label for="name" class="form-label">Location/Address</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    placeholder="Enter farmer's location or address"
                                    value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="contact_info" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="contact_info" name="contact_info" required
                                    placeholder="Enter phone number (e.g., +254712345678)"
                                    pattern="^\+?\d{10,15}$"
                                    title="Phone number must be 10-15 digits and may start with +"
                                    value="<?= isset($_POST['contact_info']) ? htmlspecialchars($_POST['contact_info']) : '' ?>">
                                <div class="form-text">Format: +254712345678 or 0712345678</div>
                            </div>

                            <hr class="my-4">

                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                    placeholder="Enter first name"
                                    value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                    placeholder="Enter last name"
                                    value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    placeholder="Enter email address"
                                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required
                                    placeholder="Choose a password"
                                    minlength="6">
                                <div class="form-text">Minimum 6 characters</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Farmer Account
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
