document.addEventListener('DOMContentLoaded', function(){
    // Declare a variable to store all teams for filtering
    window.allTeams =[];

    // Fetch teams when the page loads
    fetchTeams();

    // Add event listeners for filters
    document.getElementById('searchInput').addEventListener('input', filterTeams);

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
            window.openEditModal(teamId);
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

// Expose functions that need to be called from team-modals.js
window.refreshTeamList = fetchTeams;