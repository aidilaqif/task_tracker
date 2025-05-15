document.addEventListener('DOMContentLoaded', function () {
    // Initialize dashboard
    loadDashboardMetrics();

    // Event listener for refresh button
    document.getElementById('refreshDashboardBtn').addEventListener('click', function () {
        loadDashboardMetrics();
    });

    // Add Task button event listener
    document.getElementById('addTaskBtn').addEventListener('click', function () {
        // Reuse the existing task creation modal
        fetchUsers();
        document.getElementById('addTaskModal').classList.add('show');
    });

    // Close add task modal when clicking X
    document.getElementById('closeAddTaskModal').addEventListener('click', function () {
        document.getElementById('addTaskModal').classList.remove('show');
    });

    // Close create task modal when clicking the Cancel button
    document.getElementById('cancelTaskCreate').addEventListener('click', function () {
        document.getElementById('addTaskModal').classList.remove('show');
    });

    // Handle task creation form submission
    document.getElementById('createTaskForm').addEventListener('submit', function (e) {
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
                    // Success - refresh dashboard metrics
                    loadDashboardMetrics();

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

    // Close modal when clicking outside the modal content
    window.addEventListener('click', function (event) {
        const addTaskModal = document.getElementById('addTaskModal');

        if (event.target === addTaskModal) {
            addTaskModal.classList.remove('show');
        }
    });

    // Add escape key support to close modal
    document.addEventListener('keydown', function (event) {
        if (event.key === "Escape") {
            document.getElementById('addTaskModal').classList.remove('show');
        }
    });
});

// Function to fetch users for the dropdown
function fetchUsers() {
    // Clear existing options first
    const addDropdown = document.getElementById('assignedTo');
    addDropdown.innerHTML = '<option value="">Select user...</option>';

    // Fetch users from API
    fetch('/users')
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data && data.data.users) {
                // Add users to dropdown
                data.data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name + ' (' + user.email + ')';
                    addDropdown.appendChild(option);
                });
            } else {
                console.error('Failed to load users for dropdown');
                addDropdown.innerHTML += '<option value="" disabled>No users found</option>';
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            addDropdown.innerHTML += '<option value="" disabled>Error loading users</option>';
        });
}