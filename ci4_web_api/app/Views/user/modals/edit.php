<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit User</h3>
            <span class="close-modal" id="closeEditUserModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editUserForm">
                <input type="hidden" id="editUserId" name="editUserId">
                <div class="form-group">
                    <label for="editUserName">Name*</label>
                    <input type="text" id="editUserName" name="editUserName" required>
                </div>
                <div class="form-group">
                    <label for="editUserEmail">Email*</label>
                    <input type="email" id="editUserEmail" name="editUserEmail" required>
                </div>
                <div class="form-group">
                    <label for="editUserPassword">Password (leave empty to keep current)</label>
                    <input type="password" id="editUserPassword" name="editUserPassword">
                    <small>Only fill this if you want to change the password</small>
                </div>
                <div class="form-group">
                    <label for="editUserRole">Role*</label>
                    <select name="editUserRole" id="editUserRole" required>
                        <option value="">Select role...</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editUserTeam">Team</label>
                    <select name="editUserTeam" id="editUserTeam">
                        <option value="">No team</option>
                        <!-- Teams will be loaded dynamically -->
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelEditUser" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>