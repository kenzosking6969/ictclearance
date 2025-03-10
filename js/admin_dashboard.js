document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    lucide.createIcons();

    // Use event delegation to handle edit button clicks
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-btn');
        if (editBtn) {
            const id = editBtn.getAttribute('data-clearance-id');
            editStatus(id);
        }
    });

    // Attach hidden event listener to reset modal fields
    const editStatusModal = document.getElementById('editStatusModal');
    if (editStatusModal) {
        editStatusModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('editStatusId').value = '';
            document.getElementById('editStatusSelect').value = '';
            // Remove any lingering modal backdrops
            document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                backdrop.remove();
            });
        });
    }

    // Save status button handler
    const saveStatusBtn = document.getElementById('saveStatusBtn');
    if (saveStatusBtn) {
        saveStatusBtn.addEventListener('click', function() {
            const id = document.getElementById('editStatusId').value;
            const status = document.getElementById('editStatusSelect').value;
            updateStatus(id, status);
        });
    }

    // Handle clear button
    const clearBtn = document.getElementById('clearBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            const studentIdInput = document.getElementById('studentId');
            const personalInfoCard = document.getElementById('personalInfoCard');
            const clearanceTableCard = document.getElementById('clearanceTableCard');
            if (studentIdInput) studentIdInput.value = '';
            if (personalInfoCard) personalInfoCard.style.display = 'none';
            if (clearanceTableCard) clearanceTableCard.style.display = 'none';
        });
    }

    // Profile form handling
    const profileForm = document.getElementById('profileForm');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const profileUpdateMessage = document.getElementById('profileUpdateMessage');

    if (saveProfileBtn) {
        saveProfileBtn.addEventListener('click', function() {
            const formData = {
                username: document.getElementById('adminUsername').value,
                currentPassword: document.getElementById('currentPassword').value,
                newPassword: document.getElementById('newPassword').value,
                confirmPassword: document.getElementById('confirmPassword').value
            };

            // Validate form
            if (!formData.username) {
                showProfileMessage('Username is required', 'danger');
                return;
            }

            // If changing password, validate password fields
            if (formData.newPassword || formData.confirmPassword) {
                if (!formData.currentPassword) {
                    showProfileMessage('Current password is required to change password', 'danger');
                    return;
                }
                if (formData.newPassword !== formData.confirmPassword) {
                    showProfileMessage('New passwords do not match', 'danger');
                    return;
                }
                if (formData.newPassword.length < 3) {
                    showProfileMessage('Password must be at least 3 characters long', 'danger');
                    return;
                }
            }

            // Send update request
            fetch('update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showProfileMessage(data.message, 'success');
                    // Update displayed username if it was changed
                    const userIdSpan = document.querySelector('.user-id');
                    if (userIdSpan && formData.username) {
                        userIdSpan.textContent = formData.username;
                    }
                    // Clear password fields
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                    
                    // Close modal after a short delay
                    setTimeout(() => {
                        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('profileModal'));
                        modalInstance.hide();
                    }, 1500);
                } else {
                    showProfileMessage(data.message, 'danger');
                }
            })
            .catch(error => {
                showProfileMessage('An error occurred while updating profile', 'danger');
            });
        });
    }

    // Clear form when modal is hidden
    const profileModal = document.getElementById('profileModal');
    if (profileModal) {
        profileModal.addEventListener('hidden.bs.modal', function () {
            profileForm.reset();
            profileUpdateMessage.classList.add('d-none');
        });
    }

    // Initialize password toggles
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('[data-lucide]');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            
            lucide.createIcons({
                override: true,
                elements: [icon]
            });
        });
    });
});

function editStatus(id) {
    const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
    document.getElementById('editStatusId').value = id;
    modal.show();
}

function updateStatus(id, status) {
    fetch('update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: id,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the status badge in the table
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                const statusBadge = row.querySelector('.badge');
                if (statusBadge) {
                    statusBadge.className = `badge ${status === '1' ? 'status-paid' : 'status-unpaid'}`;
                    statusBadge.innerHTML = `
                        <i data-lucide="${status === '1' ? 'check-circle' : 'alert-circle'}" class="me-1"></i>
                        ${status === '1' ? 'Paid' : 'Unpaid'}
                    `;
                    lucide.createIcons();
                }
            }
            // Close the modal
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('editStatusModal'));
            modalInstance.hide();
        } else {
            alert('Failed to update status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('An error occurred while updating the status');
    });
}

function showProfileMessage(message, type) {
    if (profileUpdateMessage) {
        profileUpdateMessage.textContent = message;
        profileUpdateMessage.className = `alert alert-${type}`;
        profileUpdateMessage.classList.remove('d-none');
        
        // Hide message after 5 seconds
        setTimeout(() => {
            profileUpdateMessage.classList.add('d-none');
        }, 5000);
    }
}
