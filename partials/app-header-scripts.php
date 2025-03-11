<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    :root {
        --primary-color: #27ae60;
        --primary-dark: #219a52;
        --secondary-color: #2c3e50;
        --success-color: #059669;
        --warning-color: #f59e0b;
        --danger-color: #dc2626;
        --info-color: #2563eb;
    }

    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        background-color: #f8fafc;
    }

    .sidebar {
        background: linear-gradient(180deg, var(--secondary-color), var(--primary-dark));
        min-height: 100vh;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 0.8rem 1rem;
        border-radius: 0.5rem;
        margin: 0.2rem 0;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar .nav-link i {
        width: 1.5rem;
        text-align: center;
        margin-right: 0.75rem;
    }

    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .card.border-primary {
        border-left: 4px solid var(--primary-color) !important;
    }

    .card.border-success {
        border-left: 4px solid var(--success-color) !important;
    }

    .card.border-warning {
        border-left: 4px solid var(--warning-color) !important;
    }

    .card.border-info {
        border-left: 4px solid var(--info-color) !important;
    }

    .card.border-danger {
        border-left: 4px solid var(--danger-color) !important;
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .table {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .table thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
        font-weight: 600;
    }

    .table tbody tr:hover {
        background-color: #f1f5f9;
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(39, 174, 96, 0.25);
    }

    .alert {
        border: none;
        border-radius: 0.5rem;
    }

    .badge {
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
        background: #94a3b8;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    /* Print styles */
    @media print {
        .sidebar,
        .btn-toolbar,
        .no-print {
            display: none !important;
        }
        
        .card {
            break-inside: avoid;
            box-shadow: none !important;
        }
        
        body {
            background: white !important;
        }
    }
</style>