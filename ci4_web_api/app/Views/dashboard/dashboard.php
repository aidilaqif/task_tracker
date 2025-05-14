<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="dashboard-container">
    <h2>Dashboard</h2>
    
    <div class="dashboard-actions">
        <div class="quick-actions">
            <button id="addTaskBtn" class="action-button add">
                <i class="fas fa-plus"></i> Add New Task
            </button>
            <button id="refreshDashboardBtn" class="refresh-button">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
    
    <!-- Metrics Container -->
    <div class="metrics-container">
        <!-- Task Status Metrics Card (existing) -->
        <div class="metric-card" id="task-status-card">
            <h3>Task Status Overview</h3>
            <div class="total" id="total-tasks">-</div>
            <p>Total Tasks</p>
            
            <div class="horizontal-chart" id="tasks-chart">
                <!-- Chart segments will be added dynamically -->
            </div>
            
            <div class="status-breakdown">
                <div class="status-item status-pending">
                    <div class="status-count" id="pending-count">-</div>
                    <div class="status-label">Pending</div>
                </div>
                <div class="status-item status-in-progress">
                    <div class="status-count" id="in-progress-count">-</div>
                    <div class="status-label">In Progress</div>
                </div>
                <div class="status-item status-completed">
                    <div class="status-count" id="completed-count">-</div>
                    <div class="status-label">Completed</div>
                </div>
                <div class="status-item status-request-extension">
                    <div class="status-count" id="extension-count">-</div>
                    <div class="status-label">Extension</div>
                </div>
            </div>
        </div>
        
        <!-- NEW: Task Priority Distribution Card -->
        <div class="metric-card" id="task-priority-card">
            <h3>Task Priority Distribution</h3>
            
            <!-- Donut chart for priority distribution -->
            <div class="priority-donut-chart" id="priority-donut-chart">
                <!-- Donut segments will be added dynamically -->
                <div class="donut-hole">
                    <div class="donut-text-total" id="priority-chart-total">-</div>
                    <div class="donut-text-label">Tasks</div>
                </div>
            </div>
            
            <!-- Priority Legend -->
            <div class="priority-chart-legend">
                <div class="legend-item">
                    <div class="legend-color priority-high"></div>
                    <span>High</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color priority-medium"></div>
                    <span>Medium</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color priority-low"></div>
                    <span>Low</span>
                </div>
            </div>
            
            <!-- Priority Counts -->
            <div class="status-breakdown">
                <div class="status-item">
                    <div class="status-count" id="high-priority-count" style="color: #DC3545;">-</div>
                    <div class="status-label">High</div>
                </div>
                <div class="status-item">
                    <div class="status-count" id="medium-priority-count" style="color: #FFC107;">-</div>
                    <div class="status-label">Medium</div>
                </div>
                <div class="status-item">
                    <div class="status-count" id="low-priority-count" style="color: #28A745;">-</div>
                    <div class="status-label">Low</div>
                </div>
            </div>
        </div>
        
        <!-- More metric cards will be added for the other dashboard features -->
    </div>
</div>

<!-- Include Task Modal for quick task creation -->
<?= $this->include('task/modals/create') ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dashboard
        loadDashboardMetrics();
        
        // Event listener for refresh button
        document.getElementById('refreshDashboardBtn').addEventListener('click', function() {
            loadDashboardMetrics();
        });
        
        // Add Task button event listener
        document.getElementById('addTaskBtn').addEventListener('click', function() {
            // Reuse the existing task creation modal
            fetchUsers();
            document.getElementById('addTaskModal').classList.add('show');
        });
        
        // Close add task modal when clicking X
        document.getElementById('closeAddTaskModal').addEventListener('click', function() {
            document.getElementById('addTaskModal').classList.remove('show');
        });
        
        // Close create task modal when clicking the Cancel button
        document.getElementById('cancelTaskCreate').addEventListener('click', function() {
            document.getElementById('addTaskModal').classList.remove('show');
        });
        
        // Handle task creation form submission
        document.getElementById('createTaskForm').addEventListener('submit', function(e) {
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
        window.addEventListener('click', function(event) {
            const addTaskModal = document.getElementById('addTaskModal');
            
            if (event.target === addTaskModal) {
                addTaskModal.classList.remove('show');
            }
        });
        
        // Add escape key support to close modal
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                document.getElementById('addTaskModal').classList.remove('show');
            }
        });
    });
    
    // Function to load dashboard metrics
    function loadDashboardMetrics() {
        // Show loading state
        document.getElementById('total-tasks').textContent = 'Loading...';
        document.getElementById('pending-count').textContent = '-';
        document.getElementById('in-progress-count').textContent = '-';
        document.getElementById('completed-count').textContent = '-';
        document.getElementById('extension-count').textContent = '-';
        
        // Clear chart
        document.getElementById('tasks-chart').innerHTML = '';
        document.getElementById('priority-donut-chart').innerHTML = `
            <div class="donut-hole">
                <div class="donut-text-total" id="priority-chart-total">-</div>
                <div class="donut-text-label">Tasks</div>
            </div>
        `;
        
        // Also show loading for priority counts
        document.getElementById('high-priority-count').textContent = '-';
        document.getElementById('medium-priority-count').textContent = '-';
        document.getElementById('low-priority-count').textContent = '-';
        
        // Fetch dashboard metrics from API
        fetch('/tasks/dashboard-metrics')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status) {
                    // Update task metrics
                    updateTaskMetrics(data.data.tasks);
                    
                    // NEW: Update priority distribution
                    updatePriorityDistribution(data.data.tasks);
                } else {
                    console.error('Failed to load dashboard metrics:', data.msg);
                    document.getElementById('total-tasks').textContent = 'Error loading data';
                    document.getElementById('priority-chart-total').textContent = 'Error';
                }
            })
            .catch(error => {
                console.error('Error loading dashboard metrics:', error);
                document.getElementById('total-tasks').textContent = 'Error loading data';
                document.getElementById('priority-chart-total').textContent = 'Error';
            });
    }
    
    // Function to update task metrics (existing function)
    function updateTaskMetrics(tasksData) {
        // Set total tasks count
        const totalTasks = tasksData.total;
        document.getElementById('total-tasks').textContent = totalTasks;
        
        // Initialize counters for each status
        let pendingCount = 0;
        let inProgressCount = 0;
        let completedCount = 0;
        let extensionCount = 0;
        
        // Process status breakdown data
        tasksData.status_breakdown.forEach(status => {
            const count = parseInt(status.count);
            
            switch (status.status) {
                case 'pending':
                    pendingCount = count;
                    break;
                case 'in-progress':
                    inProgressCount = count;
                    break;
                case 'completed':
                    completedCount = count;
                    break;
                case 'request-extension':
                    extensionCount = count;
                    break;
            }
        });
        
        // Update status counts
        document.getElementById('pending-count').textContent = pendingCount;
        document.getElementById('in-progress-count').textContent = inProgressCount;
        document.getElementById('completed-count').textContent = completedCount;
        document.getElementById('extension-count').textContent = extensionCount;
        
        // Create horizontal bar chart showing status distribution
        const chartContainer = document.getElementById('tasks-chart');
        chartContainer.innerHTML = '';
        
        if (totalTasks > 0) {
            // Calculate percentages
            const pendingPercentage = (pendingCount / totalTasks) * 100;
            const inProgressPercentage = (inProgressCount / totalTasks) * 100;
            const completedPercentage = (completedCount / totalTasks) * 100;
            const extensionPercentage = (extensionCount / totalTasks) * 100;
            
            // Create chart segments
            if (pendingCount > 0) {
                const pendingSegment = document.createElement('div');
                pendingSegment.className = 'chart-segment-pending';
                pendingSegment.style.width = pendingPercentage + '%';
                pendingSegment.title = 'Pending: ' + pendingCount + ' tasks (' + pendingPercentage.toFixed(1) + '%)';
                chartContainer.appendChild(pendingSegment);
            }
            
            if (inProgressCount > 0) {
                const inProgressSegment = document.createElement('div');
                inProgressSegment.className = 'chart-segment-in-progress';
                inProgressSegment.style.width = inProgressPercentage + '%';
                inProgressSegment.title = 'In Progress: ' + inProgressCount + ' tasks (' + inProgressPercentage.toFixed(1) + '%)';
                chartContainer.appendChild(inProgressSegment);
            }
            
            if (completedCount > 0) {
                const completedSegment = document.createElement('div');
                completedSegment.className = 'chart-segment-completed';
                completedSegment.style.width = completedPercentage + '%';
                completedSegment.title = 'Completed: ' + completedCount + ' tasks (' + completedPercentage.toFixed(1) + '%)';
                chartContainer.appendChild(completedSegment);
            }
            
            if (extensionCount > 0) {
                const extensionSegment = document.createElement('div');
                extensionSegment.className = 'chart-segment-request-extension';
                extensionSegment.style.width = extensionPercentage + '%';
                extensionSegment.title = 'Request Extension: ' + extensionCount + ' tasks (' + extensionPercentage.toFixed(1) + '%)';
                chartContainer.appendChild(extensionSegment);
            }
        } else {
            // No tasks available
            const emptyMessage = document.createElement('div');
            emptyMessage.textContent = 'No tasks available';
            emptyMessage.style.width = '100%';
            emptyMessage.style.textAlign = 'center';
            emptyMessage.style.padding = '5px';
            emptyMessage.style.color = '#666';
            chartContainer.appendChild(emptyMessage);
        }
    }
    // Function to update priority distribution
    function updatePriorityDistribution(tasksData) {
        const totalTasks = tasksData.total;
        document.getElementById('priority-chart-total').textContent = totalTasks;

        // Initialize counters for each priority
        let highCount = 0;
        let mediumCount = 0;
        let lowCount = 0;

        // Process priority breakdown data
        if (tasksData.priority_breakdown) {
            tasksData.priority_breakdown.forEach(priority => {
                const count = parseInt(priority.count);

                switch (priority.priority) {
                    case 'high':
                        highCount = count;
                        break;
                    case 'medium':
                        mediumCount = count;
                        break;
                    case 'low':
                        lowCount = count;
                        break;
                }
            });
        }

        // Update priority counts
        document.getElementById('high-priority-count').textContent = highCount;
        document.getElementById('medium-priority-count').textContent = mediumCount;
        document.getElementById('low-priority-count').textContent = lowCount;

        // Get the donut chart container
        const donutContainer = document.getElementById('priority-donut-chart');

        if (totalTasks > 0) {
            // Calculate percentages
            const highPercentage = (highCount / totalTasks) * 100;
            const mediumPercentage = (mediumCount / totalTasks) * 100;
            const lowPercentage = (lowCount / totalTasks) * 100;

            // Create conic gradient for donut chart
            let conicGradient = 'conic-gradient(';
            let currentPercentage = 0;

            // Add high priority segment
            if (highCount > 0) {
                conicGradient += `#DC3545 0% ${highPercentage}%`;
                currentPercentage = highPercentage;
            }

            // Add medium priority segment
            if (mediumCount > 0) {
                if (currentPercentage > 0) conicGradient += ', ';
                conicGradient += `#FFC107 ${currentPercentage}% ${currentPercentage + mediumPercentage}%`;
                currentPercentage += mediumPercentage;
            }

            // Add low priority segment
            if (lowCount > 0) {
                if (currentPercentage > 0) conicGradient += ', ';
                conicGradient += `#28A745 ${currentPercentage}% 100%`;
            }

            conicGradient += ')';

            // Apply the conic gradient to the donut container
            donutContainer.innerHTML = `
                <div class="donut-chart" style="background: ${conicGradient}"></div>
                <div class="donut-hole">
                    <div class="donut-text-total">${totalTasks}</div>
                    <div class="donut-text-label">Tasks</div>
                </div>
            `;
        } else {
            // No tasks available
            donutContainer.innerHTML = `
                <div class="donut-chart" style="background: #e9ecef"></div>
                <div class="donut-hole">
                    <div class="donut-text-total">0</div>
                    <div class="donut-text-label">Tasks</div>
                </div>
            `;
        }
    }
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
</script>
<?= $this->endSection() ?>