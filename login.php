<?php
require_once 'database/connection.php';

// Clear any existing session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(sanitize($_POST['email'])); // Normalize email to lowercase
    $password = $_POST['password'];

    try {
        // Get user with role and farmer_id if exists
        $stmt = $pdo->prepare("
            SELECT 
                u.*,
                f.id as farmer_id 
            FROM users u 
            LEFT JOIN farmers f ON u.id = f.user_id 
            WHERE LOWER(u.email) = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Start a new session to ensure clean state
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user'] = true; // Add this line to ensure user is marked as logged in
            
            // Set farmer_id if user is a farmer
            if ($user['role'] === 'farmer' && $user['farmer_id']) {
                $_SESSION['farmer_id'] = $user['farmer_id'];
            }
            
            // Redirect based on role
            $redirect = $user['role'] === 'admin' ? 'dashboard.php' : 'farmer_dashboard.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            setFlashMessage('danger', 'Invalid email or password!');
        }
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Database error. Please try again later.');
    }
}

// Check if database needs initialization
try {
    $test = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($test == 0) {
        setFlashMessage('warning', 'No users found in database. <a href="verify_db.php" class="alert-link">Click here to initialize the database</a>.');
    }
} catch (PDOException $e) {
    setFlashMessage('warning', 'Database not initialized. <a href="verify_db.php" class="alert-link">Click here to initialize the database</a>.');
}

// Get user role if email exists
$userRole = null;
if (isset($_POST['email'])) {
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE LOWER(email) = ?");
        $stmt->execute([strtolower($_POST['email'])]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $userRole = $result['role'];
        }
    } catch (PDOException $e) {
        // Silently handle error
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dairy Milk Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            max-width: 900px;
            width: 100%;
            margin: 2rem auto;
        }
        .login-row {
            min-height: 550px;
        }
        .login-left {
            background: linear-gradient(45deg, #2c3e50, #27ae60);
            position: relative;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }
        .login-right {
            padding: 3rem;
            background: white;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
        .btn-login {
            background: #27ae60;
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 500;
            width: 100%;
            color: white;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: #219a52;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.3);
        }
        .role-indicator {
            text-align: center;
            margin: 1rem 0;
            padding: 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: none;
        }
        .role-indicator.farmer {
            background: #e1f7e9;
            color: #27ae60;
            border: 1px solid #27ae60;
        }
        .role-indicator.admin {
            background: #e8f4f8;
            color: #2980b9;
            border: 1px solid #2980b9;
        }
        .role-indicator i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .role-indicator.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .debug-info {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 0.9em;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="row g-0 login-row">
                <div class="col-md-6 login-left">
                    <h2 class="mb-4">Welcome to DMMS</h2>
                    <p class="lead mb-4">Dairy Milk Management System</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> Track milk production</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> Manage expenses</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> Handle loan requests</li>
                        <li class="mb-3"><i class="fas fa-check-circle me-2"></i> View detailed reports</li>
                    </ul>
                </div>
                <div class="col-md-6 login-right">
                    <div class="login-header">
                        <h1>Login</h1>
                        <p class="text-muted">Enter your credentials to continue</p>
                    </div>

                    <?php if ($flash = getFlashMessage()): ?>
                        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                            <?= $flash['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Role Indicator -->
                    <?php if ($userRole): ?>
                    <div class="role-indicator <?= $userRole ?> show">
                        <?php if ($userRole === 'farmer'): ?>
                            <i class="fas fa-tractor"></i>
                            <h4>Farmer Account</h4>
                            <p class="mb-0">You are logging in as a farmer</p>
                        <?php else: ?>
                            <i class="fas fa-user-shield"></i>
                            <h4>Admin Account</h4>
                            <p class="mb-0">You are logging in as an administrator</p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="loginForm">
                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" 
                                placeholder="name@example.com" required
                                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            <label for="email">Email address</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" 
                                placeholder="Password" required minlength="6">
                            <label for="password">Password</label>
                        </div>
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('email').addEventListener('blur', function() {
        const email = this.value;
        if (email) {
            fetch('check_role.php?email=' + encodeURIComponent(email))
                .then(response => response.json())
                .then(data => {
                    const roleIndicator = document.querySelector('.role-indicator');
                    if (data.role) {
                        roleIndicator.className = 'role-indicator ' + data.role + ' show';
                        if (data.role === 'farmer') {
                            roleIndicator.innerHTML = `
                                <i class="fas fa-tractor"></i>
                                <h4>Farmer Account</h4>
                                <p class="mb-0">You are logging in as a farmer</p>
                            `;
                        } else {
                            roleIndicator.innerHTML = `
                                <i class="fas fa-user-shield"></i>
                                <h4>Admin Account</h4>
                                <p class="mb-0">You are logging in as an administrator</p>
                            `;
                        }
                    } else {
                        if (roleIndicator) {
                            roleIndicator.classList.remove('show');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    });
    </script>
</body>
</html>