document.addEventListener('DOMContentLoaded', () => {

    // --- References ---
    const form = document.getElementById('register-form');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submit-btn');
    const toastContainer = document.getElementById('toast-container');

    // --- State ---
    const validity = {
        username: false,
        email: false,
        password: false,
        confirm: false
    };

    // --- UI Helpers ---

    function showFieldError(fieldId, message) {
        const input = document.getElementById(fieldId);
        const errorContainer = document.getElementById(`${fieldId}-error`);
        if (input && errorContainer) {
            input.classList.add('invalid');
            input.classList.remove('valid');
            errorContainer.innerHTML = message;
            errorContainer.style.display = 'flex';
        }
    }

    function clearFieldError(fieldId) {
        const input = document.getElementById(fieldId);
        const errorContainer = document.getElementById(`${fieldId}-error`);
        if (input && errorContainer) {
            input.classList.remove('invalid');
            input.classList.add('valid');
            errorContainer.style.display = 'none';
        }
    }

    function showToast(message, type = 'error') {
        if (!toastContainer) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-alert ${type === 'success' ? 'valid' : ''}`;
        toast.innerText = message;
        
        toastContainer.appendChild(toast);

        // Remove after 5s
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            toast.addEventListener('animationend', () => {
                toast.remove();
            });
        }, 5000);
    }

    // --- Validation Logic ---

    async function checkUsername() {
        const username = usernameInput.value.trim();
        if (!username) {
            validity.username = false;
            return;
        }

        try {
            const res = await fetch(`php/check_username.php?username=${encodeURIComponent(username)}`);
            const data = await res.json();

            if (!data.available) {
                showFieldError('username', data.message);
                validity.username = false;
            } else {
                clearFieldError('username');
                validity.username = true;
            }
        } catch (err) {
            console.error('Username check failed:', err);
        }
        updateSubmitState();
    }

    async function checkEmail() {
        const email = emailInput.value.trim();
        if (!email) {
             validity.email = false;
             return;
        }
        
        // Basic Regex first
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
             showFieldError('email', 'Invalid email format');
             validity.email = false;
             updateSubmitState();
             return;
        }

        try {
            const res = await fetch(`php/check_email.php?email=${encodeURIComponent(email)}`);
            const data = await res.json();

            if (!data.available) {
                showFieldError('email', data.message);
                validity.email = false;
            } else {
                clearFieldError('email');
                validity.email = true;
            }
        } catch (err) {
            console.error('Email check failed:', err);
        }
        updateSubmitState();
    }

    function checkPasswordMatch() {
        const val1 = passwordInput.value;
        const val2 = confirmInput.value;

        if (!val1) {
             validity.password = false;
             validity.confirm = false;
             return;
        }

        // Check strong password (client side preview)
        // 8 chars, 1 Upper, 1 Special
        const isStrong = val1.length >= 8 && /[A-Z]/.test(val1) && /[\W_]/.test(val1);
        
        if (!isStrong) {
             validity.password = false;
        } else {
             validity.password = true;
             // Clear password error if it became strong
             clearFieldError('password');
        }

        if (val2) {
            if (val1 === val2) {
                 clearFieldError('confirm_password');
                 validity.confirm = true;
            } else {
                 showFieldError('confirm_password', 'Passwords do not match');
                 validity.confirm = false;
            }
        }
        updateSubmitState();
    }
    
    function updateSubmitState() {
        // We can disable submit button here if we want strict blocking
        // For now, leaving it enabled but validated on click
    }

    // --- Listeners ---

    if (usernameInput) {
        usernameInput.addEventListener('blur', checkUsername);
        usernameInput.addEventListener('input', () => {
             // Reset checking state visual if needed
             if (usernameInput.classList.contains('invalid')) {
                 usernameInput.classList.remove('invalid');
                 document.getElementById('username-error').style.display = 'none';
             }
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', checkEmail);
        emailInput.addEventListener('input', () => {
             if (emailInput.classList.contains('invalid')) {
                 emailInput.classList.remove('invalid');
                 document.getElementById('email-error').style.display = 'none';
             }
        });
    }

    if (passwordInput && confirmInput) {
        // Password toggle
        document.querySelectorAll('.password-toggle-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.previousElementSibling;
                if (input) {
                    const isPass = input.type === 'password';
                    input.type = isPass ? 'text' : 'password';
                    btn.style.color = isPass ? '#0f172a' : '#64748b';
                }
            });
        });

        // Match logic
        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmInput.addEventListener('input', checkPasswordMatch);
        
        passwordInput.addEventListener('blur', () => {
             if (passwordInput.value.length > 0 && !validity.password) {
                  showFieldError('password', 'Password too weak (8+ chars, 1 Upper, 1 Special)');
             }
        });
        
        confirmInput.addEventListener('blur', () => {
            if (confirmInput.value.length > 0 && !validity.confirm) {
               showFieldError('confirm_password', 'Passwords do not match');
            }
        });
    }


    // --- Form Submission ---
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Trigger validations if they haven't run (e.g. user pasted and hit enter immediately)
            if (!validity.username && usernameInput.value) await checkUsername();
            if (!validity.email && emailInput.value) await checkEmail();
            checkPasswordMatch();

            // Client-side block
            const visibleErrors = document.querySelectorAll('.field-error[style="display: flex;"]');
            if (visibleErrors.length > 0) {
                // Focus the first error field
                const firstErrorId = visibleErrors[0].id.replace('-error', '');
                document.getElementById(firstErrorId)?.focus();
                return;
            }
            
            // Check empty fields
            if (!usernameInput.value || !emailInput.value || !passwordInput.value) {
                showToast("Please fill in all fields.");
                return;
            }

            const formData = new FormData(form);
            submitBtn.disabled = true;
            submitBtn.innerText = "Creating Account...";

            try {
                const res = await fetch('php/register_process.php', {
                    method: 'POST',
                    body: formData
                });
                
                const contentType = res.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    // Fallback for unexpected server errors (PHP warnings leaking etc)
                    const text = await res.text();
                    console.error("Server Response:", text);
                    showToast("Server error. Please check console.");
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Create Account";
                    return;
                }

                const data = await res.json();

                if (res.ok && data.success) {
                    showToast("Account created! Redirecting...", 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'menu.php';
                    }, 1000);
                } else {
                    // Backend validation failed
                    if (data.field && data.field !== 'general') {
                        showFieldError(data.field, data.message);
                        // Shake effect or focus?
                        const field = document.getElementById(data.field);
                        if(field) field.focus();
                    } else {
                        showToast(data.message || "Registration failed");
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Create Account";
                }

            } catch (err) {
                console.error(err);
                showToast("Connection error. Please try again.");
                submitBtn.disabled = false;
                submitBtn.innerText = "Create Account";
            }
        });
    }
});
