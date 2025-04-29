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

// Expose functions that need to be called from task-modals.js
window.displayTaskDetails = displayTaskDetails;