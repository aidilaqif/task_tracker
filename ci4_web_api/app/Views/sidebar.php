<div class="sidebar">
    <div class="sidebar-header">
        <h3>Task Tracker</h3>
    </div>
    <ul class="sidebar-menu">
        <li class="<?= ($active_menu ?? '') == 'dashboard' ? 'active' : '' ?>">
            <a href="<?= site_url('/dashboard') ?>">
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'team' ? 'active' : '' ?>">
            <a href="<?= site_url('/team') ?>">
                <span>Team</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'task' ? 'active' : '' ?>">
            <a href="<?= site_url('/task') ?>">
                <span>Task</span>
            </a>
        </li>
    </ul>
</div>

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
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    .sidebar-header {
        padding: 15px 20px;
        border-bottom: 1px solid #4f5962;
        margin-bottom: 10px;
    }
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .sidebar-menu li {
        position: relative;
        margin-bottom: 5px;
    }
    .sidebar-menu li a {
        padding: 12px 20px;
        display: block;
        color: #c2c7d0;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        font-weight: 500;
    }
    .sidebar-menu li a:hover {
        background-color: #3f474e;
        color: #fff;
        border-left-color: #6c757d;
    }
    .sidebar-menu li.active a {
        background-color: #2c3136;
        color: #fff;
        border-left: 4px solid #007bff;
        font-weight: 600;
    }
    .sidebar-menu li a span {
        position: relative;
        display: inline-block;
        vertical-align: middle;
    }

    .sidebar-menu li.active a span::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #007bff;
    }
    .content-wrapper {
        margin-left: 250px;
        padding: 20px;
        min-height: 100vh;
    }
</style>