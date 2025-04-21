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

<!-- Create Team Modal -->
<div id="createTeamModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Team</h3>
            <span class="close-modal" id="closeCreateModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="createTeamForm">
                <div class="form-group">
                    <label for="teamName">Team Name*</label>
                    <input type="text" id="teamName" name="teamName" required>
                </div>
                <div class="form-group">
                    <label for="teamDescription">Description</label>
                    <textarea id="teamDescription" name="teamDescription" rows="4"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelTeamCreate" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Create Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div id="editTeamModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Team</h3>
            <span class="close-modal" id="closeEditModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editTeamForm">
                <input type="hidden" id="editTeamId" name="editTeamId">
                <div class="form-group">
                    <label for="editTeamName">Team Name*</label>
                    <input type="text" id="editTeamName" name="editTeamName" required>
                </div>
                <div class="form-group">
                    <label for="editTeamDescription">Description</label>
                    <textarea name="editTeamDescription" id="editTeamDescription" rows="4"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelTeamEdit" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Update Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
        document.getElementById('createTeamModal').style.display = 'block';
    });

    // Close create team modal when clicking the X button
    document.getElementById('closeCreateModal').addEventListener('click', function() {
        document.getElementById('createTeamModal').style.display = 'none';
    });

    // Close edit modal when clicking the X button
    document.getElementById('closeEditModal').addEventListener('click', function() {
        document.getElementById('editTeamModal').style.display = 'none';
    });

    // Close create team modal when clicking the Cancel button
    document.getElementById('cancelTeamCreate').addEventListener('click', function() {
        document.getElementById('createTeamModal').style.display = 'none';
    });
    
    // Close edit modal modal when clicking the Cancel button
    document.getElementById('cancelTeamEdit').addEventListener('click', function(){
        document.getElementById('editTeamModal').style.display = 'none';
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
                document.getElementById('createTeamModal').style.display = 'none';
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
                document.getElementById('editTeamModal').style.display = 'none';
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
            createModal.style.display = 'none';
        }

        if (event.target === editModal) {
            editModal.style.display = 'none';
        }
    });

    // Add escape key support to close modal
    document.addEventListener('keydown', function(event){
        if (event.key === "Escape") {
            document.getElementById('createTeamModal').style.display = 'none';
            document.getElementById('editTeamModal').style.display = 'none';
        }
    });

    // Fetch teams from API
    function fetchTeams() {
        fetch('/teams')
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
        document.getElementById('editTeamModal').style.display = 'block';
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

    /* Modal styles */
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
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 16px;
    }

    .form-group input:focus,
    .form-group textarea:focus {
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