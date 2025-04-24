<!-- Status Update Modal -->
<div id="updateStatusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Task Status</h3>
            <span class="close-modal" id="closeStatusModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="updateStatusForm">
                <input type="hidden" id="statusTaskId">
                <div class="form-group">
                    <label for="newTaskStatus">Status*</label>
                    <select id="newTaskStatus" required>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="request-extension">Request Extension</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelStatusButton" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>