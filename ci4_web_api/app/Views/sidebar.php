<div class="sidebar">
    <div class="sidebar-header">
        <h3>Task Tracker</h3>
        <?php if(session()->get('isLoggedIn')): ?>
        <div class="user-info">
            <span><?= session()->get('name') ?></span>
            <small>(<?= session()->get('role') ?>)</small>
        </div>
        <?php endif; ?>
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
        <li class="<?= ($active_menu ?? '') == 'user' ? 'active' : '' ?>">
            <a href="<?= site_url('/user') ?>">
                <span>Users</span>
            </a>
        </li>
        <li class="<?= ($active_menu ?? '') == 'notifications' ? 'active' : '' ?>">
            <a href="<?= site_url('/notifications') ?>">
                <span>Notifications</span>
            </a>
        </li>
        <?php if(session()->get('isLoggedIn')): ?>
        <li class="logout-item">
            <a href="<?= site_url('/logout') ?>">
                <span>Logout</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>