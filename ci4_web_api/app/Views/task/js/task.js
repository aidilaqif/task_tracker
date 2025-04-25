document.addEventListener('DOMContentLoaded', function(){
    // Declare a variable to store all tasks for filtering
    window.allTasks = [];

    // Fetch tasks when the page loads
    fetchTasks();

    // Add event listners for filters
    document.getElementById('searchInput').addEventListener('input', filterTasks);
    document.getElementById('statusFilter').addEventListener('change', filterTasks);
    document.getElementById('priorityFilter').addEventListener('change', filterTasks);
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
            openEditTaskModal(taskId);
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

// Expose functions that need to be called from task-modals.js
window.refreshTaskList = fetchTasks;