// Initialize Lucide icons
lucide.createIcons();

// Handle Super Admin Login
document.getElementById('adminLoginForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(this);
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            const errorDiv = document.getElementById('adminLoginError');
            if (errorDiv) {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('d-none');
            }
        }
    })
    .catch(error => {
        const errorDiv = document.getElementById('adminLoginError');
        if (errorDiv) {
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.classList.remove('d-none');
        }
    });
});

// Handle Student Login
document.getElementById('studentLoginForm').addEventListener('submit', function (event) {
    event.preventDefault();
    
    const studentId = document.getElementById('studentId').value;
    const password = document.getElementById('password').value;
    const rememberMe = document.getElementById('rememberMe').checked;
    
    if (rememberMe) {
        localStorage.setItem('rememberedStudentId', studentId);
        localStorage.setItem('hasRememberedCredentials', 'true');
    } else {
        localStorage.removeItem('rememberedStudentId');
        localStorage.removeItem('hasRememberedCredentials');
    }
    
    const formData = new FormData(this);
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            const errorDiv = document.getElementById('studentLoginError');
            if (errorDiv) {
                errorDiv.textContent = data.message;
                errorDiv.classList.remove('d-none');
            }
        }
    })
    .catch(error => {
        const errorDiv = document.getElementById('studentLoginError');
        if (errorDiv) {
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.classList.remove('d-none');
        }
    });
});

// Force numbers only and 8 digits max for student ID
const studentIdInput = document.getElementById('studentId');
if (studentIdInput) {
    studentIdInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const lazyImages = document.querySelectorAll("img.lazy");
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove("lazy");
                observer.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => {
        imageObserver.observe(img);
    });

    lucide.createIcons();
    
    const rememberedStudentId = localStorage.getItem('rememberedStudentId');
    const hasRememberedCredentials = localStorage.getItem('hasRememberedCredentials');
    
    if (rememberedStudentId && hasRememberedCredentials) {
        const studentIdInput = document.getElementById('studentId');
        const rememberMeCheckbox = document.getElementById('rememberMe');
        
        if (studentIdInput) {
            studentIdInput.value = rememberedStudentId;
        }
        
        if (rememberMeCheckbox) {
            rememberMeCheckbox.checked = true;
        }
        
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.focus();
        }
    }

    // Initialize password toggles
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            
            lucide.createIcons();
        });
    });

    document.getElementById('personal-facebook').href = "https://www.facebook.com/bulagakenneth";
    document.getElementById('github-link').href = "https://github.com/kenzosking6969";
});

// Enhanced developer credit interaction
document.addEventListener('DOMContentLoaded', function() {
    const developerCredit = document.querySelector('.developer-credit');
    const systemMessage = document.getElementById('systemMessage');
    
    if (developerCredit && systemMessage) {
        const clickMessage = "⚠ WARNING: UNAUTHORIZED ACCESS DETECTED YOU ARE NOT QUALIFIED TO VIEW THIS MESSAGE. ▉▉▉▉▉▉▉▉▉▉▉▉▉▉▉▉▉▉▉ ";
        
        const notificationSound = new Audio('sounds/notification.mp3');
        notificationSound.volume = 0.5;
        
        developerCredit.setAttribute('data-message', "[SYSTEM] Arise, Misconfigured .htaccess.");
        
        developerCredit.addEventListener('click', function() {
            notificationSound.currentTime = 0;
            notificationSound.play().catch(e => console.log("Audio playback failed: ", e));
            
            const message = clickMessage;
            
            const messageText = systemMessage.querySelector('.message-text');
            const systemTag = systemMessage.querySelector('.system-tag');
            
            systemTag.style.display = 'inline-block';
            systemTag.style.fontWeight = 'bold';
            systemTag.style.color = '#3498db';
            
            systemMessage.classList.remove('d-none');
            
            systemMessage.style.animation = 'none';
            systemMessage.offsetHeight;
            systemMessage.style.animation = null;
            
            systemMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            let i = 0;
            messageText.textContent = '';
            const typeMessage = setInterval(() => {
                if (i < message.length) {
                    messageText.textContent = message.slice(0, i + 1);
                    i++;
                    
                    if (i % 3 === 0) {
                        systemMessage.scrollIntoView({ 
                            behavior: window.innerWidth > 768 ? 'smooth' : 'auto',
                            block: 'center'
                        });
                    }
                } else {
                    clearInterval(typeMessage);
                    systemMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }, 30);
            
            setTimeout(() => {
                systemMessage.classList.add('d-none');
            }, 5000);
        });
    }
});