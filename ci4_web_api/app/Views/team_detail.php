<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="team-details-container">
    <h2>Team Details</h2>
    <p>This is the team details section</p>
    <div class="team-members-containers">
        <h3>Team Members</h3>
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
                        <td>Loading team members...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Get the team ID from the URL query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const teamId = urlParams.get('team_id');

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
                    // Update page title with team name
                    const titleElement = document.querySelector('.team-details-container h2');
                    if (titleElement) {
                        titleElement.textContent = `Team: ${data.data.team.name}`;
                    }
                    
                    // Display team members
                    displayTeamMembers(data.data.members || []);
                } else {
                    displayError(data.msg || 'Failed to fetch team details');
                }
            })
            .catch(error => {
                console.error('Error fetching team details: ', error);
                displayError('Error fetching team details: ' + error.message);
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
                <td class="member-actions">
                    <button class="remove-member" data-id="${member.id}">Remove</button>
                </td>
            `;

            tableBody.appendChild(row);
        });
    }

    // Function to display error messages
    function displayError(message) {
        const tableBody = document.getElementById('teamMembersTableBody');
        tableBody.innerHTML = `<tr><td colspan="5" class="error-message">${message}</td></tr>`;
    }

    // Fetch team details if teamId is available
    if (teamId) {
        fetchTeamDetails(teamId);
    } else {
        displayError('No team ID specified');
    }
});
</script>
<?= $this->endSection() ?>