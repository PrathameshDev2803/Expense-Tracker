document.addEventListener('DOMContentLoaded', () => {
    
    // --- Navigation (Tabs) ---
    const navItems = document.querySelectorAll('.nav-item');
    const panels = document.querySelectorAll('.settings-panel');
    const feedback = document.getElementById('settingsFeedback');

    function showFeedback(message, type = 'success') {
        feedback.textContent = message;
        feedback.className = `settings-feedback ${type}`;
        setTimeout(() => {
            feedback.className = 'settings-feedback'; // Hide
        }, 5000); // 5 sec duration
    }

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            // Deactivate all
            navItems.forEach(n => n.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            // Activate current
            item.classList.add('active');
            const targetId = item.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
        });
    });


    // --- Generic Form Handling (Profile, Security, Preferences) ---
    const forms = document.querySelectorAll('.settings-form');
    
    forms.forEach(form => {
        const btn = form.querySelector('button[type="submit"]');
        const inputs = form.querySelectorAll('input, select');
        
        // Dirty Checking
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                if(btn) btn.disabled = false;
            });
            input.addEventListener('change', () => { // For select interactions
                 if(btn) btn.disabled = false;
            });
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Saving...';
            }

            const formData = new FormData(form);
            // Append form ID to identify action type
            formData.append('form_id', form.id);

            try {
                const response = await fetch('php/api_settings.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();

                if (result.success) {
                    showFeedback(result.message, 'success');
                    if(form.id === 'securityForm') form.reset();
                    // Keep the button disabled until new changes
                } else {
                    showFeedback(result.error || 'An error occurred.', 'error');
                    if (btn) btn.disabled = false; // Re-enable on error
                }
            } catch (err) {
                console.error(err);
                showFeedback('Network error. Please try again.', 'error');
                if (btn) btn.disabled = false;
            } finally {
                if (btn && btn.textContent === 'Saving...') {
                    // Reset button text
                     if(form.id === 'profileForm') btn.textContent = 'Save Changes';
                     if(form.id === 'securityForm') btn.textContent = 'Update Password';
                     if(form.id === 'preferencesForm') btn.textContent = 'Save Preferences';
                }
            }
        });
    });


    // --- Security Validation ---
    const newPwd = document.getElementById('newPassword');
    const confirmPwd = document.getElementById('confirmPassword');
    const securityBtn = document.querySelector('#securityForm button');
    const strengthIndicator = document.getElementById('passwordStrength');
    const showPwdToggle = document.getElementById('showPasswordToggle');

    if (newPwd && confirmPwd) {
        function validateSecurity() {
            const val = newPwd.value;
            let strength = '';
            let color = '';
            
            if (val.length === 0) {
                strength = '';
            } else if (val.length < 8) {
                strength = 'Weak (Too short)';
                color = '#ef4444';
            } else if (/[A-Z]/.test(val) && /[0-9]/.test(val)) {
                strength = 'Strong';
                color = '#10b981';
            } else {
                strength = 'Medium (Add numbers/caps)';
                color = '#f59e0b';
            }

            if (strengthIndicator) {
                strengthIndicator.textContent = strength;
                strengthIndicator.style.color = color;
                strengthIndicator.style.fontSize = '0.85rem';
                strengthIndicator.style.marginTop = '4px';
            }

            // Button Logic
            const isValid = val.length >= 8 && val === confirmPwd.value && document.getElementById('currentPassword').value;
            if(!isValid) securityBtn.disabled = true;
            // The general dirty checker enables it, but this specific validation should force disable implies override?
            // Actually, best to let the user try and fail handled by backend OR strict client side. 
            // The prompt says "Disable submit until all conditions are met".
            return isValid;
        }

        [newPwd, confirmPwd, document.getElementById('currentPassword')].forEach(el => {
            el?.addEventListener('input', () => {
                 const isValid = (newPwd.value.length >= 8) && (newPwd.value === confirmPwd.value) && (document.getElementById('currentPassword').value.length > 0);
                 securityBtn.disabled = !isValid;
                 validateSecurity();
            });
        });

        showPwdToggle.addEventListener('change', (e) => {
            const type = e.target.checked ? 'text' : 'password';
            document.getElementById('currentPassword').type = type;
            newPwd.type = type;
            confirmPwd.type = type;
        });
    }

    // --- Appearance (Immediate Switch) ---
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(radio => {
        radio.addEventListener('change', async (e) => {
            const theme = e.target.value;
            
            // Immediate UI update
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark-mode');
            }
            localStorage.setItem('theme', theme);

            // Persist
            const formData = new FormData();
            formData.append('form_id', 'appearanceForm'); // Virtual form
            formData.append('theme', theme);

            try {
                await fetch('php/api_settings.php', { method: 'POST', body: formData });
            } catch(err) { console.error('Failed to save theme'); }
        });
    });

    // --- Notifications (Independent Toggles) ---
    const notifSwitches = document.querySelectorAll('.toggles-list input[type="checkbox"]');
    notifSwitches.forEach(toggle => {
        toggle.addEventListener('change', async () => {
            // Collect all notification states
            const prefs = {};
            prefs.budget = document.getElementById('notif_budget').checked;
            prefs.monthly = document.getElementById('notif_monthly').checked;
            prefs.goals = document.getElementById('notif_goals').checked;
            prefs.system = document.getElementById('notif_system').checked;

            const formData = new FormData();
            formData.append('form_id', 'notificationsForm'); // Virtual form
            formData.append('prefs', JSON.stringify(prefs));

            try {
                const res = await fetch('php/api_settings.php', { method: 'POST', body: formData });
                const json = await res.json();
                if(json.success) {
                    showFeedback('Preferences saved', 'success');
                }
            } catch(err) {
                 showFeedback('Failed to save preference', 'error');
                 toggle.checked = !toggle.checked; // Revert
            }
        });
    });

    // --- Preferences (Currency) ---
    // Handled by generic form listener, but we might want to update LS for guests/fallback
    const currencySelector = document.getElementById('currencySelector');
    if(currencySelector) {
        currencySelector.addEventListener('change', () => {
            localStorage.setItem('currency', currencySelector.value);
        });
    }

});
