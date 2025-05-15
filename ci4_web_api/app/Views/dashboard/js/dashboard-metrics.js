// Function to load dashboard metrics
function loadDashboardMetrics() {
    // Show loading state
    document.getElementById('total-tasks').textContent = 'Loading...';
    document.getElementById('pending-count').textContent = '-';
    document.getElementById('in-progress-count').textContent = '-';
    document.getElementById('completed-count').textContent = '-';
    document.getElementById('extension-count').textContent = '-';

    // Loading state for overdue tasks
    document.getElementById('overdue-count').textContent = '-';
    document.getElementById('overdue-tasks-list').innerHTML = '<div class="loading-spinner">Loading overdue tasks...</div>';

    // Clear chart
    document.getElementById('tasks-chart').innerHTML = '';
    document.getElementById('priority-donut-chart').innerHTML = `
        <div class="donut-hole">
            <div class="donut-text-total" id="priority-chart-total">-</div>
            <div class="donut-text-label">Tasks</div>
        </div>
    `;

    // Show loading for priority counts
    document.getElementById('high-priority-count').textContent = '-';
    document.getElementById('medium-priority-count').textContent = '-';
    document.getElementById('low-priority-count').textContent = '-';

    // Show loading state section for upcoming tasks
    document.getElementById('upcoming-count').textContent = '-';
    document.getElementById('upcoming-tasks-list').innerHTML = '<div class="loading-spinner">Loading upcoming tasks...</div>';

    // Show loading state for team completion rates
    document.getElementById('teams-count').textContent = '-';
    document.getElementById('team-metrics-container').innerHTML = '<div class="loading-spinner">Loading team metrics...</div>';


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

                // Update priority distribution
                updatePriorityDistribution(data.data.tasks);

                // Update overdue tasks
                updateOverdueTasks(data.data);

                // Update upcoming tasks
                updateUpcomingTasks(data.data);

                // Fetch teams to display completion rates
                fetchTeamCompletionRates();
            } else {
                console.error('Failed to load dashboard metrics:', data.msg);
                document.getElementById('total-tasks').textContent = 'Error loading data';
                document.getElementById('priority-chart-total').textContent = 'Error';
                document.getElementById('overdue-count').textContent = 'Error';
                document.getElementById('overdue-tasks-list').innerHTML = '<div class="error-message">Error loading overdue tasks</div>';
                document.getElementById('upcoming-count').textContent = 'Error';
                document.getElementById('upcoming-tasks-list').innerHTML = '<div class="error-message">Error loading upcoming tasks</div>';
                document.getElementById('teams-count').textContent = 'Error';
                document.getElementById('team-metrics-container').innerHTML = '<div class="error-message">Error loading team metrics</div>';
            }
        })
        .catch(error => {
            console.error('Error loading dashboard metrics:', error);
            document.getElementById('total-tasks').textContent = 'Error loading data';
            document.getElementById('priority-chart-total').textContent = 'Error';
            document.getElementById('overdue-count').textContent = 'Error';
            document.getElementById('overdue-tasks-list').innerHTML = '<div class="error-message">Error loading overdue tasks</div>';
            document.getElementById('upcoming-count').textContent = 'Error';
            document.getElementById('upcoming-tasks-list').innerHTML = '<div class="error-message">Error loading upcoming tasks</div>';
            document.getElementById('teams-count').textContent = 'Error';
            document.getElementById('team-metrics-container').innerHTML = '<div class="error-message">Error loading team metrics</div>';
        });
}

// Function to update task metrics
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

function updateOverdueTasks(dashboardData) {
    // Get overdue tasks data
    const overdueData = dashboardData.overdue_tasks;
    const count = overdueData ? overdueData.count : 0;

    // Update count in the UI
    document.getElementById('overdue-count').textContent = count;

    // Get the container for the list
    const listContainer = document.getElementById('overdue-tasks-list');
    listContainer.innerHTML = '';

    // Check if we have any overdue tasks
    if (count > 0 && overdueData.list && overdueData.list.length > 0) {
        // Create the table
        const table = document.createElement('table');
        table.className = 'overdue-tasks-table';

        // Create table header
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th>Task</th>
                <th>Assigned To</th>
                <th>Days Overdue</th>
                <th>Priority</th>
                <th></th>
            </tr>
        `;
        table.appendChild(thead);

        // Create table body
        const tbody = document.createElement('tbody');

        // Add each overdue task to the table
        overdueData.list.forEach(task => {
            const tr = document.createElement('tr');

            // Add task priority class to the row
            tr.className = `priority-${task.priority}`;

            tr.innerHTML = `
                <td class="task-title">${task.title}</td>
                <td>${task.assigned_to || 'Unassigned'}</td>
                <td class="days-overdue">${task.days_overdue} day${task.days_overdue !== 1 ? 's' : ''}</td>
                <td><span class="priority-badge ${task.priority}">${task.priority}</span></td>
                <td>
                    <button class="view-task-btn" data-id="${task.id}">View</button>
                </td>
            `;

            tbody.appendChild(tr);
        });

        table.appendChild(tbody);
        listContainer.appendChild(table);

        // Add event listeners to view buttons
        document.querySelectorAll('.view-task-btn').forEach(button => {
            button.addEventListener('click', function () {
                const taskId = this.getAttribute('data-id');
                window.location.href = `/task_detail?task_id=${taskId}`;
            });
        });

        // Show view all link if there are more than shown
        if (count > overdueData.list.length) {
            document.getElementById('view-all-overdue').style.display = 'block';
        } else {
            document.getElementById('view-all-overdue').style.display = 'none';
        }
    } else {
        // No overdue tasks
        const noTasks = document.createElement('div');
        noTasks.className = 'no-tasks-message';
        noTasks.textContent = 'No overdue tasks.';
        listContainer.appendChild(noTasks);
    }
}

// Function to update upcoming tasks section
function updateUpcomingTasks(dashboardData) {
    // Get upcoming tasks data
    const upcomingData = dashboardData.upcoming_tasks;
    const count = upcomingData ? upcomingData.count : 0;

    // Update count in the UI
    document.getElementById('upcoming-count').textContent = count;

    // Get the container for the list
    const listContainer = document.getElementById('upcoming-tasks-list');
    listContainer.innerHTML = '';

    // Check if have any upcoming tasks
    if (count > 0 && upcomingData.list && upcomingData.list.length > 0) {
        // Create the table
        const table = document.createElement('table');
        table.className = 'upcoming-tasks-table';

        // Create table header
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th>Task</th>
                <th>Assigned To</th>
                <th>Due In</th>
                <th>Priority</th>
                <th></th>
            </tr>
        `;
        table.appendChild(thead);

        // Create table body
        const tbody = document.createElement('tbody');

        // Add each upcoming task to the table
        upcomingData.list.forEach(task => {
            const tr = document.createElement('tr');

            // Determine CSS class based on days until due
            let daysClass = '';
            if (task.days_until_due <= 1) {
                daysClass = 'days-critical';
            } else if (task.days_until_due <= 3) {
                daysClass = 'days-warning';
            } else {
                daysClass = 'days-upcoming';
            }

            // Add task priority class to the row
            tr.className = `priority- ${daysClass}`;

            // Format the due date
            const dueDate = new Date(task.due_date);
            const formattedDate = dueDate.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            const daysText = task.days_until_due === 0 ? 'Today' :
                task.days_until_due === 1 ? 'Tomorrow' :
                    `In ${task.days_until_due} days`;


            tr.innerHTML = `
                <td class="task-title">${task.title}</td>
                <td>${task.assigned_to || 'Unassigned'}</td>
                <td class="days-due">${formattedDate} <span class="days-text">(${daysText})</span></td>
                <td><span class="priority-badge ${task.priority}">${task.priority}</span></td>
                <td>
                    <button class="view-task-btn" data-id="${task.id}">View</button>
                </td>
            `;

            tbody.appendChild(tr);
        });

        table.appendChild(tbody);
        listContainer.appendChild(table);

        // Add event listeners to view buttons
        document.querySelectorAll('.view-task-btn').forEach(button => {
            button.addEventListener('click', function () {
                const taskId = this.getAttribute('data-id');
                window.location.href = `/task_detail?task_id=${taskId}`;
            });
        });

        // Show view all link if there are more than shown
        if (count > upcomingData.list.length) {
            document.getElementById('view-all-upcoming').style.display = 'block';
        } else {
            document.getElementById('view-all-upcoming').style.display = 'none';
        }
    } else {
        // No upcoming tasks
        const noTasks = document.createElement('div');
        noTasks.className = 'no-tasks-message';
        noTasks.textContent = 'No tasks due in the next 7 days.';
        listContainer.appendChild(noTasks);
    }
}

// Function to fetch teams and their completion rates
function fetchTeamCompletionRates() {
    // fetch all teams
    fetch('/teams/with-count')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch teams');
            }
            return response.json();
        })
        .then(data => {
            if (data.status && data.data && data.data.length > 0) {
                const teams = data.data;
                document.getElementById('teams-count').textContent = teams.length;

                // Create promises for fetching metrics for each team
                const metricsPromises = teams.map(team =>
                    fetch(`/teams/${team.id}/metrics`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Failed to fetch metrics for team ${team.id}`);
                            }
                            return response.json();
                        })
                        .then(metricsData => {
                            if (metricsData.status) {
                                return {
                                    team: team,
                                    metrics: metricsData.data
                                };
                            }
                            return null;
                        })
                        .catch(error => {
                            console.error(`Error fetching metrics for team ${team.id}:`, error);
                            return null;
                        })
                );
                // Wait for all metrics to be fetched
                return Promise.all(metricsPromises);
            } else {
                // No teams found
                document.getElementById('teams-count').textContent = '0';
                document.getElementById('team-metrics-container').innerHTML = '<div class="no-teams-message">No teams found.</div>';
                return [];
            }
        })
        .then(teamMetrics => {
            // Filter out any failed requests
            const validTeamMetrics = teamMetrics.filter(item => item !== null);

            if (validTeamMetrics.length > 0) {
                // Display team completion rates
                displayTeamCompletionRates(validTeamMetrics);
            } else if (teamMetrics.length > 0) {
                // Team exist but no valid metrics
                document.getElementById('team-metrics-container').innerHTML = '<div class="no-teams-message">Could not load metrics for teams.</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching team data:', error);
            document.getElementById('teams-count').textContent = 'Error';
            document.getElementById('team-metrics-container').innerHTML = '<div class="error-message">Error loading team data</div>';
        });
}

// Function to display team completion rates
function displayTeamCompletionRates(teamMetrics) {
    const container = document.getElementById('team-metrics-container');
    container.innerHTML = '';

    // Sort teams by completion rate (highest first)
    teamMetrics.sort((a, b) => {
        const rateA = a.metrics.team_completion_rate || 0;
        const rateB = b.metrics.team_completion_rate || 0;
        return rateB - rateA;
    });

    // Create bars for each team
    teamMetrics.forEach(item => {
        const { team, metrics } = item;
        const completionRate = metrics.team_completion_rate || 0;

        // Create team completion bar
        const teamBar = document.createElement('div');
        teamBar.className = 'team-completion-bar';

        // Add team name and completion rate
        const teamNameDiv = document.createElement('div');
        teamNameDiv.className = 'team-name';
        teamNameDiv.innerHTML = `
            <span class="team-name-text">${team.name}</span>
            <span class="completion-rate">${completionRate}</span>
        `;

        // Create progress bar
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-bar-container';

        const progressFill = document.createElement('div');
        progressFill.className = 'progress-bar-fill';
        progressFill.style.width = `${completionRate}%`;

        // Set color based on completion rate
        if (completionRate < 30) {
            progressFill.style.backgroundColor = '#dc3545'; // red
        } else if (completionRate < 70) {
            progressFill.style.backgroundColor = '#ffc107'; // yellow
        } else {
            progressFill.style.backgroundColor = '#28a745'; // green
        }

        // Add tool tip with additional info
        const memberCount = team.member_count || 0;
        const overdueCount = metrics.overdue_tasks || 0;

        teamBar.title = `
            Team: ${team.name}
            Members: ${memberCount}
            Completion Rate: ${completionRate}%
            Overdue Tasks: ${overdueCount}
        `;

        // Add click event to navigate to team details
        teamBar.style.cursor = 'pointer';
        teamBar.addEventListener('click', () => {
            window.location.href = `/team_detail?team_id=${team.id}`;
        });

        // Assemble the team bar
        progressContainer.appendChild(progressFill);
        teamBar.appendChild(teamNameDiv);
        teamBar.appendChild(progressContainer);
        container.appendChild(teamBar);
    });
}