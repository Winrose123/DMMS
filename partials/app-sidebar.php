<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireLogin();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="brand-section mb-4 px-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-cow fa-2x text-white me-2"></i>
                <div>
                    <h5 class="mb-0 text-white">DMMS</h5>
                    <small class="text-white-50">Dairy Management</small>
                </div>
            </div>
        </div>
        
        <div class="user-section mb-4 px-3">
            <div class="d-flex align-items-center">
                <div class="avatar-circle me-2">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h6 class="mb-0 text-white"><?= htmlspecialchars($_SESSION['name']) ?></h6>
                    <small class="text-white-50"><?= ucfirst($_SESSION['role']) ?></small>
                </div>
            </div>
        </div>
        
        <ul class="nav flex-column px-2">
            <?php if (isAdmin()): ?>
                <!-- Admin Menu -->
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" 
                        href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'manage_farmers.php' ? 'active' : '' ?>" 
                        href="manage_farmers.php">
                        <i class="fas fa-users"></i>
                        <span>Manage Farmers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'record_milk.php' ? 'active' : '' ?>" 
                        href="record_milk.php">
                        <i class="fas fa-fill-drip"></i>
                        <span>Record Milk</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'loan_requests.php' ? 'active' : '' ?>" 
                        href="loan_requests.php">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Loan Requests</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'withdrawal_requests.php' ? 'active' : '' ?>" 
                        href="withdrawal_requests.php">
                        <i class="fas fa-money-check-alt"></i>
                        <span>Withdrawal Requests</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'reports.php' ? 'active' : '' ?>" 
                        href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
            <?php else: ?>
                <!-- Farmer Menu -->
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'farmer_dashboard.php' ? 'active' : '' ?>" 
                        href="farmer_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'production_history.php' ? 'active' : '' ?>" 
                        href="production_history.php">
                        <i class="fas fa-history"></i>
                        <span>Production History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'add_expense.php' ? 'active' : '' ?>" 
                        href="add_expense.php">
                        <i class="fas fa-receipt"></i>
                        <span>Record Expense</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'request_loan.php' ? 'active' : '' ?>" 
                        href="request_loan.php">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Request Loan</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <li class="nav-item mt-2">
                <hr class="nav-divider">
            </li>
            
            <!-- Common Menu Items -->
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>" 
                    href="profile.php">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebarMenu');
    const toggle = document.getElementById('sidebar-toggle');
    const mainContent = document.querySelector('.main-content');
    
    // Create backdrop for mobile
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);
    
    // Toggle sidebar
    toggle.addEventListener('click', function() {
        if (window.innerWidth >= 768) {
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '4.5rem' : '16.67%';
            }
        } else {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        }
    });
    
    // Close sidebar when clicking backdrop
    backdrop.addEventListener('click', function() {
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        }
    });
});
</script>