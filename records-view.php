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
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .recordImages:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .btn-group {
            gap: 5px;
            white-space: nowrap;
        }
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }
        .no-image {
            color: #95a5a6;
            font-size: 0.9rem;
            font-style: italic;
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
                                                <?php if (!empty($record['image_url'])): ?>
                                                    <img class="recordImages" src="<?= htmlspecialchars($record['image_url']) ?>" 
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
        $(document).ready(function() {
            // Image preview functionality
            $('.recordImages').on('click', function() {
                window.open(this.src, '_blank');
            }).css('cursor', 'pointer');

            // Search/Filter functionality
            $('#searchInput').on('input', function() {
                const searchText = $(this).val().toLowerCase();
                const $table = $('#recordsTable');
                const $rows = $table.find('tbody tr').not('.empty-message');
                
                $rows.each(function() {
                    const $row = $(this);
                    const text = $row.find('td').not(':last-child').text().toLowerCase();
                    if (text.indexOf(searchText) > -1) {
                        $row.show();
                    } else {
                        $row.hide();
                    }
                });

                // Show/hide empty message
                const visibleRows = $rows.filter(':visible').length;
                if (visibleRows === 0) {
                    if ($table.find('.no-results').length === 0) {
                        const colSpan = isAdmin ? 8 : 7;
                        const emptyMessage = `
                            <tr class="no-results">
                                <td colspan="${colSpan}" class="empty-message">
                                    <div>
                                        <i class="fa fa-search"></i>
                                        <p>No matching records found</p>
                                    </div>
                                </td>
                            </tr>`;
                        $table.find('tbody').append(emptyMessage);
                    }
                } else {
                    $table.find('.no-results').remove();
                }
            });

            // Clear search
            $('#clearSearch').on('click', function() {
                $('#searchInput').val('').trigger('input');
            });

            // Print functionality
            $('#printRecords').on('click', function() {
                window.print();
            });

            // Delete record functionality
            $('.deleteRecord').on('click', function(e) {
                e.preventDefault();
                const recordId = $(this).data('recordid');
                const farmerName = $(this).data('name');

                Swal.fire({
                    title: 'Confirm Deletion',
                    html: `Are you sure you want to delete the record for <strong>${farmerName}</strong>?<br>
                           <small class="text-muted">This action cannot be undone.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa fa-trash"></i> Delete',
                    cancelButtonText: '<i class="fa fa-times"></i> Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            html: '<i class="fa fa-spinner fa-spin"></i>',
                            allowOutsideClick: false,
                            showConfirmButton: false
                        });

                        // Send delete request
                        $.ajax({
                            url: 'database/delete.php',
                            type: 'POST',
                            data: {
                                id: recordId,
                                table: 'milk_records'
                            },
                            dataType: 'json'
                        })
                        .done(function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.message || 'Record deleted successfully.',
                                    icon: 'success',
                                    confirmButtonColor: '#198754'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'Failed to delete record.',
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                        .fail(function() {
                            Swal.fire({
                                title: 'Server Error!',
                                text: 'Failed to process your request. Please try again.',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>