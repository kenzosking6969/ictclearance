// Initialize Lucide icons on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    }

    // Handle modal icons
    const scheduleModal = document.getElementById('scheduleModal');
    if (scheduleModal) {
        // Initialize Bootstrap modal
        const modal = new bootstrap.Modal(scheduleModal);
        
        // Reinitialize icons when modal is shown
        scheduleModal.addEventListener('shown.bs.modal', function() {
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        });

        // Reset focus and aria attributes when modal is hidden
        scheduleModal.addEventListener('hidden.bs.modal', function() {
            // Reset focus to the button that opened the modal
            const openButton = document.querySelector('.btn-collection-schedule');
            if (openButton) {
                openButton.focus();
            }
        });
    }

    // Progress bar animation
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar && window.overallProgress !== undefined) {
        progressBar.style.width = window.overallProgress + '%';
    }

    // Update clearance status
    if (window.clearanceStatus) {
        const statusElement = document.querySelector('.clearance-status');
        if (statusElement) {
            statusElement.textContent = window.clearanceStatus;
        }
    }

    const profileForm = document.getElementById('profileForm');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    const profileUpdateMessage = document.getElementById('profileUpdateMessage');

    function showProfileMessage(message, type) {
        if (profileUpdateMessage) {
            profileUpdateMessage.textContent = message;
            profileUpdateMessage.className = `alert alert-${type}`;
            profileUpdateMessage.classList.remove('d-none');
        }
    }

    if (saveProfileBtn) {
        saveProfileBtn.addEventListener('click', function() {
            const formData = {
                currentPassword: document.getElementById('currentPassword').value,
                newPassword: document.getElementById('newPassword').value,
                confirmPassword: document.getElementById('confirmPassword').value
            };

            // Validate form
            if (!formData.currentPassword) {
                showProfileMessage('Current password is required', 'danger');
                return;
            }

            if (!formData.newPassword || !formData.confirmPassword) {
                showProfileMessage('New password and confirmation are required', 'danger');
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

            // Send update request
            fetch('update_student_profile.php', {
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
                showProfileMessage('An error occurred while updating password', 'danger');
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
            
            // Toggle password visibility
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            
            // Update Lucide icon
            lucide.createIcons({
                override: true,
                elements: [icon]
            });
        });
    });
});

// Show notification on page load
setTimeout(() => {
    const unpaidCount = clearanceStatus.filter(item => item.status === 0).length;
    const notificationMessage = unpaidCount > 1 
        ? `You have ${unpaidCount} pending clearance items to complete.` 
        : unpaidCount === 1 
        ? `You have 1 pending clearance item to complete.` 
        : `All clearance items are completed.`;

    document.getElementById('notificationMessage').textContent = notificationMessage;

    const toast = new bootstrap.Toast(document.getElementById('notificationToast'));
    toast.show();
}, 2000);

// Add scroll behavior for navbar
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 10) {
        navbar.style.background = 'rgba(0, 0, 0, 0.8) !important';
    } else {
        navbar.style.background = 'rgba(0, 0, 0, 0.2) !important';
    }
});