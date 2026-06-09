<?php
include 'php/auth_check.php';
include_once 'php/db.php';

// Ensure database table exists
include 'php/setup_goals.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings Goals | ExpenseTracker</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/goals.css">
</head>
<body>
    <?php include 'php/header.php'; ?>
    
    <main class="goals-container">
        <header class="goals-header">
            <div class="goals-title">
                <h1>Savings Goals</h1>
                <p>Visualize your progress and reach your financial dreams.</p>
            </div>
            <button class="add-goal-btn" id="newGoalBtn" aria-label="Create a new savings goal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Goal
            </button>
        </header>

        <div id="goalsGrid" class="goals-grid" aria-live="polite">
            <!-- JS will populate this -->
            <div style="grid-column: 1/-1; text-align: center; color: var(--text-secondary);">Loading goals...</div>
        </div>
    </main>

    <!-- Create Goal Modal -->
    <!-- Create Goal Modal -->
    <div id="createGoalModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
        <div class="modal-content modal-split">
            <!-- Mobile Header -->
            <div class="modal-header-mobile">
                <h2 id="createModalTitleMobile">Create New Goal</h2>
                <button class="close-modal close-create-btn" aria-label="Close modal">&times;</button>
            </div>

            <!-- Left: Live Preview -->
            <div class="modal-left-preview">
                <div class="live-goal-card" aria-live="polite">
                    <div class="live-icon-wrapper">
                        <span id="liveIconPreview">🎯</span>
                    </div>
                    <div class="live-text-content">
                        <h3 id="liveGoalName">Goal Name</h3>
                        <div class="live-amount">
                            <span id="liveSavedAmount"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span>
                            <span class="live-separator">/</span>
                            <span id="liveTargetAmount"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>50,000</span>
                        </div>
                    </div>
                    
                    <div class="live-progress-container">
                        <div class="live-progress-bar" id="liveProgressBar" style="width: 0%"></div>
                    </div>
                    
                    <div class="live-helper-text">You’re building this goal</div>
                </div>
            </div>

            <!-- Right: Form -->
            <div class="modal-right-form">
                <div class="modal-header-desktop">
                    <h2 id="createModalTitle">Create New Goal</h2>
                    <button class="close-modal close-create-btn" aria-label="Close modal">&times;</button>
                </div>
                
                <form id="createGoalForm">
                    <input type="hidden" id="editGoalId">
                    <!-- Identity Group -->
                    <div class="goal-form-section">
                        <div class="goal-input-group">
                            <label class="goal-input-label" for="goalName">Goal Name</label>
                            <input type="text" id="goalName" name="goalName" class="goal-input-field" placeholder="e.g. Dream Vacation" required autocomplete="off">
                        </div>
                        
                        <div class="goal-input-group">
                            <label class="goal-input-label">Icon</label>
                            <div class="icon-grid" role="radiogroup" aria-label="Select Goal Icon">
                                <label class="icon-option">
                                    <input type="radio" name="goalIcon" value="target" checked>
                                    <span class="icon-visual">🎯</span>
                                </label>
                                <label class="icon-option">
                                    <input type="radio" name="goalIcon" value="travel">
                                    <span class="icon-visual">✈️</span>
                                </label>
                                <label class="icon-option">
                                    <input type="radio" name="goalIcon" value="gadget">
                                    <span class="icon-visual">📱</span>
                                </label>
                                <label class="icon-option">
                                    <input type="radio" name="goalIcon" value="home">
                                    <span class="icon-visual">🏠</span>
                                </label>
                                <label class="icon-option">
                                    <input type="radio" name="goalIcon" value="car">
                                    <span class="icon-visual">🚗</span>
                                </label>
                                <label class="icon-option">
                                    <input type="radio" name="goalIcon" value="emergency">
                                    <span class="icon-visual">🚑</span>
                                </label>
                                <label class="icon-option">
                                    <input type="radio" name="goalIcon" value="gift">
                                    <span class="icon-visual">🎁</span>
                                </label>
                                <label class="icon-option">
                                    <input type="radio" name="goalIcon" value="education">
                                    <span class="icon-visual">🎓</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Money Group -->
                    <div class="goal-form-section">
                        <div class="input-row">
                            <div class="goal-input-group">
                                <label class="goal-input-label" for="targetAmount">Target Amount</label>
                                <input type="number" id="targetAmount" name="targetAmount" class="goal-input-field" placeholder="50000" min="0.01" step="0.01" required>
                            </div>
                            <div class="goal-input-group">
                                <label class="goal-input-label" for="startingAmount">Starting Balance <span class="optional-label">(Optional)</span></label>
                                <input type="number" id="startingAmount" name="startingAmount" class="goal-input-field" placeholder="0.00" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <!-- Time Group -->
                    <div class="goal-form-section">
                        <div class="goal-input-group">
                            <label class="goal-input-label" for="deadline">Target Date <span class="optional-label">(Optional)</span></label>
                            <input type="date" id="deadline" name="deadline" class="goal-input-field">
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" id="cancelCreateBtn">Cancel</button>
                        <button type="submit" class="btn-primary">Start Saving</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Money Modal -->
    <!-- Add Money Modal -->
    <div id="addMoneyModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="addMoneyModalTitle">
        <div class="modal-content modal-compact">
            <div class="modal-header">
                <div class="modal-title-wrapper">
                    <span id="addMoneyGoalIcon" class="modal-goal-icon"></span>
                    <h2 id="addMoneyModalTitle">Add Funds</h2>
                </div>
                <button class="close-modal" id="closeAddMoneyModalBtn" aria-label="Close modal">&times;</button>
            </div>
            
            <!-- Context Card -->
            <div class="goal-context-card">
                <div class="context-row">
                    <span class="context-label">Target Goal</span>
                    <span class="context-value" id="addContextTarget"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span>
                </div>
                <div class="context-progress-track">
                    <div class="context-progress-fill" id="addContextBar" style="width: 0%"></div>
                    <div class="context-progress-fill-preview" id="addContextBarPreview" style="width: 0%"></div>
                </div>
                <div class="context-row small">
                    <span class="text-secondary">Saved: <strong id="addContextSaved" class="text-primary"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</strong></span>
                    <span class="text-secondary">Left: <strong id="addContextRemaining"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</strong></span>
                </div>
            </div>

            <form id="addMoneyForm">
                <input type="hidden" id="addMoneyGoalId">
                <input type="hidden" id="currentSavedAmount">
                <input type="hidden" id="currentTargetAmount">
                
                <div class="add-amount-section">
                    <label class="amount-label" for="addAmount">How much would you like to add?</label>
                    <div class="large-input-container">
                        <span class="currency-prefix"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?></span>
                        <input type="number" id="addAmount" name="addAmount" class="large-amount-input" placeholder="0" min="1" step="1" required autocomplete="off">
                    </div>
                </div>

                <div class="amount-presets">
                    <button type="button" class="preset-btn" data-amount="500">+<?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>500</button>
                    <button type="button" class="preset-btn" data-amount="1000">+<?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>1,000</button>
                    <button type="button" class="preset-btn" data-amount="2500">+<?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>2,500</button>
                    <button type="button" class="preset-btn" data-amount="5000">+<?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>5,000</button>
                </div>

                <div class="live-feedback hidden" id="liveFeedback" aria-live="polite">
                    <p class="feedback-main">This will bring you to <strong id="addPredictPercent">0%</strong></p>
                    <p class="feedback-sub"><span id="addPredictRemaining"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span> left to reach your goal</p>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancelAddMoneyBtn">Cancel</button>
                    <button type="submit" class="btn-primary" id="addMoneySubmitBtn" disabled>Add Funds</button>
                </div>
            </form>
            
            <div id="addMoneySuccessView" class="modal-success-view hidden">
                <div class="success-anim-icon">🎉</div>
                <h3 class="success-title">Nice work!</h3>
                <p class="success-message">You're one step closer to your <span id="successGoalName">goal</span>.</p>
            </div>
        </div>
    </div>

            </form>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const goalsGrid = document.getElementById('goalsGrid');
            const createModal = document.getElementById('createGoalModal');
            const addMoneyModal = document.getElementById('addMoneyModal');
            
            // Buttons
            const newGoalBtn = document.getElementById('newGoalBtn');
            const closeCreateBtns = document.querySelectorAll('.close-create-btn');
            const cancelCreateBtn = document.getElementById('cancelCreateBtn');
            const closeAddMoneyBtn = document.getElementById('closeAddMoneyModalBtn');
            const cancelAddMoneyBtn = document.getElementById('cancelAddMoneyBtn');
            
            // Live Preview Elements
            const livePreview = {
                name: document.getElementById('liveGoalName'),
                icon: document.getElementById('liveIconPreview'),
                saved: document.getElementById('liveSavedAmount'),
                target: document.getElementById('liveTargetAmount'),
                bar: document.getElementById('liveProgressBar')
            };

            const createInputs = {
                name: document.getElementById('goalName'),
                target: document.getElementById('targetAmount'),
                start: document.getElementById('startingAmount'),
                iconRadios: document.querySelectorAll('input[name="goalIcon"]')
            };

            const addMoneyElements = {
                modal: document.getElementById('addMoneyModal'),
                form: document.getElementById('addMoneyForm'),
                input: document.getElementById('addAmount'),
                submitBtn: document.getElementById('addMoneySubmitBtn'),
                presets: document.querySelectorAll('.preset-btn'),
                // Header
                title: document.getElementById('addMoneyModalTitle'),
                icon: document.getElementById('addMoneyGoalIcon'),
                // Hidden Data
                goalId: document.getElementById('addMoneyGoalId'),
                currentSaved: document.getElementById('currentSavedAmount'),
                currentTarget: document.getElementById('currentTargetAmount'),
                // Context Card
                ctxTarget: document.getElementById('addContextTarget'),
                ctxSaved: document.getElementById('addContextSaved'),
                ctxRemaining: document.getElementById('addContextRemaining'),
                ctxBar: document.getElementById('addContextBar'),
                ctxBarPreview: document.getElementById('addContextBarPreview'),
                // Live Feedback
                feedbackBox: document.getElementById('liveFeedback'),
                predictPercent: document.getElementById('addPredictPercent'),
                predictRemaining: document.getElementById('addPredictRemaining'),
                // Success State
                successView: document.getElementById('addMoneySuccessView'),
                successName: document.getElementById('successGoalName')
            };

            // Initial Load
            fetchGoals();

            // --- Live Preview Helper ---
            const moneyFmt = new Intl.NumberFormat('en-IN', { 
                style: 'currency', 
                currency: window.CURRENCY_CODE || 'INR', 
                maximumFractionDigits: 0 
            });

            function updateLivePreview() {
                // Update Name
                livePreview.name.textContent = createInputs.name.value.trim() || 'Goal Name';
                
                // Update Icon
                const selectedIcon = document.querySelector('input[name="goalIcon"]:checked');
                if(selectedIcon) {
                    const iconVisual = selectedIcon.nextElementSibling.textContent;
                    livePreview.icon.textContent = iconVisual;
                }

                // Update Amounts
                const targetVal = parseFloat(createInputs.target.value);
                const startVal = parseFloat(createInputs.start.value) || 0;
                
                // If target is empty or 0, show placeholder
                livePreview.target.textContent = (targetVal && targetVal > 0) ? moneyFmt.format(targetVal) : (window.CURRENCY_SYMBOL + '50,000');
                livePreview.saved.textContent = moneyFmt.format(startVal);

                // Update Progress Bar
                if(targetVal > 0) {
                    const progress = Math.min(100, Math.max(0, (startVal / targetVal) * 100));
                    livePreview.bar.style.width = `${progress}%`;
                } else {
                    livePreview.bar.style.width = '0%';
                }
            }

            // Bind Live Preview Events
            createInputs.name.addEventListener('input', updateLivePreview);
            createInputs.target.addEventListener('input', updateLivePreview);
            createInputs.start.addEventListener('input', updateLivePreview);
            createInputs.iconRadios.forEach(radio => radio.addEventListener('change', updateLivePreview));

            // --- Add Money Modal Logic ---
            
            // Input Live Feedback
            addMoneyElements.input.addEventListener('input', updateAddLivePreview);

            // Presets
            addMoneyElements.presets.forEach(btn => {
                btn.addEventListener('click', () => {
                   const amount = btn.dataset.amount;
                   addMoneyElements.input.value = amount;
                   addMoneyElements.presets.forEach(b => b.classList.remove('active'));
                   btn.classList.add('active');
                   updateAddLivePreview();
                   addMoneyElements.input.focus();
                });
            });

            function updateAddLivePreview() {
                const addVal = parseFloat(addMoneyElements.input.value) || 0;
                const currentSaved = parseFloat(addMoneyElements.currentSaved.value) || 0;
                const target = parseFloat(addMoneyElements.currentTarget.value) || 1; // avoid div/0
                
                let newTotal = currentSaved + addVal;
                // Cap at target for calculation if we want, or just let it show >100? 
                // Req: "Cap at 100%"
                if(newTotal > target) newTotal = target;

                // Calculate exact percentages
                const oldPercentRaw = (currentSaved / target) * 100;
                const newPercentRaw = (newTotal / target) * 100;

                // Clamp to 0-100
                const oldPercentClamped = Math.min(100, Math.max(0, oldPercentRaw));
                const newPercentClamped = Math.min(100, Math.max(0, newPercentRaw));
                
                // Diff calculation
                const diff = newPercentClamped - oldPercentClamped;
                
                const remaining = Math.max(0, target - newTotal);

                // Display Formatting (1 decimal place standard)
                const dispNewPercent = newPercentClamped.toFixed(1);
                
                // Update Bars
                addMoneyElements.ctxBar.style.width = `${oldPercentClamped}%`;
                addMoneyElements.ctxBarPreview.style.width = `${newPercentClamped}%`;
                
                // Feedback Text
                if (addVal > 0) {
                    addMoneyElements.feedbackBox.classList.remove('hidden');
                    
                    // Main Feedback: "This will bring you to 26.1%"
                    // Or "This moves you ahead by +0.15%"
                    // Let's go with "This will bring you to X%" as the primary, 
                    // and maybe show the Diff too?
                    // User Request "Replace static percentage text with dynamic precision-aware text".
                    // Examples: "This will bring you to 26.1%", "This moves you ahead by +0.15%"
                    
                    // Logic: If completed, show special message. 
                    if (newPercentClamped >= 100) {
                        addMoneyElements.predictPercent.textContent = "100%";
                        addMoneyElements.feedbackBox.querySelector('.feedback-main').innerHTML = `🎉 This completes your goal!`;
                        addMoneyElements.predictRemaining.textContent = window.CURRENCY_SYMBOL + "0";
                        addMoneyElements.submitBtn.textContent = "Complete Goal";
                    } else {
                        addMoneyElements.predictPercent.textContent = `${dispNewPercent}%`;
                        
                        // Show "Moves you ahead by +X%" if relevant? 
                        // Let's stick to the requested "This will bring you to..." structure but add diff context if needed?
                        // Actually, the user GAVE examples of what it COULD be. 
                        // "This will bring you to <strong id="...">26.1%</strong>" is what the HTML has.
                        // So I will update the text content of that strong tag.
                        
                        // Handle standard interaction
                        addMoneyElements.feedbackBox.querySelector('.feedback-main').innerHTML = `This will bring you to <strong>${dispNewPercent}%</strong>`;
                        
                        // If progress is very small but positive (<0.1%), we might want to ensure user sees something.
                        // But toFixed(1) handles 0.05 -> 0.1 usually. 
                        // If actual diff is > 0 but < 0.05, it might show 0.0 change if we just look at total.
                        // Let's check diff.
                        if (addVal > 0 && diff > 0 && diff < 0.05) {
                             // It might not move the needle on the absolute scale visually much,
                             // but we can acknowledge it. 
                             // However, "This will bring you to 26.0%" when you were at 26.0% is confusing. 
                             // So if dispNewPercent === oldPercentClamped.toFixed(1), force update?
                             if(dispNewPercent === oldPercentClamped.toFixed(1)) {
                                 // Force show next increment? Or show 2 decimals?
                                 // "26.05% (optional if precision allows)"
                                 addMoneyElements.feedbackBox.querySelector('.feedback-main').innerHTML = `This will bring you to <strong>${newPercentClamped.toFixed(2)}%</strong>`;
                             }
                        }

                        addMoneyElements.predictRemaining.textContent = moneyFmt.format(remaining);
                        addMoneyElements.submitBtn.textContent = `Add ${moneyFmt.format(addVal)} to Goal`;
                    }
                    
                    addMoneyElements.submitBtn.disabled = false;
                } else {
                    addMoneyElements.feedbackBox.classList.add('hidden');
                    addMoneyElements.submitBtn.textContent = 'Add Funds';
                    addMoneyElements.submitBtn.disabled = true;
                    // Reset preview bar to current
                    addMoneyElements.ctxBarPreview.style.width = `${oldPercentClamped}%`;
                }
            }

            // --- Modal Logic ---
            function openModal(modal) {
                modal.classList.add('active');
                if(modal === createModal) {
                    updateLivePreview();
                    createInputs.name.focus();
                } else {
                    // Add Money modal focus
                    const firstInput = modal.querySelector('input:not([type="hidden"])');
                    if(firstInput) firstInput.focus();
                }
            }

            function openEditModal(data) {
                // Set ID
                document.getElementById('editGoalId').value = data.id.toString();
                
                // Set Fields
                createInputs.name.value = data.name;
                createInputs.target.value = data.target;
                if(data.deadline) document.getElementById('deadline').value = data.deadline;
                
                // Set Icon
                const iconInput = document.querySelector(`input[name="goalIcon"][value="${data.icon}"]`);
                if(iconInput) iconInput.checked = true;

                // Hide Starting Balance (confusing during edit)
                document.getElementById('startingAmount').closest('.goal-input-group').style.display = 'none';

                // UI Updates
                document.getElementById('createModalTitle').textContent = 'Edit Goal';
                document.getElementById('createModalTitleMobile').textContent = 'Edit Goal';
                document.getElementById('createGoalForm').querySelector('button[type="submit"]').textContent = 'Save Changes';
                
                openModal(createModal);
            }

            function closeModal(modal) {
                modal.classList.remove('active');
                const form = modal.querySelector('form');
                if(form) {
                    // Slight delay to reset so UI doesn't jump while fading out
                    setTimeout(() => {
                        form.reset();
                        if(modal === createModal) {
                            // Reset Edit Mode
                            document.getElementById('editGoalId').value = '';
                            document.getElementById('createModalTitle').textContent = 'Create New Goal';
                            document.getElementById('createModalTitleMobile').textContent = 'Create New Goal';
                            document.getElementById('createGoalForm').querySelector('button[type="submit"]').textContent = 'Start Saving';
                            document.getElementById('startingAmount').closest('.goal-input-group').style.display = 'block';
                            updateLivePreview();
                        }
                    }, 200);
                }
            }

            // Create Goal Events
            newGoalBtn.addEventListener('click', () => openModal(createModal));
            closeCreateBtns.forEach(btn => btn.addEventListener('click', () => closeModal(createModal)));
            cancelCreateBtn.addEventListener('click', () => closeModal(createModal));
            
            // Add Money Events
            closeAddMoneyBtn.addEventListener('click', () => closeModal(addMoneyModal));
            cancelAddMoneyBtn.addEventListener('click', () => closeModal(addMoneyModal));

            // Close on outside click
            window.addEventListener('click', (e) => {
                if (e.target === createModal) closeModal(createModal);
                if (e.target === addMoneyModal) closeModal(addMoneyModal);
            });
            
            // Escape key
            window.addEventListener('keydown', (e) => {
                if(e.key === 'Escape') {
                    closeModal(createModal);
                    closeModal(addMoneyModal);
                }
            });

            // --- Form Submissions ---
            
            document.getElementById('createGoalForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const iconVal = document.querySelector('input[name="goalIcon"]:checked').value;
                const editId = document.getElementById('editGoalId').value;
                
                const formData = {
                    action: editId ? 'update' : 'create',
                    goal_name: document.getElementById('goalName').value,
                    target_amount: document.getElementById('targetAmount').value,
                    deadline: document.getElementById('deadline').value,
                    icon: iconVal
                };

                if(editId) {
                    formData.goal_id = editId;
                } else {
                    formData.saved_amount = document.getElementById('startingAmount').value || 0;
                }

                try {
                    const res = await fetch('php/api_goals.php', {
                        method: 'POST',
                        body: JSON.stringify(formData)
                    });
                    const data = await res.json();
                    if(data.status === 'success') {
                        closeModal(createModal);
                        fetchGoals();
                        showToast('Goal created successfully!', 'success');
                    } else {
                        showToast(data.message || 'Error creating goal', 'error');
                    }
                } catch(err) {
                    console.error(err);
                    showToast('Connection failed', 'error');
                }
            });

            document.getElementById('addMoneyForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = {
                    action: 'add_funds',
                    goal_id: document.getElementById('addMoneyGoalId').value,
                    amount: document.getElementById('addAmount').value
                };

                // Optimistic UI could be done here, but let's wait for server
                addMoneyElements.submitBtn.disabled = true;
                addMoneyElements.submitBtn.textContent = 'Adding...';

                try {
                    const res = await fetch('php/api_goals.php', {
                        method: 'POST',
                        body: JSON.stringify(formData)
                    });
                    const data = await res.json();
                    if(data.status === 'success') {
                        // Show Success State
                        const modalContent = addMoneyElements.modal.querySelector('.modal-content');
                        modalContent.classList.add('success-mode');
                        addMoneyElements.successName.textContent = addMoneyElements.title.textContent.replace('Add to ', '');
                        
                        // Wait then Close
                        setTimeout(() => {
                            closeModal(addMoneyModal);
                            fetchGoals(); 
                            // Toast is optional now since we showed explicit success, but keeps history
                            // showToast('Funds added successfully!', 'success'); 
                        }, 1500);
                    } else {
                        showToast(data.message || 'Error adding funds', 'error');
                        addMoneyElements.submitBtn.disabled = false;
                        updateAddLivePreview(); // Reset button text
                    }
                } catch(err) {
                    console.error(err);
                    showToast('Connection failed', 'error');
                    addMoneyElements.submitBtn.disabled = false;
                    updateAddLivePreview();
                }
            });

            // --- Rendering ---

            async function fetchGoals() {
                try {
                    const res = await fetch('php/api_goals.php?action=fetch_all');
                    const json = await res.json();
                    if(json.status === 'success') {
                        renderGoals(json.data);
                    } else {
                        goalsGrid.innerHTML = '<div class="empty-state">Error loading goals.</div>';
                    }
                } catch(err) {
                    goalsGrid.innerHTML = '<div class="empty-state">Failed to connect.</div>';
                }
            }

            function renderGoals(goals) {
                if(!goals || goals.length === 0) {
                    goalsGrid.innerHTML = `
                        <div class="empty-state">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                            <h3>No savings goals yet</h3>
                            <p>Create your first goal to start tracking progress.</p>
                        </div>
                    `;
                    return;
                }

                goalsGrid.innerHTML = goals.map(goal => generateGoalCard(goal)).join('');
                
                // Attach event listeners to dynamic buttons
                document.querySelectorAll('.add-funds-trigger').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const data = {
                            id: btn.dataset.id,
                            name: btn.dataset.name,
                            target: btn.dataset.target,
                            saved: btn.dataset.saved,
                            icon: btn.dataset.icon
                        };
                        openAddMoneyModal(data);
                    });
                });

                document.querySelectorAll('.edit-goal-trigger').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        // Close dropdown if needed or just let logic handle it. 
                        // Note: Dropdown details might need manual closing if not standardized.
                        e.target.closest('details').removeAttribute('open');
                        
                        const data = {
                            id: btn.dataset.id,
                            name: btn.dataset.name,
                            target: btn.dataset.target,
                            deadline: btn.dataset.deadline,
                            icon: btn.dataset.icon
                        };
                        openEditModal(data);
                    });
                });

                document.querySelectorAll('.delete-goal-trigger').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation(); // prevent interfering with other clicks
                        const id = btn.dataset.id;
                        if(confirm('Are you sure you want to delete this goal?')) {
                            deleteGoal(id);
                        }
                    });
                });
            }

            // Icons Map for Goal Types
            const goalIcons = {
                'target': '<path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z"/><path d="M12 6a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0 10a4 4 0 1 1 4-4 4 4 0 0 1-4 4z"/>',
                'travel': '<path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/>',
                'gadget': '<rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
                'home': '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
                'car': '<path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H5.24a2 2 0 0 0-1.8 1.1l-.8 1.63A6 6 0 0 0 2 12a6 6 0 0 0 6 6h12a6 6 0 0 0 6-6c0 .09 0 .19-.05.28ZM9 22a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>',
                'emergency': '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
                'gift': '<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
                'education': '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>'
            };

            function generateGoalCard(goal) {
                const target = parseFloat(goal.target_amount);
                const saved = parseFloat(goal.saved_amount);
                const percent = Math.min(100, Math.max(0, (saved / target) * 100));
                
                // Visual State
                let state = 'neutral';
                if(percent > 40) state = 'positive';
                if(percent > 75) state = 'almost';
                if(percent >= 100) state = 'success';

                const isCompleted = percent >= 100;
                
                // Mini Ring Props
                const radius = 24; 
                const circumference = 2 * Math.PI * radius;
                const offset = circumference - (percent / 100) * circumference;

                // Icons
                const iconSvgContent = goalIcons[goal.icon] || goalIcons['target'];
                const iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">${iconSvgContent}</svg>`;

                const fmt = new Intl.NumberFormat('en-IN', { style: 'currency', currency: window.CURRENCY_CODE || 'INR', maximumFractionDigits: 0 });
                const displayPercent = percent.toFixed(1).replace(/\.0$/, '');
                const remaining = Math.max(0, target - saved);

                return `
                <div class="goal-card ${isCompleted ? 'completed' : ''}" data-state="${state}">
                    <div class="goal-header">
                        <div class="goal-header-left">
                            <div class="goal-icon">${iconSvg}</div>
                            <div class="goal-title" title="${goal.goal_name}">${goal.goal_name}</div>
                        </div>
                        <details class="goal-menu-dropdown">
                            <summary class="goal-menu-btn" aria-label="Goal options">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                            </summary>
                            <div class="goal-menu-content">
                                <button class="menu-item edit-goal-trigger" 
                                    data-id="${goal.goal_id}"
                                    data-name="${goal.goal_name}"
                                    data-target="${goal.target_amount}"
                                    data-deadline="${goal.deadline}"
                                    data-icon="${goal.icon}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                    Edit
                                </button>
                                <button class="menu-item delete-item delete-goal-trigger" data-id="${goal.goal_id}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    Delete
                                </button>
                            </div>
                        </details>
                    </div>
                    
                    <div class="goal-body">
                        <div class="goal-amounts">
                            <div class="saved-amount">${fmt.format(saved)}</div>
                            <div class="target-amount">of ${fmt.format(target)}</div>
                        </div>
                        <div class="mini-progress-ring">
                            <svg class="mini-progress-svg" viewBox="0 0 56 56">
                                <circle class="mini-ring-bg" cx="28" cy="28" r="${radius}" />
                                <circle class="mini-ring-fill" cx="28" cy="28" r="${radius}" 
                                    style="stroke-dasharray: ${circumference}; stroke-dashoffset: ${offset};" />
                            </svg>
                            <div class="mini-progress-text">${displayPercent}%</div>
                        </div>
                    </div>

                    <div class="goal-footer">
                        <div class="remaining-group">
                             <div class="remaining-label">${isCompleted ? 'Goal Reached' : 'Remaining'}</div>
                             ${!isCompleted ? `<div class="remaining-value">${fmt.format(remaining)}</div>` : ''}
                        </div>
                        ${!isCompleted ? `
                            <button class="action-btn-sm add-funds-trigger" 
                                data-id="${goal.goal_id}" 
                                data-name="${goal.goal_name}"
                                data-target="${goal.target_amount}"
                                data-saved="${goal.saved_amount}"
                                data-icon="${goal.icon}">
                                Add Funds
                            </button>
                        ` : `
                             <div class="completed-text">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Done
                             </div>
                        `}
                    </div>
                </div>
                `;
            }

            function openAddMoneyModal(data) {
                // Populate Identity
                addMoneyElements.goalId.value = data.id;
                addMoneyElements.title.textContent = `Add to ${data.name}`;
                
                // Icon
                const iconSvgContent = goalIcons[data.icon] || goalIcons['target'];
                addMoneyElements.icon.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24">${iconSvgContent}</svg>`;

                // Populate Hidden Logic Data
                addMoneyElements.currentSaved.value = data.saved;
                addMoneyElements.currentTarget.value = data.target;

                // Populate Context Card
                const saved = parseFloat(data.saved);
                const target = parseFloat(data.target);
                const percent = Math.min(100, Math.max(0, (saved / target) * 100));
                const remaining = Math.max(0, target - saved);

                addMoneyElements.ctxTarget.textContent = moneyFmt.format(target);
                addMoneyElements.ctxSaved.textContent = moneyFmt.format(saved);
                addMoneyElements.ctxRemaining.textContent = moneyFmt.format(remaining);
                
                // Set initial bar width with precision
                addMoneyElements.ctxBar.style.width = `${percent}%`;
                addMoneyElements.ctxBarPreview.style.width = `${percent}%`; 

                // Reset Form
                addMoneyElements.input.value = '';
                addMoneyElements.feedbackBox.classList.add('hidden');
                addMoneyElements.submitBtn.textContent = 'Add Funds';
                addMoneyElements.submitBtn.disabled = true;
                addMoneyElements.presets.forEach(p => p.classList.remove('active'));
                
                // Reset Success Mode
                const modalParams = addMoneyElements.modal.querySelector('.modal-content');
                if(modalParams) modalParams.classList.remove('success-mode');

                openModal(addMoneyElements.modal);
                // slight delay to ensure modal visibility before focus
                setTimeout(() => addMoneyElements.input.focus(), 50);
            }

            async function deleteGoal(id) {
                try {
                     const res = await fetch('php/api_goals.php', {
                        method: 'POST',
                        body: JSON.stringify({ action: 'delete', goal_id: id })
                    });
                    const data = await res.json();
                    if(data.status === 'success') {
                        fetchGoals();
                        showToast('Goal deleted', 'success');
                    } else {
                        showToast('Error deleting goal', 'error');
                    }
                } catch(e) {
                     showToast('Connection error', 'error');
                }
            }

            function showToast(message, type = 'success') {
                const container = document.getElementById('toastContainer');
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.innerHTML = `
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        ${type === 'success' ? '<polyline points="20 6 9 17 4 12"></polyline>' : '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>'}
                    </svg>
                    <span>${message}</span>
                `;
                
                container.appendChild(toast);
                
                // Trigger reflow for animation
                requestAnimationFrame(() => {
                    toast.classList.add('show');
                });

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>
