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

<script>
    document.addEventListener('DOMContentLoaded', function (){
        // Get task ID from the URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const taskId = urlParams.get('task_id');

        if (!taskId) {
            displayError('No task ID specified');
            return;
        }

        // Fetch task details
        fetchTaskDetails(taskId);

        // Add event listeners for action buttons
        document.getElementById('editTaskBtn').addEventListener('click', function(){
            alert('Edit functionality soon be implemented');
        });

        document.getElementById('updateStatusBtn').addEventListener('click', function(){
            alert('Status update functionality soon be implemented');
        });

        document.getElementById('updateProgressBtn').addEventListener('click', function(){
            alert('Progress update functionality soon be implemented');
        });
    });

    function fetchTaskDetails (taskId) {
        fetch(`/tasks/view/${taskId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status) {
                    displayTaskDetails(data.data);
                } else {
                    displayError(data.msg || 'Failed to fetch task details');
                }
            })
            .catch(error => {
                console.error('Error fetch task details:', error);
                displayError('Error fetching task details: ' + error.message);
            });
    }

    function displayTaskDetails(task) {
        // Update task title and basic info
        document.getElementById('taskTitle').textContent = task.title;
        document.getElementById('taskId').textContent = task.id;

        // Update task status with appropriate class
        const statusElem = document.getElementById('taskStatus');
        statusElem.textContent = task.status;
        statusElem.className = 'task-status status-' + task.status;

        // Update task priority with appropriate class
        const priorityElem = document.getElementById('taskPriority');
        priorityElem.textContent = task.priority;
        priorityElem.className = 'task-priority priority-' + task.priority;

        // Update assingment, dates and description
        document.getElementById('assignedTo').textContent = task.assigned_to || 'Unassigned';
        document.getElementById('dueDate').textContent = task.due_date || 'No due date';
        document.getElementById('taskDescription').textContent = task.description || 'No decription provided';

        // Format dates
        const createdDate = new Date(task.created_at);
        const updatedDate = new Date(task.updated_at);
        document.getElementById('createdAt').textContent = formatDate(createdDate);
        document.getElementById('updatedAt').textContent = formatDate(updatedDate);

        const progress = task.progress || 0;
        // Set progress bar color based on completion
        const progressBar = document.getElementById('progressBar');
        progressBar.style.width = progress + '%';

        if (progress < 30) {
            progressBar.className = 'progress-bar progress-low';
        } else if (progress < 70) {
            progressBar.className = 'progress-bar progress-medium';
        } else {
            progressBar.className = 'progress-bar progress-high';
        }

        // Update progress text
        document.getElementById('progressValue').textContent = progress + '%';
    }

    function formatDate(date) {
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    function displayError(message) {
        const container = document.querySelector('.task-detail-container');
        container.innerHTML = `
            <div class="error-message">
                <h3>Error</h3>
                <p>${message}</p>
                <a href="<?= site_url('/task') ?>" class="btn-back">Back to Tasks</a>
            </div>
        `;
    }
</script>

<style>
    .task-detail-container {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 30px;
    }

    .back-button {
        margin-bottom: 20px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        color: #495057;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .btn-back:hover {
        color: #007bff;
    }

    .btn-back i {
        margin-right: 8px;
    }

    .task-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 15px;
    }

    .task-header h2 {
        margin: 0;
        color: #212529;
        font-size: 1.8rem;
    }

    .task-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 10px;
    }

    .task-id {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .task-section {
        margin-bottom: 30px;
    }

    .task-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #495057;
        font-size: 1.2rem;
        font-weight: 600;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 8px;
    }

    .detail-group {
        display: flex;
        margin-bottom: 12px;
    }

    .detail-group label {
        font-weight: 600;
        min-width: 120px;
        color: #495057;
    }

    .task-description {
        line-height: 1.6;
    }

    .progress-bar-container {
        width: 100%;
        height: 20px;
        background-color: #e9ecef;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
        margin-top: 5px;
    }

    .progress-bar {
        height: 100%;
        background-color: #28a745;
        width: 0%;
        transition: width 0.4s ease;
    }

    .progress-low {
        background-color: #dc3545;
    }

    .progress-medium {
        background-color: #ffc107;
    }

    .progress-high {
        background-color: #28a745;
    }

    #progressValue {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #212529;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .task-actions {
        margin-top: 30px;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .action-button {
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        color: white;
    }

    .action-button.edit {
        background-color: #6c757d;
    }

    .action-button.status {
        background-color: #17a2b8;
    }

    .action-button.progress {
        background-color: #28a745;
    }

    .action-button:hover {
        opacity: 0.9;
    }

    .error-message {
        text-align: center;
        padding: 30px;
    }

    .error-message h3 {
        color: #dc3545;
        margin-bottom: 15px;
    }

    .error-message .btn-back {
        display: inline-block;
        margin-top: 20px;
        padding: 8px 16px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
</style>
<?= $this->endSection() ?>