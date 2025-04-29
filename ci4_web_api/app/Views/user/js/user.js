document.addEventListener('DOMContentLoaded', function(){
    // Store current page state
    window.userState = {
        users: [],
        currentPage: 1,
        limit: 10,
        totalPages: 1,
        filters: {
            search: '',
            role: '',
            team_id: ''
        }
    };

    // Load initial data
    fetchUsers();

    // Load teams for filter dropdown
    fetchTeams();

    // Add event listeners for search and filters
    document.getElementById('searchInput').addEventListener('input', function(){
        window.userState.filters.search = this.value;
        window.userState.currentPage = 1; // Reset to first page on filter change
        fetchUsers();
    });

    document.getElementById('roleFilter').addEventListener('change', function(){
        window.userState.filters.role = this.value;
        window.userState.currentPage = 1; // Reset to first page on filter change
        fetchUsers();
    });

    document.getElementById('teamFilter').addEventListener('change', function(){
        window.userState.filters.team_id = this.value;
        window.userState.currentPage = 1; // Reset to first page on filter change
        fetchUsers();
    });
});

// Fetch users from the API with current filters and pagination
function fetchUsers(){
    // Show loading indicator
    document.getElementById('usersTableBody').innerHTML = '<tr><td colspan="6">Loading user data...</td></tr>';

    // Build query parameters
    const params = new URLSearchParams();
    params.append('page', window.userState.currentPage);
    params.append('limit', window.userState.limit);

    // Add filters if they exists
    if (window.userState.filters.search) {
        params.append('search', window.userState.filters.search);
    }
    if (window.userState.filters.role) {
        params.append('role', window.userState.filters.role);
    }
    if (window.userState.filters.team_id) {
        params.append('team_id', window.userState.filters.team_id);
    }

    // Make API call
    fetch(`/users?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status) {
                // Store users data and pagination info
                window.userState.users = data.data.users;
                window.userState.totalPages = data.data.pagination.total_pages;

                // Display users
                displayUsers(data.data.users);

                // Update pagination controls
                updatePagination(data.data.pagination);
            } else {
                displayError(data.msg || 'Failed to fetch users');
            }
        })
        .catch(error => {
            console.error('Error fetching users:', error);
            displayError('Error fetching users: ' + error.message);
        });
}

// Display users in the table
function displayUsers(users) {
    const tableBody = document.getElementById('usersTableBody');

    // Clear existing rows
    tableBody.innerHTML = '';

    if (users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6">No users found</td></tr>';
        return;
    }

    // Create a row for each user
    users.forEach(user => {
        const row = document.createElement('tr');

        // Get team name (if user is in a team)
        let teamName = 'Not Assigned';

        if (user.team_id && window.teams) {
            const team = window.teams.find(t => t.id == user.team_id);
            if (team) {
                teamName = team.name;
            }
        }

        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td><span class="role-${user.role}">${user.role}</span></td>
            <td>${teamName}</td>
            <td>
                <button class="edit" data-id="${user.id}">Edit</button>
                <button class="remove" data-id="${user.id}" data-name="${user.name}">Delete</button>
            </td>
        `;

        tableBody.appendChild(row);
    });

    // Add event listeners to buttons
    addButtonEventListeners();
}

// Update pagination controls
function updatePagination(pagination) {
    const container = document.getElementById('paginationContainer');
    
    // Calculate range of pages to show
    const currentPage = pagination.page;
    const totalPages = pagination.total_pages;
    
    if (totalPages <= 1) {
        container.innerHTML = ''; // No pagination needed
        return;
    }
    
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    
    // Adjust start page if we're near the end
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }
    
    // Create pagination HTML
    let html = '<ul class="pagination">';
    
    // Previous button
    html += `
        <li class="${currentPage === 1 ? 'disabled' : ''}">
            <a href="#" data-page="${currentPage - 1}" ${currentPage === 1 ? 'tabindex="-1"' : ''}>Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="${i === currentPage ? 'active' : ''}">
                <a href="#" data-page="${i}">${i}</a>
            </li>
        `;
    }
    
    // Next button
    html += `
        <li class="${currentPage === totalPages ? 'disabled' : ''}">
            <a href="#" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'tabindex="-1"' : ''}>Next</a>
        </li>
    `;
    
    html += '</ul>';
    container.innerHTML = html;
    
    // Add event listeners to pagination links
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Don't do anything if the link is disabled
            if (this.parentNode.classList.contains('disabled')) {
                return;
            }
            
            // Update current page and fetch users
            window.userState.currentPage = parseInt(this.getAttribute('data-page'));
            fetchUsers();
        });
    });
}

// Fetch teams for the filter dropdown
function fetchTeams() {
    fetch('/teams/with-count')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch teams');
            }
            return response.json();
        })
        .then(data => {
            if (data.status) {
                // Store teams for reference
                window.teams = data.data;
                
                // Populate team filter dropdown
                const teamFilter = document.getElementById('teamFilter');
                data.data.forEach(team => {
                    const option = document.createElement('option');
                    option.value = team.id;
                    option.textContent = team.name;
                    teamFilter.appendChild(option);
                });

                 // After teams are loaded, refresh the user display to show team names
                 if (window.userState && window.userState.users.length > 0) {
                    displayUsers(window.userState.users);
                }
            }
        })
        .catch(error => {
            console.error('Error fetching teams:', error);
        });
}

// Add event listeners to the edit and delete buttons
function addButtonEventListeners() {
    // Add click events to edit buttons
    document.querySelectorAll('button.edit').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            if (typeof window.openEditUserModal === 'function') {
                window.openEditUserModal(userId);
            } else {
                console.error('Edit user modal function not found');
            }
        });
    });
    
    // Add click events to delete buttons
    document.querySelectorAll('button.remove').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            
            if (typeof window.openDeleteConfirmationModal === 'function') {
                window.openDeleteConfirmationModal(userId, userName);
            } else {
                // Fallback to browser confirm if modal function not found
                if (confirm(`Are you sure you want to delete user "${userName}"?`)) {
                    deleteUser(userId);
                }
            }
        });
    });
}

// Display error message in the table
function displayError(message) {
    const tableBody = document.getElementById('usersTableBody');
    tableBody.innerHTML = `<tr><td colspan="6" class="error-message">${message}</td></tr>`;
}

// Expose fetchUsers function to be called from user-modals.js
window.fetchUsers = fetchUsers;