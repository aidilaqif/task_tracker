<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="users-container">
    <div class="page-header">
        <h2>User Management</h2>
        <button id="addUserBtn" class="action-button add">Add New User</button>
    </div>
    <div class="filters-container">
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search users by name or email...">
        </div>
        <div class="filter-options">
            <select id="roleFilter">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
            <select id="teamFilter">
                <option value="">All Teams</option>
                <!-- Team options will be loaded dynamically -->
            </select>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Team</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="usersTableBody">
            <!-- User data will be loaded here -->
             <tr>
                <td colspan="6">Loading users data...</td>
             </tr>
        </tbody>
    </table>
    <div class="pagination-container" id="paginationContainer">
        <!-- Pagination controls will be added here -->
    </div>
</div>

<?= $this->include('user/modals/add') ?>
<?= $this->include('user/modals/edit') ?>

<script>
    <?= $this->include('user/js/user.js') ?>
    <?= $this->include('user/js/user-modals.js') ?>
</script>

<?= $this->endSection() ?>