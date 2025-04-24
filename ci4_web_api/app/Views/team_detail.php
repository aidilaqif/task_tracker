<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="team-details-container">
    <div class="back-button">
        <a href="<?= site_url('/team') ?>" class="btn-back"><i class="fas fa-arrow-left"></i>Back to Teams</a>
    </div>
    <div class="team-header">
        <h2 id="teamName">Team Details</h2>
        <div class="team-meta">
            <span class="team-id">ID: <span id="teamId">-</span></span>
        </div>
    </div>

    <div class="team-description" id="teamDescription">
        Loading team description...
    </div>
    <!-- Team Members Section -->
    <div class="team-members-containers">
        <div class="section-header">
            <h3>Team Members</h3>
            <button id="addMemberBtn" class="action-button add">Add Member</button>
        </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="teamMembersTableBody">
                    <!-- Data will loaded here from API -->
                    <tr>
                        <td colspan="5">Loading team members...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="team-performance-container">
        <div class="section-header">
            <h3>Performance Metrics</h3>
            <button id="refreshMetricsBtn" class="action-button refresh">Refresh Metrics</button>
        </div>
        <!-- Add the missing metricsLoading div -->
        <div id="metricsLoading" class="metrics-loading">
            Loading performance metrics...
        </div>
        <div class="metrics-container" id="metricsContainer" style="display: none;">
            <!-- Metrics will be loaded here -->
        </div>
    </div>
</div>

<?= $this->include('modals/team_detail/add_member') ?>
<?= $this->include('modals/team_detail/remove_confirmation') ?>





<script>
document.addEventListener('DOMContentLoaded', function(){
    // Get the team ID from the URL query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const teamId = urlParams.get('team_id');

    // Store current team data
    let currentTeam = null;
    let teamMembers = [];
    let availableUsers = [];

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
                    currentTeam = data.data.team;
                    teamMembers = data.data.members || [];

                    // Update page title with team name
                    document.getElementById('teamName').textContent = `Team: ${currentTeam.name}`;
                    document.getElementById('teamId').textContent = currentTeam.id;

                    // Update team description
                    const descriptionElem = document.getElementById('teamDescription');
                    descriptionElem.textContent = currentTeam.description || 'No description provided';
                    
                    // Display team members
                    displayTeamMembers(teamMembers);

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
                    availableUsers = data.data || [];
                    updateAvailableUsersDropdown();
                } else {
                    console.error('Error fetching available users:', data ? data.msg : 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error fetching available users:', error);
            });
    }

    // Function to update available users dropdown
    function updateAvailableUsersDropdown() {
        const dropdown = document.getElementById('availableUsers');
        if (!dropdown) {
            console.error('Available users dropdown element not found');
            return;
        }
        dropdown.innerHTML = '<option value="">Select a user to add...</option>';

        if (availableUsers.length === 0) {
            dropdown.innerHTML += '<option value="" disabled>No available users found</option>';
            return;
        }

        availableUsers.forEach(user => {
            dropdown.innerHTML += `<option value="${user.id}">${user.name} (${user.email})</option>`;
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

            // Create row content - Fixed missing quotation mark
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
                // Fixed template string syntax
                openConfirmationModal(
                    `Are you sure you want to remove ${userName} from this team?`,
                    () => removeUserFromTeam(userId)
                );
            });
        });
    }

    // Function to remove user from team - corrected the endpoint
    function removeUserFromTeam(userId) {
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

    // Function to add user to team
    function addUserToTeam(userId) {
        fetch('/users/team', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: userId,
                team_id: teamId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to add user to team');
            }
            return response.json();
        })
        .then(data => {
            if (data.status) {
                // Refresh team details
                fetchTeamDetails(teamId);
                showNotification('User added to team successfully');
            } else {
                showNotification('Failed to add user to team: ' + (data.msg || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error adding user to team:', error);
            showNotification('Error adding user to team: ' + error.message, 'error');
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
        
        // Create metrics HTML content - with error handling for each section
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
        
        // Member workload section - only if data exists
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

    // Function to open the confirmation modal
    function openConfirmationModal(message, confirmCallback) {
        const modalElement = document.getElementById('confirmationModal');
        const messageElement = document.getElementById('confirmationMessage');
        
        if (!modalElement || !messageElement) {
            console.error('Confirmation modal elements not found');
            if (confirmCallback && confirm(message)) {
                confirmCallback();
            }
            return;
        }
        
        messageElement.textContent = message;
        
        // Store the callback function for use when confirmed
        document.getElementById('confirmAction').onclick = function() {
            closeConfirmationModal();
            confirmCallback();
        };
        
        modalElement.classList.add('show');
    }

    // Function to close the confirmation modal
    function closeConfirmationModal() {
        const modalElement = document.getElementById('confirmationModal');
        if (modalElement) {
            modalElement.classList.remove('show');
        }
    }

    // Function to show notifications
    function showNotification(message, type = 'success') {
        alert(message);
    }

    // Event Listeners

    // Add member button
    const addMemberBtn = document.getElementById('addMemberBtn');
    if (addMemberBtn) {
        addMemberBtn.addEventListener('click', function() {
            const modalElement = document.getElementById('addMemberModal');
            if (modalElement) {
                modalElement.classList.add('show');
            }
        });
    }

    // Close add member modal
    const closeAddMemberModal = document.getElementById('closeAddMemberModal');
    if (closeAddMemberModal) {
        closeAddMemberModal.addEventListener('click', function() {
            const modalElement = document.getElementById('addMemberModal');
            if (modalElement) {
                modalElement.classList.remove('show');
            }
        });
    }

    // Cancel add member
    const cancelAddMember = document.getElementById('cancelAddMember');
    if (cancelAddMember) {
        cancelAddMember.addEventListener('click', function() {
            const modalElement = document.getElementById('addMemberModal');
            if (modalElement) {
                modalElement.classList.remove('show');
            }
        });
    }

    // Confirm add member
    const confirmAddMember = document.getElementById('confirmAddMember');
    if (confirmAddMember) {
        confirmAddMember.addEventListener('click', function() {
            const userSelect = document.getElementById('availableUsers');
            if (!userSelect) {
                console.error('User selection dropdown not found');
                return;
            }
            
            const userId = userSelect.value;
            
            if (!userId) {
                showNotification('Please select a user to add to the team', 'error');
                return;
            }
            
            addUserToTeam(userId);
            
            const modalElement = document.getElementById('addMemberModal');
            if (modalElement) {
                modalElement.classList.remove('show');
            }
        });
    }

    // Close confirmation modal
    const closeConfirmationModalBtn = document.getElementById('closeConfirmationModal');
    if (closeConfirmationModalBtn) {
        closeConfirmationModalBtn.addEventListener('click', function() { 
            closeConfirmationModal(); 
        });
    }
    
    const cancelConfirmation = document.getElementById('cancelConfirmation');
    if (cancelConfirmation) {
        cancelConfirmation.addEventListener('click', function() {
            closeConfirmationModal();
        });
    }

    // Refresh metrics button
    const refreshMetricsBtn = document.getElementById('refreshMetricsBtn');
    if (refreshMetricsBtn) {
        refreshMetricsBtn.addEventListener('click', function() {
            fetchTeamMetrics(teamId);
        });
    }

    // Close modals when clicking outside modal content
    window.addEventListener('click', function(event) {
        const addMemberModal = document.getElementById('addMemberModal');
        const confirmationModal = document.getElementById('confirmationModal');
        
        if (event.target === addMemberModal && addMemberModal) {
            addMemberModal.classList.remove('show');
        }
        
        if (event.target === confirmationModal && confirmationModal) {
            confirmationModal.classList.remove('show');
        }
    });

    // Add escape key support to close modals
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            const addMemberModal = document.getElementById('addMemberModal');
            const confirmationModal = document.getElementById('confirmationModal');
            
            if (addMemberModal) {
                addMemberModal.classList.remove('show');
            }
            
            if (confirmationModal) {
                confirmationModal.classList.remove('show');
            }
        }
    });

    // Fetch team details if teamId is available
    if (teamId) {
        fetchTeamDetails(teamId);
    } else {
        displayError('No team ID specified');
    }
});
</script>

<style>
    .team-details-container {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 30px;
    }

    .back-button {
        margin-bottom: 20px;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        color: #495057;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .btn-back:hover {
        color: #007bff;
    }

    .btn-back i {
        margin-right: 8px;
    }

    .team-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 15px;
    }

    .team-header h2 {
        margin: 0;
        color: #212529;
        font-size: 1.8rem;
    }

    .team-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 10px;
    }

    .team-id {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .team-description {
        margin-bottom: 25px;
        padding: 0 0 15px 0;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
    }

    .team-members-containers, .team-performance-container {
        margin-bottom: 30px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .section-header h3 {
        margin: 0;
        font-size: 1.25rem;
        color: #343a40;
    }

    .action-button {
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        color: white;
        font-size: 0.875rem;
    }

    .action-button.add {
        background-color: #28a745;
    }

    .action-button.refresh {
        background-color: #17a2b8;
    }

    .action-button:hover {
        opacity: 0.9;
    }

    .metrics-loading {
        text-align: center;
        padding: 20px;
        color: #6c757d;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .metric-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .metric-title {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: #343a40;
    }

    .metrics-section {
        margin-bottom: 25px;
    }

    .metrics-section h4 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.1rem;
        color: #495057;
    }

    .metrics-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .metrics-table th {
        text-align: left;
        background-color: #f8f9fa;
        padding: 8px 12px;
        border-bottom: 2px solid #dee2e6;
    }

    .metrics-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #dee2e6;
    }

    .metrics-columns {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }

    .mini-progress {
        width: 100%;
        height: 18px;
        background-color: #e9ecef;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }

    .mini-progress-bar {
        height: 100%;
        background-color: #28a745;
    }

    .mini-progress span {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.75rem;
        color: #212529;
        font-weight: 600;
    }

    .error-message {
        color: #dc3545;
        padding: 15px;
        background-color: #f8d7da;
        border-radius: 4px;
        margin-bottom: 15px;
    }
</style>

<?= $this->endSection() ?>