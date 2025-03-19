   <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/scripts.js"></script>

<script>
// Utility functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2
    }).format(amount);
};

const formatNumber = (number, decimals = 2) => {
    return new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleString('en-KE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Toast notifications
const showToast = (message, type = 'success') => {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    Toast.fire({
        icon: type,
        title: message
    });
};

// Confirmation dialogs
const confirmAction = async (title, text, type = 'warning') => {
    const result = await Swal.fire({
        title: title,
        text: text,
        icon: type,
        showCancelButton: true,
        confirmButtonColor: '#27ae60',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
    });

    return result.isConfirmed;
};

// Form validation
const validateForm = (formElement) => {
    const form = $(formElement);
    if (form[0].checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        form.addClass('was-validated');
        return false;
    }
    return true;
};

// AJAX form submission
const submitForm = async (formElement, successMessage = 'Operation successful') => {
    const form = $(formElement);
    
    if (!validateForm(form)) return;

    try {
        const response = await $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json'
        });

        if (response.success) {
            showToast(successMessage);
            return response;
        } else {
            showToast(response.message || 'Operation failed', 'error');
            return false;
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
        console.error('Form submission error:', error);
        return false;
    }
};

// Initialize Bootstrap components
$(document).ready(function() {
    // Initialize all tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize all popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Add validation classes to forms
    const forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>
