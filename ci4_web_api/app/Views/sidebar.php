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
        <li class="<?= ($active_menu ?? '') == 'user' ? 'active' : '' ?>">
            <a href="<?= site_url('/user') ?>">
                <span>Users</span>
            </a>
        </li>
    </ul>
</div>

