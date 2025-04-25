<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="team-details-container">
    <div class="back-button">
        <a href="<?= site_url('/team') ?>" class="btn-back"><i class="fas fa-arrow-left"></i>Back to Teams</a>
    </div>
    <div class="team-header">
        <h2 id="teamName">Team Details</h2>
        <div class="team-meta">
            <span class="team-id">ID: <span id="teamId">-</span></span>
        </div>
    </div>

    <div class="team-description" id="teamDescription">
        Loading team description...
    </div>
    <!-- Team Members Section -->
    <div class="team-members-containers">
        <div class="section-header">
            <h3>Team Members</h3>
            <button id="addMemberBtn" class="action-button add">Add Member</button>
        </div>
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
                        <td colspan="5">Loading team members...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="team-performance-container">
        <div class="section-header">
            <h3>Performance Metrics</h3>
            <button id="refreshMetricsBtn" class="action-button refresh">Refresh Metrics</button>
        </div>
        <!-- Add the missing metricsLoading div -->
        <div id="metricsLoading" class="metrics-loading">
            Loading performance metrics...
        </div>
        <div class="metrics-container" id="metricsContainer" style="display: none;">
            <!-- Metrics will be loaded here -->
        </div>
    </div>
</div>

<?= $this->include('team_detail/modals/add_member') ?>
<?= $this->include('team_detail/modals/remove_confirmation') ?>





<script>
    <?= $this->include('team_detail/js/team_detail.js')?>
</script>
<?= $this->endSection() ?>