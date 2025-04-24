<!-- Edit Team Modal -->
<div id="editTeamModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Team</h3>
            <span class="close-modal" id="closeEditModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editTeamForm">
                <input type="hidden" id="editTeamId" name="editTeamId">
                <div class="form-group">
                    <label for="editTeamName">Team Name*</label>
                    <input type="text" id="editTeamName" name="editTeamName" required>
                </div>
                <div class="form-group">
                    <label for="editTeamDescription">Description</label>
                    <textarea name="editTeamDescription" id="editTeamDescription" rows="4"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelTeamEdit" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Update Team</button>
                </div>
            </form>
        </div>
    </div>
</div>