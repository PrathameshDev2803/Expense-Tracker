<?php
include 'php/auth_check.php';
include_once 'php/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions</title>
    <link rel="stylesheet" href="css/main.css">
    <script>
    // Apply density preference immediately to prevent layout shift
    (function() {
      const density = localStorage.getItem('navbarDensity');
      if (density === 'compact') document.documentElement.classList.add('compact-mode');
      const theme = localStorage.getItem('theme');
      if (theme === 'dark') document.documentElement.classList.add('dark-mode');
    })();
    </script>
    <style>
        :root {
            /* --- THEME TOKENS (Light Mode Default) --- */
            --background-body: #f3f4f6;
            --background-surface: #ffffff;
            --brand-primary: #3b82f6;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --border-color-light: #f9fafb;
            --sub-danger: #ef4444;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
        }

        html.dark-mode {
            /* --- THEME TOKENS (Dark Mode Overrides) --- */
            --background-body: #0f172a;
            --background-surface: #1e293b;
            --brand-primary: #60a5fa;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --border-color-light: #475569;
        }

        /* Subscription Specific Mappings */
        body {
            background-color: var(--background-body);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .sub-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .sub-title h1 {
            color: var(--text-primary);
            margin: 0;
            font-size: 1.8rem;
        }
        
        .sub-title p {
            color: var(--text-secondary);
            margin: 5px 0 0 0;
            font-size: 0.9rem;
        }

        .btn-back {
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .btn-back:hover {
            color: var(--brand-primary);
        }

        /* Summary Cards */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: var(--background-surface);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .metric-label {
            color: var(--text-secondary);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .metric-value {
            color: var(--text-primary);
            font-size: 1.8rem;
            font-weight: 700;
        }

        .metric-sub {
            font-size: 0.8rem;
            color: var(--brand-primary);
            margin-top: 4px;
        }

        /* Controls */
        .controls-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .controls-bar h2 {
             color: var(--text-primary) !important;
             margin:0; 
             font-size:1.4rem;
        }

        .btn-scan {
            background: var(--brand-primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-scan:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-scan:disabled {
            opacity: 0.7;
            cursor: wait;
        }

        /* Subscriptions Grid */
        .subs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .sub-card {
            background: var(--background-surface);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .sub-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border-color: var(--brand-primary);
        }

        .sub-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }

        .merchant-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
            text-transform: capitalize;
        }

        .billing-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 20px;
            background: color-mix(in srgb, var(--brand-primary) 10%, transparent);
            color: var(--brand-primary);
            font-weight: 600;
            text-transform: uppercase;
        }

        .sub-amount {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .sub-period {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .sub-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 20px;
            padding: 12px;
            background: var(--background-body);
            border-radius: 8px;
        }

        .sub-actions {
            display: flex;
            gap: 12px;
        }

        .btn-action {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.2s;
        }

        .btn-cancel-sub {
            background: rgba(239, 68, 68, 0.1);
            color: var(--sub-danger);
        }
        
        .btn-cancel-sub:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        .btn-ignore {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        
        .btn-ignore:hover {
            background: var(--border-color);
            color: var(--text-primary);
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px;
            background: var(--background-surface);
            border-radius: 16px;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        
        .empty-state h3 {
             color: var(--text-primary);
        }

        /* Status Badges */
        .status-badge {
            position: absolute;
            top: 24px;
            right: 24px;
        }

        .sub-card.canceled {
            opacity: 0.6;
            filter: grayscale(0.8);
        }
        
        /* Spinner */
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }
        
        .btn-scan.loading .spinner {
            display: inline-block;
        }
        
        .btn-scan.loading span {
            opacity: 0.8;
        }

        @keyframes spin { to { transform: rotate(360deg); } }


    </style>
</head>
<body>

<div class="container">
    <?php include 'php/header.php'; ?>
    
    <style>
        /* --- REDESIGN #1: INSIGHT-FIRST DASHBOARD --- */
        
        .sub-header-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
        }

        .sub-header-title h1 {
            font-size: 1.8rem;
            color: var(--text-primary);
            margin: 0 0 8px 0;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .sub-header-title p {
            color: var(--text-secondary);
            font-size: 1rem;
            margin: 0;
        }

        .header-actions {
             display: flex;
             gap: 12px;
        }

        .btn-secondary-action {
             background: transparent;
             border: 1px solid var(--border-color);
             color: var(--text-primary);
             padding: 8px 16px;
             border-radius: 8px;
             font-weight: 600;
             cursor: pointer;
             transition: all 0.2s;
        }
        .btn-secondary-action:hover {
            background: var(--background-surface);
            border-color: var(--text-secondary);
        }

        /* METRIC CARDS GRID */
        .metrics-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr; /* Priority width for monthly */
            gap: 24px;
            margin-bottom: 48px;
        }

        .insight-card {
            background: var(--background-surface);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 160px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .insight-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        
        .insight-card:focus-within {
            outline: 2px solid var(--brand-primary);
            outline-offset: 2px;
        }

        /* Card 1: Monthly (Primary) */
        .card-primary {
            background: linear-gradient(145deg, var(--background-surface) 0%, color-mix(in srgb, var(--brand-primary) 5%, transparent) 100%);
            border-color: color-mix(in srgb, var(--brand-primary) 20%, transparent);
        }

        .card-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .card-value {
            font-size: 2.4rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-variant-numeric: tabular-nums;
        }

        .card-micro {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }
        
        .micro-highlight {
            color: var(--brand-primary);
            font-weight: 600;
        }
        
        .micro-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: #fef3c7; 
            color: #92400e;
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 12px;
        }

        /* Card 2 & 3: Secondary */
        .card-secondary {
            border-top: 4px solid var(--border-color); /* Visual distinction */
        }
        
        .card-secondary.accent-amber { border-top-color: #f59e0b; }
        .card-secondary.accent-blue { border-top-color: var(--brand-primary); }

        .card-cta-link {
            margin-top: auto;
            color: var(--brand-primary);
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .card-cta-link:hover { text-decoration: underline; }

        /* RESPONSIVE */
        @media (max-width: 960px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            .card-primary {
                 min-height: auto;
            }
        }
    </style>
    
    <div class="sub-header-wrapper">
        <div class="sub-header-title">
            <h1>Subscription Overview</h1>
            <p>Track recurring charges, spot waste, and take control.</p>
        </div>
        <div class="header-actions">
            <!-- Review button is decorative for now -->
            <button class="btn-secondary-action" onclick="document.getElementById('subsGrid').scrollIntoView({behavior:'smooth'})">
                Review list &darr;
            </button>
        </div>
    </div>

    <!-- INSIGHTS GRID -->
    <div class="metrics-grid">
        
        <!-- 1. MONTHLY SPEND (PRIMARY) -->
        <div class="insight-card card-primary" tabindex="0">
            <div>
                <div class="card-label">Monthly Subscription Spend</div>
                <div class="card-value" id="cardMonthly"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0.00</div>
                <div class="card-micro">That's <span class="micro-highlight" id="cardYearlyContext"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span> per year if unchanged.</div>
            </div>
            
            <div class="micro-badge" id="unusedBadge" style="display:none;">
                <span style="width:6px; height:6px; background:currentColor; border-radius:50%;"></span>
                2 subscriptions potentially unused
            </div>
        </div>

        <!-- 2. YEARLY IMPACT -->
        <div class="insight-card card-secondary accent-amber" tabindex="0">
             <div>
                <div class="card-label">Projected Annual Cost</div>
                <div class="card-value" style="font-size: 1.8rem;" id="cardYearly"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0.00</div>
                <div class="card-micro">Equivalent to ≈ <span style="font-weight:600">1 month of rent</span>.</div>
             </div>
        </div>

        <!-- 3. ACTIVE COUNT -->
        <div class="insight-card card-secondary accent-blue" tabindex="0">
            <div>
                <div class="card-label">Active Subscriptions</div>
                <div class="card-value" style="font-size: 1.8rem;" id="cardCount">0</div>
                <div class="card-micro" id="cardRenewals">0 renewed recently</div>
            </div>
            <a href="#subsGrid" class="card-cta-link">View list &rarr;</a>
        </div>

    </div>

    <div class="controls-bar">
        <h2 style="color:var(--text-primary); margin:0; font-size:1.4rem;">All Subscriptions</h2>
        <button class="btn-scan" id="btnScan" onclick="rescan()">
            <div class="spinner"></div>
            <span>Scan for New Subscriptions</span>
        </button>
    </div>

    <div class="subs-grid" id="subsGrid">
        <!-- Rendered via JS -->
    </div>

<script>
    // Utils
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: window.CURRENCY_CODE || 'INR',
            maximumFractionDigits: 0
        }).format(amount);
    };

    const formatDate = (dateStr) => {
        if(!dateStr) return 'N/A';
        return new Date(dateStr).toLocaleDateString(undefined, {
            year: 'numeric', month: 'short', day: 'numeric'
        });
    };

    // State
    let isScanning = false;

    // Actions
    async function loadSubscriptions() {
        const grid = document.getElementById('subsGrid');
        
        try {
            const res = await fetch('php/get_subscriptions.php');
            const json = await res.json();
            
            if (!json.success) throw new Error(json.error);
            
            // --- INSIGHTS LOGIC ---
            const monthly = json.summary.total_monthly_cost;
            const yearly = json.summary.total_yearly_cost;
            const active = json.summary.active_count;
            
            document.getElementById('cardMonthly').textContent = formatCurrency(monthly);
            document.getElementById('cardYearlyContext').textContent = formatCurrency(yearly);
            document.getElementById('cardYearly').textContent = formatCurrency(yearly);
            document.getElementById('cardCount').textContent = active;

            // Mock Logic: Active renewal count
            // In real app: filter activeSubs where last_charged < 30 days ago
            const recentRenewals = Math.floor(Math.random() * (active / 2));
            document.getElementById('cardRenewals').textContent = `${recentRenewals} renewed in last 30 days`;
            
            // Mock Logic: Unused badge
            const showUnused = Math.random() > 0.5;
            if (active > 0 && showUnused) {
                 const badge = document.getElementById('unusedBadge');
                 badge.style.display = 'inline-flex';
                 badge.innerHTML = `<span style="width:6px; height:6px; background:currentColor; border-radius:50%;"></span> ${Math.ceil(active * 0.2)} subscriptions potentially unused`;
            }

            // --- RENDER CARD GRID ---
            grid.innerHTML = '';
            
            const allSubs = [];
            Object.keys(json.data).forEach(cycle => {
                json.data[cycle].forEach(sub => allSubs.push(sub));
            });

            if (allSubs.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <h3>No subscriptions detected yet.</h3>
                        <p>Click "Scan" to analyze your transaction history.</p>
                    </div>
                `;
                return;
            }

            allSubs.forEach(sub => {
                const card = document.createElement('div');
                card.className = `sub-card ${sub.status}`;
                if(sub.status !== 'active') card.style.opacity = '0.5';

                card.innerHTML = `
                    <div class="sub-card-header">
                        <div class="merchant-name">${sub.merchant_name}</div>
                        <div class="billing-badge">${sub.billing_cycle}</div>
                    </div>
                    
                    <div class="sub-amount">${formatCurrency(sub.average_amount)}</div>
                    <div class="sub-period">per ${sub.billing_cycle === 'monthly' ? 'month' : 'cycle'}</div>
                    
                    <div class="sub-meta">
                        <div>Last Charged: ${formatDate(sub.last_charged_date)}</div>
                        <div>Status: <span style="text-transform:capitalize; color: ${sub.status === 'active' ? '#10b981' : '#ef4444'}">${sub.status}</span></div>
                    </div>

                    ${sub.status === 'active' ? `
                    <div class="sub-actions">
                        <button class="btn-action btn-cancel-sub" onclick="updateStatus(${sub.id}, 'canceled')">
                            Cancel Sub
                        </button>
                        <button class="btn-action btn-ignore" onclick="updateStatus(${sub.id}, 'ignored')">
                            Ignore
                        </button>
                    </div>
                    ` : `
                    <div class="sub-actions">
                         <button class="btn-action btn-ignore" onclick="updateStatus(${sub.id}, 'active')">
                            Reactivate
                        </button>
                    </div>
                    `}
                `;
                grid.appendChild(card);
            });

        } catch (err) {
            console.error(err);
            grid.innerHTML = `<p style="color:red">Error loading data: ${err.message}</p>`;
        }
    }

    async function rescan() {
        if (isScanning) return;
        const btn = document.getElementById('btnScan');
        
        isScanning = true;
        btn.classList.add('loading');
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Scanning...';

        try {
            const res = await fetch('php/rescan_subscriptions.php', { method: 'POST' });
            const json = await res.json();
            
            if (json.success) {
                // Refresh
                await loadSubscriptions();
                // success feedback?
            } else {
                alert('Scan failed: ' + json.error);
            }
        } catch (err) {
            console.error(err);
            alert('Error connecting to server.');
        } finally {
            isScanning = false;
            btn.classList.remove('loading');
            btn.disabled = false;
            btn.querySelector('span').textContent = 'Scan for New Subscriptions';
        }
    }

    async function updateStatus(id, status) {
        if (status === 'canceled' && !confirm('Are you sure you want to mark this as canceled? It will remain in history.')) return;
        if (status === 'ignored' && !confirm('This will hide this subscription from stats. Continue?')) return;

        try {
            const res = await fetch('php/update_subscription_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
            const json = await res.json();
            
            if (json.success) {
                loadSubscriptions(); // Reload UI
            } else {
                alert('Error: ' + json.error);
            }
        } catch (err) {
            alert('Request failed');
        }
    }

    // Init
    loadSubscriptions();
</script>
