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
        document.getElementById('addTaskModal').classList.add('show');
    });

    // Close add task modal when clicking X
    document.getElementById('closeAddTaskModal').addEventListener('click', function(){
        document.getElementById('addTaskModal').classList.remove('show');
    });

    // Clode edit task modal when clicking X
    document.getElementById('closeEditTaskModal').addEventListener('click', function(){
        document.getElementById('editTaskModal').classList.remove('show');
    });

    // Close create task modal when clicking Cancel button
    document.getElementById('cancelTaskCreate').addEventListener('click', function(){
        document.getElementById('addTaskModal').classList.remove('show');
    });

    // Close edit task modal when clicking Cancel button
    document.getElementById('cancelTaskEdit').addEventListener('click', function(){
        document.getElementById('editTaskModal').classList.remove('show');
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
                document.getElementById('addTaskModal').classList.remove('show');
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

    // Handle task edit form submission
    document.getElementById('editTaskForm').addEventListener('submit', function(e){
        e.preventDefault();

        const taskId = document.getElementById('editTaskId').value;
        const taskTitle = document.getElementById('editTaskTitle').value;
        const taskDescription = document.getElementById('editTaskDescription').value;
        const userId = document.getElementById('editAssignedTo').value;
        const dueDate = document.getElementById('editDueDate').value;
        const status = document.getElementById('editTaskStatus').value;
        const priority = document.getElementById('editTaskPriority').value;
        const progress = document.getElementById('editTaskProgress').value;

        // Update progress
        fetch(`/tasks/progress/${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                progress: parseInt(progress)
            })
        })
        .then(response => {
            if (!response.ok) {
                console.warn('Progress update failed, continuing with general update');
                return { status: false };
            }
            return response.json();
        })
        .then ((progressResponse) => {
            // Get the automatically updated status if available
            let updatedStatus = status; // Default to the form value
        
            if (progressResponse && progressResponse.status && progressResponse.data) {
                // If the progress update was successful and returned the updated task, use the status from the response
                console.log("Progress update response:", progressResponse);
                updatedStatus = progressResponse.data.status;
                console.log("Status after progress update:", updatedStatus);
            }
            // Create data object for API
            const data = {
                user_id: parseInt(userId),
                title: taskTitle,
                description: taskDescription,
                due_date: dueDate || null,
                status: updatedStatus,
                priority: priority,
            };

            // Call the API to update task
            return fetch(`/tasks/edit/${taskId}`,{
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
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
                // Close modal
                document.getElementById('editTaskModal').classList.remove('show');
                alert('Task updated successfully!');
            } else {
                alert(data.msg || 'Failed to update task');
            }
        })
        .catch(error => {
            console.error('Error updating task:', error);
            alert('Error updating task: ' + error.message);
        });
    });

    // Function to fetch users for assingment dropdown
    function fetchUsers() {
        // Clear existing options first
        const addDropdown = document.getElementById('assignedTo');
        const editDropdown = document.getElementById('editAssignedTo');

        if (addDropdown) addDropdown.innerHTML = '<option value="">Select user...</option>';
        if (editDropdown) editDropdown.innerHTML = '<option value="">Select user...</option>';

        // Fetch all available users - without team and with team
        const fetchNoTeamUsers = fetch('/users/no-team')
            .then(response => response.json())
            .then(data => data.status ? data.data || [] : [])
            .catch(error => {
                console.error('Error fetching users without team:', error);
                return [];
            });

        // Fetch teams to get users from each team
        const fetchTeamsAndUsers = fetch('/teams/with-count')
            .then(response => response.json())
            .then(data => {
                if (!data.status && !data.data) return[];

                // Create an array of promises for fetching users from each team
                const teamUserPromises = data.data.map(team =>
                    fetch(`/users/team/${team.id}`)
                        .then(response => response.json())
                        .then(data => data.status ? data.data || [] : [])
                        .catch(error => {
                            console.error(`Error fetching users for team ${team.id}:`, error);
                            return [];
                        })
                );

                // Wait for all team user request to complete
                return Promise.all(teamUserPromises);
            })
            .then(teamUserResults =>{
                // Flatten the array of arrays into a single array of users
                return teamUserResults.flat();
            })
            .catch(error => {
                console.error('Error fetching teams or team users:', error);
                return [];
            });

        // Wait for both user fetching operations to complete
        return Promise.all([fetchNoTeamUsers, fetchTeamsAndUsers])
            .then(results => {
                // Combine all users from different sources
                const allUsers = [...results[0], ...results[1]];

                // Remove duplicates by user id
                const uniqueUsers = [...new Map(allUsers.map(user => [user.id, user])).values()];

                // Update dropdowns
                updateUserDropdown(uniqueUsers);

                return uniqueUsers;
            });
    }

    // Function to update the users dropdown
    function updateUserDropdown(users) {
        const addDropdown = document.getElementById('assignedTo');
        const editDropdown = document.getElementById('editAssignedTo');

        if (users.length === 0) {
            const noUsersOption = '<option value="" disabled>No users found</option>';
            if (addDropdown) addDropdown.innerHTML += noUsersOption;
            if (editDropdown) editDropdown.innerHTML += noUsersOption;
            return;
        }

        // Add users to dropdown
        users.forEach(user => {
            const option = `<option value="${user.id}">${user.name} (${user.email || 'No email'})</option>`;
                if (addDropdown) addDropdown.innerHTML += option;
                if (editDropdown) editDropdown.innerHTML += option;
        });
    }

    // Open edit modal and populate date
    function openEditTaskModal(taskId) {
        // Find the task in global tasks array
        const task = window.allTasks.find(t => t.id == taskId);

        if (!task) {
            alert('Could not find task data');
            return;
        }
        // Fetch users for dropdown
        fetchUsers()
            .then(() => {
                // Populate form fields with task data
                document.getElementById('editTaskId').value = task.id;
                document.getElementById('editTaskTitle').value = task.title;
                document.getElementById('editTaskDescription').value = task.description || '';
                document.getElementById('editAssignedTo').value = task.user_id;

                // Set due data if it exists
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

                document.getElementById('editTaskStatus').value = task.status;
                document.getElementById('editTaskPriority').value = task.priority;
                document.getElementById('editTaskProgress').value = task.progress || 0;

                // Show modal
                document.getElementById('editTaskModal').classList.add('show');
        })
        .catch(error => {
            console.error('Error preparing edit modal:', error);
            alert('Error loading users: ' + error.message);
        });
    }

    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event){
        const addTaskModal = document.getElementById('addTaskModal');
        const editTaskModal = document.getElementById('editTaskModal');

        if (event.target === addTaskModal) {
            addTaskModal.classList.remove('show');
        }

        if (event.target === editTaskModal) {
            editTaskModal.classList.remove('show');
        }
    });

    // Add escape key support to close modal
    document.addEventListener('keydown', function(event){
        if (event.key === "Escape") {
            document.getElementById('addTaskModal').classList.remove('show');
            document.getElementById('editTaskModal').classList.remove('show');
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