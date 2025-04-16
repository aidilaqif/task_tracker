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
                <th>ID</th>
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

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Declare a variable to store all tasks for filtering
    window.allTasks = [];

    // Fetch tasks when the page loads
    fetchTasks();

    // Add event listners for filters
    document.getElementById('searchInput').addEventListener('input', filterTasks);
    document.getElementById('statusFilter').addEventListener('change', filterTasks);
    document.getElementById('priorityFilter').addEventListener('change', filterTasks);

    // Add task button event listener
    document.getElementById('addTaskBtn').addEventListener('click', function(){
        // console('Add Task');
    });

    // Fetch tasks from API
    function fetchTasks(){
        fetch('/tasks')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Check if the request successful
                if (data.status) {
                    // Store tasks data globally for filtering
                    window.allTasks = data.data || [];
                    displayTasks(window.allTasks);
                } else {
                    displayError(data.msg || 'Failed to fetch tasks');
                }
            })
            .catch(error => {
                console.error('Error fetching tasks: ', error);
                displayError('Error fetching tasks data: ' + error.message);
            });
    }

    // Display tasks in the table
    function displayTasks(tasks) {
        const tableBody = document.getElementById('tasksTableBody');

        // Clear loading message
        tableBody.innerHTML = '';

        if (tasks.length === 0) {
            // If no teams found, display message
            tableBody.innerHTML = '<tr><td colspan="8">No tasks found</td></tr>';
            return;
        }

        // Loop through each task and create table rows
        tasks.forEach(task => {
            const row = document.createElement('tr');

            // Format the due date
            let formattedDueDate = 'No due date';
            if (task.due_date) {
                const dueDate = new Date(task.due_date);
                formattedDueDate = dueDate.toLocaleDateString();
            }

            // Create progress bar HTML
            const progress = task.progress || 0;
            const progressBarHTML = `
                <div class="progress-mini">
                    <div class="progress-bar" style="width: ${progress}%" class="${progress < 30 ? 'low' : progress < 70 ? 'medium' : 'high'}"></div>
                    <span>${progress}%</span>
                </div>
            `;

            // Create row content
            row.innerHTML = `
                <td>${task.id}</td>
                <td>${task.title}</td>
                <td>${task.assigned_to || 'Unassigned'}</td>
                <td><span class="status-${task.status}">${task.status}</span></td>
                <td>${formattedDueDate}</td>
                <td><span class="priority-${task.priority}">${task.priority}</span></td>
                <td>${progressBarHTML}</td>
                <td>
                    <button class="view" data-id="${task.id}">View</button>
                    <button class="edit" data-id="${task.id}">Edit</button>
                </td>
            `;

            tableBody.appendChild(row);
        });

        addButtonEventListener();
    }

    // Function to add event listeners to the view and edit button
    function addButtonEventListener() {
        // Add click event to view buttons
        document.querySelectorAll('button.view').forEach(button => {
            button.addEventListener('click', function(){
                const taskId = this.getAttribute('data-id');
                window.location.href = `task_detail?task_id=${taskId}`;
            });
        });
        // Add click event to edit buttons
        document.querySelectorAll('button.edit').forEach(button => {
            button.addEventListener('click', function(){
                const taskId = this.getAttribute('data-id');
                alert(`Edit task ${taskId} functionality would go here`);
            });
        });
    }

    // Function to filter tasks based on search input and dropdown selections
    function filterTasks() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const priorityFilter = document.getElementById('priorityFilter').value;

        // Check if there are tasks to filter
        if (!window.allTasks || window.allTasks.length === 0) return;

        // Apply filters
        const filteredTasks = window.allTasks.filter(task => {
            // Search term filter (check title and description)
            const matchesSearch = task.title.toLowerCase().includes(searchTerm) || (task.description && task.description.toLowerCase().includes(searchTerm));

            // Status filter
            const matchesStatus = !statusFilter || task.status === statusFilter;

            // Priority filter
            const matchesPriority = !priorityFilter || task.priority === priorityFilter;

            return matchesSearch && matchesStatus && matchesPriority;

        });

        // Display filtered tasks
        displayTasks(filteredTasks);
    }

    // Function to display error messages
    function displayError(message) {
        const tableBody = document.getElementById('tasksTableBody');
        tableBody.innerHTML = `<tr><td colspan="8" class="error-message">${message}</td></tr>`;
    }

});
</script>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .action-button.add {
        background-color: #28a745;
        padding: 8px 16px;
        font-size: 14px;
    }

    .filters-container {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .search-container {
        flex: 1;
        min-width: 200px;
    }

    .search-container input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    .filter-options {
        display: flex;
        gap: 10px;
    }

    .filter-options select {
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: white;
    }

    .progress-mini {
        width: 100%;
        background-color: #e9ecef;
        border-radius: 4px;
        position: relative;
        height: 18px;
        overflow: hidden;
    }

    .progress-mini .progress-bar {
        height: 100%;
        background-color: #28a745;
    }

    .progress-mini .progress-bar.low {
        background-color: #dc3545;
    }

    .progress-mini .progress-bar.medium {
        background-color: #ffc107;
    }

    .progress-mini .progress-bar.high {
        background-color: #28a745;
    }

    .progress-mini span {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.75rem;
        color: #212529;
        font-weight: 600;
        text-shadow: 0 0 2px rgba(255, 255, 255, 0.7);
    }

    .error-message {
        color: #dc3545;
        text-align: center;
        padding: 20px;
    }


</style>

<?= $this->endSection() ?>