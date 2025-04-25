document.addEventListener('DOMContentLoaded', function() {
    // Get the team ID from the URL query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const teamId = urlParams.get('team_id');

    // Store current team data
    window.currentTeam = null;
    window.teamMembers = [];
    window.availableUsers = [];

    // Fetch team details if teamId is available
    if (teamId) {
        fetchTeamDetails(teamId);
    } else {
        displayError('No team ID specified');
    }

    // Refresh metrics button
    const refreshMetricsBtn = document.getElementById('refreshMetricsBtn');
    if (refreshMetricsBtn) {
        refreshMetricsBtn.addEventListener('click', function() {
            fetchTeamMetrics(teamId);
        });
    }
});

// Function to fetch team details and members
function fetchTeamDetails(teamId) {
    fetch(`/teams/${teamId}/members`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Check if request successful
            if (data.status) {
                // Store current team data
                window.currentTeam = data.data.team;
                window.teamMembers = data.data.members || [];

                // Update page title with team name
                document.getElementById('teamName').textContent = `Team: ${window.currentTeam.name}`;
                document.getElementById('teamId').textContent = window.currentTeam.id;

                // Update team description
                const descriptionElem = document.getElementById('teamDescription');
                descriptionElem.textContent = window.currentTeam.description || 'No description provided';
                
                // Display team members
                displayTeamMembers(window.teamMembers);

                // Get available users (users not in this team)
                fetchAvailableUsers();

                // Fetch team performance metrics
                fetchTeamMetrics(teamId);
            } else {
                displayError(data.msg || 'Failed to fetch team details');
            }
        })
        .catch(error => {
            console.error('Error fetching team details: ', error);
            displayError('Error fetching team details: ' + error.message);
        });
}

// Function to fetch users without a team
function fetchAvailableUsers() {
    fetch('/users/no-team')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch available users');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.status) {
                window.availableUsers = data.data || [];
                if (typeof window.updateAvailableUsersDropdown === 'function') {
                    window.updateAvailableUsersDropdown();
                }
            } else {
                console.error('Error fetching available users:', data ? data.msg : 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Error fetching available users:', error);
        });
}

// Function to display team members
function displayTeamMembers(members) {
    const tableBody = document.getElementById('teamMembersTableBody');

    // Clear loading message
    tableBody.innerHTML = '';

    if (members.length === 0) {
        // If no members found, display a message
        tableBody.innerHTML = '<tr><td colspan="5">No team members found</td></tr>';
        return;
    }

    // Loop through each member and create table rows
    members.forEach(member => {
        const row = document.createElement('tr');

        // Create row content
        row.innerHTML = `
            <td>${member.id}</td>
            <td>${member.name}</td>
            <td>${member.email}</td>
            <td>${member.role}</td>
            <td>
                <button class="remove" data-id="${member.id}" data-name="${member.name}">Remove</button>
            </td>
        `;

        tableBody.appendChild(row);
    });

    // Event listeners for remove buttons
    addRemoveButtonListeners();
}

// Function to add event listeners to remove buttons
function addRemoveButtonListeners() {
    document.querySelectorAll('#teamMembersTableBody button.remove').forEach(button => {
        button.addEventListener('click', function(){
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            if (typeof window.openConfirmationModal === 'function') {
                window.openConfirmationModal(
                    `Are you sure you want to remove ${userName} from this team?`,
                    () => removeUserFromTeam(userId)
                );
            } else {
                if (confirm(`Are you sure you want to remove ${userName} from this team?`)) {
                    removeUserFromTeam(userId);
                }
            }
        });
    });
}

// Function to remove user from team
function removeUserFromTeam(userId) {
    const urlParams = new URLSearchParams(window.location.search);
    const teamId = urlParams.get('team_id');
    
    fetch(`/users/team/${userId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to remove user from team');
        }
        return response.json();
    })
    .then(data => {
        if (data.status) {
            // Refresh the team details
            fetchTeamDetails(teamId);
            showNotification('User removed from team successfully');
        } else {
            showNotification('Failed to remove user from team: ' + (data.msg || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error removing user from team:', error);
        showNotification('Error removing user from team: ' + error.message, 'error');
    });
}

// Function to fetch team performance metrics
function fetchTeamMetrics(teamId) {
    const loadingElement = document.getElementById('metricsLoading');
    const containerElement = document.getElementById('metricsContainer');
    
    if (!loadingElement || !containerElement) {
        console.error('Metrics container or loading elements not found');
        return;
    }
    
    loadingElement.style.display = 'block';
    containerElement.style.display = 'none';
    
    fetch(`/teams/${teamId}/metrics`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch team metrics');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.status) {
                displayTeamMetrics(data.data);
            } else {
                containerElement.innerHTML = 
                    `<div class="error-message">Failed to load metrics: ${data ? data.msg : 'Unknown error'}</div>`;
                loadingElement.style.display = 'none';
                containerElement.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error fetching team metrics:', error);
            containerElement.innerHTML = 
                `<div class="error-message">Error loading metrics: ${error.message}</div>`;
            loadingElement.style.display = 'none';
            containerElement.style.display = 'block';
        });
}

// Function to display team performance metrics
function displayTeamMetrics(metrics) {
    const container = document.getElementById('metricsContainer');
    const loadingElement = document.getElementById('metricsLoading');
    
    if (!container || !loadingElement) {
        console.error('Metrics container or loading elements not found');
        return;
    }
    
    // Check if metrics data is valid
    if (!metrics) {
        container.innerHTML = '<div class="error-message">No metrics data available</div>';
        loadingElement.style.display = 'none';
        container.style.display = 'block';
        return;
    }
    
    // Clear previous content
    container.innerHTML = '';
    
    // Create metrics HTML content
    let metricsHTML = '<div class="metrics-grid">';
    
    // Add completion rate card
    metricsHTML += `
        <div class="metric-card">
            <div class="metric-title">Team Completion Rate</div>
            <div class="metric-value">${metrics.team_completion_rate || 0}%</div>
        </div>
    `;
    
    // Add overdue tasks card
    metricsHTML += `
        <div class="metric-card">
            <div class="metric-title">Overdue Tasks</div>
            <div class="metric-value">${metrics.overdue_tasks || 0}</div>
        </div>
    `;
    
    // Add average completion time card
    metricsHTML += `
        <div class="metric-card">
            <div class="metric-title">Avg. Completion Time</div>
            <div class="metric-value">${metrics.avg_completion_time || 0} days</div>
        </div>
    `;
    
    metricsHTML += '</div>';
    
    // Member workload section
    if (metrics.member_workload && metrics.member_workload.length > 0) {
        metricsHTML += `
            <div class="metrics-section">
                <h4>Member Workload</h4>
                <table class="metrics-table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Total Tasks</th>
                            <th>Completed</th>
                            <th>Progress</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        metrics.member_workload.forEach(member => {
            metricsHTML += `
                <tr>
                    <td>${member.name}</td>
                    <td>${member.total_tasks}</td>
                    <td>${member.completed_tasks}</td>
                    <td>
                        <div class="mini-progress">
                            <div class="mini-progress-bar" style="width: ${member.avg_progress || 0}%"></div>
                            <span>${Math.round(member.avg_progress || 0)}%</span>
                        </div>
                    </td>
                    <td>${member.completion_rate || 0}%</td>
                </tr>
            `;
        });
        
        metricsHTML += `
                    </tbody>
                </table>
            </div>
        `;
    } else {
        metricsHTML += `
            <div class="metrics-section">
                <h4>Member Workload</h4>
                <p>No workload data available</p>
            </div>
        `;
    }
    
    // Status and priority distribution in columns
    metricsHTML += '<div class="metrics-columns">';
    
    // Status distribution section
    metricsHTML += `
        <div class="metrics-section">
            <h4>Status Distribution</h4>
    `;
    
    if (metrics.status_distribution && metrics.status_distribution.length > 0) {
        metricsHTML += `
            <table class="metrics-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        metrics.status_distribution.forEach(status => {
            metricsHTML += `
                <tr>
                    <td><span class="status-${status.status}">${status.status}</span></td>
                    <td>${status.count}</td>
                </tr>
            `;
        });
        
        metricsHTML += `
                </tbody>
            </table>
        `;
    } else {
        metricsHTML += '<p>No status data available</p>';
    }
    
    metricsHTML += '</div>';
    
    // Priority distribution section
    metricsHTML += `
        <div class="metrics-section">
            <h4>Priority Distribution</h4>
    `;
    
    if (metrics.priority_distribution && metrics.priority_distribution.length > 0) {
        metricsHTML += `
            <table class="metrics-table">
                <thead>
                    <tr>
                        <th>Priority</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        metrics.priority_distribution.forEach(priority => {
            metricsHTML += `
                <tr>
                    <td><span class="priority-${priority.priority}">${priority.priority}</span></td>
                    <td>${priority.count}</td>
                </tr>
            `;
        });
        
        metricsHTML += `
                </tbody>
            </table>
        `;
    } else {
        metricsHTML += '<p>No priority data available</p>';
    }
    
    metricsHTML += '</div>';
    
    // Close metrics columns div
    metricsHTML += '</div>';
    
    // Set the HTML content
    container.innerHTML = metricsHTML;
    
    // Hide loading indicator and show metrics
    loadingElement.style.display = 'none';
    container.style.display = 'block';
}

// Function to display error message
function displayError(message) {
    const tableBody = document.getElementById('teamMembersTableBody');
    if (tableBody) {
        tableBody.innerHTML = `<tr><td colspan="5" class="error-message">${message}</td></tr>`;
    }
}

// Function to show notifications
function showNotification(message, type = 'success') {
    alert(message);
}

// Expose functions that need to be called from team_detail-modals.js
window.displayTeamMembers = displayTeamMembers;
window.fetchTeamDetails = fetchTeamDetails;
window.removeUserFromTeam = removeUserFromTeam;