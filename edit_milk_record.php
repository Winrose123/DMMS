<?php
require_once 'database/connection.php';
require_once 'database/auth.php';
requireAdmin();

if (!isset($_GET['id'])) {
    header('Location: records-view.php');
    exit();
}

$record_id = sanitize($_GET['id']);

// Get record details
$stmt = $pdo->prepare("
    SELECT mr.*, CONCAT(u.first_name, ' ', u.last_name) as farmer_name
    FROM milk_records mr
    JOIN farmers f ON mr.farmer_id = f.id
    JOIN users u ON f.user_id = u.id
    WHERE mr.id = ?");
$stmt->execute([$record_id]);
$record = $stmt->fetch();

if (!$record) {
    setFlashMessage('danger', 'Record not found');
    header('Location: records-view.php');
    exit();
}

// Get all farmers for the dropdown
$farmers = $pdo->query("
    SELECT f.id as farmer_id, u.first_name, u.last_name 
    FROM farmers f 
    JOIN users u ON f.user_id = u.id
    ORDER BY u.first_name, u.last_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmer_id = sanitize($_POST['farmer_id']);
    $quantity = sanitize($_POST['quantity']);
    $price = sanitize($_POST['price']);
    
    try {
        $pdo->beginTransaction();
        
        // Handle new image upload if provided
        $image_url = $record['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/milk_records/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                // Delete old image if exists
                if ($image_url && file_exists($image_url)) {
                    unlink($image_url);
                }
                
                $image_url = $upload_dir . uniqid() . '.' . $file_extension;
                move_uploaded_file($_FILES['image']['tmp_name'], $image_url);
            }
        }
        
        // Update record
        $stmt = $pdo->prepare("
            UPDATE milk_records 
            SET farmer_id = ?, quantity_liters = ?, price_per_liter = ?, image_url = ?
            WHERE id = ?");
        $stmt->execute([$farmer_id, $quantity, $price, $image_url, $record_id]);
        
        $pdo->commit();
        setFlashMessage('success', 'Record updated successfully');
        header('Location: records-view.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlashMessage('danger', 'Error updating record: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Milk Record - DMMS</title>
    <?php include 'partials/app-header-scripts.php'; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'partials/app-sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Edit Milk Record</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="records-view.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Records
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
                            <i class="fas fa-edit me-2"></i>Edit Record Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" class="row g-3">
                            <div class="col-md-6">
                                <label for="farmer" class="form-label">Farmer</label>
                                <select class="form-select" id="farmer" name="farmer_id" required>
                                    <?php foreach ($farmers as $farmer): ?>
                                        <option value="<?= $farmer['farmer_id'] ?>" 
                                            <?= ($farmer['farmer_id'] == $record['farmer_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity (Liters)</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                    step="0.01" min="0" required value="<?= htmlspecialchars($record['quantity_liters']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="price" class="form-label">Price per Liter (Ksh)</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                    step="0.01" min="0" required value="<?= htmlspecialchars($record['price_per_liter']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="image" class="form-label">New Image (Optional)</label>
                                <input type="file" class="form-control" id="image" name="image" 
                                    accept="image/jpeg,image/png" onchange="previewImage(this);">
                                <div class="form-text">Upload a new image or leave empty to keep the current one</div>
                            </div>

                            <?php if ($record['image_url']): ?>
                            <div class="col-12">
                                <label class="form-label">Current Image</label>
                                <div>
                                    <img src="<?= htmlspecialchars($record['image_url']) ?>" 
                                        alt="Current record image" 
                                        class="img-fluid rounded" 
                                        style="max-height: 200px;">
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <a href="records-view.php" class="btn btn-secondary">
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
    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.createElement('img');
                preview.src = e.target.result;
                preview.className = 'img-fluid rounded mt-2';
                preview.style.maxHeight = '200px';
                
                var container = input.parentElement;
                var existingPreview = container.querySelector('img');
                if (existingPreview) {
                    container.removeChild(existingPreview);
                }
                container.appendChild(preview);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>
