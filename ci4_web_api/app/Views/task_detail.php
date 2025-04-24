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

<!-- Edit Task Modal -->
<div id="editTaskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Task</h3>
            <span class="close-modal" id="closeEditTaskModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editTaskForm">
                <input type="hidden" id="editTaskId">
                <div class="form-group">
                    <label for="editTaskTitle">Task Title*</label>
                    <input type="text" id="editTaskTitle" required>
                </div>
                <div class="form-group">
                    <label for="editTaskDescription">Description</label>
                    <textarea id="editTaskDescription" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="editDueDate">Due Date</label>
                    <input type="date" id="editDueDate">
                </div>
                <div class="form-group">
                    <label for="editTaskPriority">Priority*</label>
                    <select id="editTaskPriority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editTaskStatus">Status*</label>
                    <select id="editTaskStatus" required>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="request-extension">Request Extension</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelEditBtn" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="updateStatusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Task Status</h3>
            <span class="close-modal" id="closeStatusModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="updateStatusForm">
                <input type="hidden" id="statusTaskId">
                <div class="form-group">
                    <label for="newTaskStatus">Status*</label>
                    <select id="newTaskStatus" required>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="request-extension">Request Extension</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelStatusButton" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Progress Update Modal -->
<div id="updateProgressModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Task Progress</h3>
            <span class="close-modal" id="closeProgressModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="updateProgressForm">
                <input type="hidden" id="progressTaskId">
                <div class="form-group">
                    <label for="newTaskProgress">*Progress (%)</label>
                    <input type="number" id="updateTaskProgress" name="updateTaskProgress" min="0" max="100" value="0">
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelProgressButton" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function (){
        // Get task ID from the URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const taskId = urlParams.get('task_id');

        // Store current task data globally
        window.currentTask = null;

        if (!taskId) {
            displayError('No task ID specified');
            return;
        }

        // Fetch task details
        fetchTaskDetails(taskId);

        // Edit Task button event listener
        document.getElementById('editTaskBtn').addEventListener('click', function(){
            openEditTaskModal();
        });

        // Close edit task modal when clicking X
        document.getElementById('closeEditTaskModal').addEventListener('click', function(){
            document.getElementById('editTaskModal').classList.remove('show');
        });

        // Close edit task modal when clicking Cancel button
        document.getElementById('cancelEditBtn').addEventListener('click', function(){
            document.getElementById('editTaskModal').classList.remove('show');
        });

        // Handle edit task form submission
        document.getElementById('editTaskForm').addEventListener('submit', function(e){
            e.preventDefault();
            updateTask();
        });

        // Update Status button event listener
        document.getElementById('updateStatusBtn').addEventListener('click', function(){
            openStatusModal();
        });

        // Close status modal when clicking X
        document.getElementById('closeStatusModal').addEventListener('click', function(){
            document.getElementById('updateStatusModal').classList.remove('show');
        });

        // Close status modal when clicking Cancel button
        document.getElementById('cancelStatusButton').addEventListener('click', function(){
            document.getElementById('updateStatusModal').classList.remove('show');
        });

        // Handle status update form submission
        document.getElementById('updateStatusForm').addEventListener('submit', function(e){
            e.preventDefault();
            updateTaskStatus();
        })

        // Update Progress button event listener
        document.getElementById('updateProgressBtn').addEventListener('click', function(){
            openProgressModal();
        });

        // Close progress modal when clicking X
        document.getElementById('closeProgressModal').addEventListener('click', function(){
            document.getElementById('updateProgressModal').classList.remove('show');
        });

        // Close progress modal when clicking Cancel button
        document.getElementById('cancelProgressButton').addEventListener('click', function(){
            document.getElementById('updateProgressModal').classList.remove('show');
        });

        // Handle progress update form submission
        document.getElementById('updateProgressForm').addEventListener('submit', function(e){
            e.preventDefault();
            updateTaskProgress();
        })

        // Close modal when clicking outside
        window.addEventListener('click', function(event){
            const editModal = document.getElementById('editTaskModal');
            const statusModal = document.getElementById('updateStatusModal');
            const progressModal = document.getElementById('updateProgressModal');

            if (event.target === editModal) {
                editModal.classList.remove('show');
            }

            if (event.target === statusModal) {
                statusModal.classList.remove('show');
            }

            if (event.target === progressModal) {
                progressModal.classList.remove('show');
            }
        });

        // Add escape key support to close modal
        document.addEventListener('keydown', function(event){
            if (event.key === "Escape") {
                document.getElementById('editTaskModal').classList.remove('show');
                document.getElementById('updateStatusModal').classList.remove('show');
                document.getElementById('updateProgressModal').classList.remove('show');
            }
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
                    window.currentTask = data.data;
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

    function openEditTaskModal() {
        if (!window.currentTask) {
            alert('Task data not available');
            return;
        }

        const task = window.currentTask;

        // Populate form with current task data
        document.getElementById('editTaskId').value = task.id;
        document.getElementById('editTaskTitle').value = task.title;
        document.getElementById('editTaskDescription').value = task.description || '';
        document.getElementById('editTaskStatus').value = task.status;
        document.getElementById('editTaskPriority').value = task.priority;

        // Format and set due date if available
        if (task.due_date) {
            // Format date to YYYY-MM-DD for date input
            const dueDate = new Date(task.due_date);
            const year = dueDate.getFullYear();
            const month = String(dueDate.getMonth() + 1).padStart(2, '0');
            const day = String(dueDate.getDate()).padStart(2, '0');
            document.getElementById('editDueDate').value = `${year}-${month}-${day}`;
        } else {
            document.getElementById('editDueDate').value = '';
        }

        // Show the modal
        document.getElementById('editTaskModal').classList.add('show');
    }

    function updateTask() {
        const taskId = document.getElementById('editTaskId').value;
        const title = document.getElementById('editTaskTitle').value;
        const description = document.getElementById('editTaskDescription').value;
        const dueDate = document.getElementById('editDueDate').value;
        const status = document.getElementById('editTaskStatus').value;
        const priority = document.getElementById('editTaskPriority').value;

        // Create data object for API
        const data = {
            title: title,
            description: description,
            due_date: dueDate || null,
            status: status,
            priority: priority,
        };

        // Call the API to update task
        fetch(`/tasks/edit/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status) {
                // Success - update the UI with the new task data
                window.currentTask = data.data;
                displayTaskDetails(data.data);

                // Close the modal
                document.getElementById('editTaskModal').classList.remove('show');

                // Show success message
                alert('Task updated successfully!');
            } else {
                alert(data.msg || 'Failed to update task');
            }
        })
        .catch(error => {
            console.error('Error updating task:', error);
            alert('Error updating task: ' + error.message);
        });
    }

    function openStatusModal() {
        if (!window.currentTask) {
            alert('Task data not available');
            return;
        }

        const task = window.currentTask;

        // Set task ID
        document.getElementById('statusTaskId').value = task.id;

        // Set current status
        document.getElementById('newTaskStatus').value = task.status;

        // Show the modal
        document.getElementById('updateStatusModal').classList.add('show');
    }

    function formatDate(date) {
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    function updateTaskStatus() {
        const taskId = document.getElementById('statusTaskId').value;
        const status = document.getElementById('newTaskStatus').value;

        // Call the API to update task status
        fetch(`/tasks/status/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status) {
                // Success - update UI with the new task data
                window.currentTask = data.data;
                displayTaskDetails(data.data);

                // Close modal
                document.getElementById('updateStatusModal').classList.remove('show');

                // Show success message
                alert('Task status updated succesfully!');
            } else {
                alert(data.msg || 'Failed to update task status');
            }
        })
        .catch(error => {
            console.error('Error updating task status:', error);
            alert('Error updating task status: ' + error.message);
        });
    }

    function openProgressModal(){
        if (!window.currentTask) {
            alert('Task data not available');
            return;
        }

        const task = window.currentTask;

        // Set task ID
        document.getElementById('progressTaskId').value = task.id;

        // Set current progress
        document.getElementById('updateTaskProgress').value = task.progress || 0;
        // Show the modal
        document.getElementById('updateProgressModal').classList.add('show');
    }

    function updateTaskProgress(){
        const taskId = window.currentTask.id;
        const progress = parseInt(document.getElementById('updateTaskProgress').value);

        // Validate progress value
        if (isNaN(progress) || progress < 0 || progress > 100) {
            alert('Please enter a valid progress value between 0 and 100');
            return;
        }

        // Call the API to update task progress
        fetch(`/tasks/progress/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ progress: progress })
        })
        .then(response => {
            if(!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then( data => {
            if (data.status) {
                // Success - udpate UI with new task data
                window.currentTask = data.data;
                displayTaskDetails(data.data);

                // Close modal
                document.getElementById('updateProgressModal').classList.remove('show');

                // Show success message
                alert('Task progress updated successfully!');
            } else {
                alert(data.msg || 'Failed to update task progress');
            }
        })
        .catch(error => {
            console.error('Error updating task progress:', error);
            alert('Error updating task progress: ' + error.message);
        });

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