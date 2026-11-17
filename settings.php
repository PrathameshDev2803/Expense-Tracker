<?php
include 'php/auth_check.php';
include_once 'php/db.php';
require_once __DIR__ . '/php/asset_helper.php';

$user_id = $_SESSION['user_id'];

// Self-healing: Ensure user preference columns exist
try {
    $colsNeeded = [
        'currency' => "ALTER TABLE users ADD COLUMN currency VARCHAR(10) DEFAULT 'INR'",
        'theme' => "ALTER TABLE users ADD COLUMN theme VARCHAR(20) DEFAULT 'light'",
        'remember_token' => 'ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) NULL',
        'notification_prefs' => 'ALTER TABLE users ADD COLUMN notification_prefs TEXT NULL DEFAULT NULL',
    ];
    foreach ($colsNeeded as $col => $sql) {
        $c = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
        if ($c && $c->num_rows === 0) {
            $conn->query($sql);
        }
    }
} catch (Throwable $e) {
}

$stmt = $conn->prepare('SELECT id, username, email, created_at, currency, theme, notification_prefs FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user_data) {
    header('Location: login.php');
    exit;
}

if (empty($user_data['currency']))
    $user_data['currency'] = 'INR';
if (empty($user_data['theme']))
    $user_data['theme'] = 'light';
$notifs = json_decode($user_data['notification_prefs'] ?? '{}', true) ?: [];

$user_initial = strtoupper(substr($user_data['username'] ?? 'U', 0, 1));
$created_date = !empty($user_data['created_at']) ? date('M j, Y', strtotime($user_data['created_at'])) : date('M j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | ExpenseTracker</title>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/settings.css') ?>">
</head>
<body>
    <?php include 'php/header.php'; ?>
    
    <div class="settings-page-wrapper">
        <div class="settings-layout-grid">
            
            <!-- LEFT SETTINGS SIDEBAR -->
            <aside class="settings-sidebar-card" aria-label="Settings navigation">
                <!-- Sidebar Header -->
                <div class="sidebar-header">
                    <div class="settings-header-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                    <div class="sidebar-header-text">
                        <h2>Settings</h2>
                        <p>Manage your account, preferences, and app settings.</p>
                    </div>
                </div>

                <!-- Navigation Groups -->
                <nav class="settings-nav-groups" role="tablist">
                    <!-- Group 1: Account -->
                    <div class="nav-group">
                        <span class="group-title">Account</span>
                        <button type="button" class="settings-nav-item active" data-tab="profile" role="tab" aria-selected="true" aria-controls="panel-profile" id="tab-profile">
                            <svg class="nav-item-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Profile</span>
                        </button>
                        <button type="button" class="settings-nav-item" data-tab="security" role="tab" aria-selected="false" aria-controls="panel-security" id="tab-security">
                            <svg class="nav-item-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <span>Security</span>
                        </button>
                    </div>

                    <!-- Group 2: App & Preferences -->
                    <div class="nav-group">
                        <span class="group-title">App & Preferences</span>
                        <button type="button" class="settings-nav-item" data-tab="currency" role="tab" aria-selected="false" aria-controls="panel-currency" id="tab-currency">
                            <svg class="nav-item-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="8"></circle>
                                <line x1="12" y1="2" x2="12" y2="4"></line>
                                <line x1="12" y1="20" x2="12" y2="22"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                            <span>Currency & Format</span>
                        </button>
                        <button type="button" class="settings-nav-item" data-tab="privacy" role="tab" aria-selected="false" aria-controls="panel-privacy" id="tab-privacy">
                            <svg class="nav-item-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Data & Privacy</span>
                        </button>
                    </div>

                    <!-- Group 3: About -->
                    <div class="nav-group">
                        <span class="group-title">About</span>
                        <button type="button" class="settings-nav-item" data-tab="help" role="tab" aria-selected="false" aria-controls="panel-help" id="tab-help">
                            <svg class="nav-item-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <span>Help & Support</span>
                        </button>
                        <button type="button" class="settings-nav-item" data-tab="about" role="tab" aria-selected="false" aria-controls="panel-about" id="tab-about">
                            <svg class="nav-item-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span>About ExpenseTracker</span>
                        </button>
                    </div>
                </nav>
            </aside>

            <!-- RIGHT SETTINGS CONTENT AREA -->
            <main class="settings-content-area">

                <!-- Toast Feedback Notification -->
                <div id="settingsToast" class="settings-toast-alert" role="status" aria-live="polite"></div>

                <!-- SECTION 1: PROFILE TAB -->
                <section class="settings-tab-panel active" id="panel-profile" role="tabpanel" aria-labelledby="tab-profile">
                    
                    <!-- Page Header -->
                    <div class="tab-page-header">
                        <div class="header-titles">
                            <h1>Profile</h1>
                            <p>View and update your personal information.</p>
                        </div>
                        <button type="button" class="btn-ghost-action" id="btnViewProfile" title="View profile summary">
                            <span>View Profile</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <line x1="10" y1="14" x2="21" y2="3"></line>
                            </svg>
                        </button>
                    </div>

                    <!-- Profile Overview Card -->
                    <div class="profile-overview-card">
                        <div class="overview-avatar-wrapper">
                            <div class="profile-avatar-circle" id="profileAvatarDisplay" aria-label="Profile avatar">
                                <span class="avatar-letter" id="avatarInitialText"><?= htmlspecialchars($user_initial) ?></span>
                            </div>
                        </div>

                        <div class="overview-details">
                            <h2 class="overview-username" id="overviewUsername"><?= htmlspecialchars($user_data['username']) ?></h2>
                            <p class="overview-email"><?= htmlspecialchars($user_data['email']) ?></p>
                        </div>
                    </div>

                    <!-- Profile Information Card -->
                    <div class="content-panel-card">
                        <div class="card-section-header">
                            <div class="section-header-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="section-header-text">
                                <h3>Profile Information</h3>
                                <p>Update your personal details and account information.</p>
                            </div>
                        </div>

                        <form id="profileForm" class="settings-form" novalidate autocomplete="off">
                            <div class="form-grid-2col">
                                <!-- Field 1: Full Name (Editable) -->
                                <div class="form-field-group">
                                    <label for="fullName">Full Name</label>
                                    <div class="input-container">
                                        <input type="text" id="fullName" name="username" 
                                               value="<?= htmlspecialchars($user_data['username']) ?>" 
                                               data-original="<?= htmlspecialchars($user_data['username']) ?>"
                                               required 
                                               maxlength="50"
                                               autocomplete="name" 
                                               placeholder="Your full name">
                                    </div>
                                    <span class="field-error-msg" id="nameError" role="alert"></span>
                                </div>

                                <!-- Field 2: Email Address (Read-only) -->
                                <div class="form-field-group">
                                    <label for="emailDisplay">Email Address</label>
                                    <div class="input-container">
                                        <input type="email" id="emailDisplay" value="<?= htmlspecialchars($user_data['email']) ?>" readonly disabled class="input-readonly">
                                    </div>
                                    <small class="field-help-text">Email cannot be changed directly for security reasons.</small>
                                </div>

                                <!-- Field 3: Username (Read-only + Copy) -->
                                <div class="form-field-group">
                                    <label for="usernameDisplay">Username</label>
                                    <div class="input-container input-with-copy-btn">
                                        <input type="text" id="usernameDisplay" value="<?= htmlspecialchars($user_data['username']) ?>" readonly class="input-readonly">
                                        <button type="button" class="btn-input-copy" id="copyUsernameBtn" title="Copy username to clipboard" aria-label="Copy username to clipboard">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Field 4: Account Created (Read-only) -->
                                <div class="form-field-group">
                                    <label for="createdDisplay">Account Created</label>
                                    <div class="input-container input-with-leading-icon">
                                        <svg class="leading-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <input type="text" id="createdDisplay" value="<?= htmlspecialchars($created_date) ?>" readonly disabled class="input-readonly">
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions-toolbar">
                                <button type="submit" class="btn-save-primary" id="btnSaveProfile" disabled>
                                    <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span class="btn-text">Save Changes</span>
                                </button>
                                <button type="button" class="btn-reset-outline" id="btnResetProfile" disabled>
                                    <svg class="btn-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                        <path d="M3 3v5h5"></path>
                                    </svg>
                                    <span class="btn-text">Reset</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- SECTION 2: SECURITY TAB -->
                <section class="settings-tab-panel" id="panel-security" role="tabpanel" aria-labelledby="tab-security">
                    <div class="tab-page-header">
                        <div class="header-titles">
                            <h1>Security</h1>
                            <p>Manage your password and enhance account safety.</p>
                        </div>
                    </div>

                    <!-- Security Two-Column Card -->
                    <div class="security-card-wrapper">
                        <div class="security-card-grid">
                            
                            <!-- Left Section: Guidance Panel -->
                            <div class="security-guidance-panel">
                                <div class="guidance-illustration-container">
                                    <div class="security-shield-graphic">
                                        <svg width="116" height="116" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 12px 24px rgba(49, 92, 245, 0.22));">
                                            <defs>
                                                <linearGradient id="shieldLeft" x1="17" y1="18" x2="60" y2="102" gradientUnits="userSpaceOnUse">
                                                    <stop offset="0%" stop-color="#3B82F6"/>
                                                    <stop offset="100%" stop-color="#2563EB"/>
                                                </linearGradient>
                                                <linearGradient id="shieldRight" x1="60" y1="18" x2="103" y2="102" gradientUnits="userSpaceOnUse">
                                                    <stop offset="0%" stop-color="#2563EB"/>
                                                    <stop offset="100%" stop-color="#1D4ED8"/>
                                                </linearGradient>
                                                <filter id="shieldGlow" x="0" y="0" width="120" height="120" filterUnits="userSpaceOnUse">
                                                    <feGaussianBlur stdDeviation="9" result="blur"/>
                                                </filter>
                                            </defs>
                                            <!-- Soft Ambient Halo Glow -->
                                            <ellipse cx="60" cy="58" rx="44" ry="40" fill="#93C5FD" opacity="0.45" filter="url(#shieldGlow)"/>
                                            
                                            <!-- Accent Sparkle Dashes (Top Right) -->
                                            <line x1="84" y1="16" x2="90" y2="11" stroke="#38BDF8" stroke-width="3" stroke-linecap="round"/>
                                            <line x1="73" y1="9" x2="75" y2="3" stroke="#38BDF8" stroke-width="3" stroke-linecap="round"/>
                                            <line x1="93" y1="27" x2="99" y2="27" stroke="#38BDF8" stroke-width="3" stroke-linecap="round"/>

                                            <!-- Left Shield Half -->
                                            <path d="M 60 18 C 43 18, 25 23.5, 19 28 C 17.5 29.5, 16.5 32, 16.5 35 L 16.5 60 C 16.5 80.5, 42.5 97, 60 102 Z" fill="url(#shieldLeft)"/>
                                            
                                            <!-- Right Shield Half -->
                                            <path d="M 60 18 C 77 18, 95 23.5, 101 28 C 102.5 29.5, 103.5 32, 103.5 35 L 103.5 60 C 103.5 80.5, 77.5 97, 60 102 Z" fill="url(#shieldRight)"/>
                                            
                                            <!-- Subtle Center Vertical Highlight -->
                                            <line x1="60" y1="18" x2="60" y2="102" stroke="rgba(255,255,255,0.18)" stroke-width="1.5"/>

                                            <!-- Inner Subtle Border for crispness -->
                                            <path d="M 60 21 C 44 21, 28 26, 22 30 C 20.5 31.5, 19.5 33.5, 19.5 36 L 19.5 59 C 19.5 77, 43 92.5, 60 97.5 C 77 92.5, 100.5 77, 100.5 59 L 100.5 36 C 100.5 33.5, 99.5 31.5, 98 30 C 92 26, 76 21, 60 21 Z" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="1.5"/>

                                            <!-- Lock Shackle -->
                                            <path d="M 52 50 V 41 C 52 36.5, 55.5 33, 60 33 C 64.5 33, 68 36.5, 68 41 V 50" fill="none" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round"/>
                                            
                                            <!-- Lock Body -->
                                            <rect x="46" y="49" width="28" height="23" rx="5" fill="#FFFFFF"/>
                                            
                                            <!-- Keyhole -->
                                            <circle cx="60" cy="58" r="2.8" fill="#2563EB"/>
                                            <path d="M 58.7 59 L 58.2 64 H 61.8 L 61.3 59 Z" fill="#2563EB"/>
                                        </svg>
                                    </div>
                                </div>

                                <div class="guidance-text-content">
                                    <h3>Change Password</h3>
                                    <p>Ensure your account uses a strong, unique password.</p>
                                </div>

                                <ul class="guidance-rules-list">
                                    <li class="guidance-rule-item">
                                        <span class="rule-icon-badge" aria-hidden="true">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </span>
                                        <span class="rule-text">Use at least 8 characters</span>
                                    </li>
                                    <li class="guidance-rule-item">
                                        <span class="rule-icon-badge font-badge" aria-hidden="true">Aa</span>
                                        <span class="rule-text">Include uppercase & lowercase letters</span>
                                    </li>
                                    <li class="guidance-rule-item">
                                        <span class="rule-icon-badge font-badge" aria-hidden="true">123</span>
                                        <span class="rule-text">Add a number and a special character (e.g. ! @ # $ %)</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Right Section: Change Password Form -->
                            <div class="security-form-panel">
                                <div class="security-form-header">
                                    <div class="form-header-badge" aria-hidden="true">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                    </div>
                                    <div class="form-header-titles">
                                        <h2>Change Password</h2>
                                        <p>Update your password to keep your account secure.</p>
                                    </div>
                                </div>

                                <form id="securityForm" class="security-password-form" novalidate autocomplete="off">
                                    <!-- Field 1: Current Password -->
                                    <div class="security-field-group">
                                        <label for="currentPassword">Current Password</label>
                                        <div class="password-input-wrapper">
                                            <span class="leading-lock-icon" aria-hidden="true">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                </svg>
                                            </span>
                                            <input type="password" id="currentPassword" name="current_password" required autocomplete="current-password" placeholder="Enter current password">
                                            <button type="button" class="btn-toggle-eye" data-target="currentPassword" aria-label="Toggle current password visibility" title="Toggle password visibility">
                                                <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path class="eye-off-slash" d="M1 1l22 22"></path>
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <span class="field-error-msg" id="currentPasswordError" role="alert"></span>
                                    </div>

                                    <!-- Field 2: New Password -->
                                    <div class="security-field-group">
                                        <label for="newPassword">New Password</label>
                                        <div class="password-input-wrapper">
                                            <span class="leading-lock-icon" aria-hidden="true">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                </svg>
                                            </span>
                                            <input type="password" id="newPassword" name="new_password" required autocomplete="new-password" placeholder="Enter new password">
                                            <button type="button" class="btn-toggle-eye" data-target="newPassword" aria-label="Toggle new password visibility" title="Toggle password visibility">
                                                <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path class="eye-off-slash" d="M1 1l22 22"></path>
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- 4-Segment Strength Indicator -->
                                        <div class="password-strength-container" aria-live="polite">
                                            <div class="strength-bars-group" aria-hidden="true">
                                                <div class="strength-segment" id="strengthBar1"></div>
                                                <div class="strength-segment" id="strengthBar2"></div>
                                                <div class="strength-segment" id="strengthBar3"></div>
                                                <div class="strength-segment" id="strengthBar4"></div>
                                            </div>
                                            <span class="strength-label-text">Password strength: <strong id="strengthLabelText">&mdash;</strong></span>
                                        </div>
                                        <span class="field-error-msg" id="newPasswordError" role="alert"></span>
                                    </div>

                                    <!-- Field 3: Confirm New Password -->
                                    <div class="security-field-group">
                                        <label for="confirmPassword">Confirm New Password</label>
                                        <div class="password-input-wrapper">
                                            <span class="leading-lock-icon" aria-hidden="true">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                </svg>
                                            </span>
                                            <input type="password" id="confirmPassword" name="confirm_password" required autocomplete="new-password" placeholder="Confirm new password">
                                            <button type="button" class="btn-toggle-eye" data-target="confirmPassword" aria-label="Toggle confirm password visibility" title="Toggle password visibility">
                                                <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path class="eye-off-slash" d="M1 1l22 22"></path>
                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <span class="field-error-msg" id="confirmPasswordError" role="alert"></span>
                                    </div>

                                    <!-- Checkbox: Show Passwords -->
                                    <div class="security-checkbox-row">
                                        <label class="custom-checkbox-label">
                                            <input type="checkbox" id="showAllPasswords">
                                            <span class="checkbox-caption">Show passwords</span>
                                        </label>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="security-form-actions">
                                        <button type="submit" class="btn-save-primary" id="btnUpdatePassword">
                                            <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            <span class="btn-text">Update Password</span>
                                        </button>
                                        <button type="button" class="btn-reset-outline" id="btnResetSecurity">
                                            <svg class="btn-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                                <path d="M3 3v5h5"></path>
                                            </svg>
                                            <span class="btn-text">Reset</span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </section>

                <!-- SECTION 3: CURRENCY & FORMAT TAB -->
                <section class="settings-tab-panel" id="panel-currency" role="tabpanel" aria-labelledby="tab-currency">
                    <div class="tab-page-header">
                        <div class="header-titles">
                            <h1>Currency & Format</h1>
                            <p>Set standard currency symbols and localization formats.</p>
                        </div>
                    </div>

                    <div class="content-panel-card">
                        <div class="card-section-header">
                            <div class="section-header-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="8"></circle>
                                    <line x1="12" y1="2" x2="12" y2="4"></line>
                                    <line x1="12" y1="20" x2="12" y2="22"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <div class="section-header-text">
                                <h3>Default Currency</h3>
                                <p>This symbol is applied across all dashboard balances and charts.</p>
                            </div>
                        </div>

                        <form id="preferencesForm" class="settings-form">
                            <div class="form-field-group" style="max-width: 400px;">
                                <label for="currencySelector">Active Currency</label>
                                <select id="currencySelector" name="currency">
                                    <option value="INR" <?= ($user_data['currency'] === 'INR') ? 'selected' : '' ?>>₹ INR (Indian Rupee)</option>
                                    <option value="USD" <?= ($user_data['currency'] === 'USD') ? 'selected' : '' ?>>$ USD (US Dollar)</option>
                                    <option value="EUR" <?= ($user_data['currency'] === 'EUR') ? 'selected' : '' ?>>€ EUR (Euro)</option>
                                    <option value="GBP" <?= ($user_data['currency'] === 'GBP') ? 'selected' : '' ?>>£ GBP (British Pound)</option>
                                </select>
                            </div>
                            <div class="form-actions-toolbar">
                                <button type="submit" class="btn-save-primary" id="btnSaveCurrency" disabled>
                                    <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span>Save Currency</span>
                                </button>
                            </div>
                        </form>

                        <!-- Live Exchange Rate Information Box -->
                        <?php
                        require_once 'php/currency_service.php';
                        $ratesInfo = CurrencyService::getExchangeRates(EXPENSE_TRACKER_BASE_CURRENCY);
                        $activeCurr = $user_data['currency'] ?: 'INR';
                        $activeRate = CurrencyService::getRate(EXPENSE_TRACKER_BASE_CURRENCY, $activeCurr);
                        $formattedRate = ($activeCurr === 'INR') ? '1.00 INR' : number_format($activeRate, 6) . ' ' . $activeCurr;
                        $lastUpdatedFormatted = !empty($ratesInfo['last_updated']) ? date('M d, Y h:i A', strtotime($ratesInfo['last_updated'])) : date('M d, Y h:i A');
                        ?>
                        <div class="currency-conversion-info-card" id="currencyInfoBox">
                            <div class="conversion-info-head">
                                <div class="info-head-title">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                    <span>Exchange Rate Information</span>
                                </div>
                                <span class="badge-status-pill <?= !empty($ratesInfo['is_cached']) ? 'cached' : 'live' ?>" id="rateStatusPill">
                                    <?= !empty($ratesInfo['is_cached']) ? 'Cached' : 'Live' ?>
                                </span>
                            </div>
                            <div class="conversion-details-grid">
                                <div class="detail-cell">
                                    <span class="detail-label">Display Currency</span>
                                    <strong class="detail-value" id="dispCurrencyVal"><?= htmlspecialchars($activeCurr) ?> (<?= CurrencyService::getCurrencySymbol($activeCurr) ?>)</strong>
                                </div>
                                <div class="detail-cell">
                                    <span class="detail-label">Base Currency</span>
                                    <strong class="detail-value">INR (₹)</strong>
                                </div>
                                <div class="detail-cell">
                                    <span class="detail-label">Exchange Rate</span>
                                    <strong class="detail-value" id="dispExchangeRate">1 INR = <?= $formattedRate ?></strong>
                                </div>
                                <div class="detail-cell">
                                    <span class="detail-label">Last Updated</span>
                                    <strong class="detail-value" id="dispLastUpdated"><?= $lastUpdatedFormatted ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 6: DATA & PRIVACY TAB -->
                <section class="settings-tab-panel" id="panel-privacy" role="tabpanel" aria-labelledby="tab-privacy">
                    <div class="tab-page-header">
                        <div class="header-titles">
                            <h1>Data & Privacy</h1>
                            <p>Download your records, inspect privacy controls, or wipe data.</p>
                        </div>
                    </div>

                    <div class="content-panel-card">
                        <div class="card-section-header">
                            <div class="section-header-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                            </div>
                            <div class="section-header-text">
                                <h3>Data Portability</h3>
                                <p>Export all transactions, goals, and recurring subscription data.</p>
                            </div>
                        </div>

                        <div class="action-card-box">
                            <div class="action-copy">
                                <h4>Export Financial Data</h4>
                                <p>Download your complete transaction ledger in standard CSV format for spreadsheets or tax filing.</p>
                            </div>
                            <a href="main.php" class="btn-secondary-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                <span>Go to Dashboard Export</span>
                            </a>
                        </div>
                    </div>
                </section>

                <!-- SECTION 7: HELP & SUPPORT TAB -->
                <section class="settings-tab-panel" id="panel-help" role="tabpanel" aria-labelledby="tab-help">
                    <div class="tab-page-header">
                        <div class="header-titles">
                            <h1>Help & Support</h1>
                            <p>Frequently asked questions, usage guides, and support.</p>
                        </div>
                    </div>

                    <div class="content-panel-card">
                        <div class="faq-list">
                            <div class="faq-item">
                                <h4>How does subscription detection work?</h4>
                                <p>The app analyzes historical expense intervals and merchant descriptions. Expenses that occur at regular 7-day, 30-day, or 365-day intervals are recognized automatically.</p>
                            </div>
                            <div class="faq-item">
                                <h4>Can I change my registered email?</h4>
                                <p>Email changes require verification to safeguard financial logs and prevent account takeover. Contact support if an email update is needed.</p>
                            </div>
                            <div class="faq-item">
                                <h4>Where is my data stored?</h4>
                                <p>All data is hosted securely in an encrypted MySQL relational database with strict password hashing (BCrypt) and CSRF protection.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 8: ABOUT TAB -->
                <section class="settings-tab-panel" id="panel-about" role="tabpanel" aria-labelledby="tab-about">
                    <div class="tab-page-header">
                        <div class="header-titles">
                            <h1>About ExpenseTracker</h1>
                            <p>Application architecture and build details.</p>
                        </div>
                    </div>

                    <div class="content-panel-card">
                        <div class="about-grid">
                            <div class="about-stat">
                                <span class="stat-label">Application Version</span>
                                <span class="stat-value">v2.4.0 (2026 Edition)</span>
                            </div>
                            <div class="about-stat">
                                <span class="stat-label">Security Architecture</span>
                                <span class="stat-value">BCrypt, CSRF-tokenized, Session Guards</span>
                            </div>
                            <div class="about-stat">
                                <span class="stat-label">Stack</span>
                                <span class="stat-value">PHP 8.2+ / MySQLi / Chart.js</span>
                            </div>
                            <div class="about-stat">
                                <span class="stat-label">Developer</span>
                                <span class="stat-value">Prathamesh Dev</span>
                            </div>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <!-- View Profile Modal -->
    <div id="viewProfileModal" class="settings-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="viewProfileTitle" style="display:none;">
        <div class="settings-modal-card">
            <div class="modal-card-header">
                <h3 id="viewProfileTitle">Profile Summary</h3>
                <button type="button" class="btn-close-modal" id="btnCloseProfileModal" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-card-body">
                <div class="modal-profile-badge">
                    <div class="modal-avatar"><?= htmlspecialchars($user_initial) ?></div>
                    <div class="modal-user-info">
                        <h4 id="modalNameText"><?= htmlspecialchars($user_data['username']) ?></h4>
                        <p><?= htmlspecialchars($user_data['email']) ?></p>
                    </div>
                </div>
                <div class="modal-stats-list">
                    <div class="modal-stat-row">
                        <span>Member Since</span>
                        <strong><?= htmlspecialchars($created_date) ?></strong>
                    </div>
                    <div class="modal-stat-row">
                        <span>Default Currency</span>
                        <strong id="modalCurrencyText"><?= htmlspecialchars($user_data['currency']) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>";
    </script>
    <script src="<?= asset('js/settings.js') ?>"></script>
</body>
</html>
