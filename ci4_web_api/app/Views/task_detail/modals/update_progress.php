<!-- Progress Update Modal -->
<div id="updateProgressModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Task Progress</h3>
            <span class="close-modal" id="closeProgressModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="updateProgressForm">
                <input type="hidden" id="progressTaskId">
                <div class="form-group">
                    <label for="newTaskProgress">*Progress (%)</label>
                    <input type="number" id="updateTaskProgress" name="updateTaskProgress" min="0" max="100" value="0">
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelProgressButton" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>