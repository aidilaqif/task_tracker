document.addEventListener('DOMContentLoaded', function(){
    // Add member button
    const addMemberBtn = document.getElementById('addMemberBtn');
    if (addMemberBtn) {
        addMemberBtn.addEventListener('click', function() {
            const modalElement = document.getElementById('addMemberModal');
            if (modalElement) {
                modalElement.classList.add('show');
            }
        });
    }

    // Close add member modal
    const closeAddMemberModal = document.getElementById('closeAddMemberModal');
    if (closeAddMemberModal) {
        closeAddMemberModal.addEventListener('click', function() {
            const modalElement = document.getElementById('addMemberModal');
            if (modalElement) {
                modalElement.classList.remove('show');
            }
        });
    }

    // Cancel add member
    const cancelAddMember = document.getElementById('cancelAddMember');
    if (cancelAddMember) {
        cancelAddMember.addEventListener('click', function() {
            const modalElement = document.getElementById('addMemberModal');
            if (modalElement) {
                modalElement.classList.remove('show');
            }
        });
    }

    // Confirm add member
    const confirmAddMember = document.getElementById('confirmAddMember');
    if (confirmAddMember) {
        confirmAddMember.addEventListener('click', function() {
            const userSelect = document.getElementById('availableUsers');
            if (!userSelect) {
                console.error('User selection dropdown not found');
                return;
            }
            
            const userId = userSelect.value;
            
            if (!userId) {
                showNotification('Please select a user to add to the team', 'error');
                return;
            }
            
            addUserToTeam(userId);
            
            const modalElement = document.getElementById('addMemberModal');
            if (modalElement) {
                modalElement.classList.remove('show');
            }
        });
    }

    // Close confirmation modal
    const closeConfirmationModalBtn = document.getElementById('closeConfirmationModal');
    if (closeConfirmationModalBtn) {
        closeConfirmationModalBtn.addEventListener('click', function() { 
            closeConfirmationModal(); 
        });
    }
    
    const cancelConfirmation = document.getElementById('cancelConfirmation');
    if (cancelConfirmation) {
        cancelConfirmation.addEventListener('click', function() {
            closeConfirmationModal();
        });
    }

    // Close modals when clicking outside modal content
    window.addEventListener('click', function(event) {
        const addMemberModal = document.getElementById('addMemberModal');
        const confirmationModal = document.getElementById('confirmationModal');
        
        if (event.target === addMemberModal && addMemberModal) {
            addMemberModal.classList.remove('show');
        }
        
        if (event.target === confirmationModal && confirmationModal) {
            confirmationModal.classList.remove('show');
        }
    });

    // Add escape key support to close modals
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            const addMemberModal = document.getElementById('addMemberModal');
            const confirmationModal = document.getElementById('confirmationModal');
            
            if (addMemberModal) {
                addMemberModal.classList.remove('show');
            }
            
            if (confirmationModal) {
                confirmationModal.classList.remove('show');
            }
        }
    });
});

// Function to update available users dropdown
function updateAvailableUsersDropdown() {
    const dropdown = document.getElementById('availableUsers');
    if (!dropdown) {
        console.error('Available users dropdown element not found');
        return;
    }
    dropdown.innerHTML = '<option value="">Select a user to add...</option>';

    if (window.availableUsers.length === 0) {
        dropdown.innerHTML += '<option value="" disabled>No available users found</option>';
        return;
    }

    window.availableUsers.forEach(user => {
        dropdown.innerHTML += `<option value="${user.id}">${user.name} (${user.email})</option>`;
    });
}

// Function to add user to team
function addUserToTeam(userId) {
    const urlParams = new URLSearchParams(window.location.search);
    const teamId = urlParams.get('team_id');
    
    fetch('/users/team', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id: userId,
            team_id: teamId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to add user to team');
        }
        return response.json();
    })
    .then(data => {
        if (data.status) {
            // Refresh team details
            if (typeof window.fetchTeamDetails === 'function') {
                window.fetchTeamDetails(teamId);
            }
            showNotification('User added to team successfully');
        } else {
            showNotification('Failed to add user to team: ' + (data.msg || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error adding user to team:', error);
        showNotification('Error adding user to team: ' + error.message, 'error');
    });
}

// Function to open the confirmation modal
function openConfirmationModal(message, confirmCallback) {
    const modalElement = document.getElementById('confirmationModal');
    const messageElement = document.getElementById('confirmationMessage');
    
    if (!modalElement || !messageElement) {
        console.error('Confirmation modal elements not found');
        if (confirmCallback && confirm(message)) {
            confirmCallback();
        }
        return;
    }
    
    messageElement.textContent = message;
    
    // Store the callback function for use when confirmed
    document.getElementById('confirmAction').onclick = function() {
        closeConfirmationModal();
        confirmCallback();
    };
    
    modalElement.classList.add('show');
}

// Function to close the confirmation modal
function closeConfirmationModal() {
    const modalElement = document.getElementById('confirmationModal');
    if (modalElement) {
        modalElement.classList.remove('show');
    }
}

// Function to show notifications (reused for consistency)
function showNotification(message, type = 'success') {
    alert(message);
}

// Expose functions that need to be called from team_detail.js
window.updateAvailableUsersDropdown = updateAvailableUsersDropdown;
window.openConfirmationModal = openConfirmationModal;
window.closeConfirmationModal = closeConfirmationModal;