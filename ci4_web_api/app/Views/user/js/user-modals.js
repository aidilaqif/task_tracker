document.addEventListener('DOMContentLoaded', function(){
    // Add User button click event
    document.getElementById('addUserBtn').addEventListener('click', function(){
        openAddUserModal();
    });

    // Close modal when clicking the X button
    document.getElementById('closeAddUserModal').addEventListener('click', function(){
        closeAddUserModal();
    });

    // Close modal when clicking Cancel button
    document.getElementById('cancelAddUser').addEventListener('click', function(){
        closeAddUserModal();
    });

    // Handle form submission
    document.getElementById('addUserForm').addEventListener('submit', function(e){
        e.preventDefault();
        createUser();
    });

    // Close modal when clicking outside of the modal
    window.addEventListener('click', function(event){
        const modal = this.document.getElementById('addUserModal');
        if (event.target === modal) {
            closeAddUserModal();
        }
    });

    // Add escape key support to close modal
    document.addEventListener('keydown', function(event){
        if (event.key === "Escape") {
            closeAddUserModal();
        }
    });
});

// Open the Add User modal
function openAddUserModal() {
    // Clear the form
    document.getElementById('addUserForm').reset();

    // Load teams for the dropdown if not already loaded
    populateTeamDropdown();

    // Show modal
    document.getElementById('addUserModal').classList.add('show');
}

// Close the Add User modal
function closeAddUserModal() {
    document.getElementById('addUserModal').classList.remove('show');
}

// Populate the team dropdown in the Add User modal
function populateTeamDropdown() {
    const dropdown = document.getElementById('userTeam');

    // If there is options other than default, skip
    if (dropdown.options.length > 1) return;

    // If there is teams data in memory, use it
    if (window.teams && window.teams.length > 0) {
        window.teams.forEach(team => {
            const option = document.createElement('option');
            option.value = team.id;
            option.textContent = team.name;
            dropdown.appendChild(option);
        });
    } else {
        fetch('/teams/with-count')
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Store teams for future use
                    window.teams = data.data;

                    // Add team options to dropdown
                    data.data.forEach(team => {
                        const option = document.createElement('option');
                        option.value = team.id;
                        option.textContent = team.name;
                        dropdown.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching teams:', error);
            });
    }
}

// Create a new user with form data
function createUser() {
    // Get form values
    const name = document.getElementById('userName').value;
    const email = document.getElementById('userEmail').value;
    const password = document.getElementById('userPassword').value;
    const role = document.getElementById('userRole').value;
    const teamId = document.getElementById('userTeam').value || null;

    // Create data object for API
    const data = {
        name: name,
        email: email,
        password: password,
        role: role,
        team_id: teamId ? parseInt(teamId) : null
    };

    // Call API to create user
    fetch('/users/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then (response => response.json())
    .then(data => {
        if (data.status) {
            // Success - close modal and refresh user list
            closeAddUserModal();
            fetchUsers(); // Refresh user list
            alert('User created successfully!');
        } else {
            alert(data.msg || 'Failed to create user');
        }
    })
    .catch(error => {
        console.error('Error creating user:', error);
        alert('Error creating user: ' + error.message);
    });
}