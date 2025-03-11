<?php
session_start();

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'dashboard.php' : 'farmer_dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Dairy Milk Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            opacity: 0.1;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #27ae60;
            margin-bottom: 1.5rem;
        }
        .cta-section {
            background: #f8f9fa;
            padding: 80px 0;
        }
        .btn-primary {
            background: #27ae60;
            border-color: #27ae60;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #219a52;
            border-color: #219a52;
            transform: translateY(-2px);
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            color: #27ae60;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .nav-link {
            color: #2c3e50;
            font-weight: 500;
            margin: 0 10px;
        }
        .nav-link:hover {
            color: #27ae60;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cow me-2"></i>DMMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center hero-content">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Dairy Milk Management System</h1>
                    <p class="lead mb-4">Streamline your dairy operations with our comprehensive management system. Track production, manage expenses, and grow your dairy business efficiently.</p>
                    <a href="login.php" class="btn btn-light btn-lg">Get Started</a>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="images/img_3.jpg" alt="Dairy Management" class="img-fluid rounded shadow-lg" style="max-height: 500px; object-fit: cover;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" id="features">
        <div class="container">
            <h2 class="text-center mb-5">Key Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-chart-line feature-icon"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">Production Tracking</h3>
                        <p class="text-muted">Record and monitor daily milk production with detailed statistics and trends analysis.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-calculator feature-icon"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">Financial Management</h3>
                        <p class="text-muted">Track expenses, manage payments, and monitor your dairy farm's financial health.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card p-4">
                        <div class="text-center">
                            <i class="fas fa-mobile-alt feature-icon"></i>
                        </div>
                        <h3 class="h5 text-center mb-3">Easy Access</h3>
                        <p class="text-muted">Access your data anytime, anywhere with our user-friendly interface.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="cta-section" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-4">About Our System</h2>
                    <p class="lead mb-4">DMMS is designed to help dairy farmers manage their operations more efficiently. From tracking milk production to managing finances, our system provides all the tools you need to run your dairy farm successfully.</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Real-time production monitoring</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Comprehensive financial tracking</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Secure data storage</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>User-friendly interface</li>
                    </ul>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="images/img_2.jpg" alt="About DMMS" class="img-fluid rounded shadow-lg" style="max-height: 500px; object-fit: cover;">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Dairy Milk Management System</h5>
                    <p class="mb-0">Empowering dairy farmers with modern management tools.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
