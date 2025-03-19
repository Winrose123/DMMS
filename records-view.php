<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once 'database/auth.php';
require_once 'database/connection.php';
$_SESSION['table'] = 'milk_records';
$milk_records = include('database/show.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Records - Dairy Milk Management System</title>
    <?php include('partials/app-header-scripts.php');?>
    <style>
        #dashboardMainContainer {
            display: flex;
            min-height: 100vh;
        }
        .dashboard_content_container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8f9fa;
        }
        .dashboard_content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        .section-header {
            margin: 0 0 25px 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .section-header .title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
        }
        .section-header i {
            color: #3498db;
            font-size: 1.8rem;
        }
        .section_content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            height: calc(100vh - 180px);
            overflow-y: auto;
        }
        .table-responsive {
            overflow-x: auto;
            margin: 0 -20px;
            padding: 0 20px;
        }
        .table {
            width: 100%;
            margin-bottom: 0;
            background: white;
        }
        .table thead {
            position: sticky;
            top: 0;
            background: #f1f8ff;
            z-index: 1;
        }
        .table th {
            border-bottom: 2px solid #e9ecef;
            color: #2c3e50;
            font-weight: 600;
            padding: 15px;
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
            padding: 12px 15px;
            color: #34495e;
        }
        .recordImages {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .recordImages:hover {
            transform: scale(1.1);
        }
        .btn-group {
            gap: 5px;
            white-space: nowrap;
        }
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }
        .btn-outline-primary {
            border-color: #3498db;
            color: #3498db;
        }
        .btn-outline-primary:hover {
            background-color: #3498db;
            color: white;
        }
        .btn-outline-danger {
            border-color: #e74c3c;
            color: #e74c3c;
        }
        .btn-outline-danger:hover {
            background-color: #e74c3c;
            color: white;
        }
        .usercount {
            margin-top: 15px;
            color: #7f8c8d;
            font-size: 0.9rem;
            position: sticky;
            bottom: 0;
            background: white;
            padding: 10px 0;
            border-top: 1px solid #eee;
        }
        .table tr:hover {
            background-color: #f8f9fa;
        }
        .no-image {
            color: #95a5a6;
            font-size: 0.9rem;
        }
        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        .empty-message i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #bdc3c7;
        }
        @media (max-width: 768px) {
            .section_content {
                height: calc(100vh - 150px);
            }
            .table td, .table th {
                padding: 10px;
            }
            .recordImages {
                max-width: 60px;
                max-height: 60px;
            }
        }
    </style>
</head>
<body>
    <div id="dashboardMainContainer">
        <?php include('partials/app-sidebar.php') ?>
        <div class="dashboard_content_container">

            <div class="dashboard_content">
                <div class="section-header">
                    <div class="title">
                        <i class="fa fa-list"></i>
                        <span>Milk Production Records</span>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search records...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <?php if (isAdmin()): ?>
                            <a href="record_milk.php" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Add New Record
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="section_content">
                    <div class="table-responsive">
                        <table class="table table-hover" id="recordsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Farmer Name</th>
                                    <th>Quantity (L)</th>
                                    <th>Price/L (Ksh)</th>
                                    <th>Total (Ksh)</th>
                                    <th>Date & Time</th>
                                    <?php if (isAdmin()): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($milk_records)): ?>
                                    <tr>
                                        <td colspan="<?= isAdmin() ? 8 : 7 ?>" class="empty-message">
                                            <div>
                                                <i class="fa fa-info-circle"></i>
                                                <p>No records found</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($milk_records as $index => $record): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <?php if (!empty($record['image'])): ?>
                                                    <img class="recordImages" src="uploads/records/<?= htmlspecialchars($record['image']) ?>" 
                                                        alt="Record image" title="Click to view full size"/>
                                                <?php else: ?>
                                                    <span class="no-image">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($record['farmer_name']) ?></td>
                                            <td><?= number_format($record['quantity_liters'], 2) ?></td>
                                            <td><?= number_format($record['price_per_liter'], 2) ?></td>
                                            <td><?= number_format($record['quantity_liters'] * $record['price_per_liter'], 2) ?></td>
                                            <td><?= date('M d, Y @ h:i A', strtotime($record['created_at'])) ?></td>
                                            <?php if (isAdmin()): ?>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="edit_milk_record.php?id=<?= $record['id']?>" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="Edit record">
                                                            <i class="fa fa-pencil"></i> Edit
                                                        </a>
                                                        <a href="#" 
                                                           class="btn btn-sm btn-outline-danger deleteRecord" 
                                                           data-name="<?= htmlspecialchars($record['farmer_name'])?>" 
                                                           data-recordid="<?= $record['id']?>"
                                                           title="Delete record">
                                                            <i class="fa fa-trash"></i> Delete
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="usercount d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fa fa-calculator"></i> 
                            Total Records: <span id="totalRecords"><?= number_format(count($milk_records)) ?></span>
                        </div>
                        <div class="d-flex gap-2">
                          <!--  <button class="btn btn-sm btn-outline-primary" id="exportCSV">
                                <i class="fa fa-file-excel-o"></i> Export to CSV
                            </button> -->
                            <button class="btn btn-sm btn-outline-primary" id="printRecords">
                                <i class="fa fa-print"></i> Print
                            </button>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('partials/app-scripts.php');?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize image preview functionality
            document.querySelectorAll('.recordImages').forEach(function(img) {
                img.addEventListener('click', function() {
                    window.open(this.src, '_blank');
                });
                img.style.cursor = 'pointer';
            });

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const clearSearch = document.getElementById('clearSearch');
            const table = document.getElementById('recordsTable');
            const totalRecordsSpan = document.getElementById('totalRecords');
            let visibleRecords = <?= count($milk_records) ?>;

            function updateTotalRecords() {
                totalRecordsSpan.textContent = visibleRecords.toLocaleString();
            }

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                visibleRecords = 0;
                
                table.querySelectorAll('tbody tr').forEach(row => {
                    let found = false;
                    row.querySelectorAll('td').forEach(cell => {
                        if (cell.textContent.toLowerCase().includes(searchTerm)) {
                            found = true;
                        }
                    });
                    row.style.display = found ? '' : 'none';
                    if (found) visibleRecords++;
                });
                
                updateTotalRecords();
            });

            clearSearch.addEventListener('click', function() {
                searchInput.value = '';
                table.querySelectorAll('tbody tr').forEach(row => {
                    row.style.display = '';
                });
                visibleRecords = <?= count($milk_records) ?>;
                updateTotalRecords();
            });

            // Export to CSV functionality
            document.getElementById('exportCSV').addEventListener('click', function() {
                let csv = [];
                const headers = [];
                
                // Get headers
                table.querySelectorAll('thead th').forEach(th => {
                    if (th.textContent !== 'Image' && th.textContent !== 'Actions') {
                        headers.push(th.textContent.trim());
                    }
                });
                csv.push(headers.join(','));

                // Get visible rows
                table.querySelectorAll('tbody tr').forEach(row => {
                    if (row.style.display !== 'none') {
                        const rowData = [];
                        row.querySelectorAll('td').forEach((cell, index) => {
                            // Skip image and actions columns
                            if (index !== 1 && index !== (isAdmin ? 7 : 6)) {
                                rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
                            }
                        });
                        csv.push(rowData.join(','));
                    }
                });

                const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'milk_records_' + new Date().toISOString().split('T')[0] + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Print functionality
            document.getElementById('printRecords').addEventListener('click', function() {
                const printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>Milk Production Records</title>');
                printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">');
                printWindow.document.write('<style>body { padding: 20px; } .no-print { display: none; }</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write('<h3 class="mb-4">Milk Production Records</h3>');
                
                const tableClone = table.cloneNode(true);
                // Remove image and actions columns
                tableClone.querySelectorAll('tr').forEach(row => {
                    const cells = row.cells;
                    if (cells.length > 0) {
                        row.deleteCell(isAdmin ? 7 : 6); // Remove actions column if it exists
                        row.deleteCell(1); // Remove image column
                    }
                });
                
                printWindow.document.write(tableClone.outerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                
                // Wait for CSS to load
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 1000);
            });

            // Handle delete record functionality
            document.addEventListener('click', function(e) {
                const targetElement = e.target;
                if (targetElement.classList.contains('deleteRecord') || targetElement.closest('.deleteRecord')) {
                    e.preventDefault();
                    const recordElement = targetElement.classList.contains('deleteRecord') ? 
                        targetElement : targetElement.closest('.deleteRecord');
                    const recordId = recordElement.dataset.recordid;
                    const farmerName = recordElement.dataset.name;

                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_DANGER,
                        title: '<i class="fa fa-exclamation-triangle"></i> Confirm Deletion',
                        message: `Are you sure you want to delete the record for <strong>${farmerName}</strong>?<br>
                                <small class="text-muted">This action cannot be undone.</small>`,
                        btnOKLabel: '<i class="fa fa-trash"></i> Delete',
                        btnCancelLabel: '<i class="fa fa-times"></i> Cancel',
                        btnOKClass: 'btn-danger',
                        callback: function(isDelete) {
                            if (isDelete) {
                                // Show loading state
                                const loadingDialog = BootstrapDialog.show({
                                    message: '<i class="fa fa-spinner fa-spin"></i> Deleting record...',
                                    closable: false
                                });

                                $.ajax({
                                    method: 'POST',
                                    url: 'database/delete.php',
                                    data: {
                                        id: recordId,
                                        table: 'milk_records'
                                    },
                                    dataType: 'json',
                                    success: function(response) {
                                        loadingDialog.close();
                                        
                                        BootstrapDialog.show({
                                            type: response.success ? 
                                                BootstrapDialog.TYPE_SUCCESS : 
                                                BootstrapDialog.TYPE_DANGER,
                                            title: response.success ? 'Success' : 'Error',
                                            message: response.message || 'An error occurred while deleting the record.',
                                            buttons: [{
                                                label: 'OK',
                                                action: function(dialog) {
                                                    dialog.close();
                                                    if (response.success) {
                                                        window.location.reload();
                                                    }
                                                }
                                            }]
                                        });
                                    },
                                    error: function(xhr) {
                                        loadingDialog.close();
                                        
                                        BootstrapDialog.show({
                                            type: BootstrapDialog.TYPE_DANGER,
                                            title: 'Server Error',
                                            message: 'Failed to process your request. Please try again.',
                                            buttons: [{
                                                label: 'OK',
                                                action: function(dialog) {
                                                    dialog.close();
                                                }
                                            }]
                                        });
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>