document.addEventListener('DOMContentLoaded', function(){
    // Edit Task button event listener
    document.getElementById('editTaskBtn').addEventListener('click', function(){
        openEditTaskModal();
    });

    // Close edit task modal when clicking X
    document.getElementById('closeEditTaskModal').addEventListener('click', function(){
        document.getElementById('editTaskModal').classList.remove('show');
    });

    // Close edit task modal when clicking Cancel button
    document.getElementById('cancelEditBtn').addEventListener('click', function(){
        document.getElementById('editTaskModal').classList.remove('show');
    });

    // Handle edit task form submission
    document.getElementById('editTaskForm').addEventListener('submit', function(e){
        e.preventDefault();
        updateTask();
    });

    // Update Status button event listener
    document.getElementById('updateStatusBtn').addEventListener('click', function(){
        openStatusModal();
    });

    // Close status modal when clicking X
    document.getElementById('closeStatusModal').addEventListener('click', function(){
        document.getElementById('updateStatusModal').classList.remove('show');
    });

    // Close status modal when clicking Cancel button
    document.getElementById('cancelStatusButton').addEventListener('click', function(){
        document.getElementById('updateStatusModal').classList.remove('show');
    });

    // Handle status update form submission
    document.getElementById('updateStatusForm').addEventListener('submit', function(e){
        e.preventDefault();
        updateTaskStatus();
    })

    // Update Progress button event listener
    document.getElementById('updateProgressBtn').addEventListener('click', function(){
        openProgressModal();
    });

    // Close progress modal when clicking X
    document.getElementById('closeProgressModal').addEventListener('click', function(){
        document.getElementById('updateProgressModal').classList.remove('show');
    });

    // Close progress modal when clicking Cancel button
    document.getElementById('cancelProgressButton').addEventListener('click', function(){
        document.getElementById('updateProgressModal').classList.remove('show');
    });

    // Handle progress update form submission
    document.getElementById('updateProgressForm').addEventListener('submit', function(e){
        e.preventDefault();
        updateTaskProgress();
    })

    // Close modal when clicking outside
    window.addEventListener('click', function(event){
        const editModal = document.getElementById('editTaskModal');
        const statusModal = document.getElementById('updateStatusModal');
        const progressModal = document.getElementById('updateProgressModal');

        if (event.target === editModal) {
            editModal.classList.remove('show');
        }

        if (event.target === statusModal) {
            statusModal.classList.remove('show');
        }

        if (event.target === progressModal) {
            progressModal.classList.remove('show');
        }
    });

    // Add escape key support to close modal
    document.addEventListener('keydown', function(event){
        if (event.key === "Escape") {
            document.getElementById('editTaskModal').classList.remove('show');
            document.getElementById('updateStatusModal').classList.remove('show');
            document.getElementById('updateProgressModal').classList.remove('show');
        }
    });
});

function openEditTaskModal() {
    if (!window.currentTask) {
        alert('Task data not available');
        return;
    }

    const task = window.currentTask;

    // Populate form with current task data
    document.getElementById('editTaskId').value = task.id;
    document.getElementById('editTaskTitle').value = task.title;
    document.getElementById('editTaskDescription').value = task.description || '';
    document.getElementById('editTaskStatus').value = task.status;
    document.getElementById('editTaskPriority').value = task.priority;

    // Format and set due date if available
    if (task.due_date) {
        // Format date to YYYY-MM-DD for date input
        const dueDate = new Date(task.due_date);
        const year = dueDate.getFullYear();
        const month = String(dueDate.getMonth() + 1).padStart(2, '0');
        const day = String(dueDate.getDate()).padStart(2, '0');
        document.getElementById('editDueDate').value = `${year}-${month}-${day}`;
    } else {
        document.getElementById('editDueDate').value = '';
    }

    // Show the modal
    document.getElementById('editTaskModal').classList.add('show');
}

function updateTask() {
    const taskId = document.getElementById('editTaskId').value;
    const title = document.getElementById('editTaskTitle').value;
    const description = document.getElementById('editTaskDescription').value;
    const dueDate = document.getElementById('editDueDate').value;
    const status = document.getElementById('editTaskStatus').value;
    const priority = document.getElementById('editTaskPriority').value;

    // Create data object for API
    const data = {
        title: title,
        description: description,
        due_date: dueDate || null,
        status: status,
        priority: priority,
    };

    // Call the API to update task
    fetch(`/tasks/edit/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.status) {
            // Success - update the UI with the new task data
            window.currentTask = data.data;
            window.displayTaskDetails(data.data);

            // Close the modal
            document.getElementById('editTaskModal').classList.remove('show');

            // Show success message
            alert('Task updated successfully!');
        } else {
            alert(data.msg || 'Failed to update task');
        }
    })
    .catch(error => {
        console.error('Error updating task:', error);
        alert('Error updating task: ' + error.message);
    });
}

function openStatusModal() {
    if (!window.currentTask) {
        alert('Task data not available');
        return;
    }

    const task = window.currentTask;

    // Set task ID
    document.getElementById('statusTaskId').value = task.id;

    // Set current status
    document.getElementById('newTaskStatus').value = task.status;

    // Show the modal
    document.getElementById('updateStatusModal').classList.add('show');
}

function updateTaskStatus() {
    const taskId = document.getElementById('statusTaskId').value;
    const status = document.getElementById('newTaskStatus').value;

    // Call the API to update task status
    fetch(`/tasks/status/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.status) {
            // Success - update UI with the new task data
            window.currentTask = data.data;
            window.displayTaskDetails(data.data);

            // Close modal
            document.getElementById('updateStatusModal').classList.remove('show');

            // Show success message
            alert('Task status updated succesfully!');
        } else {
            alert(data.msg || 'Failed to update task status');
        }
    })
    .catch(error => {
        console.error('Error updating task status:', error);
        alert('Error updating task status: ' + error.message);
    });
}

function openProgressModal(){
    if (!window.currentTask) {
        alert('Task data not available');
        return;
    }

    const task = window.currentTask;

    // Set task ID
    document.getElementById('progressTaskId').value = task.id;

    // Set current progress
    document.getElementById('updateTaskProgress').value = task.progress || 0;
    // Show the modal
    document.getElementById('updateProgressModal').classList.add('show');
}

function updateTaskProgress(){
    const taskId = window.currentTask.id;
    const progress = parseInt(document.getElementById('updateTaskProgress').value);

    // Validate progress value
    if (isNaN(progress) || progress < 0 || progress > 100) {
        alert('Please enter a valid progress value between 0 and 100');
        return;
    }

    // Call the API to update task progress
    fetch(`/tasks/progress/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ progress: progress })
    })
    .then(response => {
        if(!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then( data => {
        if (data.status) {
            // Success - udpate UI with new task data
            window.currentTask = data.data;
            window.displayTaskDetails(data.data);

            // Close modal
            document.getElementById('updateProgressModal').classList.remove('show');

            // Show success message
            alert('Task progress updated successfully!');
        } else {
            alert(data.msg || 'Failed to update task progress');
        }
    })
    .catch(error => {
        console.error('Error updating task progress:', error);
        alert('Error updating task progress: ' + error.message);
    });
}

// Expose functions that need to be called from task_detail.js
window.openEditTaskModal = openEditTaskModal;
window.openStatusModal = openStatusModal;
window.openProgressModal = openProgressModal;

