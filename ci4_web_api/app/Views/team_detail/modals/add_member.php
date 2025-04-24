<!-- Add Member Modal -->
<div id="addMemberModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add Team Member</h3>
            <span class="close-modal" id="closeAddMemberModal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="availableUsers">Select User</label>
                <select id="availableUsers" class="form-control">
                    <option value="">Select a user to add...</option>
                    <!-- Available users will be loaded here -->
                </select>
            </div>
            <div class="form-actions">
                <button type="button" id="cancelAddMember" class="cancel-button">Cancel</button>
                <button type="button" id="confirmAddMember" class="submit-button">Add to Team</button>
            </div>
        </div>
    </div>
</div>