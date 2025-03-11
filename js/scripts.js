document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    var sideBarIsOpen = true;
    var toggleBtn = document.getElementById('sidebar-toggle');
    var dashboard_sidebar = document.getElementById('sidebarMenu');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(event) {
            event.preventDefault();
            if (sideBarIsOpen) {
                dashboard_sidebar.classList.remove('col-lg-2');
                dashboard_sidebar.classList.add('col-lg-1');
                sideBarIsOpen = false;
            } else {
                dashboard_sidebar.classList.remove('col-lg-1');
                dashboard_sidebar.classList.add('col-lg-2');
                sideBarIsOpen = true;
            }
        });
    }

    // Add active class to current nav item
    let currentPath = window.location.pathname;
    let currentPage = currentPath.substring(currentPath.lastIndexOf('/') + 1);
    
    // Find the nav link that matches the current page
    let navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(function(link) {
        let href = link.getAttribute('href');
        if (href && (href === currentPage || href === './' + currentPage)) {
            link.classList.add('active');
        }
    });
});