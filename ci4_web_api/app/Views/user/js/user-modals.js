document.addEventListener('DOMContentLoaded', function(){
    // Add User button click event
    document.getElementById('addUserBtn').addEventListener('click', function(){
        openAddUserModal();
    });

    // Close add modal when clicking the X button
    document.getElementById('closeAddUserModal').addEventListener('click', function(){
        closeAddUserModal();
    });

    // Close edit modal when clicking the X button
    document.getElementById('closeEditUserModal').addEventListener('click', function(){
        closeEditUserModal();
    });

    // Close add modal when clicking Cancel button
    document.getElementById('cancelAddUser').addEventListener('click', function(){
        closeAddUserModal();
    });

    // Close edit modal when clicking Cancel button
    document.getElementById('cancelEditUser').addEventListener('click', function(){
        closeEditUserModal();
    });

    // Handle add user form submission
    document.getElementById('addUserForm').addEventListener('submit', function(e){
        e.preventDefault();
        createUser();
    });

    // Handle edit user form submission
    document.getElementById('editUserForm').addEventListener('submit', function(e){
        e.preventDefault();
        updateUser();
    });

    // Close modals when clicking outside of the modal
    window.addEventListener('click', function(event){
        const addModal = document.getElementById('addUserModal');
        const editModal = document.getElementById('editUserModal');
        
        if (event.target === addModal) {
            closeAddUserModal();
        }
        
        if (event.target === editModal) {
            closeEditUserModal();
        }
    });

    // Add escape key support to close modals
    document.addEventListener('keydown', function(event){
        if (event.key === "Escape") {
            closeAddUserModal();
            closeEditUserModal();
        }
    });
});

// Open the Add User modal
function openAddUserModal() {
    // Clear the form
    document.getElementById('addUserForm').reset();

    // Load teams for the dropdown if not already loaded
    populateTeamDropdown('userTeam');

    // Show modal
    document.getElementById('addUserModal').classList.add('show');
}

// Close the Add User modal
function closeAddUserModal() {
    document.getElementById('addUserModal').classList.remove('show');
}

// Open the Edit User modal
function openEditUserModal(userId) {
    // Find the user in the current user list
    const user = window.userState.users.find(u => u.id == userId);
    
    if (!user) {
        alert('User not found');
        return;
    }
    
    // Populate form with user data
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUserName').value = user.name;
    document.getElementById('editUserEmail').value = user.email;
    document.getElementById('editUserPassword').value = ''; // Clear password field
    document.getElementById('editUserRole').value = user.role;
    
    // Load teams for dropdown and set selected team
    populateTeamDropdown('editUserTeam', function() {
        document.getElementById('editUserTeam').value = user.team_id || '';
    });
    
    // Show modal
    document.getElementById('editUserModal').classList.add('show');
}

// Close the Edit User modal
function closeEditUserModal() {
    document.getElementById('editUserModal').classList.remove('show');
}

// Populate the team dropdown
function populateTeamDropdown(dropdownId, callback) {
    const dropdown = document.getElementById(dropdownId);

    // If there are options other than default, skip
    if (dropdown.options.length > 1) {
        if (callback) callback();
        return;
    }

    // If there is teams data in memory, use it
    if (window.teams && window.teams.length > 0) {
        window.teams.forEach(team => {
            const option = document.createElement('option');
            option.value = team.id;
            option.textContent = team.name;
            dropdown.appendChild(option);
        });
        
        if (callback) callback();
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
                    
                    if (callback) callback();
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
    .then(response => response.json())
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

// Update an existing user with form data
function updateUser() {
    // Get form values
    const userId = document.getElementById('editUserId').value;
    const name = document.getElementById('editUserName').value;
    const email = document.getElementById('editUserEmail').value;
    const password = document.getElementById('editUserPassword').value;
    const role = document.getElementById('editUserRole').value;
    const teamId = document.getElementById('editUserTeam').value;

    // Create data object for API
    const data = {
        name: name,
        email: email,
        role: role,
        team_id: teamId === '' ? null : (teamId ? parseInt(teamId) : null)
    };

    // Only include password if it's provided
    if (password) {
        data.password = password;
    }

    // Call API to update user
    fetch(`/users/${userId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(`Server error: ${err.msg || response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status) {
            // Success - close modal and refresh user list
            closeEditUserModal();
            fetchUsers(); // Refresh user list
            alert('User updated successfully!');
        } else {
            alert(data.msg || 'Failed to update user');
        }
    })
    .catch(error => {
        console.error('Error updating user:', error);
        alert('Error updating user: ' + error.message);
    });
}

// Expose functions to be called from user.js
window.openEditUserModal = openEditUserModal;