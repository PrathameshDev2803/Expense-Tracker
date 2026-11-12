document.addEventListener('DOMContentLoaded', () => {

    // --- Base Path & Endpoint Resolver ---
    function getAppBasePath() {
        if (typeof window.APP_BASE_PATH === 'string' && window.APP_BASE_PATH.trim().length > 0) {
            const bp = window.APP_BASE_PATH.trim();
            return bp.endsWith('/') ? bp : bp + '/';
        }
        const metaTag = document.querySelector('meta[name="app-base-path"]');
        if (metaTag && metaTag.getAttribute('content')) {
            const val = metaTag.getAttribute('content').trim();
            return val.endsWith('/') ? val : val + '/';
        }
        // Fallback: derive base path from script src if loaded from /js/auth.js
        const scripts = document.getElementsByTagName('script');
        for (let i = scripts.length - 1; i >= 0; i--) {
            const src = scripts[i].src || '';
            const idx = src.lastIndexOf('/js/auth.js');
            if (idx !== -1) {
                try {
                    const u = new URL(src);
                    return u.pathname.substring(0, idx + 1);
                } catch (_) {}
            }
        }
        // Fallback: derive directory from window.location.pathname
        const path = window.location.pathname;
        const lastSlash = path.lastIndexOf('/');
        return lastSlash !== -1 ? path.substring(0, lastSlash + 1) : '/';
    }

    function resolveEndpoint(relativePath) {
        const base = getAppBasePath().replace(/\/+$/, '');
        const cleanRel = relativePath.replace(/^\/+/, '');
        return `${base}/${cleanRel}`;
    }

    // --- Safe API Response Parser ---
    async function parseApiResponse(res, endpointUrl) {
        const status = res.status;
        const contentType = res.headers.get('content-type') || '';
        let rawText = '';
        let data = null;

        try {
            rawText = await res.text();
            data = JSON.parse(rawText);
        } catch (_) {
            console.error(`[API Diagnostic] Non-JSON response received (HTTP ${status}) from ${endpointUrl}:`, rawText);
        }

        return {
            ok: res.ok,
            status,
            data,
            rawText,
            contentType
        };
    }

    // --- References ---
    const form = document.getElementById('register-form');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submit-btn');
    const toastContainer = document.getElementById('toast-container');

    // --- State ---
    let isSubmitting = false;
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
        const container = toastContainer || document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-alert ${type === 'success' ? 'valid' : ''}`;
        toast.innerText = message;
        
        container.appendChild(toast);

        // Remove after 5s
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            toast.addEventListener('animationend', () => {
                toast.remove();
            });
        }, 5000);
    }

    // --- Password Toggle (Works across register.php & login.php) ---
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

    // --- Validation Logic ---
    async function checkUsername() {
        if (!usernameInput) return;
        const username = usernameInput.value.trim();
        if (!username) {
            validity.username = false;
            return;
        }

        if (username.length < 3 || username.length > 30 || !/^[a-zA-Z0-9_]+$/.test(username)) {
            showFieldError('username', 'Username must be 3-30 characters (letters, numbers, underscores only)');
            validity.username = false;
            return;
        }

        const endpoint = resolveEndpoint(`php/check_username.php?username=${encodeURIComponent(username)}`);
        try {
            const res = await fetch(endpoint, {
                headers: { 'Accept': 'application/json' }
            });
            const { ok, status, data, rawText } = await parseApiResponse(res, endpoint);

            if (!data) {
                console.error(`[API Diagnostic] check_username failed (HTTP ${status}) from ${endpoint}:`, rawText);
                if (status === 404) {
                    showFieldError('username', 'Username check endpoint not found (HTTP 404)');
                } else {
                    showFieldError('username', 'Unable to verify username. Please try again.');
                }
                validity.username = false;
                return;
            }

            if (!data.available) {
                showFieldError('username', data.message || 'Username already taken');
                validity.username = false;
            } else {
                clearFieldError('username');
                validity.username = true;
            }
        } catch (err) {
            console.error(`[API Diagnostic] Network error checking username at ${endpoint}:`, err);
            showFieldError('username', 'Network error checking username.');
            validity.username = false;
        }
    }

    async function checkEmail() {
        if (!emailInput) return;
        const email = emailInput.value.trim();
        if (!email) {
            validity.email = false;
            return;
        }
        
        // Regex format check
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showFieldError('email', 'Invalid email format');
            validity.email = false;
            return;
        }

        const endpoint = resolveEndpoint(`php/check_email.php?email=${encodeURIComponent(email)}`);
        try {
            const res = await fetch(endpoint, {
                headers: { 'Accept': 'application/json' }
            });
            const { ok, status, data, rawText } = await parseApiResponse(res, endpoint);

            if (!data) {
                console.error(`[API Diagnostic] check_email failed (HTTP ${status}) from ${endpoint}:`, rawText);
                if (status === 404) {
                    showFieldError('email', 'Email check endpoint not found (HTTP 404)');
                } else {
                    showFieldError('email', 'Unable to verify email. Please try again.');
                }
                validity.email = false;
                return;
            }

            if (!data.available) {
                showFieldError('email', data.message || 'This email is already registered');
                validity.email = false;
            } else {
                clearFieldError('email');
                validity.email = true;
            }
        } catch (err) {
            console.error(`[API Diagnostic] Network error checking email at ${endpoint}:`, err);
            showFieldError('email', 'Network error checking email.');
            validity.email = false;
        }
    }

    function checkPasswordMatch() {
        if (!passwordInput) return;
        const val1 = passwordInput.value;
        const val2 = confirmInput ? confirmInput.value : '';

        if (!val1) {
            validity.password = false;
            validity.confirm = false;
            return;
        }

        // Strong password check: 8+ chars, 1 uppercase, 1 special character
        const isStrong = val1.length >= 8 && /[A-Z]/.test(val1) && /[\W_]/.test(val1);
        if (!isStrong) {
            validity.password = false;
        } else {
            validity.password = true;
            clearFieldError('password');
        }

        if (confirmInput && val2) {
            if (val1 === val2) {
                clearFieldError('confirm_password');
                validity.confirm = true;
            } else {
                showFieldError('confirm_password', 'Passwords do not match');
                validity.confirm = false;
            }
        }
    }

    // --- Input Event Listeners ---
    if (usernameInput) {
        usernameInput.addEventListener('blur', checkUsername);
        usernameInput.addEventListener('input', () => {
            if (usernameInput.classList.contains('invalid')) {
                usernameInput.classList.remove('invalid');
                const err = document.getElementById('username-error');
                if (err) err.style.display = 'none';
            }
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', checkEmail);
        emailInput.addEventListener('input', () => {
            if (emailInput.classList.contains('invalid')) {
                emailInput.classList.remove('invalid');
                const err = document.getElementById('email-error');
                if (err) err.style.display = 'none';
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('blur', () => {
            if (passwordInput.value.length > 0 && !validity.password) {
                showFieldError('password', 'Password too weak (8+ chars, 1 Upper, 1 Special)');
            }
        });
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', checkPasswordMatch);
        confirmInput.addEventListener('blur', () => {
            if (confirmInput.value.length > 0 && !validity.confirm) {
                showFieldError('confirm_password', 'Passwords do not match');
            }
        });
    }

    // --- Form Submission Workflow ---
    if (form) {
        form.addEventListener('submit', async (e) => {
            // Guarantee native form submission and page reloads are halted immediately
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            // Prevent duplicate submissions while in-flight
            if (isSubmitting) {
                return false;
            }

            // Trigger validations if they haven't run
            if (!validity.username && usernameInput && usernameInput.value) {
                await checkUsername();
            }
            if (!validity.email && emailInput && emailInput.value) {
                await checkEmail();
            }
            checkPasswordMatch();

            // Client-side block if visible field errors remain
            const visibleErrors = document.querySelectorAll('.field-error[style*="display: flex"]');
            if (visibleErrors.length > 0) {
                const firstErrorId = visibleErrors[0].id.replace('-error', '');
                document.getElementById(firstErrorId)?.focus();
                return false;
            }

            // Check empty fields
            if (!usernameInput?.value?.trim() || !emailInput?.value?.trim() || !passwordInput?.value) {
                showToast("Please fill in all fields.");
                return false;
            }

            if (!confirmInput?.value) {
                showFieldError('confirm_password', 'Please confirm your password.');
                confirmInput?.focus();
                return false;
            }

            if (passwordInput.value !== confirmInput.value) {
                showFieldError('confirm_password', 'Passwords do not match.');
                confirmInput?.focus();
                return false;
            }

            // Set loading state & lock submission
            isSubmitting = true;
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = "Creating Account...";
            }

            const restoreSubmitButton = () => {
                isSubmitting = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Create Account";
                }
            };

            const endpoint = resolveEndpoint('php/register_process.php');
            const formData = new FormData(form);

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const { ok, status, data, rawText } = await parseApiResponse(res, endpoint);

                // Handle non-JSON or unexpected responses (such as 404 HTML, 500 HTML)
                if (!data) {
                    console.error(`[API Diagnostic] Endpoint ${endpoint} returned non-JSON response (HTTP ${status}):`, rawText);
                    if (status === 404) {
                        showToast(`Registration endpoint not found (HTTP 404). Please verify deployment.`);
                    } else if (status >= 500) {
                        showToast(`Server error occurred (HTTP ${status}). Please try again later.`);
                    } else {
                        showToast(`Unexpected server response (HTTP ${status}). Please try again.`);
                    }
                    restoreSubmitButton();
                    return false;
                }

                // Handle successful account creation
                if (ok && data.success) {
                    showToast("Account created! Redirecting...", 'success');
                    let redirectTarget = data.redirect || 'menu.php';
                    if (!redirectTarget.startsWith('http://') && !redirectTarget.startsWith('https://') && !redirectTarget.startsWith('/')) {
                        redirectTarget = resolveEndpoint(redirectTarget);
                    }
                    setTimeout(() => {
                        window.location.href = redirectTarget;
                    }, 1000);
                    return true;
                }

                // Handle structured error responses
                console.warn(`[API Diagnostic] Registration failed (HTTP ${status}) from ${endpoint}:`, data);

                switch (status) {
                    case 400: // Validation failure
                        if (data.field && data.field !== 'general') {
                            showFieldError(data.field, data.message || 'Validation error');
                            document.getElementById(data.field)?.focus();
                        } else {
                            showToast(data.message || 'Validation error. Please verify your entries.');
                        }
                        break;
                    case 404: // Endpoint not found
                        showToast(data.message || 'Registration service endpoint not found (HTTP 404).');
                        break;
                    case 409: // Duplicate email or username
                        if (data.field && data.field !== 'general') {
                            showFieldError(data.field, data.message || 'Account already exists');
                            document.getElementById(data.field)?.focus();
                        }
                        showToast(data.message || 'An account with this email or username already exists.');
                        break;
                    case 429: // Rate limit
                        showToast(data.message || 'Too many attempts. Please try again later.');
                        break;
                    case 500: // Internal server error
                    default:
                        showToast(data.message || 'A system error occurred. Please try again later.');
                        break;
                }

                restoreSubmitButton();
                return false;

            } catch (networkErr) {
                console.error(`[API Diagnostic] Network fetch failure connecting to ${endpoint}:`, networkErr);
                showToast("Connection failed. Please check your network connection and try again.");
                restoreSubmitButton();
                return false;
            }
        });
    }
});
