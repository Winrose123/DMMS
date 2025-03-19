<?php
require_once 'database/connection.php';

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
    <title>DMMS HomePage - Dairy Milk Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #27ae60;
            --secondary-color: #2ecc71;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--dark-color);
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0 50px;
            position: relative;
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

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .hero h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: white;
            color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--dark-color);
            color: white;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            margin-left: 15px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .about {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .about h2 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .about-list {
            list-style: none;
            padding: 0;
        }

        .about-list li {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .about-list i {
            color: var(--primary-color);
            margin-right: 10px;
        }

        .features {
            padding: 100px 0;
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .footer {
            background: var(--dark-color);
            color: white;
            padding: 50px 0;
        }

        .footer a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero h3 {
                font-size: 1.5rem;
            }
            
            .btn-secondary {
                margin: 15px 0 0 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
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
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h3>Welcome to</h3>
                    <h1>Dairy Milk Management System</h1>
                    <p>Pure, fresh, and organic dairy products straight from the farm. Efficiently track milk production, manage records, and access financial insights.</p>
                    <div>
                        <a href="login.php" class="btn btn-primary">Get Started</a>
                      <!--  <button class="btn btn-secondary">
                            <i class="fas fa-play me-2"></i>Watch Video
                        </button> -->
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="images/img_3.jpg" alt="Dairy Farm" class="img-fluid rounded shadow-lg" style="max-height: 400px; object-fit: cover;">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2>WE CREATE THE BEST DAIRY PRODUCTS</h2>
                    <p class="lead mb-4">We ensure top-quality dairy products made with 100% organic ingredients while providing efficient management tools.</p>
                    <ul class="about-list">
                        <li><i class="fas fa-check-circle"></i> Quality Dairy Products</li>
                        <li><i class="fas fa-check-circle"></i> 100% Organic and Natural</li>
                        <li><i class="fas fa-check-circle"></i> Trusted by Thousands</li>
                        <li><i class="fas fa-check-circle"></i> Modern Management System</li>
                    </ul>
                    <a href="#features" class="btn btn-primary mt-3">Learn More</a>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="images/img_1.jpg" alt="About Us" class="img-fluid rounded shadow-lg" style="max-height: 400px; object-fit: cover;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="text-center mb-5">Our Features</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <h3 class="h5">Production Tracking</h3>
                        <p>View graphical trends of milk production over time.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <i class="fas fa-money-bill-wave feature-icon"></i>
                        <h3 class="h5">Income Insights</h3>
                        <p>Analyze total income generated from milk sales.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <i class="fas fa-hand-holding-usd feature-icon"></i>
                        <h3 class="h5">Loan Management</h3>
                        <p>Assess eligibility for financial assistance with ease.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <i class="fas fa-calculator feature-icon"></i>
                        <h3 class="h5">Expense Tracking</h3>
                        <p>Keep track of all expenses including feeds and treatments.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-12">
                    <a href="#about">About</a>
                    <a href="#features">Features</a>
                    <a href="login.php">Login</a>
                    <a href="#">Support</a>
                    <a href="#">Privacy Policy</a>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <p>&copy; <?= date('Y') ?> Dairy Milk Management System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>