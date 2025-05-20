<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-section">
            <h3 class="app-title">Task Tracker</h3>
            <button id="sidebarToggle" class="sidebar-toggle-btn" title="Toggle Sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        
        <?php if(session()->get('isLoggedIn')): ?>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <span class="user-name"><?= session()->get('name') ?></span>
                <span class="user-role">(<?= session()->get('role') ?>)</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <ul class="sidebar-menu">
        <li class="<?= ($active_menu ?? '') == 'dashboard' ? 'active' : '' ?>">
            <a href="<?= site_url('/dashboard') ?>" data-title="Dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'team' ? 'active' : '' ?>">
            <a href="<?= site_url('/team') ?>" data-title="Team">
                <i class="fas fa-users"></i>
                <span>Team</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'task' ? 'active' : '' ?>">
            <a href="<?= site_url('/task') ?>" data-title="Task">
                <i class="fas fa-tasks"></i>
                <span>Task</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'user' ? 'active' : '' ?>">
            <a href="<?= site_url('/user') ?>" data-title="Users">
                <i class="fas fa-user"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'notifications' ? 'active' : '' ?>">
            <a href="<?= site_url('/notifications') ?>" data-title="Notifications">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </li>
        <?php if(session()->get('isLoggedIn')): ?>
        <li class="logout-item">
            <a href="<?= site_url('/logout') ?>" data-title="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>