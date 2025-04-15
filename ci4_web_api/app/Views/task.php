<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="tasks-container">
    <h2>Task</h2>
    <p>This is the task section</p>
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
                    displayTasks(data.data || []);
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

            // Create row content
            row.innerHTML = `
                <td>${task.id}</td>
                <td>${task.title}</td>
                <td>${task.assigned_to || 'Unassigned'}</td>
                <td><span class="status-${task.status}">${task.status}</span></td>
                <td>${formattedDueDate}</td>
                <td><span class="priority-${task.priority}">${task.priority}</span></td>
                <td>${task.progress || '0'}%</td>
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
        })
    }

    // Function to display error messages
    function displayError(message) {
        const tableBody = document.getElementById('tasksTableBody');
        tableBody.innerHTML = `<tr><td colspan="8">${message}</td></tr>`;
    }

    // Fetch tasks when the page loads
    fetchTasks();
});
</script>

<?= $this->endSection() ?>