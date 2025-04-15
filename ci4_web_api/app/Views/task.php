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
                <th>Description</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Priority</th>
                <th>Created At</th>
                <th>Updated At</th>
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

            // Format the date
            const createdAt = new Date(task.created_at);
            const formattedCreateDate = createdAt.toLocaleDateString() + '' + createdAt.toLocaleTimeString();
            const updatedAt = new Date(task.updated_at);
            const formattedUpdateDate = updatedAt.toLocaleDateString() + '' + updatedAt.toLocaleTimeString();

            // Create row content
            row.innerHTML = `
                <td>${task.id}</td>
                <td>${task.title}</td>
                <td>${task.description || '-'}</td>
                <td>${task.assigned_to || 'Unassigned'}</td>
                <td><span class="status-${task.status}">${task.status}</span></td>
                <td>${task.due_date || '-'}</td>
                <td><span class="priority-${task.priority}">${task.priority}</span></td>
                <td>${formattedCreateDate}</td>
                <td>${formattedUpdateDate}</td>
                <td>${task.progress || '0'}%</td>
                <td>
                    <button class="action-button view" data-id="${task.id}">View</button>
                    <button class="action-button" data-id="${task.id}">Edit</button>
                </td>
            `;

            tableBody.appendChild(row);
        });
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