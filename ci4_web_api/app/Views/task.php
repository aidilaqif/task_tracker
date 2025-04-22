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

<!-- Create Task Modal -->
<div id="addTaskModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Task</h3>
            <span class="close-modal" id="closeAddTaskModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="createTaskForm">
                <div class="form-group">
                    <label for="taskTitle">Task Title*</label>
                    <input type="text" id="taskTitle" name="taskTitle" required>
                </div>
                <div class="form-group">
                    <label for="taskDescription">Description</label>
                    <textarea name="taskDescription" id="taskDescription" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="assignedTo">Assign To*</label>
                    <select name="assignedTo" id="assignedTo" required>
                        <option value="">Select user...</option>
                        <!-- Users will be loaded here -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="dueDate">Due Date</label>
                    <input type="date" id="dueDate" name="dueDate">
                </div>
                <div class="form-group">
                    <label for="taskPriority">Priority*</label>
                    <select name="taskPriority" id="taskPriority" required>
                        <option value="">Select priority...</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelTaskCreate" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Create Task</button>
                </div>
            </form>
        </div>
    </div>
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

    // Add Task button event listener
    document.getElementById('addTaskBtn').addEventListener('click', function(){
        fetchUsers();
        document.getElementById('addTaskModal').style.display = 'block';
    });

    // Close add task modal when clicking X
    document.getElementById('closeAddTaskModal').addEventListener('click', function(){
        document.getElementById('addTaskModal').style.display = 'none';
    });

    // Close create task modal when clicking Cancel button
    document.getElementById('cancelTaskCreate').addEventListener('click', function(){
        document.getElementById('addTaskModal').style.display = 'none';
    });

    // Handle task creation form submission
    document.getElementById('createTaskForm').addEventListener('submit', function(e){
        e.preventDefault();

        const taskTitle = document.getElementById('taskTitle').value;
        const taskDescription = document.getElementById('taskDescription').value;
        const userId = document.getElementById('assignedTo').value;
        const dueDate = document.getElementById('dueDate').value;
        const priority = document.getElementById('taskPriority').value;

        // Create data object for API
        const data = {
            user_id: parseInt(userId),
            title: taskTitle,
            description: taskDescription,
            due_date: dueDate || null,
            status: 'pending',
            priority: priority
        };

        // Call the API to create task
        fetch('/tasks/add', {
            method: 'POST',
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
                // Success - refresh task list
                fetchTasks();
                // Reset form and close modal
                document.getElementById('createTaskForm').reset();
                document.getElementById('addTaskModal').style.display = 'none';
                alert('Task created successfully!');
            } else {
                alert(data.msg || 'Failed to create task');
            }
        })
        .catch(error => {
            console.error('Error creating task:', error);
            alert('Error creating task: ' + error.message);
        });
    });

    // Function to fetch users for assingment dropdown
    function fetchUsers() {
        // Array to hold al promises for fetching users
        const fetchPromises = [];

        // Fetch users without a team
        fetchPromises.push(
            fetch('/users/no-team')
                .then(response => response.json())
                .then(data => data.status ? data.data || [] : [])
                .catch(error => {
                    console.error('Error fetching users without team:', error);
                    return [];
                })
        );

        // Fetch teams to get their IDs
        fetch('/teams/with-count')
            .then(response => response.json())
            .then(data => {
                if (data.status && data.data) {
                    // For each team, fetch its users
                    data.data.forEach(team => {
                        fetchPromises.push(
                            fetch(`/users/team/${team.id}`)
                                .then(response => response.json())
                                .then(data => data.status ? data.data || [] : [])
                                .catch(error => {
                                    console.error(`Error fetching users for team ${team.id}:`);
                                    return [];
                                })
                        );
                    });

                    // Wait for all fetches to complete
                    Promise.all(fetchPromises)
                        .then(results => {
                            // Combine all users fromn different sources
                            let allUsers = [];
                            results.forEach(users => {
                                allUsers = [...allUsers, ...users];
                            });

                            // Remove duplicates
                            const uniqueUsers = [...new Map(allUsers.map(user => [user.id, user])).values()];

                            // Update dropdown
                            updateUserDropdown(uniqueUsers);
                        });
                }
            })
            .catch(error => {
                console.error('Error fetchin teams:', error);
                // If fetching teams fails, try to get users without teams
                Promise.all(fetchPromises)
                    .then(results => {
                        let allUsers = [];
                        results.forEach(users => {
                            allUsers = [...allUsers, ...users];
                        });
                        updateUsersDropdown(allUsers);
                    });
            });
    }

    // Function to update the users dropdown
    function updateUserDropdown(users) {
        const dropdown = document.getElementById('assignedTo');

        // Clear existing options except the default one
        dropdown.innerHTML = '<option value="">Select user...</option>';

        if (users.length === 0) {
            dropdown.innerHTML += '<option value="" disabled>No users found</option>';
            return;
        }

        // Add users to dropdown, avoid duplicates
        const addedUserIds = new Set();

        users.forEach(user => {
            if (!addedUserIds.has(user.id)) {
                dropdown.innerHTML += `<option value="${user.id}">${user.name} (${user.email})</option>`;
                addedUserIds.add(user.id);
            }
        });
    }

    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event){
        const addTaskModal = document.getElementById('addTaskModal');

        if (event.target === addTaskModal) {
            addTaskModal.style.display = 'none';
        }
    });

    // Add escape key support to close modal
    document.addEventListener('keydown', function(event){
        if (event.key === "Escape") {
            document.getElementById('addTaskModal').style.display = 'none';
        }
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

    /* Modal Style */
    .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black background */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000; /* Ensure it's above other content */
    transition: all 0.3s ease;
}

.modal-content {
    background-color: #fff;
    border-radius: 8px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    margin: 0 auto;
    position: relative;
    top: 0;
    transform: translateY(0);
    animation: modalAppear 0.3s ease-out;
}

@keyframes modalAppear {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
}

.modal-header h3 {
    margin: 0;
    color: #212529;
}

.close-modal {
    font-size: 24px;
    font-weight: bold;
    color: #adb5bd;
    cursor: pointer;
    transition: color 0.2s;
}

.close-modal:hover {
    color: #495057;
}

.modal-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #495057;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 16px;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #80bdff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
}

.cancel-button {
    background-color: #6c757d;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.cancel-button:hover {
    background-color: #5a6268;
}

.submit-button {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.submit-button:hover {
    background-color: #218838;
}

</style>

<?= $this->endSection() ?>