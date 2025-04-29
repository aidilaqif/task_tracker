document.addEventListener('DOMContentLoaded', function(){
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
                if (typeof window.refreshTaskList === 'function') {
                    window.refreshTaskList();
                }
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
                if (typeof window.refreshTaskList === 'function') {
                    window.refreshTaskList();
                }
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


// Expose functions that need to be called from task.js
window.openEditTaskModal = openEditTaskModal;