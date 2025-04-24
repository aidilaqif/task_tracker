<!-- Create Task Modal -->
<div id="addTaskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Task</h3>
            <span class="close-modal" id="closeAddTaskModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="createTaskForm">
                <div class="form-group">
                    <label for="taskTitle">Task Title*</label>
                    <input type="text" id="taskTitle" name="taskTitle" required>
                </div>
                <div class="form-group">
                    <label for="taskDescription">Description</label>
                    <textarea name="taskDescription" id="taskDescription" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="assignedTo">Assign To*</label>
                    <select name="assignedTo" id="assignedTo" required>
                        <option value="">Select user...</option>
                        <!-- Users will be loaded here -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="dueDate">Due Date</label>
                    <input type="date" id="dueDate" name="dueDate">
                </div>
                <div class="form-group">
                    <label for="taskPriority">Priority*</label>
                    <select name="taskPriority" id="taskPriority" required>
                        <option value="">Select priority...</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" id="cancelTaskCreate" class="cancel-button">Cancel</button>
                    <button type="submit" class="submit-button">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>