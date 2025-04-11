<!-- Sidebar Component -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Task Tracker</h3>
    </div>
    <ul class="sidebar-menu">
        <li class="<?= ($active_menu ?? '') == 'dashboard' ? 'active' : '' ?>">
            <a href="<?= site_url('admin/dashboard') ?>">
                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'team' ? 'active' : '' ?>">
            <a href="<?= site_url('admin/team') ?>">
                <i class="fa fa-users"></i> <span>Team Management</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'tasks' ? 'active' : '' ?>">
            <a href="<?= site_url('admin/tasks') ?>">
                <i class="fa fa-tasks"></i> <span>Task Management</span>
            </a>
        </li>
        <li>
            <a href="<?= site_url('logout') ?>">
                <i class="fa fa-sign-out"></i> <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<!-- Add simple CSS for the sidebar -->
<style>
    .sidebar {
        width: 250px;
        min-height: 100vh;
        background-color: #343a40;
        color: #fff;
        position: fixed;
        left: 0;
        top: 0;
        padding: 20px 0;
    }
    
    .sidebar-header {
        padding: 10px 20px;
        border-bottom: 1px solid #4f5962;
    }
    
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sidebar-menu li {
        position: relative;
    }
    
    .sidebar-menu li a {
        padding: 12px 20px;
        display: block;
        color: #c2c7d0;
        text-decoration: none;
        transition: background 0.3s;
    }
    
    .sidebar-menu li a:hover,
    .sidebar-menu li.active a {
        background-color: #3f474e;
        color: #fff;
    }
    
    .sidebar-menu li.active a {
        border-left: 4px solid #007bff;
    }
    
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
        min-height: 100vh;
    }
</style>