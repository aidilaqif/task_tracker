document.addEventListener('DOMContentLoaded', function(){
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
                if (typeof window.refreshTeamList === 'function') {
                    window.refreshTeamList();
                }
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

        const teamId = document.getElementById('editTeamId').value;
        const teamName = document.getElementById('editTeamName').value;
        const teamDescription = document.getElementById('editTeamDescription').value;

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
                if (typeof window.refreshTeamList === 'function') {
                    window.refreshTeamList();
                }
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
});

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

// Expose functions that need to be called from team.js
window.openEditModal = openEditModal;