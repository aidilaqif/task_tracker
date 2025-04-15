<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="teams-container">
    <h2>Team</h2>
    <p>This is the team section</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
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


<script>
document.addEventListener('DOMContentLoaded', function(){
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
                    displayTeams(data.data || []);
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
                <td>${team.id}</td>
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

    // Function to display error messages
    function displayError(message) {
        const tableBody = document.getElementById('teamsTableBody');
        tableBody.innerHTML = `<tr><td colspan="8">${message}</td></tr>`;
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
    }
    // Fetch teams when the page loads
    fetchTeams();
});
</script>

<?= $this->endSection() ?>