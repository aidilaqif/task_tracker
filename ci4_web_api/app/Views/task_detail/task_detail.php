<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="task-detail-container">
    <div class="back-button">
        <a href="<?= site_url('/task') ?>" class="btn-back"><i class="fas fa-arrow-left"></i>Back to Tasks</a>
    </div>

    <div class="task-header">
        <h2 id="taskTitle">Task Details</h2>
        <div class="task-meta">
            <span class="task-id">ID: <span id="taskId">-</span></span>
            <span class="task-status" id="taskStatus">-</span>
            <span class="task-priority" id="taskPriority">-</span>
        </div>
    </div>

    <div class="task-content">
        <div class="task-section">
            <h3>Basic Information</h3>
            <div class="detail-group">
                <label>Assigned To:</label>
                <div id="assignedTo">-</div>
            </div>
            <div class="detail-group">
                <label>Due Date:</label>
                <div id="dueDate">-</div>
            </div>
            <div class="detail-group">
                <label>Progress:</label>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar"></div>
                    <span id="progressValue">0%</span>
                </div>
            </div>
        </div>

        <div class="task-section">
            <h3>Description</h3>
            <div class="task-description" id="taskDescription">-</div>
        </div>

        <div class="task-section">
            <h3>Timeline</h3>
            <div class="detail-group">
                <label>Created:</label>
                <div id="createdAt">-</div>
            </div>
            <div class="detail-group">
                <label>Last Updated:</label>
                <div id="updatedAt">-</div>
            </div>
        </div>

        <div class="task-actions">
            <button id="editTaskBtn" class="action-button edit">Edit Task</button>
            <button id="updateStatusBtn" class="action-button status">Update Status</button>
            <button id="updateProgressBtn" class="action-button progress">Update Progress</button>
        </div>
    </div>
</div>

<?= $this->include('task_detail/modals/edit') ?>
<?= $this->include('task_detail/modals/update_progress') ?>
<?= $this->include('task_detail/modals/update_status') ?>

<script>
    <?= $this->include('task_detail/js/task_detail.js') ?>
</script>
<?= $this->endSection() ?>