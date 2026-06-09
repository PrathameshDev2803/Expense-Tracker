<?php
include 'php/auth_check.php';
include_once 'php/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, created_at, currency, theme, notification_prefs FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Default values if null
if (!$user_data['currency']) $user_data['currency'] = 'INR';
if (!$user_data['theme']) $user_data['theme'] = 'light';
$notifs = json_decode($user_data['notification_prefs'] ?? '{}', true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | ExpenseTracker</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>
    <?php include 'php/header.php'; ?>
    
    <main class="settings-container">
        <header class="settings-page-header">
            <h1>Account Settings</h1>
            <p>Manage your profile, preferences, and security settings.</p>
        </header>

        <div class="settings-layout">
            <!-- Left Sidebar -->
            <aside class="settings-sidebar">
                <nav class="settings-nav">
                    <button class="nav-item active" data-target="profile">
                        <span class="nav-icon">👤</span> Profile
                    </button>
                    <button class="nav-item" data-target="security">
                        <span class="nav-icon">🔒</span> Security
                    </button>
                    <button class="nav-item" data-target="preferences">
                        <span class="nav-icon">⚙️</span> Preferences
                    </button>
                    <button class="nav-item" data-target="appearance">
                        <span class="nav-icon">🎨</span> Appearance
                    </button>
                    <button class="nav-item" data-target="notifications">
                        <span class="nav-icon">🔔</span> Notifications
                    </button>
                </nav>
            </aside>

            <!-- Right Content Panel -->
            <section class="settings-content">
                
                <!-- Feedback Messages -->
                <div id="settingsFeedback" class="settings-feedback" role="alert" aria-live="polite"></div>

                <!-- PROFILE SECTION -->
                <div id="profile" class="settings-panel active">
                    <h2>Profile Information</h2>
                    <p class="panel-desc">Update your personal details here.</p>
                    
                    <form id="profileForm" class="settings-form">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly disabled class="read-only-input">
                                <span class="badge verified">Verified</span>
                            </div>
                            <small>Email cannot be changed directly for security reasons.</small>
                        </div>
                        <div class="form-group">
                            <label>Account Created</label>
                            <input type="text" value="<?php echo date('F j, Y', strtotime($user_data['created_at'])); ?>" readonly disabled class="read-only-input">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-primary" disabled>Save Changes</button>
                        </div>
                    </form>
                </div>

                <!-- SECURITY SECTION -->
                <div id="security" class="settings-panel">
                    <h2>Security</h2>
                    <p class="panel-desc">Manage your password and account security.</p>
                    
                    <form id="securityForm" class="settings-form">
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="current_password" required autocomplete="current-password">
                        </div>
                        
                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="new_password" required autocomplete="new-password">
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="confirm_password" required autocomplete="new-password">
                        </div>

                        <div class="form-check">
                            <input type="checkbox" id="showPasswordToggle">
                            <label for="showPasswordToggle">Show Passwords</label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary" disabled>Update Password</button>
                        </div>
                    </form>
                </div>

                <!-- PREFERENCES SECTION -->
                <div id="preferences" class="settings-panel">
                    <h2>Preferences</h2>
                    <p class="panel-desc">Customize your regional and functional defaults.</p>
                    
                    <form id="preferencesForm" class="settings-form">
                        <div class="form-group">
                            <label for="currencySelector">Default Currency</label>
                            <select id="currencySelector" name="currency">
                                <option value="INR" <?php echo $user_data['currency'] === 'INR' ? 'selected' : ''; ?>>₹ INR (Indian Rupee)</option>
                                <option value="USD" <?php echo $user_data['currency'] === 'USD' ? 'selected' : ''; ?>>$ USD (US Dollar)</option>
                                <option value="EUR" <?php echo $user_data['currency'] === 'EUR' ? 'selected' : ''; ?>>€ EUR (Euro)</option>
                                <option value="GBP" <?php echo $user_data['currency'] === 'GBP' ? 'selected' : ''; ?>>£ GBP (British Pound)</option>
                            </select>
                            <small>This will update all currency displays across the app.</small>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-primary" disabled>Save Preferences</button>
                        </div>
                    </form>
                </div>

                <!-- APPEARANCE SECTION -->
                <div id="appearance" class="settings-panel">
                    <h2>Appearance</h2>
                    <p class="panel-desc">Choose how the application looks to you.</p>
                    
                    <div class="theme-options">
                        <label class="theme-card">
                            <input type="radio" name="theme" value="light" <?php echo $user_data['theme'] === 'light' ? 'checked' : ''; ?>>
                            <div class="theme-visual light-preview"></div>
                            <span>Light Mode</span>
                        </label>
                        <label class="theme-card">
                            <input type="radio" name="theme" value="dark" <?php echo $user_data['theme'] === 'dark' ? 'checked' : ''; ?>>
                            <div class="theme-visual dark-preview"></div>
                            <span>Dark Mode</span>
                        </label>
                         <!-- System Default could be added if JS handles it -->
                    </div>
                </div>

                <!-- NOTIFICATIONS SECTION -->
                <div id="notifications" class="settings-panel">
                    <h2>Notifications</h2>
                    <p class="panel-desc">Control what alerts you receive.</p>

                    <div class="toggles-list">
                        <div class="toggle-item">
                            <div class="toggle-info">
                                <span class="toggle-label">Budget Alerts</span>
                                <span class="toggle-desc">Get notified when you approach your spending limits.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="notif_budget" <?php echo ($notifs['budget'] ?? true) ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="toggle-item">
                            <div class="toggle-info">
                                <span class="toggle-label">Monthly Summary</span>
                                <span class="toggle-desc">Receive a monthly report of your finances.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="notif_monthly" <?php echo ($notifs['monthly'] ?? true) ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="toggle-item">
                            <div class="toggle-info">
                                <span class="toggle-label">Goal Progress</span>
                                <span class="toggle-desc">Updates on your savings goals achievements.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="notif_goals" <?php echo ($notifs['goals'] ?? true) ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="toggle-item">
                            <div class="toggle-info">
                                <span class="toggle-label">System Announcements</span>
                                <span class="toggle-desc">Important updates and feature announcements.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="notif_system" <?php echo ($notifs['system'] ?? true) ? 'checked' : ''; ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </main>

    <script src="js/settings.js"></script>
</body>
</html>
