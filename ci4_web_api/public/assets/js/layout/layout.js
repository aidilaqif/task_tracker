document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const body = document.body;

    // Create backdrop for mobile slide-in sidebar (when needed)
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-mobile-backdrop';
    document.body.appendChild(backdrop);

    // Function to handle responsive sidebar behavior
    function handleResponsiveSidebar() {
        const isMobile = window.innerWidth < 768;

        if (isMobile) {
            // On mobile: convert to bottom navigation
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('mobile-open');
            sidebar.classList.add('bottom-nav');
            body.classList.remove('sidebar-collapsed');
            backdrop.classList.remove('show');
        } else {
            // On desktop: restore normal sidebar
            sidebar.classList.remove('bottom-nav');
            sidebar.classList.remove('mobile-open');

            // Restore desktop collapsed state if previously set
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                body.classList.add('sidebar-collapsed');
            }
        }
    }

    // Function to toggle sidebar (for desktop only)
    function toggleSidebar() {
        const isMobile = window.innerWidth < 768;

        if (!isMobile) {
            // Only toggle on desktop
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

    // Initial setup based on screen size
    handleResponsiveSidebar();

    // Toggle sidebar on button click (desktop only)
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    // Mobile toggle button (if we need slide-in navigation)
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('mobile-open');
            backdrop.classList.toggle('show');
        });
    }

    // Close sidebar when clicking on backdrop
    backdrop.addEventListener('click', function () {
        sidebar.classList.remove('mobile-open');
        backdrop.classList.remove('show');
    });

    // Handle window resize
    window.addEventListener('resize', function () {
        handleResponsiveSidebar();
    });
});