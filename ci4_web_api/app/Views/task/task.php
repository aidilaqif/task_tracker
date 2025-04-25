<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="tasks-container">
    <div class="page-header">
        <h2>Task</h2>
        <button id="addTaskBtn" class="action-button add">Add New Task</button>
    </div>
    <div class="filters-container">
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search tasks...">
        </div>
        <div class="filter-options">
            <select id="statusFilter">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="in-progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="request-extension">Request Extension</option>
            </select>
            <select id="priorityFilter">
                <option value="">All Priorities</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Task</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Progress</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="tasksTableBody">
            <!-- Load data from the API -->
             <tr>
                <td colspan="8">Loading tasks data...</td>
             </tr>
        </tbody>
    </table>
</div>

<?= $this->include('task/modals/create') ?>
<?= $this->include('task/modals/edit') ?>

<script>
    <?= $this->include('task/js/task.js') ?>
    <?= $this->include('task/js/task-modals.js') ?>
</script>
<?= $this->endSection() ?>