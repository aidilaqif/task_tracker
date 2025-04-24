<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="teams-container">
    <div class="page-header">
        <h2>Teams</h2>
        <button id="addTeamsBtn" class="action-button add">Add New Team</button>
    </div>
    <div class="filters-container">
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search teams...">
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Team Name</th>
                <th>Description</th>
                <th>Counts</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="teamsTableBody">
            <!-- Load data from the API -->
            <tr>
                <td colspan="8">Loading teams data...</td>
            </tr>
        </tbody>
    </table>
</div>

<?= $this->include('modals/team/create')?>
<?= $this->include('modals/team/edit')?>





<script>
document.addEventListener('DOMContentLoaded', function(){
    // Declare a variable to store all teams for filtering
    window.allTeams =[];

    // Fetch teams when the page loads
    fetchTeams();

    // Add event listeners for filters
    document.getElementById('searchInput').addEventListener('input', filterTeams);

    // Add Team button event listener
    document.getElementById('addTeamsBtn').addEventListener('click', function() {
        document.getElementById('createTeamModal').classList.add('show');
    });

    // Close create team modal when clicking the X button
    document.getElementById('closeCreateModal').addEventListener('click', function() {
        document.getElementById('createTeamModal').classList.remove('show');
    });

    // Close edit modal when clicking the X button
    document.getElementById('closeEditModal').addEventListener('click', function() {
        document.getElementById('editTeamModal').classList.remove('show');
    });

    // Close create team modal when clicking the Cancel button
    document.getElementById('cancelTeamCreate').addEventListener('click', function() {
        document.getElementById('createTeamModal').classList.remove('show');
    });
    
    // Close edit modal modal when clicking the Cancel button
    document.getElementById('cancelTeamEdit').addEventListener('click', function(){
        document.getElementById('editTeamModal').classList.remove('show');
    });
    // Handle team creation form submission
    document.getElementById('createTeamForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const teamName = document.getElementById('teamName').value;
        const teamDescription = document.getElementById('teamDescription').value;
        
        // Create data object for API
        const data = {
            name: teamName,
            description: teamDescription
        };
        
        // Call the API to create team
        fetch('/teams', {
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
                // Success - refresh team list
                fetchTeams();
                // Reset form and close modal
                document.getElementById('createTeamForm').reset();
                document.getElementById('createTeamModal').classList.remove('show');
                alert('Team created successfully!');
            } else {
                alert(data.msg || 'Failed to create team');
            }
        })
        .catch(error => {
            console.error('Error creating team:', error);
            alert('Error creating team: ' + error.message);
        });
    });

    // Handle team edit form submission
    document.getElementById('editTeamForm').addEventListener('submit', function(e){
        e.preventDefault();

        const teamId = document.getElemenyById('editTeamId').value;
        const teamName = document.getElemenyById('editTeamName').value;
        const teamDescription = document.getElemenyById('editTeamDescription').value;

        // Create data object for API
        const data = {
            name: teamName,
            description: teamDescription
        };

        // Call the API to update team
        fetch(`/teams/${teamId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok')
            }
            return response.json();
        })
        .then(data => {
            if (data.status) {
                // Success - refresh team list
                fetchTeams();
                // Close modal
                document.getElementById('editTeamModal').classList.remove('show');
                alert('Team updated successfully!');
            } else {
                alert(data.msg || 'Failed to update team');
            }
        })
        .catch(error => {
            console.error('Error udpating team:', error);
            alert('Error updating team: ' + error.message);
        });
    });

    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
        const createModal = document.getElementById('createTeamModal');
        const editModal = document.getElementById('editTeamModal');

        if (event.target === createModal) {
            createModal.classList.remove('show');
        }

        if (event.target === editModal) {
            editModal.classList.remove('show');
        }
    });

    // Add escape key support to close modal
    document.addEventListener('keydown', function(event){
        if (event.key === "Escape") {
            document.getElementById('createTeamModal').classList.remove('show');
            document.getElementById('editTeamModal').classList.remove('show');
        }
    });

    // Fetch teams from API
    function fetchTeams() {
        fetch('/teams/with-count')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Check if the request successful
                if (data.status) {
                    window.allTeams = data.data || [];
                    displayTeams(window.allTeams);
                } else {
                    displayError(data.msg || 'Failed to fetch teams');
                }
            })
            .catch(error => {
                console.error('Error fetching teams:', error);
                displayError('Error fetching teams data: ' + error.message);
            });
    }

    // Open edit modal and populate with team data
    function openEditModal(teamId) {
        // Find team data from global teams array
        const team = window.allTeams.find(t => t.id == teamId);

        if (!team) {
            alert('Could not find team data');
            return;
        }

        // Populate the form fields
        document.getElementById('editTeamId').value = team.id;
        document.getElementById('editTeamName').value = team.name;
        document.getElementById('editTeamDescription').value = team.description || '';

        // Show the modal
        document.getElementById('editTeamModal').classList.add('show');
    }

    // Display teams in the table
    function displayTeams(teams) {
        const tableBody = document.getElementById('teamsTableBody');

        // Clear loading message
        tableBody.innerHTML = '';

        if (teams.length === 0) {
            // If no teams found, display message
            tableBody.innerHTML = '<tr><td colspan="8">No teams found</td></tr>';
            return;
        }

        // Loop through each team and create table rows
        teams.forEach(team => {
            const row = document.createElement('tr');

            // Format the date
            const createdAt = new Date(team.created_at);
            const formattedDate = createdAt.toLocaleDateString() + '' + createdAt.toLocaleTimeString();

            // Create row content
            row.innerHTML = `
                <td>${team.name}</td>
                <td>${team.description}</td>
                <td>${team.member_count}</td>
                <td>${formattedDate}</td>
                <td class="team-actions">
                    <button class="view" data-id="${team.id}">View</button>
                    <button class="edit" data-id="${team.id}">Edit</button>
                </td>
            `;

            tableBody.appendChild(row);
        });

        addButtonEventListeners();

    }

    // Function to add event listeners to the view and edit buttons
    function addButtonEventListeners() {
        // Add click event to view buttons
        document.querySelectorAll('button.view').forEach(button => {
            button.addEventListener('click', function(){
                const teamId = this.getAttribute('data-id');
                window.location.href = `/team_detail?team_id=${teamId}`;
            });
        });
        // Add click event to edit buttons
        document.querySelectorAll('button.edit').forEach(button => {
            button.addEventListener('click', function(){
                const teamId = this.getAttribute('data-id');
                openEditModal(teamId);
            });
        });
    }

    // Function to filter teams based on search input
    function filterTeams() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();

        // Check if there are teams to filter
        if (!window.allTeams || window.allTeams.length === 0) return;

        // Apply filters
        const filteredTeams = window.allTeams.filter(team => {
            // Search term filter
            const matchesSearch = team.name.toLowerCase().includes(searchTerm) || (team.description && team.description.toLowerCase().includes(searchTerm));

            return  matchesSearch;
        });

        displayTeams(filteredTeams);
    }

    // Function to display error messages
    function displayError(message) {
        const tableBody = document.getElementById('teamsTableBody');
        tableBody.innerHTML = `<tr><td colspan="8" class="error-message">${message}</td></tr>`;
    }

});
</script>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items:center;
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

    .error-message {
        color: #dc3545;
        text-align: center;
        padding: 20px;
    }
</style>

<?= $this->endSection() ?>