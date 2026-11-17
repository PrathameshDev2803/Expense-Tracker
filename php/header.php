<?php
// Ensure session is started (usually handled by the including file, but checking doesn't hurt)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/asset_helper.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Include Header CSS -->
<link rel="stylesheet" href="<?= asset('css/header.css') ?>">

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
        // --- Global Currency Configuration & Helpers ---
        <?php
        if (!function_exists('get_currencies_config')) {
            require_once __DIR__ . '/auth_check.php';
        }
        require_once __DIR__ . '/currency_service.php';
        $currency_cfg_json = json_encode(get_currencies_config());
        $exchangeRatesData = CurrencyService::getExchangeRates(EXPENSE_TRACKER_BASE_CURRENCY);
        ?>
        window.BASE_CURRENCY = '<?= EXPENSE_TRACKER_BASE_CURRENCY ?>';
        window.CURRENCIES = <?php echo $currency_cfg_json; ?>;
        window.CURRENCY_SYMBOL = "<?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>";
        window.CURRENCY_CODE = "<?php echo $_SESSION['currency'] ?? 'INR'; ?>";
        window.EXCHANGE_RATES = <?php echo json_encode($exchangeRatesData['rates'] ?? ['INR'=>1, 'USD'=>0.010643, 'EUR'=>0.009675, 'GBP'=>0.008072]); ?>;
        window.EXCHANGE_RATES_META = <?php echo json_encode([
            'base' => $exchangeRatesData['base'] ?? 'INR',
            'last_updated' => $exchangeRatesData['last_updated'] ?? date('Y-m-d H:i:s'),
            'source' => $exchangeRatesData['source'] ?? 'open.er-api.com',
            'is_cached' => $exchangeRatesData['is_cached'] ?? false
        ]); ?>;

        window.getExchangeRates = function(base = window.BASE_CURRENCY) {
            return window.EXCHANGE_RATES || { 'INR': 1, 'USD': 0.010643, 'EUR': 0.009675, 'GBP': 0.008072 };
        };

        window.getCurrencySymbol = function(code) {
            code = (code || window.CURRENCY_CODE || 'INR').toUpperCase();
            return (window.CURRENCIES && window.CURRENCIES[code]) ? window.CURRENCIES[code].symbol : '₹';
        };

        window.getActiveDisplayCurrency = function() {
            return window.CURRENCY_CODE || 'INR';
        };

        window.convertCurrency = function(amount, fromCurrency = window.BASE_CURRENCY, toCurrency = window.CURRENCY_CODE) {
            const num = parseFloat(amount);
            if (isNaN(num)) return 0;
            fromCurrency = (fromCurrency || window.BASE_CURRENCY || 'INR').toUpperCase();
            toCurrency = (toCurrency || window.CURRENCY_CODE || 'INR').toUpperCase();
            if (fromCurrency === toCurrency) return num;

            const rates = window.getExchangeRates(window.BASE_CURRENCY);
            const base = window.BASE_CURRENCY || 'INR';

            if (fromCurrency === base) {
                const rate = rates[toCurrency] || 1;
                return num * rate;
            }
            if (toCurrency === base) {
                const rate = rates[fromCurrency] || 1;
                return rate > 0 ? (num / rate) : num;
            }

            // Cross-conversion via base
            const rateFrom = rates[fromCurrency] || 1;
            const rateTo = rates[toCurrency] || 1;
            const inr = rateFrom > 0 ? (num / rateFrom) : num;
            return inr * rateTo;
        };

        window.formatCurrency = function(amount, showSymbol = true, customDecimals = null, alreadyConverted = false) {
            let num = parseFloat(amount);
            if (isNaN(num)) num = 0;

            if (!alreadyConverted && window.CURRENCY_CODE !== window.BASE_CURRENCY) {
                num = window.convertCurrency(num, window.BASE_CURRENCY, window.CURRENCY_CODE);
            }

            const config = (window.CURRENCIES && window.CURRENCIES[window.CURRENCY_CODE]) 
                ? window.CURRENCIES[window.CURRENCY_CODE] 
                : { code: 'INR', symbol: '₹', locale: 'en-IN', decimals: 2 };
            const decimals = customDecimals !== null ? customDecimals : (config.decimals ?? 2);

            try {
                if (showSymbol) {
                    return new Intl.NumberFormat(config.locale, {
                        style: 'currency',
                        currency: config.code,
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    }).format(num);
                } else {
                    return new Intl.NumberFormat(config.locale, {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    }).format(num);
                }
            } catch (e) {
                return (showSymbol ? (window.CURRENCY_SYMBOL || config.symbol) : '') + num.toLocaleString(config.locale || 'en-IN', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }
        };

        window.updateDisplayCurrency = function(currencyCode, newRates = null) {
            currencyCode = (currencyCode || 'INR').toUpperCase();
            window.CURRENCY_CODE = currencyCode;
            window.CURRENCY_SYMBOL = window.getCurrencySymbol(currencyCode);
            if (newRates) {
                window.EXCHANGE_RATES = newRates;
            }
            window.dispatchEvent(new CustomEvent('currencychange', {
                detail: {
                    currency: currencyCode,
                    symbol: window.CURRENCY_SYMBOL,
                    rates: window.EXCHANGE_RATES
                }
            }));
        };
        
        // Clean up any legacy dark-mode or compact-mode state
        document.documentElement.classList.remove('compact-mode', 'dark-mode');
        localStorage.removeItem('navbarDensity');
        localStorage.removeItem('theme');

        const userMenuTrigger = document.getElementById('userMenuTrigger');
        const userDropdown = document.getElementById('userDropdown');

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
    })();
</script>
