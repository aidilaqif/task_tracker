<!-- Edit Task Modal -->
<div id="editTaskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Task</h3>
            <span class="close-modal" id="closeEditTaskModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editTaskForm">
                <input type="hidden" id="editTaskId">
                <div class="form-group">
                    <label for="editTaskTitle">Task Title*</label>
                    <input type="text" id="editTaskTitle" required>
                </div>
                <div class="form-group">
                    <label for="editTaskDescription">Description</label>
                    <textarea id="editTaskDescription" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="editDueDate">Due Date</label>
                    <input type="date" id="editDueDate">
                </div>
                <div class="form-group">
                    <label for="editTaskPriority">Priority*</label>
                    <select id="editTaskPriority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editTaskStatus">Status*</label>
                    <select id="editTaskStatus" required>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="request-extension">Request Extension</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelEditBtn" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>