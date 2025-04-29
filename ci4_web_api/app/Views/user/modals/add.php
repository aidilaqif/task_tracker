<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New User</h3>
            <span class="close-modal" id="closeAddUserModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addUserForm">
                <div class="form-group">
                    <label for="userName">Name*</label>
                    <input type="text" id="userName" name="userName" required>
                </div>
                <div class="form-group">
                    <label for="userEmail">Email*</label>
                    <input type="text" id="userEmail" name="userEmail" required>
                </div>
                <div class="form-group">
                    <label for="userPassword">Password*</label>
                    <input type="password" id="userPassword" name="userPassword" required>
                </div>
                <div class="form-group">
                    <label for="userRole">Role*</label>
                    <select name="userRole" id="userRole" required>
                        <option value="">Select role...</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="userTeam">Team</label>
                    <select name="userTeam" id="userTeam">
                        <option value="">No team</option>
                        <!-- Teams will be loaded dynamically -->
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelAddUser" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>