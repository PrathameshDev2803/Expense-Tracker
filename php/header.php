<?php
// Ensure session is started (usually handled by the including file, but checking doesn't hurt)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Include Header CSS -->
<link rel="stylesheet" href="css/header.css">

<header class="app-header">
  <div class="header-left">
    <div class="logo-icon-new">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
      </svg>
    </div>
    <span class="app-name-new">ExpenseTracker</span>
  </div>

  <nav class="app-nav">
    <a href="main.php" class="nav-link <?php echo ($current_page == 'main.php') ? 'active' : ''; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
      Home
    </a>
    <a href="subscriptions.php" class="nav-link <?php echo ($current_page == 'subscriptions.php') ? 'active' : ''; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
      Subscriptions
    </a>

    <a href="goals.php" class="nav-link <?php echo ($current_page == 'goals.php') ? 'active' : ''; ?>">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
      Goals
    </a>
    <a href="#" class="nav-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
      Design
    </a>
  </nav>

  <div class="header-right">
    <div class="user-menu-container">
      <div class="user-menu-trigger" id="userMenuTrigger" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
        <div class="avatar-new"><?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?></div>
        <span class="username-new"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
        <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </div>
      <div class="user-dropdown" id="userDropdown">
        <div class="dropdown-item" id="densityItem">
          <span>Compact Mode</span>
          <label class="switch-sm"><input type="checkbox" id="densityToggle"><span class="slider-sm"></span></label>
        </div>
        <div class="dropdown-item" id="themeItem">
          <span>Dark Mode</span>
          <label class="switch-sm"><input type="checkbox" id="themeToggle"><span class="slider-sm"></span></label>
        </div>
        <a href="settings.php" class="dropdown-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
          </svg>
          Settings
        </a>
        <a href="subscriptions.php" class="dropdown-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
            <line x1="1" y1="10" x2="23" y2="10"></line>
          </svg>
          Subscriptions
        </a>
        <a href="goals.php" class="dropdown-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
            <circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle>
          </svg>
          Goals
        </a>
        <a href="menu.php" class="dropdown-item">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
            <path d="M12 20h9"></path>
            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
          </svg>
          Edit Categories
        </a>
        <div class="dropdown-divider"></div>
        <a href="logout.php" class="dropdown-item danger"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg> Logout</a>
      </div>
    </div>
  </div>
</header>
<script>
    // --- User Menu & Density Logic ---
    (function() {
        // --- Global Currency Symbol ---
        window.CURRENCY_SYMBOL = "<?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>";
        window.CURRENCY_CODE = "<?php echo $_SESSION['currency'] ?? 'INR'; ?>";
        
        const userMenuTrigger = document.getElementById('userMenuTrigger');
        const userDropdown = document.getElementById('userDropdown');
        const densityToggle = document.getElementById('densityToggle');
        const themeToggle = document.getElementById('themeToggle');

        // Toggle Dropdown (Click & Keyboard)
        function toggleUserMenu(e) {
            if (e.type === 'keydown' && !(e.key === 'Enter' || e.key === ' ')) return;
            if (e.type === 'keydown') e.preventDefault(); // Prevent scroll on Space
            
            e.stopPropagation();
            userDropdown.classList.toggle('show');
            
            if (userMenuTrigger && userDropdown) {
                const isExpanded = userDropdown.classList.contains('show');
                userMenuTrigger.setAttribute('aria-expanded', isExpanded);
            }
        }

        if (userMenuTrigger) {
            userMenuTrigger.addEventListener('click', toggleUserMenu);
            userMenuTrigger.addEventListener('keydown', toggleUserMenu);
        }

        // Close Dropdown on Click Outside or Escape
        document.addEventListener('click', (e) => {
            if (userDropdown && userMenuTrigger && !userDropdown.contains(e.target) && !userMenuTrigger.contains(e.target)) {
                userDropdown.classList.remove('show');
                userMenuTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && userDropdown && userDropdown.classList.contains('show')) {
                userDropdown.classList.remove('show');
                userMenuTrigger.setAttribute('aria-expanded', 'false');
                userMenuTrigger.focus();
            }
        });

        // Initialize Toggle States
        if (typeof densityToggle !== 'undefined' && densityToggle) {
            if (localStorage.getItem('navbarDensity') === 'compact') {
                densityToggle.checked = true;
            }
            densityToggle.addEventListener('change', (e) => {
                const isCompact = e.target.checked;
                document.documentElement.classList.toggle('compact-mode', isCompact);
                localStorage.setItem('navbarDensity', isCompact ? 'compact' : 'standard');
            });
        }

        if (typeof themeToggle !== 'undefined' && themeToggle) {
             if (localStorage.getItem('theme') === 'dark') {
                themeToggle.checked = true;
            }
            themeToggle.addEventListener('change', (e) => {
                const isDark = e.target.checked;
                document.documentElement.classList.toggle('dark-mode', isDark);
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }
    })();
</script>
