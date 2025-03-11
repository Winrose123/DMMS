<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

// Get all farmers with their IDs
$farmers = $pdo->query("
    SELECT f.id as farmer_id, u.first_name, u.last_name 
    FROM farmers f 
    JOIN users u ON f.user_id = u.id
    ORDER BY u.first_name, u.last_name")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmer_id = sanitize($_POST['farmer_id']);
    $quantity = sanitize($_POST['quantity']);
    $price = sanitize($_POST['price']);
    
    // Validate farmer exists
    $check_farmer = $pdo->prepare("SELECT id FROM farmers WHERE id = ?");
    $check_farmer->execute([$farmer_id]);
    if ($check_farmer->rowCount() === 0) {
        setFlashMessage('danger', 'Invalid farmer selected.');
        header('Location: record_milk.php');
        exit();
    }
    
    // Handle image upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/milk_records/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $image_url = $upload_dir . uniqid() . '.' . $file_extension;
            move_uploaded_file($_FILES['image']['tmp_name'], $image_url);
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO milk_records (farmer_id, quantity_liters, price_per_liter, image_url) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$farmer_id, $quantity, $price, $image_url]);
        
        setFlashMessage('success', 'Milk record added successfully!');
        header('Location: record_milk.php');
        exit();
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Error adding milk record: ' . $e->getMessage());
    }
}

// Get today's records
$today_records = $pdo->query("
    SELECT mr.*, CONCAT(u.first_name, ' ', u.last_name) as farmer_name 
    FROM milk_records mr 
    JOIN farmers f ON mr.farmer_id = f.id 
    JOIN users u ON f.user_id = u.id 
    WHERE DATE(mr.created_at) = CURRENT_DATE 
    ORDER BY mr.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Milk - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Record Milk Production</h1>
                </div>

                <?php if ($flash = getFlashMessage()): ?>
                    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $flash['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Record Form -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-plus-circle me-2"></i>New Record
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="farmer" class="form-label">Farmer</label>
                                        <select class="form-select" id="farmer" name="farmer_id" required>
                                            <option value="">Select Farmer</option>
                                            <?php foreach ($farmers as $farmer): ?>
                                                <option value="<?= $farmer['farmer_id'] ?>">
                                                    <?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity (Liters)</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" 
                                            step="0.01" min="0" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price per Liter (Ksh)</label>
                                        <input type="number" class="form-control" id="price" name="price" 
                                            step="0.01" min="0" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="image" class="form-label">Image (Optional)</label>
                                        <input type="file" class="form-control" id="image" name="image" 
                                            accept="image/jpeg,image/png" onchange="previewImage(this);">
                                        <div class="form-text">Upload an image of the milk measurement</div>
                                        <div id="imagePreview" class="mt-2" style="display: none;">
                                            <img id="preview" src="#" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-2"></i>Save Record
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Records -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>Today's Records
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Farmer</th>
                                                <th>Quantity (L)</th>
                                                <th>Price/L</th>
                                                <th>Total</th>
                                                <th>Image</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($today_records)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        No records for today
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($today_records as $record): ?>
                                                    <tr>
                                                        <td><?= date('H:i', strtotime($record['created_at'])) ?></td>
                                                        <td><?= htmlspecialchars($record['farmer_name']) ?></td>
                                                        <td><?= number_format($record['quantity_liters'], 2) ?></td>
                                                        <td>Ksh <?= number_format($record['price_per_liter'], 2) ?></td>
                                                        <td>Ksh <?= number_format($record['quantity_liters'] * $record['price_per_liter'], 2) ?></td>
                                                        <td>
                                                            <?php if ($record['image_url']): ?>
                                                                <a href="<?= $record['image_url'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-image"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">No image</span>
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
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include 'partials/app-scripts.php'; ?>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewDiv = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewDiv.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '#';
                previewDiv.style.display = 'none';
            }
        }
    </script>
</body>
</html>
