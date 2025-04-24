<!-- Edit Task Modal -->
<div id="editTaskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Task</h3>
            <span class="close-modal" id="closeEditTaskModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editTaskForm">
                <input type="hidden" id="editTaskId" name="editTaskId">
                <div class="form-group">
                    <label for="editTaskTitle">Task Title*</label>
                    <input type="text" id="editTaskTitle" name="editTaskTitle" required>
                </div>
                <div class="form-group">
                    <label for="editTaskDescription">Description</label>
                    <textarea name="editTaskDescription" id="editTaskDescription" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="editAssignedTo">Assign To*</label>
                    <select name="editAssignedTo" id="editAssignedTo" required>
                        <option value="">Select user...</option>
                        <!-- Users will be loaded here -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="editDueDate">Due Date</label>
                    <input type="date" id="editDueDate" name="editDueDate">
                </div>
                <div class="form-group">
                    <label for="editTaskStatus">Status*</label>
                    <select name="editTaskStatus" id="editTaskStatus" required>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="request-extension">Request Extension</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editTaskPriority">Priority*</label>
                    <select name="editTaskPriority" id="editTaskPriority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editTaskProgress">Progress (%)</label>
                    <input type="number" id="editTaskProgress" name="editTaskProgress" min="0" max="100" value="0">
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelTaskEdit" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>