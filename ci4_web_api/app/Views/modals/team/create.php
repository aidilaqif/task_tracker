<!-- Create Team Modal -->
<div id="createTeamModal" class="modal">
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