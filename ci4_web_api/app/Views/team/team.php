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

<?= $this->include('team/modals/create')?>
<?= $this->include('team/modals/edit')?>
<script>
    <?= $this->include('team/js/team.js') ?>
</script>

<?= $this->endSection() ?>