document.addEventListener('DOMContentLoaded', () => {

    // --- State & Utility: Toast Notification ---
    const toast = document.getElementById('settingsToast');
    let toastTimeout = null;

    function showToast(message, type = 'success') {
        if (!toast) return;
        if (toastTimeout) clearTimeout(toastTimeout);

        const iconSvg = type === 'success' 
            ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
            : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

        toast.className = `settings-toast-alert ${type}`;
        toast.innerHTML = `${iconSvg}<span>${escapeHtml(message)}</span>`;
        toast.style.display = 'flex';

        toastTimeout = setTimeout(() => {
            toast.style.display = 'none';
            toast.className = 'settings-toast-alert';
        }, 4000);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // --- 1. Settings Navigation (Sidebar Tabs) ---
    const navItems = document.querySelectorAll('.settings-nav-item');
    const panels = document.querySelectorAll('.settings-tab-panel');

    function switchTab(tabId) {
        if (!tabId) return;
        const targetPanel = document.getElementById(`panel-${tabId}`);
        const targetNav = document.getElementById(`tab-${tabId}`);

        if (!targetPanel || !targetNav) return;

        navItems.forEach(n => {
            n.classList.remove('active');
            n.setAttribute('aria-selected', 'false');
        });
        panels.forEach(p => p.classList.remove('active'));

        targetNav.classList.add('active');
        targetNav.setAttribute('aria-selected', 'true');
        targetPanel.classList.add('active');

        // Update URL hash without jumping page
        if (history.replaceState) {
            history.replaceState(null, null, `#${tabId}`);
        }
    }

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const tabId = item.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // Check hash on load (e.g. #security, #notifications)
    const initialHash = window.location.hash.replace('#', '');
    if (initialHash && document.getElementById(`panel-${initialHash}`)) {
        switchTab(initialHash);
    }

    // --- 2. Profile Overview: Copy Username ---
    const avatarInitial = document.getElementById('avatarInitialText');
    const copyBtn = document.getElementById('copyUsernameBtn');
    const usernameInput = document.getElementById('usernameDisplay');

    if (copyBtn && usernameInput) {
        copyBtn.addEventListener('click', async () => {
            const textToCopy = usernameInput.value;
            if (!textToCopy) return;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(textToCopy);
                } else {
                    // Fallback
                    usernameInput.select();
                    document.execCommand('copy');
                    usernameInput.blur();
                }

                // Show visual checkmark icon briefly
                const origSvg = copyBtn.innerHTML;
                copyBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16A36A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                setTimeout(() => {
                    copyBtn.innerHTML = origSvg;
                }, 2000);

                showToast(`Username "${textToCopy}" copied to clipboard!`, 'success');
            } catch (err) {
                console.error('Clipboard copy failed:', err);
                showToast('Could not copy username', 'error');
            }
        });
    }

    // --- 3. Profile Information Form (Dirty Checking, Save, Reset) ---
    const profileForm = document.getElementById('profileForm');
    const fullNameInput = document.getElementById('fullName');
    const btnSaveProfile = document.getElementById('btnSaveProfile');
    const btnResetProfile = document.getElementById('btnResetProfile');
    const nameError = document.getElementById('nameError');
    const overviewUsername = document.getElementById('overviewUsername');

    if (profileForm && fullNameInput && btnSaveProfile && btnResetProfile) {
        function checkProfileDirty() {
            const originalVal = fullNameInput.getAttribute('data-original') || '';
            const currentVal = fullNameInput.value.trim();

            if (nameError) nameError.textContent = '';

            if (currentVal.length === 0) {
                if (nameError) nameError.textContent = 'Full name cannot be empty.';
                btnSaveProfile.disabled = true;
                btnResetProfile.disabled = false;
                return;
            }

            const isDirty = currentVal !== originalVal;
            btnSaveProfile.disabled = !isDirty;
            btnResetProfile.disabled = !isDirty;
        }

        fullNameInput.addEventListener('input', checkProfileDirty);

        btnResetProfile.addEventListener('click', () => {
            const originalVal = fullNameInput.getAttribute('data-original') || '';
            fullNameInput.value = originalVal;
            if (nameError) nameError.textContent = '';
            btnSaveProfile.disabled = true;
            btnResetProfile.disabled = true;
        });

        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const currentVal = fullNameInput.value.trim();
            const originalVal = fullNameInput.getAttribute('data-original') || '';

            if (!currentVal) {
                if (nameError) nameError.textContent = 'Full name cannot be empty.';
                return;
            }

            if (currentVal === originalVal) {
                return; // Nothing changed
            }

            btnSaveProfile.disabled = true;
            btnResetProfile.disabled = true;
            const originalBtnHtml = btnSaveProfile.innerHTML;
            btnSaveProfile.innerHTML = '<span class="btn-text">Saving...</span>';

            const formData = new FormData();
            formData.append('form_id', 'profileForm');
            formData.append('username', currentVal);
            if (window.CSRF_TOKEN) formData.append('csrf_token', window.CSRF_TOKEN);

            try {
                const response = await fetch('php/api_settings.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message || 'Profile updated successfully', 'success');
                    fullNameInput.setAttribute('data-original', currentVal);
                    if (usernameInput) usernameInput.value = currentVal;
                    if (overviewUsername) overviewUsername.textContent = currentVal;

                    // Update modal if present
                    const modalName = document.getElementById('modalNameText');
                    if (modalName) modalName.textContent = currentVal;

                    // Update initials if no image
                    const newInitial = currentVal.charAt(0).toUpperCase();
                    if (avatarInitial) avatarInitial.textContent = newInitial;
                    const navAvatar = document.querySelector('.avatar-new');
                    if (navAvatar) navAvatar.textContent = newInitial;
                    const navName = document.querySelector('.username-new');
                    if (navName) navName.textContent = currentVal;

                    btnSaveProfile.disabled = true;
                    btnResetProfile.disabled = true;
                } else {
                    showToast(result.error || 'Failed to update profile.', 'error');
                    if (nameError) nameError.textContent = result.error || 'Update failed.';
                    btnSaveProfile.disabled = false;
                    btnResetProfile.disabled = false;
                }
            } catch (err) {
                console.error('Profile update error:', err);
                showToast('Network error while saving profile.', 'error');
                btnSaveProfile.disabled = false;
                btnResetProfile.disabled = false;
            } finally {
                btnSaveProfile.innerHTML = originalBtnHtml;
            }
        });
    }

    // --- 4. View Profile Modal ---
    const btnViewProfile = document.getElementById('btnViewProfile');
    const viewProfileModal = document.getElementById('viewProfileModal');
    const btnCloseProfileModal = document.getElementById('btnCloseProfileModal');

    if (btnViewProfile && viewProfileModal) {
        btnViewProfile.addEventListener('click', () => {
            viewProfileModal.style.display = 'flex';
        });

        if (btnCloseProfileModal) {
            btnCloseProfileModal.addEventListener('click', () => {
                viewProfileModal.style.display = 'none';
            });
        }

        viewProfileModal.addEventListener('click', (e) => {
            if (e.target === viewProfileModal) {
                viewProfileModal.style.display = 'none';
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && viewProfileModal.style.display === 'flex') {
                viewProfileModal.style.display = 'none';
            }
        });
    }

    // --- 5. Security Tab (Password Validation, Strength & Visibility) ---
    const securityForm = document.getElementById('securityForm');
    const currentPwd = document.getElementById('currentPassword');
    const newPwd = document.getElementById('newPassword');
    const confirmPwd = document.getElementById('confirmPassword');
    const btnUpdatePassword = document.getElementById('btnUpdatePassword');
    const btnResetSecurity = document.getElementById('btnResetSecurity');
    const showAllPasswords = document.getElementById('showAllPasswords');

    const strengthBars = [
        document.getElementById('strengthBar1'),
        document.getElementById('strengthBar2'),
        document.getElementById('strengthBar3'),
        document.getElementById('strengthBar4')
    ];
    const strengthLabel = document.getElementById('strengthLabelText');

    const errCurrent = document.getElementById('currentPasswordError');
    const errNew = document.getElementById('newPasswordError');
    const errConfirm = document.getElementById('confirmPasswordError');

    const eyeSlashedSvg = '<svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 1l22 22"></path><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path></svg>';
    const eyeOpenSvg = '<svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';

    if (securityForm && currentPwd && newPwd && confirmPwd && btnUpdatePassword) {

        // 4-Segment Strength Evaluation
        function updatePasswordStrength(val) {
            strengthBars.forEach(bar => {
                if (bar) bar.className = 'strength-segment';
            });

            if (!val || val.length === 0) {
                if (strengthLabel) {
                    strengthLabel.innerHTML = '&mdash;';
                    strengthLabel.style.color = 'inherit';
                }
                return 0;
            }

            let score = 0;
            const hasLen = val.length >= 8;
            const hasCase = /[a-z]/.test(val) && /[A-Z]/.test(val);
            const hasNum = /[0-9]/.test(val);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>_\-\\\/+=~`\[\]]/.test(val);

            if (hasLen) score++;
            if (hasCase) score++;
            if (hasNum) score++;
            if (hasSpecial) score++;

            // Extra security: if length < 8, cap at score 1 (Weak)
            if (!hasLen && score > 1) score = 1;

            if (score === 1) {
                if (strengthBars[0]) strengthBars[0].classList.add('active-weak');
                if (strengthLabel) {
                    strengthLabel.textContent = 'Weak';
                    strengthLabel.style.color = '#EF4444';
                }
            } else if (score === 2) {
                if (strengthBars[0]) strengthBars[0].classList.add('active-fair');
                if (strengthBars[1]) strengthBars[1].classList.add('active-fair');
                if (strengthLabel) {
                    strengthLabel.textContent = 'Fair';
                    strengthLabel.style.color = '#F59E0B';
                }
            } else if (score === 3) {
                if (strengthBars[0]) strengthBars[0].classList.add('active-good');
                if (strengthBars[1]) strengthBars[1].classList.add('active-good');
                if (strengthBars[2]) strengthBars[2].classList.add('active-good');
                if (strengthLabel) {
                    strengthLabel.textContent = 'Good';
                    strengthLabel.style.color = '#3B82F6';
                }
            } else if (score >= 4) {
                strengthBars.forEach(bar => bar?.classList.add('active-strong'));
                if (strengthLabel) {
                    strengthLabel.textContent = 'Strong';
                    strengthLabel.style.color = '#10B981';
                }
            }

            return score;
        }

        // Live input listeners to clear errors on fix & update strength
        newPwd.addEventListener('input', () => {
            updatePasswordStrength(newPwd.value);
            if (errNew && errNew.textContent) {
                validateNewPassword(false);
            }
        });

        currentPwd.addEventListener('input', () => {
            if (currentPwd.value.trim().length > 0 && errCurrent) {
                errCurrent.textContent = '';
                errCurrent.classList.remove('show');
                currentPwd.classList.remove('input-error');
            }
        });

        confirmPwd.addEventListener('input', () => {
            if (confirmPwd.value === newPwd.value && errConfirm) {
                errConfirm.textContent = '';
                errConfirm.classList.remove('show');
                confirmPwd.classList.remove('input-error');
            }
        });

        // Individual Eye Toggle Buttons
        const eyeButtons = securityForm.querySelectorAll('.btn-toggle-eye');
        eyeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                if (!targetInput) return;

                const isCurrentlyPassword = targetInput.type === 'password';
                targetInput.type = isCurrentlyPassword ? 'text' : 'password';
                btn.innerHTML = isCurrentlyPassword ? eyeOpenSvg : eyeSlashedSvg;
            });
        });

        // Show All Passwords Checkbox
        if (showAllPasswords) {
            showAllPasswords.addEventListener('change', (e) => {
                const targetType = e.target.checked ? 'text' : 'password';
                [currentPwd, newPwd, confirmPwd].forEach(input => {
                    input.type = targetType;
                });
                eyeButtons.forEach(btn => {
                    btn.innerHTML = e.target.checked ? eyeOpenSvg : eyeSlashedSvg;
                });
            });
        }

        // Field Validation Functions
        function setFieldError(fieldInput, errorEl, msg) {
            if (errorEl) {
                errorEl.textContent = msg;
                errorEl.classList.add('show');
            }
            if (fieldInput) fieldInput.classList.add('input-error');
        }

        function clearFieldError(fieldInput, errorEl) {
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.remove('show');
            }
            if (fieldInput) fieldInput.classList.remove('input-error');
        }

        function validateNewPassword(showError = true) {
            const val = newPwd.value;
            if (!val) {
                if (showError) setFieldError(newPwd, errNew, 'Please enter a new password.');
                return false;
            }
            if (val.length < 8) {
                if (showError) setFieldError(newPwd, errNew, 'Password must be at least 8 characters long.');
                return false;
            }
            if (!/[A-Z]/.test(val) || !/[a-z]/.test(val)) {
                if (showError) setFieldError(newPwd, errNew, 'Must include both uppercase and lowercase letters.');
                return false;
            }
            if (!/[0-9]/.test(val)) {
                if (showError) setFieldError(newPwd, errNew, 'Must include at least one number.');
                return false;
            }
            if (!/[!@#$%^&*(),.?":{}|<>_\-\\\/+=~`\[\]]/.test(val)) {
                if (showError) setFieldError(newPwd, errNew, 'Must include at least one special character (e.g. ! @ # $ %).');
                return false;
            }
            if (currentPwd.value && val === currentPwd.value) {
                if (showError) setFieldError(newPwd, errNew, 'New password cannot be identical to current password.');
                return false;
            }

            clearFieldError(newPwd, errNew);
            return true;
        }

        // Reset Action
        function resetSecurityForm() {
            securityForm.reset();
            [currentPwd, newPwd, confirmPwd].forEach(input => {
                input.type = 'password';
                input.classList.remove('input-error');
            });
            [errCurrent, errNew, errConfirm].forEach(err => {
                if (err) {
                    err.textContent = '';
                    err.classList.remove('show');
                }
            });
            eyeButtons.forEach(btn => {
                btn.innerHTML = eyeSlashedSvg;
            });
            updatePasswordStrength('');
            btnUpdatePassword.disabled = false;
        }

        if (btnResetSecurity) {
            btnResetSecurity.addEventListener('click', resetSecurityForm);
        }

        // Form Submission
        securityForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            let isValid = true;
            clearFieldError(currentPwd, errCurrent);
            clearFieldError(newPwd, errNew);
            clearFieldError(confirmPwd, errConfirm);

            // 1. Current Password Check
            if (!currentPwd.value.trim()) {
                setFieldError(currentPwd, errCurrent, 'Please enter your current password.');
                isValid = false;
            }

            // 2. New Password Check
            if (!validateNewPassword(true)) {
                isValid = false;
            }

            // 3. Confirm Password Check
            if (!confirmPwd.value) {
                setFieldError(confirmPwd, errConfirm, 'Please confirm your new password.');
                isValid = false;
            } else if (confirmPwd.value !== newPwd.value) {
                setFieldError(confirmPwd, errConfirm, 'New password and confirmation do not match.');
                isValid = false;
            }

            if (!isValid) return;

            btnUpdatePassword.disabled = true;
            const origHtml = btnUpdatePassword.innerHTML;
            btnUpdatePassword.innerHTML = '<span class="btn-text">Updating...</span>';

            const formData = new FormData();
            formData.append('form_id', 'securityForm');
            formData.append('current_password', currentPwd.value);
            formData.append('new_password', newPwd.value);
            formData.append('confirm_password', confirmPwd.value);
            if (window.CSRF_TOKEN) formData.append('csrf_token', window.CSRF_TOKEN);

            try {
                const res = await fetch('php/api_settings.php', { method: 'POST', body: formData });
                const json = await res.json();

                if (json.success) {
                    showToast(json.message || 'Password updated successfully', 'success');
                    resetSecurityForm();
                } else {
                    const errorMsg = json.error || 'Failed to update password.';
                    showToast(errorMsg, 'error');

                    if (errorMsg.toLowerCase().includes('current password')) {
                        setFieldError(currentPwd, errCurrent, errorMsg);
                        currentPwd.focus();
                    } else if (errorMsg.toLowerCase().includes('match')) {
                        setFieldError(confirmPwd, errConfirm, errorMsg);
                        confirmPwd.focus();
                    } else {
                        setFieldError(newPwd, errNew, errorMsg);
                        newPwd.focus();
                    }
                }
            } catch (err) {
                console.error('Password update network error:', err);
                showToast('Network error while updating password.', 'error');
            } finally {
                btnUpdatePassword.disabled = false;
                btnUpdatePassword.innerHTML = origHtml;
            }
        });
    }

    // --- 6. Currency & Format Tab ---
    const preferencesForm = document.getElementById('preferencesForm');
    const currencySelector = document.getElementById('currencySelector');
    const btnSaveCurrency = document.getElementById('btnSaveCurrency');

    if (preferencesForm && currencySelector && btnSaveCurrency) {
        let originalCurrency = currencySelector.value;

        currencySelector.addEventListener('change', () => {
            btnSaveCurrency.disabled = (currencySelector.value === originalCurrency);
        });

        preferencesForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const selectedCurrency = currencySelector.value;
            if (selectedCurrency === originalCurrency) {
                btnSaveCurrency.disabled = true;
                return;
            }

            btnSaveCurrency.disabled = true;
            const origHtml = btnSaveCurrency.innerHTML;
            btnSaveCurrency.innerHTML = '<span class="btn-text">Saving...</span>';

            const formData = new FormData(preferencesForm);
            formData.append('form_id', 'preferencesForm');
            if (window.CSRF_TOKEN) formData.append('csrf_token', window.CSRF_TOKEN);

            try {
                const res = await fetch('php/api_settings.php', { method: 'POST', body: formData });
                const json = await res.json();
                if (json.success) {
                    originalCurrency = selectedCurrency;
                    btnSaveCurrency.disabled = true;
                    if (json.currency) {
                        window.CURRENCY_CODE = json.currency;
                        window.CURRENCY_SYMBOL = json.symbol || '₹';
                    }
                    if (json.rates) {
                        window.EXCHANGE_RATES = json.rates;
                    }
                    if (typeof window.updateDisplayCurrency === 'function') {
                        window.updateDisplayCurrency(selectedCurrency, json.rates);
                    }
                    localStorage.setItem('currency', selectedCurrency);

                    const modalCurrencyText = document.getElementById('modalCurrencyText');
                    if (modalCurrencyText) {
                        modalCurrencyText.textContent = selectedCurrency;
                    }

                    // Update live exchange rate details card
                    const dispCurrencyVal = document.getElementById('dispCurrencyVal');
                    const dispExchangeRate = document.getElementById('dispExchangeRate');
                    const dispLastUpdated = document.getElementById('dispLastUpdated');
                    const rateStatusPill = document.getElementById('rateStatusPill');

                    if (dispCurrencyVal) {
                        dispCurrencyVal.textContent = `${json.currency} (${json.symbol || ''})`;
                    }
                    if (dispExchangeRate && json.exchange_rate) {
                        const formattedRate = (json.currency === 'INR') 
                            ? '1.00 INR' 
                            : Number(json.exchange_rate).toFixed(6) + ' ' + json.currency;
                        dispExchangeRate.textContent = `1 INR = ${formattedRate}`;
                    }
                    if (dispLastUpdated && json.last_updated) {
                        const dateObj = new Date(json.last_updated);
                        dispLastUpdated.textContent = isNaN(dateObj.getTime()) ? json.last_updated : dateObj.toLocaleString('en-US', {
                            month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
                        });
                    }
                    if (rateStatusPill) {
                        const isCached = !!json.is_cached;
                        rateStatusPill.textContent = isCached ? 'Cached' : 'Live';
                        rateStatusPill.className = 'badge-status-pill ' + (isCached ? 'cached' : 'live');
                    }

                    showToast(json.message || 'Currency preference saved successfully', 'success');
                } else {
                    showToast(json.error || 'Failed to save currency', 'error');
                    btnSaveCurrency.disabled = false;
                }
            } catch (err) {
                console.error('Currency save network error:', err);
                showToast('Network error while saving currency', 'error');
                btnSaveCurrency.disabled = false;
            } finally {
                btnSaveCurrency.innerHTML = origHtml;
            }
        });
    }

});
