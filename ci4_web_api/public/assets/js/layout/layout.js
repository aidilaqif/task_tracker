document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const body = document.body;

    // Create backdrop for mobile
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-mobile-backdrop';
    document.body.appendChild(backdrop);

    // Function to toggle sidebar
    function toggleSidebar() {
        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            sidebar.classList.toggle('mobile-open');
            backdrop.classList.toggle('show');
        } else {
            sidebar.classList.toggle('collapsed');
            body.classList.toggle('sidebar-collapsed');

            // Save desktop state to localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
    }

    // Check localStorage for saved sidebar state (desktop only)
    if (window.innerWidth >= 768 && localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }

    // Toggle sidebar on button click
    sidebarToggle.addEventListener('click', toggleSidebar);

    // Mobile toggle button
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when clicking on backdrop
    backdrop.addEventListener('click', function () {
        sidebar.classList.remove('mobile-open');
        backdrop.classList.remove('show');
    });

    // Handle clicks on sidebar links on mobile
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('mobile-open');
                backdrop.classList.remove('show');
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function () {
        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            // On mobile: remove desktop classes, potentially add mobile ones
            sidebar.classList.remove('collapsed');
            body.classList.remove('sidebar-collapsed');
        } else {
            // On desktop: remove mobile classes, potentially add desktop ones
            sidebar.classList.remove('mobile-open');
            backdrop.classList.remove('show');

            // Restore desktop state from localStorage
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                body.classList.add('sidebar-collapsed');
            }
        }
    });
});