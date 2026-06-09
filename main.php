<?php
include 'php/auth_check.php';
include_once 'php/db.php';


$user_id = $_SESSION['user_id'];

// Security Check: Verify user actually exists in DB (Handles DB wipes)
$user_check = $conn->query("SELECT id FROM users WHERE id = '$user_id'");
if ($user_check->num_rows == 0) {
  session_unset();
  session_destroy();
  header("Location: login.php");
  exit();
}

// Check if user has completed setup (has expense genres saved)
$setup_check_exp = $conn->query("SELECT id FROM expense_genres WHERE user_id = '$user_id' LIMIT 1");
$setup_check_inc = $conn->query("SELECT id FROM income_genres WHERE user_id = '$user_id' LIMIT 1");

// Only redirect if BOTH tables are empty (user has no categories at all)
if (($setup_check_exp && $setup_check_exp->num_rows == 0) && ($setup_check_inc && $setup_check_inc->num_rows == 0)) {
  header("Location: menu.php");
  exit();
}

// Fetch categories for dropdowns to ensure they appear even if localStorage is empty
$income_cats = [];
$expense_cats = [];

// Fetch Income Categories
$q_inc = $conn->query("SELECT category_name FROM income_genres WHERE user_id = '$user_id'");
if ($q_inc) while ($row = $q_inc->fetch_assoc()) $income_cats[] = $row['category_name'];

// Fetch Expense Categories
$q_exp = $conn->query("SELECT category_name FROM expense_genres WHERE user_id = '$user_id'");
if ($q_exp) while ($row = $q_exp->fetch_assoc()) $expense_cats[] = $row['category_name'];

// --- ANALYTICS ENGINE ---
include 'php/get_analytics.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Expense Tracker Dashboard</title>
  <link rel="stylesheet" href="css/main.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/2.2.1/chartjs-plugin-annotation.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
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
      --focus-ring-color: rgba(99, 102, 241, 0.5);

      --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
      --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px -1px rgba(0, 0, 0, 0.3);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -2px rgba(0, 0, 0, 0.3);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -4px rgba(0, 0, 0, 0.3);
      --radius-sm: 0.25rem;
      --radius-md: 0.5rem;
      --radius-lg: 0.75rem;
      --radius-xl: 1rem;

      /* --- NAVBAR DENSITY TOKENS (Standard) --- */
      --nav-padding-y: 12px;
      --nav-padding-x: 24px;
      --nav-icon-size: 36px;
      --nav-font-size: 1rem;
      --nav-avatar-size: 32px;
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

    /* Compact Mode Overrides */
    html.compact-mode {
      --nav-padding-y: 8px;
      --nav-padding-x: 20px;
      --nav-icon-size: 32px;
      --nav-font-size: 0.9rem;
      --nav-avatar-size: 28px;
    }

    /* Fine-tuning for Compact + Dark mode */
    html.dark-mode.compact-mode {
      --nav-padding-y: 10px;
      /* Slightly more vertical space than light-compact */
    }

    @media (max-width: 768px) {

      /* On mobile, density settings are ignored for a consistent touch experience */
      html.compact-mode,
      html.dark-mode.compact-mode {
        --nav-padding-y: 12px;
        --nav-padding-x: 24px;
        --nav-icon-size: 36px;
        --nav-font-size: 1rem;
        --nav-avatar-size: 32px;
      }
    }

    body {
      background-color: var(--background-body);
      color: var(--text-primary);
    }

    /* Enhanced Date Picker Styles */
    /* All Cards */
    .stat-card,
    .chart-card,
    .date-section,
    .form-section,
    .transaction-panel,
    .filter-panel {
      background: var(--background-surface);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-md);
      padding: 1.5rem;
      margin-bottom: 2rem;
      color: var(--text-primary);
    }

    .date-header h3,
    .stat-info h3,
    .balance-value,
    .stat-mini-value,
    .brand-text h1,
    .username-text,
    .chart-title,
    .form-section h2,
    .panel-title h2,
    .tx-category {
      color: var(--text-primary);
    }

    .stat-period,
    .balance-label,
    .stat-mini-label,
    .brand-text .subtitle,
    .greeting-text,
    .panel-title p,
    .tx-desc,
    .summary-label {
      color: var(--text-secondary);
    }

    .date-box,
    .nav-btn,
    .logout-btn {
      color: var(--text-secondary);
    }

    .date-section {
      background: transparent; /* Clean background */
      border: none;
      box-shadow: none;
      padding: 0;
      margin: 0 0 32px 0;
    }

    .date-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
      padding: 0 8px;
    }

    .date-header h3 {
      font-size: 0.85rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin: 0;
    }

    .calendar-trigger {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      color: #64748b;
      padding: 6px 12px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.8rem;
      font-weight: 500;
      transition: all 0.2s;
      box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .calendar-trigger:hover {
      background: #f8fafc;
      color: #334155;
      border-color: #cbd5e1;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      transform: none;
    }

    .date-strip-wrapper {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 20px; /* More rounded for modern feel */
      padding: 12px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); /* Gentle shadow */
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .nav-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%; /* Circular buttons */
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      color: #94a3b8;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
      flex-shrink: 0;
    }

    .nav-btn:hover:not(:disabled) {
      background: #e2e8f0;
      color: #1e293b;
      transform: scale(1.05);
      border-color: #cbd5e1;
    }

    .nav-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      background: #f1f5f9;
    }

    .scrollable-dates {
      gap: 8px;
      padding: 4px; /* Space for focus rings/shadows */
    }

    .date-box {
      min-width: 64px;
      height: 80px;
      background: transparent;
      border: 1px solid transparent;
      border-radius: 16px; /* Rounded corners */
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      cursor: pointer; 
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); /* Smooth transition */
      color: #64748b;
      position: relative;
    }

    .date-box:hover {
      background: #f8fafc;
      transform: translateY(-2px); /* Upward movement */
    }

    .date-box strong {
      font-size: 0.7rem; /* Small */
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 6px;
      color: #94a3b8; /* Muted */
      transition: color 0.2s;
    }

    .date-box .date-num {
      font-size: 1.25rem; /* Larger */
      font-weight: 700; /* Bold */
      color: #334155;
      font-variant-numeric: tabular-nums;
      transition: color 0.2s;
    }

    .date-box .month-label {
      display: none;
    }

    /* States */
    .date-box.past {
      opacity: 0.8;
    }

    .date-box.future {
      opacity: 0.5;
    }

    /* Active State - Light Theme Corporate Style */
    .date-box.active {
      background: #ffffff;
      border-color: var(--brand-primary);
      color: var(--brand-primary);
      box-shadow: 0 8px 16px -4px rgba(59, 130, 246, 0.15), 0 4px 6px -2px rgba(59, 130, 246, 0.1); /* Gentle colored shadow */
      transform: translateY(-2px);
      z-index: 2;
    }

    .date-box.active strong {
      color: var(--brand-primary);
      opacity: 0.8;
    }

    .date-box.active .date-num {
      color: var(--brand-primary);
    }

    /* Indicator Dot */
    .date-indicator {
      display: none; /* Removed in favor of cleaner look */
    }

    /* Today Indicator (Green Dot) */
    .date-box.today::after {
      content: '';
      position: absolute;
      bottom: 8px;
      width: 4px;
      height: 4px;
      border-radius: 50%;
      background: #10b981; /* Green dot for today */
    }

    .date-context-label {
      text-align: center;
      font-size: 0.85rem;
      color: #64748b;
      margin-top: 12px;
      font-weight: 500;
      opacity: 0;
      animation: fadeIn 0.4s ease forwards;
    }

    /* Stats Overview Redesign */
    .stats-overview {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
      margin: 32px 0;
    }

    /* Fintech Card Style */
    .stat-card {
      background: #ffffff;
      border: 1px solid #f1f5f9;
      border-radius: 16px;
      /* Slightly tighter radius for professional look */
      padding: 24px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
      /* Ultra-subtle base shadow */
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
      min-height: 180px;
    }

    .stat-card:hover {
      box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.06);
      transform: translateY(-2px);
      border-color: #e2e8f0;
    }

    /* Active/Tactical Card Highlight */
    .stat-card.primary {
      background: linear-gradient(to bottom right, #ffffff, #f8fafc);
      /* Very subtle gradient */
      border-color: #e2e8f0;
      position: relative;
    }

    /* subtle indicator for active card */
    .stat-card.primary::before {
      content: '';
      position: absolute;
      top: 24px;
      left: 0;
      width: 3px;
      height: 24px;
      background: #3b82f6;
      border-radius: 0 4px 4px 0;
      opacity: 0.8;
    }

    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .stat-label {
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #64748b;
    }

    .stat-icon-subtle {
      color: #cbd5e1;
      transition: color 0.2s;
    }

    .stat-card:hover .stat-icon-subtle {
      color: #94a3b8;
    }

    .balance-wrapper {
      margin-bottom: 20px;
    }

    .balance-value {
      font-size: 2rem;
      font-weight: 600;
      color: #0f172a;
      letter-spacing: -0.02em;
      line-height: 1.1;
      margin-bottom: 8px;
      font-variant-numeric: tabular-nums;
      font-feature-settings: "tnum";
    }

    .insight-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 0.75rem;
      font-weight: 500;
      padding: 2px 0;
      color: #64748b;
      letter-spacing: 0.01em;
    }

    .insight-badge.positive {
      color: #059669;
    }

    .insight-badge.negative {
      color: #dc2626;
    }

    .mini-stats-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      border-top: 1px solid #f1f5f9;
      padding-top: 16px;
    }

    .mini-stat-item {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .mini-label {
      font-size: 0.7rem;
      color: #64748b;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .mini-value {
      font-size: 0.95rem;
      font-weight: 600;
      color: #334155;
      font-variant-numeric: tabular-nums;
    }

    .mini-value.inc {
      color: #059669;
    }

    .mini-value.exp {
      color: #dc2626;
    }



    @media (max-width: 768px) {
      .context-subtitle {
        display: none;
      }

      .username-new {
        display: none;
      }

      .header-context {
        align-items: flex-start;
      }
    }

    /* Charts Grid System */
    .charts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
      margin: 32px 0;
    }

    .chart-card {
      background: var(--background-surface);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-xl);
      padding: 24px;
      position: relative;
      display: flex;
      flex-direction: column;
      box-shadow: var(--shadow-md);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .chart-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
      border-color: rgba(255, 255, 255, 0.2);
    }

    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .chart-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-primary);
      margin: 0;
      letter-spacing: -0.025em;
    }

    .chart-container {
      position: relative;
      flex: 1;
      min-height: 250px;
      width: 100%;
    }

    /* Form Section Redesign */
    .form-section {
      background: #ffffff;
      /* Clean white surface */
      border: 1px solid #f1f5f9;
      border-radius: 32px;
      /* Softer, more modern radius */
      padding: 40px;
      /* More breathing room */
      margin: 32px 0;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
      /* Soft, premium shadow */
      transition: all 0.3s ease;
    }

    .form-header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 16px;
    }

    .form-section h2 {
      margin: 0;
      font-size: 1.1rem;
      color: var(--text-primary);
      font-weight: 600;
      letter-spacing: -0.01em;
    }

    .form-title-enhanced {
      font-size: 1.5rem;
      font-weight: 700;
      background: linear-gradient(to right, #111827, #374151);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .entry-type-toggle {
      background: #f8fafc;
      padding: 4px;
      border-radius: 12px;
      display: flex;
      gap: 4px;
      border: 1px solid #e2e8f0;
    }

    .type-switch-btn {
      background: transparent;
      border: none;
      color: var(--text-secondary);
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .type-switch-btn svg {
      transition: transform 0.3s ease;
    }

    .type-switch-btn:hover {
      color: var(--text-primary);
    }

    .type-switch-btn.active {
      color: white;
      box-shadow: 0 4px 12px -2px rgba(59, 130, 246, 0.5);
    }

    .type-switch-btn.active svg {
      transform: scale(1.1);
    }

    .type-switch-btn[data-type="income"].active {
      background: #10b981;
      /* Emerald 500 */
      box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.3);
    }

    .type-switch-btn[data-type="expense"].active {
      background: #334155;
      /* Slate 700 - Neutral/Grounded */
      box-shadow: 0 4px 12px -2px rgba(51, 65, 85, 0.3);
    }

    .form-grid-enhanced {
      display: grid;
      grid-template-columns: 1.5fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
      align-items: start;
    }

    .form-group-enhanced {
      position: relative;
    }

    .full-width-enhanced {
      grid-column: 1 / -1;
    }

    .input-wrapper {
      position: relative;
      background: #ffffff;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .input-wrapper:hover {
      border-color: #cbd5e1;
      background: #f8fafc;
    }

    .input-wrapper:focus-within {
      background: #ffffff;
      border-color: #94a3b8;
      box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.1);
      /* Softer, wider ring */
    }

    .amount-wrapper {
      background: transparent;
      border-radius: 0;
      border: none;
      border-bottom: 2px solid #e2e8f0;
      /* Minimalist underline */
      display: flex;
      align-items: baseline;
      /* Align symbol with text baseline */
      padding: 0 0 10px 0;
      transition: all 0.2s;
      box-shadow: none;
      height: auto;
      margin-bottom: 10px;
    }

    .amount-wrapper:focus-within {
      background: transparent;
      box-shadow: none;
      border-bottom-color: #3b82f6;
      /* Highlight color */
    }

    /* Dynamic Focus Colors based on Mode */
    .form-section.mode-income .amount-wrapper:focus-within {
      border-bottom-color: #10b981;
    }

    .form-section.mode-expense .amount-wrapper:focus-within {
      border-bottom-color: #334155;
    }

    .input-wrapper input,
    .input-wrapper select {
      width: 100%;
      padding: 16px 40px 16px 16px;
      background: transparent;
      border: none;
      color: var(--text-primary);
      font-size: 1rem;
      outline: none;
      border-radius: 12px;
      appearance: none;
      cursor: pointer;
    }

    .input-wrapper input::placeholder {
      color: #94a3b8;
    }

    .amount-input-enhanced {
      width: 100%;
      height: 100%;
      background: transparent;
      border: none;
      color: #1e293b;
      font-size: 3.5rem;
      /* Hero Size */
      font-weight: 800;
      /* Extra Bold */
      letter-spacing: -2px;
      /* Tight tracking */
      outline: none;
      padding: 0;
      appearance: none;
      -webkit-appearance: none;
      font-family: inherit;
      caret-color: #3b82f6;
      /* Default caret */
    }

    .amount-input-enhanced:focus {
      outline: none;
      box-shadow: none;
      border: none;
    }

    /* Dynamic Text Colors */
    .form-section.mode-income .amount-input-enhanced {
      color: #059669;
      caret-color: #059669;
    }

    .form-section.mode-expense .amount-input-enhanced {
      color: #1e293b;
      caret-color: #334155;
    }

    .amount-input-enhanced::placeholder {
      color: #e2e8f0;
      /* Extremely subtle placeholder */
      font-weight: 600;
    }

    .input-wrapper select {
      cursor: pointer;
    }

    .currency-symbol-enhanced {
      font-size: 2rem;
      font-weight: 500;
      color: #94a3b8;
      margin-right: 4px;
      transform: translateY(-2px);
      /* Fine-tune baseline alignment */
    }

    .input-wrapper select option {
      background-color: #0f172a;
      color: var(--text-primary);
    }

    .input-icon {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      pointer-events: none;
    }

    #addEntry {
      width: 100%;
      padding: 18px;
      background: #3b82f6; /* Brand Blue Base */
      color: white;
      border: none;
      border-radius: 16px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      margin-top: 12px;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      letter-spacing: 0.5px;
      opacity: 1;
    }

    #addEntry:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      box-shadow: none;
      transform: none;
      background: #94a3b8 !important;
      /* Neutral disabled state */
    }

    #addEntry:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.15);
    }

    /* Dynamic Button Colors */
    .form-section.mode-income #addEntry {
      background: linear-gradient(135deg, #3b82f6 0%, #10b981 100%);
      box-shadow: 0 8px 20px -6px rgba(16, 185, 129, 0.4);
    }

    .form-section.mode-income #addEntry:hover {
      background: linear-gradient(135deg, #2563eb 0%, #059669 100%);
      box-shadow: 0 12px 24px -8px rgba(16, 185, 129, 0.5);
    }

    .form-section.mode-expense #addEntry {
      background: linear-gradient(135deg, #3b82f6 0%, #ef4444 100%);
      box-shadow: 0 8px 20px -6px rgba(239, 68, 68, 0.4);
    }

    .form-section.mode-expense #addEntry:hover {
      background: linear-gradient(135deg, #2563eb 0%, #dc2626 100%);
      box-shadow: 0 12px 24px -8px rgba(239, 68, 68, 0.5);
    }

    #addEntry:active {
      transform: translateY(0);
    }

    #addEntry span {
      transition: transform 0.2s ease;
    }

    #addEntry:hover span {
      transform: translateX(2px);
    }

    @media (max-width: 768px) {
      .form-grid-enhanced {
        grid-template-columns: 1fr;
      }
    }

    /* --- ENTERPRISE TRANSACTION TABLE DESIGN --- */
    .enterprise-panel {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
      margin: 40px auto;
      max-width: 1600px;
      width: 96%;
      overflow: hidden;
    }

    /* Header */
    .ent-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 32px;
      border-bottom: 1px solid #e5e7eb;
      background: #ffffff;
    }

    .ent-header h2 {
      margin: 0;
      font-size: 1.1rem;
      font-weight: 600;
      color: #111827;
    }

    .ent-header-actions {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .avatar-circle {
      width: 32px;
      height: 32px;
      background: var(--brand-primary);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.8rem;
      font-weight: 600;
      position: relative;
      flex-shrink: 0;
    }

    .btn-create-ent {
      background: #3b82f6;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 0.9rem;
      font-weight: 500;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      margin: 0;
    }

    .btn-create-ent:hover {
      background: #2563eb;
    }

    /* Filters Bar */
    .ent-filters {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 32px;
      flex-wrap: wrap;
      gap: 16px;
      background: #ffffff;
    }

    .ent-tabs {
      display: flex;
      gap: 8px;
    }

    .ent-tab {
      padding: 6px 16px;
      border-radius: 20px;
      border: 1px solid #e5e7eb;
      background: white;
      color: #374151;
      font-size: 0.85rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }

    .ent-tab.active {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
    }

    .ent-controls {
      display: flex;
      gap: 10px;
    }

    .ent-control-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 6px 12px;
      border: 1px solid #e5e7eb;
      background: white;
      border-radius: 6px;
      color: #374151;
      font-size: 0.85rem;
      cursor: pointer;
    }

    .ent-control-btn:hover {
      border-color: #d1d5db;
    }

    /* Table */
    .table-responsive {
      width: 100%;
      overflow-x: auto;
    }

    .ent-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }

    .ent-table th {
      background: #f1f3f6;
      padding: 16px 32px;
      font-size: 0.85rem;
      font-weight: 600;
      color: #4b5563;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      border-bottom: 1px solid #e5e7eb;
      white-space: nowrap;
      text-align: left;
    }
    
    /* Column Widths & Alignment */
    .ent-table th:nth-child(1) { width: 35%; } /* Name */
    .ent-table th:nth-child(2) { width: 15%; } /* Type */
    .ent-table th:nth-child(3) { width: 20%; text-align: right; } /* Amount */
    .ent-table th:nth-child(4) { width: 30%; } /* Remarks */

    /* Date Group Header */
    .date-group-header {
      /* Row styling handled in td */
    }

    .date-group-header td {
      background-color: #f8fafc;
      color: #374151;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 14px 32px;
      border-bottom: 1px solid #e5e7eb;
      border-top: 1px solid #e5e7eb;
    }

    .ent-table td {
      padding: 16px 32px;
      border-bottom: 1px solid #edf0f4;
      color: #374151;
      font-size: 0.95rem;
      vertical-align: middle;
      transition: background 0.15s ease;
    }
    
    /* Right align amount cells */
    .ent-table td:nth-child(3) { text-align: right; }

    .ent-table tr:last-child td {
      border-bottom: none;
    }

    .ent-table tr:not(.date-group-header):hover td {
      background: #f9fafb;
      cursor: pointer;
    }

    /* Table Cells */
    .cell-name-primary {
      font-weight: 600;
      color: #111827;
      display: block;
      font-size: 1rem;
    }

    .cell-name-secondary {
      font-size: 0.8rem;
      color: #6b7280;
      margin-top: 4px;
      display: block;
    }

    .ent-badge {
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: capitalize;
      display: inline-block;
      letter-spacing: 0.02em;
    }

    .ent-badge.income {
      background: #dcfce7;
      color: #166534;
    }

    .ent-badge.expense {
      background: #fee2e2;
      color: #991b1b;
    }

    .cell-amount {
      font-weight: 600;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size: 1rem;
      letter-spacing: -0.02em;
    }
    
    .cell-amount.income {
      color: #16a34a;
    }
    
    .cell-amount.expense {
      color: #dc2626;
    }

    .cell-amount.zero {
      color: #94a3b8;
    }

    .cell-remarks {
      color: #374151;
      max-width: 250px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .cell-remarks.empty {
      color: #9ca3af;
      font-style: italic;
    }

    /* Pagination */
    .ent-pagination {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 32px;
      border-top: 1px solid #e5e7eb;
      background: #f9fafb;
    }

    /* Segmented Pagination Control */
    .ent-rows-control {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .rows-label {
      font-size: 0.85rem;
      color: var(--text-secondary);
      font-weight: 500;
    }

    .rows-options {
      display: inline-flex;
      background: var(--border-color-light); /* Neutral background */
      padding: 3px;
      border-radius: 8px; /* Rounded pill shape container */
      gap: 2px;
    }

    .rows-option {
      border: none;
      background: transparent;
      padding: 4px 10px;
      font-size: 0.85rem;
      font-weight: 500;
      color: var(--text-secondary);
      cursor: pointer;
      border-radius: 6px;
      transition: all 0.2s ease;
      line-height: 1.2;
    }

    .rows-option:hover:not(.active) {
      background: rgba(0, 0, 0, 0.03);
      color: var(--text-primary);
    }

    .rows-option.active {
      background: #ffffff;
      color: var(--brand-primary);
      box-shadow: 0 1px 2px rgba(0,0,0,0.05); /* Subtle lift */
      font-weight: 600;
    }
    
    .rows-option:focus-visible {
      outline: 2px solid var(--brand-primary);
      outline-offset: 1px;
      z-index: 10;
    }

    /* Dark mode adjustments */
    html.dark-mode .rows-options {
      background: #334155;
    }
    html.dark-mode .rows-option.active {
      background: #475569;
      color: #60a5fa;
    }
    html.dark-mode .rows-option:hover:not(.active) {
        background: rgba(255,255,255,0.05);
        color: #f1f5f9;
    }

    .ent-page-info {
      font-size: 0.85rem;
      color: #6b7280;
    }

    .ent-page-nav {
      display: flex;
      gap: 4px;
    }

    .ent-page-btn {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      border: 1px solid #e5e7eb;
      background: white;
      color: #374151;
      font-size: 0.85rem;
      cursor: pointer;
    }

    .ent-page-btn.active {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
    }

    .ent-page-btn:disabled {
      cursor: not-allowed;
      background: #f8fafc;
      color: #94a3b8; /* Slate-400: Much more visible than previously */
    }

    .ent-page-btn svg {
      width: 16px;
      height: 16px;
      display: block;
    }

    .tx-left {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .tx-icon-box {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .tx-icon-box.income {
      background: #ecfdf5;
      /* Emerald 50 */
      color: #059669;
      /* Emerald 600 */
      border: none;
    }

    .tx-icon-box.expense {
      background: #fff1f2;
      /* Rose 50 */
      color: #e11d48;
      /* Rose 600 */
      border: none;
    }

    .tx-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .tx-category {
      font-size: 0.95rem;
      font-weight: 700;
      color: #1e293b;
    }

    .tx-desc {
      font-size: 0.85rem;
      color: #64748b;
    }

    .tx-right {
      display: flex;
      align-items: center;
      gap: 24px;
    }

    .tx-amount {
      font-size: 1rem;
      font-weight: 700;
      font-variant-numeric: tabular-nums;
    }

    .tx-amount.income {
      color: #059669;
      /* Emerald 600 */
    }

    .tx-amount.expense {
      color: #e11d48;
      /* Rose 600 */
    }

    .tx-actions {
      display: flex;
      gap: 8px;
      opacity: 0;
      transition: opacity 0.2s;
    }

    .tx-row:hover .tx-actions {
      opacity: 1;
    }

    .action-btn {
      width: 32px;
      height: 32px;
      border-radius: 6px;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
      background: transparent !important;
      color: #94a3b8;
    }

    .action-btn:hover {
      background: transparent;
      color: #3b82f6;
    }

    .action-btn.delete:hover {
      background: transparent;
      color: #ef4444;
    }

    .empty-log {
      text-align: center;
      padding: 60px 0;
      color: #64748b;
    }

    .empty-icon {
      margin-bottom: 16px;
      opacity: 0.2;
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-10px);
      }
    }

    /* Filter Panel Styles */
    .filter-group label {
      display: block;
      color: var(--text-secondary);
      font-size: 0.9rem;
      margin-bottom: 10px;
    }

    .category-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .chip {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-color);
      color: var(--text-secondary);
      padding: 6px 12px;
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .chip:hover {
      background: rgba(255, 255, 255, 0.1);
      color: var(--text-primary);
    }

    .chip.active {
      background: var(--brand-primary);
      color: white;
      border-color: var(--brand-primary);
    }

    .type-toggle {
      display: flex;
      background: rgba(0, 0, 0, 0.2);
      padding: 4px;
      border-radius: 10px;
      width: fit-content;
    }

    .type-btn {
      padding: 6px 16px;
      border-radius: 8px;
      background: transparent;
      color: var(--text-secondary);
      border: none;
      cursor: pointer;
      transition: all 0.2s;
    }

    .type-btn.active {
      background: var(--background-surface);
      color: var(--text-primary);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .amount-range {
      display: flex;
      align-items: center;
      gap: 10px;
      color: var(--text-primary);
    }

    .amount-range input[type=range] {
      flex: 1;
      accent-color: var(--brand-primary);
    }

    .filter-buttons {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .apply-btn,
    .clear-btn {
      padding: 10px 20px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.2s;
    }

    .apply-btn {
      background: var(--brand-primary);
      color: white;
    }

    .apply-btn:hover {
      opacity: 0.9;
    }

    .clear-btn {
      background: transparent;
      border: 1px solid var(--border-color);
      color: var(--text-secondary);
    }

    .clear-btn:hover {
      border-color: var(--text-primary);
      color: var(--text-primary);
    }

    /* Hide Checkbox and Date columns as per redesign */
    .col-checkbox, .col-date {
      display: none;
    }


    /* Custom Select Dropdown Styles */
    .custom-select-wrapper {
      position: relative;
      user-select: none;
      width: 100%;
      z-index: 20;
    }

    .custom-select-trigger {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      padding: 16px;
      background: #f9fafb;
      border-radius: 12px;
      cursor: pointer;
      color: var(--text-primary) !important;
      font-size: 1rem;
      transition: all 0.2s;
    }

    .custom-select-value {
      line-height: 1.5;
      display: block;
      color: var(--text-primary) !important;
    }

    .custom-select-trigger:hover {
      background: #ffffff;
    }

    .custom-options {
      position: absolute;
      display: block;
      top: 100%;
      left: 0;
      right: 0;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-lg);
      background: var(--background-surface);
      z-index: 50;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      max-height: 250px;
      overflow-y: auto;
      margin-top: 8px;
    }

    .custom-select-wrapper.open .custom-options {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .custom-option {
      position: relative;
      display: block;
      padding: 12px 16px;
      font-size: 0.95rem;
      color: var(--text-secondary);
      cursor: pointer;
      transition: all 0.2s;
    }

    .custom-option:hover,
    .custom-option.selected {
      background: rgba(99, 102, 241, 0.1);
      color: var(--brand-primary);
    }

    /* Ensure native options are also visible if they ever appear */
    option {
      color: var(--text-primary);
      background: #ffffff;
    }

    /* --- BALANCE GRAPH SECTION --- */
    .balance-section {
      margin-bottom: 32px;
    }

    .balance-card-enhanced {
      background: var(--background-surface);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-xl);
      padding: 24px;
      box-shadow: var(--shadow-md);
    }

    .time-selector {
      display: flex;
      background: #f1f5f9;
      /* Light gray container */
      padding: 4px;
      border-radius: 10px;
      gap: 4px;
    }

    .time-btn {
      border: none;
      background: transparent;
      padding: 6px 16px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      color: #64748b;
      /* Muted text */
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .time-btn:hover {
      color: #334155;
    }

    .time-btn.active {
      background: #0f766e;
      /* Dark Teal */
      color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chart-controls {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .compare-toggle {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .compare-toggle label {
      font-size: 0.85rem;
      font-weight: 500;
      color: #64748b;
      cursor: pointer;
    }

    .toggle-switch {
      appearance: none;
      width: 36px;
      height: 20px;
      background: #e2e8f0;
      border-radius: 10px;
      position: relative;
      cursor: pointer;
      transition: background 0.2s ease;
    }

    .toggle-switch::before {
      content: '';
      position: absolute;
      width: 14px;
      height: 14px;
      border-radius: 50%;
      background: white;
      top: 3px;
      left: 3px;
      transition: transform 0.2s ease;
    }

    .toggle-switch:checked {
      background: #0f766e;
    }

    .toggle-switch:checked::before {
      transform: translateX(16px);
    }

    /* Mobile Chart Interactions */
    #balanceChart {
      touch-action: pan-y;
      /* Critical: Allows vertical scroll but captures horizontal scrub */
    }

    .chart-annotation-popover {
      position: fixed;
      /* Fixed to handle viewport positioning easily */
      background: rgba(255, 255, 255, 0.98);
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 12px 16px;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
      z-index: 1000;
      display: none;
      min-width: 160px;
      pointer-events: none;
      /* Prevent blocking interactions underneath initially */
      transform: translate(-50%, -100%);
      /* Center horizontally, position above */
      margin-top: -15px;
      /* Offset from finger */
      transition: opacity 0.15s ease, transform 0.15s ease;
    }

    .popover-label {
      font-size: 0.75rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 2px;
    }

    .popover-value {
      font-size: 1.1rem;
      font-weight: 700;
      color: #0f172a;
      font-variant-numeric: tabular-nums;
    }

    .popover-value.pos {
      color: #059669;
    }

    .popover-value.neg {
      color: #ef4444;
    }

    .popover-date {
      font-size: 0.75rem;
      color: #94a3b8;
      margin-top: 4px;
      border-top: 1px solid #f1f5f9;
      padding-top: 4px;
    }

    /* --- INSIGHTS SECTION --- */
    .insights-section {
      background: #f8fafc;
      /* Subtle off-white */
      border: 1px solid #f1f5f9;
      border-radius: 16px;
      padding: 20px 24px;
      margin: -16px auto 32px auto;
      /* Pull it up slightly to connect with the chart card */
      max-width: 1000px;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    .insights-title {
      font-size: 0.8rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin: 0 0 12px 0;
      border-bottom: 1px solid #f1f5f9;
      padding-bottom: 12px;
    }

    .insights-list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .insight-item {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      font-size: 0.9rem;
      color: #334155;
      line-height: 1.5;
    }

    .insight-item svg {
      flex-shrink: 0;
      margin-top: 4px;
      color: #94a3b8;
    }

    /* --- REDESIGNED FILTER PANEL --- */
    .filter-panel-redesigned {
      background: var(--background-surface);
      border: 1px solid var(--border-color);
      border-radius: 24px;
      padding: 24px;
      margin-bottom: 32px;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      gap: 20px;
      transition: all 0.3s ease;
    }

    .filter-header-row {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }

    /* Segmented Control for Type */
    .segment-control {
      display: flex;
      background: #f1f5f9;
      padding: 4px;
      border-radius: 12px;
      gap: 4px;
    }

    .segment-btn {
      padding: 8px 16px;
      border-radius: 8px;
      border: none;
      background: transparent;
      color: var(--text-secondary);
      font-size: 0.9rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .segment-btn:hover {
      color: var(--text-primary);
    }

    .segment-btn.active {
      background: #ffffff;
      color: var(--text-primary);
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
      font-weight: 600;
    }

    /* Amount Popover Trigger */
    .filter-popover-wrapper {
      position: relative;
    }

    .filter-pill-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background: #ffffff;
      border: 1px solid var(--border-color);
      border-radius: 10px;
      color: var(--text-secondary);
      font-size: 0.9rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }

    .filter-pill-btn:hover,
    .filter-pill-btn.active {
      border-color: var(--brand-primary);
      color: var(--brand-primary);
      background: color-mix(in srgb, var(--brand-primary) 5%, transparent);
    }

    .popover-content {
      position: absolute;
      top: 100%;
      left: 0;
      margin-top: 8px;
      background: var(--background-surface);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      padding: 20px;
      box-shadow: var(--shadow-lg);
      z-index: 50;
      min-width: 300px;
      display: none;
      animation: fadeIn 0.1s ease;
    }

    .popover-content.show {
      display: block;
    }

    /* Category Row */
    .category-row {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      align-items: center;
    }

    .chip-modern {
      padding: 6px 14px;
      border-radius: 20px;
      border: 1px solid var(--border-color);
      background: transparent;
      color: var(--text-secondary);
      font-size: 0.85rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }

    .chip-modern:hover {
      border-color: #cbd5e1;
      color: var(--text-primary);
    }

    .chip-modern.active {
      background: var(--brand-primary);
      color: white;
      border-color: var(--brand-primary);
    }

    .chip-more {
      color: var(--brand-primary);
      border-color: transparent;
      background: transparent;
      font-weight: 600;
    }

    .chip-more:hover {
      background: color-mix(in srgb, var(--brand-primary) 5%, transparent);
    }

    /* Actions */
    .filter-actions {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-left: auto;
    }

    .btn-reset {
      background: transparent;
      border: none;
      color: var(--text-secondary);
      font-size: 0.9rem;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s;
      padding: 0;
    }

    .btn-reset:hover {
      color: var(--text-primary);
      text-decoration: underline;
    }

    .btn-apply {
      background: var(--brand-primary);
      color: white;
      border: none;
      padding: 10px 24px;
      border-radius: 10px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }

    .btn-apply:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
    }

    @media (max-width: 768px) {
      .filter-header-row {
        flex-direction: column;
        align-items: stretch;
      }

      .filter-actions {
        margin-left: 0;
        justify-content: space-between;
        width: 100%;
        margin-top: 16px;
        border-top: 1px solid var(--border-color);
        padding-top: 16px;
      }

      .segment-control {
        width: 100%;
      }

      .segment-btn {
        flex: 1;
        text-align: center;
      }
    }

    /* --- INLINE FILTER SUMMARY --- */
    .filter-summary {
      padding: 0 24px 16px 24px;
      font-size: 0.85rem;
      color: var(--text-secondary);
      display: none;
      align-items: center;
      flex-wrap: wrap;
      gap: 6px;
      animation: fadeIn 0.2s ease;
    }

    .filter-summary.visible {
      display: flex;
    }

    .filter-summary-label {
      color: #94a3b8;
      font-weight: 500;
    }

    .filter-summary-token {
      color: var(--text-primary);
      font-weight: 600;
    }

    .filter-summary-separator {
      color: #cbd5e1;
      margin: 0 2px;
    }

    .filter-summary-clear {
      margin-left: 8px;
      color: var(--brand-primary);
      cursor: pointer;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.8rem;
      opacity: 0.8;
      transition: opacity 0.2s;
    }

    /* --- MODERN CUSTOM DROPDOWN STYLES --- */
    .custom-dropdown-container {
      position: relative;
      width: 100%;
    }

    .custom-dropdown-trigger {
      width: 100%;
      padding: 16px;
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      transition: all 0.2s ease;
      color: #9ca3af; /* Default placeholder color */
      font-size: 1rem;
      font-family: inherit;
      user-select: none;
    }

    .custom-dropdown-trigger.has-value {
      color: #374151;
    }

    .custom-dropdown-trigger:hover {
      border-color: #cbd5e1;
      background: #f8fafc;
    }

    .custom-dropdown-trigger.active {
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
      background: #ffffff;
    }

    .custom-dropdown-menu {
      position: absolute;
      top: calc(100% + 8px);
      left: 0;
      width: 100%;
      background: #ffffff;
      border: 1px solid #f1f5f9;
      border-radius: 16px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
      z-index: 50;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
    }

    .custom-dropdown-menu.open {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .dropdown-search-wrapper {
      padding: 12px;
      border-bottom: 1px solid #f1f5f9;
      position: relative;
    }

    .dropdown-search {
      width: 100%;
      padding: 10px 12px 10px 38px;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      font-size: 0.9rem;
      outline: none;
      color: #1e293b;
      transition: border-color 0.2s;
      background: #f8fafc;
    }

    .dropdown-search:focus {
      border-color: #3b82f6;
      background: #ffffff;
    }

    .search-icon-input {
      position: absolute;
      left: 24px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      pointer-events: none;
    }

    .dropdown-options {
      max-height: 260px;
      overflow-y: auto;
      padding: 6px;
    }

    /* Custom Scrollbar */
    .dropdown-options::-webkit-scrollbar { width: 6px; }
    .dropdown-options::-webkit-scrollbar-track { background: transparent; }
    .dropdown-options::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }

    .filter-summary-clear:hover {
      opacity: 1;
      text-decoration: underline;
    }

    /* Sort Option Styles */
    .sort-option {
      padding: 10px 12px;
      border-radius: 8px;
      cursor: pointer;
      color: var(--text-secondary);
      font-size: 0.9rem;
      transition: all 0.2s;
    }
    .sort-option:hover {
      background: #f1f5f9;
      color: var(--text-primary);
    }
    .sort-option.selected {
      background: color-mix(in srgb, var(--brand-primary) 10%, transparent);
      color: var(--brand-primary);
      font-weight: 600;
    }
  </style>
</head>

<body>
  <div class="container">
    <?php include 'php/header.php'; ?>
    <div class="stats-overview">
      <!-- Today Card -->
      <div class="stat-card primary">
        <div class="stat-header">
          <span class="stat-label">Today's Position</span>
          <svg class="stat-icon-subtle" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" title="Daily Snapshot">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </div>
        <div class="balance-wrapper">
          <div class="balance-value" id="today-bal"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</div>
          <div class="insight-badge" id="today-insight">--</div>
        </div>
        <div class="mini-stats-grid">
          <div class="mini-stat-item">
            <span class="mini-label">Income</span>
            <span class="mini-value inc" id="today-inc"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span>
          </div>
          <div class="mini-stat-item">
            <span class="mini-label">Expense</span>
            <span class="mini-value exp" id="today-exp"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span>
          </div>
        </div>
      </div>

      <!-- Month Card -->
      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Monthly Performance</span>
          <svg class="stat-icon-subtle" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" title="Current Month">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
        </div>
        <div class="balance-wrapper">
          <div class="balance-value" id="month-bal"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</div>
          <div class="insight-badge" id="month-insight">--</div>
        </div>
        <div class="mini-stats-grid">
          <div class="mini-stat-item">
            <span class="mini-label">Income</span>
            <span class="mini-value inc" id="month-inc"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span>
          </div>
          <div class="mini-stat-item">
            <span class="mini-label">Expense</span>
            <span class="mini-value exp" id="month-exp"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span>
          </div>
        </div>
      </div>

      <!-- Year Card -->
      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Yearly Net Worth</span>
          <svg class="stat-icon-subtle" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" title="Year to Date">
            <path d="M3 3v18h18"></path>
            <path d="M18 17V9"></path>
            <path d="M13 17V5"></path>
            <path d="M8 17v-3"></path>
          </svg>
        </div>
        <div class="balance-wrapper">
          <div class="balance-value" id="year-bal"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</div>
          <div class="insight-badge" id="year-insight">--</div>
        </div>
        <div class="mini-stats-grid">
          <div class="mini-stat-item">
            <span class="mini-label">Income</span>
            <span class="mini-value inc" id="year-inc"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span>
          </div>
          <div class="mini-stat-item">
            <span class="mini-label">Expense</span>
            <span class="mini-value exp" id="year-exp"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?>0</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ACCOUNT BALANCE GRAPH -->
    <div class="balance-section">
      <div class="balance-card-enhanced">
        <div class="chart-header">
          <h2 class="chart-title">Account Balance</h2>
          <div class="chart-controls">
            <div class="compare-toggle">
              <input type="checkbox" id="compareToggle" class="toggle-switch">
              <label for="compareToggle">Compare</label>
            </div>
            <div class="time-selector">
              <button class="time-btn" data-range="1D">Day</button>
              <button class="time-btn" data-range="1W">Week</button>
              <button class="time-btn" data-range="1M">Month</button>
              <button class="time-btn active" data-range="1Y">Year</button>
            </div>
          </div>
        </div>
        <div class="chart-container" style="height: 320px; width: 100%;">
          <canvas id="balanceChart"></canvas>
        </div>
        <!-- Annotation Popover -->
        <div id="chartPopover" class="chart-annotation-popover">
          <div class="popover-label" id="popLabel"></div>
          <div class="popover-value" id="popValue"></div>
          <div class="popover-date" id="popDate"></div>
        </div>
      </div>
    </div>

    <!-- INSIGHTS SECTION -->
    <div class="insights-section">
      <h3 class="insights-title">Key Insights</h3>
      <ul class="insights-list" id="chartInsightsList">
        <!-- Insights will be generated here by JS -->
      </ul>
    </div>

    <div class="charts-grid">
      <div class="chart-card">
        <div class="chart-header">
          <h2 class="chart-title">Top Expenses</h2>
        </div>
        <div class="chart-container">
          <canvas id="expenseCategoryChart"></canvas>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-header">
          <h2 class="chart-title">Income Sources</h2>
        </div>
        <div class="chart-container">
          <canvas id="incomeCategoryChart"></canvas>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-header">
          <h2 class="chart-title">Monthly Overview</h2>
          <!-- Optional: Menu Icon -->
          <div style="color: #64748b; cursor: pointer;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="1"></circle>
              <circle cx="19" cy="12" r="1"></circle>
              <circle cx="5" cy="12" r="1"></circle>
            </svg>
          </div>
        </div>
        <div class="chart-container">
          <canvas id="incomeExpenseChart"></canvas>
        </div>
      </div>
    </div>

    <div class="date-section">
      <div class="date-header">
        <h3 id="currentMonthDisplay">Select Date</h3>
        <button id="openCalendar" class="calendar-trigger">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          <span>Calendar</span>
        </button>
      </div>
      <div class="date-strip-wrapper">
        <button id="prevDates" class="nav-btn" title="Previous">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <div id="dateContainer" class="scrollable-dates"></div>
        <button id="nextDates" class="nav-btn" title="Next">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
      <div id="dateContextText" class="date-context-label"></div>
    </div>
    
    <!-- NEW QUICK ADD COMPONENT -->
    <div class="qa-card-new" id="quickAddCard">
      <input type="hidden" id="type" value="income">

      <!-- 1. Type Segmented Control -->
      <div class="qa-header-new">
        <div class="qa-segment-new">
          <button class="qa-segment-btn active" data-type="income">Income</button>
          <button class="qa-segment-btn" data-type="expense">Expense</button>
        </div>
      </div>

      <!-- 2. Hero Amount Input -->
      <div class="qa-hero-section">
        <span class="qa-currency-symbol"><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?></span>
        <input type="text" id="amount" class="qa-amount-field" placeholder="0" inputmode="decimal" autocomplete="off" required>
      </div>

      <!-- 3. Secondary Details -->
      <div class="qa-body-new">
        <!-- Category Select -->
        <div class="custom-dropdown-container" id="categoryDropdown">
          <!-- Hidden input stores the actual value for form submission -->
          <input type="hidden" id="category" name="category" required>
          
          <div class="custom-dropdown-trigger" id="categoryTrigger" tabindex="0">
            <span id="categoryTriggerText">Select Category</span>
            <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
          </div>
          
          <div class="custom-dropdown-menu" id="categoryDropdownMenu">
            <div class="dropdown-search-wrapper">
              <svg class="search-icon-input" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              <input type="text" class="dropdown-search" placeholder="Search..." id="categorySearch" autocomplete="off">
            </div>
            <div class="dropdown-options" id="categoryOptions">
              <!-- Options populated by JS -->
            </div>
          </div>
        </div>

        <!-- Collapsible Note -->
        <div class="qa-note-section">
          <button type="button" id="toggleNoteBtn" class="qa-note-toggle">
            <span class="qa-plus-icon">+</span> Add Note
          </button>
          <input type="text" id="desc" class="qa-note-field hidden" placeholder="What is this for?" autocomplete="off">
        </div>
      </div>

      <!-- 4. Primary Action -->
      <div class="qa-footer-new">
        <button id="addEntry" class="qa-primary-btn" disabled>
          <span id="addEntryText">Add Income</span>
        </button>
      </div>
    </div>

    <!-- REDESIGNED FILTER PANEL -->
    <div class="filter-panel-redesigned">
      <div class="filter-header-row">
        <!-- Type Segmented Control -->
        <div class="segment-control">
          <button class="segment-btn active" data-type="all">All</button>
          <button class="segment-btn" data-type="income">Income</button>
          <button class="segment-btn" data-type="expense">Expense</button>
        </div>

        <!-- Amount Popover Trigger -->
        <div class="filter-popover-wrapper">
          <button id="amountTrigger" class="filter-pill-btn">
            <span>Amount</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div id="amountPopover" class="popover-content">
            <div class="amount-range">
              <span><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?><span id="minVal">0</span></span>
              <input type="range" id="minAmount" min="0" max="100000" step="100" value="0">
              <input type="range" id="maxAmount" min="0" max="100000" step="100" value="100000">
              <span><?php echo $_SESSION['currency_symbol'] ?? '₹'; ?><span id="maxVal">100000</span></span>
            </div>
          </div>
        </div>

        <!-- Sort Popover Trigger -->
        <div class="filter-popover-wrapper">
          <button id="sortTrigger" class="filter-pill-btn">
            <span id="sortLabel">Sort: Date (Newest)</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>
          <div id="sortPopover" class="popover-content" style="min-width: 220px; padding: 8px;">
             <div class="sort-option selected" data-sort="date-desc">Date (Newest)</div>
             <div class="sort-option" data-sort="date-asc">Date (Oldest)</div>
             <div class="sort-option" data-sort="amount-desc">Amount (High to Low)</div>
             <div class="sort-option" data-sort="amount-asc">Amount (Low to High)</div>
          </div>
        </div>

        <div class="filter-actions">
          <button class="btn-reset" id="clearFiltersBtn">Reset</button>
          <button class="btn-apply" id="applyFiltersBtn">Apply Filters</button>
        </div>
      </div>

      <!-- Category Row -->
      <div class="category-row" id="categoryChipsContainer">
        <!-- Populated by JS -->
      </div>
    </div>

    </div><!-- Close global container to allow full-width breakout -->

    <!-- ENTERPRISE TRANSACTION TABLE -->
    <div class="enterprise-panel expanded-layout">
      <div class="ent-header">
        <h2>Transactions</h2>
        <div class="ent-header-actions">
          <button class="btn-create-ent" onclick="document.querySelector('.form-section').scrollIntoView({behavior: 'smooth'})">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Create
          </button>
          <div class="avatar-circle">
            <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
          </div>
        </div>
      </div>

      <div class="ent-filters">
        <div class="ent-tabs">
          <button class="ent-tab active" data-type="all">All</button>
          <button class="ent-tab" data-type="income">Income</button>
          <button class="ent-tab" data-type="expense">Expense</button>
        </div>
        <div class="ent-tabs" id="dateRangeTabs" style="margin-left: 16px; border-left: 1px solid #e5e7eb; padding-left: 16px;">
          <button class="ent-tab active" data-range="today">Today</button>
          <button class="ent-tab" data-range="day">Day</button>
          <button class="ent-tab" data-range="month">Month</button>
          <button class="ent-tab" data-range="year">Year</button>
        </div>
        <div class="ent-controls">
          <div style="position: relative; display: inline-block;">
            <button class="ent-control-btn" id="entDateFilter">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              <span id="entDateLabel">Today</span>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>
          </div>
          <button class="ent-control-btn" id="exportBtn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Export
          </button>
        </div>
      </div>

      <!-- Inline Filter Summary -->
      <div id="filterSummary" class="filter-summary"></div>

      <div class="table-responsive">
        <table class="ent-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody id="ent-table-body">
            <!-- Rows injected via JS -->
          </tbody>
        </table>
      </div>

      <div class="ent-pagination">
        <div class="ent-rows-control">
           <span class="rows-label" id="rowsPerPagelabel">Rows:</span>
           <div class="rows-options" role="group" aria-labelledby="rowsPerPagelabel">
             <button type="button" class="rows-option" aria-pressed="false" data-value="10">10</button>
             <button type="button" class="rows-option" aria-pressed="false" data-value="25">25</button>
             <button type="button" class="rows-option" aria-pressed="false" data-value="50">50</button>
             <button type="button" class="rows-option active" aria-pressed="true" data-value="all">All</button>
           </div>
        </div>
        <div class="ent-page-info" id="paginationInfo">Loading...</div>
        <div class="ent-page-nav" id="paginationControls">
          <!-- Rendered by JS -->
        </div>
      </div>
    </div>
  <div id="calendarModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
      <h3 id="modalTitle">Select a date</h3>
      <div class="date-input-wrapper">
        <input type="date" id="datePicker" max="9999-12-31" class="date-input-mode">
        <input type="month" id="monthPicker" class="date-input-mode" style="display:none">
        <select id="yearPicker" class="date-input-mode" style="display:none"></select>
      </div>
      <div class="modal-actions">
        <button id="closeCalendar">Cancel</button>
        <button id="goToDateBtn">Go</button>
      </div>
    </div>
  </div>


  <script>
    const selectedDate = document.querySelector(".date-box.active")?.dataset.date;
    const typeInput = document.getElementById("type");
    const typeToggleButtons = document.querySelectorAll(".qa-segment-btn");
    const formSection = document.getElementById("quickAddCard"); // Now targets .qa-card-new

    const incomeGenres = <?php echo json_encode($income_cats); ?> || [];
    const expenseGenres = <?php echo json_encode($expense_cats); ?> || [];
    let selectedCategories = [];
    let selectedType = 'all';
    let minAmount = 0;
    let maxAmount = 100000;
    let currentSort = 'date-desc';
    let currentFetchParams = { date: new Date().toISOString().split('T')[0] };
    let currentTableData = []; // Store visible data for export
    let activeEntRange = 'today'; // Tracks the active tab: 'today', 'month', 'year'

    // Populate category dropdown based on selected type
    function populateCategories() {
      const optionsContainer = document.getElementById("categoryOptions");
      const hiddenInput = document.getElementById("category");
      const triggerText = document.getElementById("categoryTriggerText");
      const trigger = document.getElementById("categoryTrigger");
      
      // Clear current options
      optionsContainer.innerHTML = "";
      
      // Reset selection
      hiddenInput.value = "";
      triggerText.textContent = "Select Category";
      trigger.classList.remove("has-value");

      const currentType = typeInput.value.toLowerCase();
      let genres = currentType === "income" ? incomeGenres : expenseGenres;

      // Filter out empty categories
      genres = genres.filter(cat => cat && cat.trim() !== "");

      if (genres.length === 0) {
        const emptyState = document.createElement("div");
        emptyState.className = "dropdown-item";
        emptyState.style.padding = "12px";
        emptyState.style.color = "#94a3b8";
        emptyState.style.textAlign = "center";
        emptyState.style.fontSize = "0.9rem";
        emptyState.textContent = "No categories found";
        optionsContainer.appendChild(emptyState);
      } else {
        genres.forEach(cat => {
          const item = document.createElement("div");
          item.className = "dropdown-item";
          item.dataset.value = cat;
          
          // Generate visual icon (First letter)
          const initial = cat.charAt(0).toUpperCase();
          
          item.innerHTML = `
            <div class="item-icon">${initial}</div>
            <span class="item-label">${cat}</span>
          `;
          
          item.addEventListener("click", () => {
            selectCategory(cat, item);
          });
          
          optionsContainer.appendChild(item);
        });
      }
    }

    function selectCategory(value, itemElement) {
        const hiddenInput = document.getElementById("category");
        const triggerText = document.getElementById("categoryTriggerText");
        const trigger = document.getElementById("categoryTrigger");
        const dropdown = document.getElementById("categoryDropdownMenu");
        
        hiddenInput.value = value;
        triggerText.textContent = value;
        trigger.classList.add("has-value");
        
        // Visual selection state
        document.querySelectorAll(".dropdown-item").forEach(el => el.classList.remove("selected"));
        if(itemElement) itemElement.classList.add("selected");
        
        // Close dropdown
        dropdown.classList.remove("open");
        trigger.classList.remove("active");
    }

    // --- Custom Dropdown Event Listeners ---
    document.addEventListener("DOMContentLoaded", () => {
        const trigger = document.getElementById("categoryTrigger");
        const dropdown = document.getElementById("categoryDropdownMenu");
        const searchInput = document.getElementById("categorySearch");

        // Toggle Dropdown
        trigger.addEventListener("click", (e) => {
            e.stopPropagation();
            const isOpen = dropdown.classList.contains("open");
            
            if (isOpen) {
                dropdown.classList.remove("open");
                trigger.classList.remove("active");
            } else {
                dropdown.classList.add("open");
                trigger.classList.add("active");
                searchInput.value = ""; // Clear search on open
                searchInput.focus();
                // Trigger input event to reset list
                searchInput.dispatchEvent(new Event('input'));
            }
        });

        // Search Filtering
        searchInput.addEventListener("input", (e) => {
            const term = e.target.value.toLowerCase();
            const items = document.querySelectorAll(".dropdown-item");
            
            items.forEach(item => {
                const text = item.querySelector(".item-label")?.textContent.toLowerCase() || "";
                if (text.includes(term)) {
                    item.style.display = "flex";
                } else {
                    item.style.display = "none";
                }
            });
        });

        // Close on Click Outside
        document.addEventListener("click", (e) => {
            if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove("open");
                trigger.classList.remove("active");
            }
        });
    });
    
    typeToggleButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        typeToggleButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const type = btn.dataset.type;
        typeInput.value = type;

        // Update Form Mode for Styling & Button Text
        // We use data attributes or just class toggles for the button color
        const submitBtn = document.getElementById('addEntry');
        submitBtn.classList.remove('btn-income', 'btn-expense');
        submitBtn.classList.add(type === 'income' ? 'btn-income' : 'btn-expense');
        
        // Update Card Mode for Contextual Colors
        formSection.classList.remove('mode-income', 'mode-expense');
        formSection.classList.add('mode-' + type);

        document.getElementById('addEntryText').textContent = type === 'income' ? 'Add Income' : 'Add Expense';
        populateCategories();
      });

    });

    // Note Toggle Logic
    const noteBtn = document.getElementById('toggleNoteBtn');
    const noteInput = document.getElementById('desc');
    if(noteBtn) {
        noteBtn.addEventListener('click', () => {
            noteInput.classList.remove('hidden');
            noteInput.focus();
            noteBtn.style.display = 'none';
        });
    }

    // Enterprise Tab Logic
    const entTabs = document.querySelectorAll('.ent-tab[data-type]');
    entTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        entTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        selectedType = tab.dataset.type;
        fetchEntries(currentFetchParams);
      });
    });

    // Date Range Tab Logic
    const rangeTabs = document.querySelectorAll('.ent-tab[data-range]');
    rangeTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        rangeTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const range = tab.dataset.range;
        activeEntRange = range;
        const today = new Date();
        const filterBtn = document.getElementById('entDateFilter');

        if (range === 'today') {
          const dateStr = today.toISOString().split('T')[0];
          currentFetchParams = { date: dateStr };
          // Sync with date strip
          document.querySelectorAll(".date-box").forEach(d => d.classList.remove("active"));
          const todayBox = document.querySelector(`.date-box[data-date="${dateStr}"]`);
          if (todayBox) todayBox.classList.add("active");
          document.getElementById("entDateLabel").textContent = formatDisplayDate(dateStr);
          
          // Enable filter button (allow changing date from Today view)
          filterBtn.style.opacity = '1';
          filterBtn.style.cursor = 'pointer';

        } else if (range === 'day') {
          // Default to today or preserve current date if available
          const dateStr = currentFetchParams.date || new Date().toISOString().split('T')[0];
          currentFetchParams = { date: dateStr };
          
          document.querySelectorAll(".date-box").forEach(d => d.classList.remove("active"));
          // Highlight if visible in strip
          const box = document.querySelector(`.date-box[data-date="${dateStr}"]`);
          if(box) box.classList.add("active");

          document.getElementById("entDateLabel").textContent = formatDisplayDate(dateStr);
          filterBtn.style.opacity = '1';
          filterBtn.style.cursor = 'pointer';

        } else if (range === 'month') {
          // Preserve year if switching from Year mode, else use current
          let targetYear = new Date().getFullYear();
          if (currentFetchParams.year) targetYear = parseInt(currentFetchParams.year);
          else if (currentFetchParams.month) targetYear = parseInt(currentFetchParams.month.split('-')[0]);
          
          // Default to current month if we don't have a specific month selected, or keep current if valid
          let targetMonth = new Date().getMonth() + 1;
          if (currentFetchParams.month) targetMonth = parseInt(currentFetchParams.month.split('-')[1]);
          else if (currentFetchParams.date) targetMonth = parseInt(currentFetchParams.date.split('-')[1]); // Preserve month from Day mode

          const monthStr = `${targetYear}-${String(targetMonth).padStart(2, '0')}`;
          currentFetchParams = { month: monthStr };
          
          document.querySelectorAll(".date-box").forEach(d => d.classList.remove("active"));
          const d = new Date(targetYear, targetMonth - 1);
          document.getElementById("entDateLabel").textContent = d.toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });
          
          filterBtn.style.opacity = '1';
          filterBtn.style.cursor = 'pointer';

        } else if (range === 'year') {
          // Preserve year
          let targetYear = currentFetchParams.year || (currentFetchParams.month ? currentFetchParams.month.split('-')[0] : today.getFullYear());
          currentFetchParams = { year: targetYear.toString() };
          
          document.querySelectorAll(".date-box").forEach(d => d.classList.remove("active"));
          document.getElementById("entDateLabel").textContent = targetYear.toString();
          
          filterBtn.style.opacity = '1';
          filterBtn.style.cursor = 'pointer';
        }
        fetchEntries(currentFetchParams);
      });
    });

    let offset = 0;
    const maxOffset = 365;
    const chunkSize = 30;
    let currentFetchController = null;

    function isToday(date) {
      const today = new Date();
      return date.toDateString() === today.toDateString();
    }

    function formatDisplayDate(isoDate) {
      const date = new Date(isoDate);
      const day = date.toLocaleDateString("en-IN", {
        weekday: "short"
      });
      const dateNum = date.getDate().toString().padStart(2, '0');
      const month = date.toLocaleDateString("en-IN", {
        month: "long"
      });
      return `${dateNum} ${month}, ${day}`;
    }

    function updateContextLabel(isoDate) {
      const date = new Date(isoDate);
      const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
      const text = date.toLocaleDateString('en-IN', options);
      const label = document.getElementById("dateContextText");
      if(label) label.textContent = `Viewing transactions for ${text}`;
    }

    function populateDates(offset = 0) {
      const dateContainer = document.getElementById('dateContainer');
      dateContainer.innerHTML = '';

      // Update Month Display
      const endDate = new Date();
      endDate.setDate(endDate.getDate() - offset);
      const startDate = new Date();
      startDate.setDate(startDate.getDate() - offset - chunkSize + 1);
      const monthLabel = document.getElementById('currentMonthDisplay');
      const startMonth = startDate.toLocaleDateString("en-IN", {
        month: "long",
        year: "numeric"
      });
      const endMonth = endDate.toLocaleDateString("en-IN", {
        month: "long",
        year: "numeric"
      });
      monthLabel.textContent = (startMonth === endMonth) ? startMonth : `${startMonth} - ${endMonth}`;

      // Date Comparison Helpers
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      for (let i = chunkSize - 1; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - offset - i);
        const dateBox = document.createElement("div");
        dateBox.classList.add("date-box");

        // Determine State
        const checkDate = new Date(date);
        checkDate.setHours(0, 0, 0, 0);
        if (checkDate < today) dateBox.classList.add('past');
        if (checkDate > today) dateBox.classList.add('future');
        if (checkDate.getTime() === today.getTime()) dateBox.classList.add('today');

        const day = date.toLocaleDateString("en-IN", {
          weekday: "short"
        });
        const dateNum = date.getDate();

        dateBox.innerHTML = `
            <strong>${day}</strong>
            <div class="date-num">${dateNum}</div>
        `;

        // Use local date string to prevent timezone shifts (UTC vs Local)
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const dayStr = String(date.getDate()).padStart(2, '0');
        dateBox.dataset.date = `${year}-${month}-${dayStr}`;

        dateBox.addEventListener("click", () => {
          document.querySelectorAll(".date-box").forEach(d => d.classList.remove("active"));
          dateBox.classList.add("active");
          
          // Update Range Tabs to reflect specific date selection (effectively 'Today' mode if today, else custom)
          rangeTabs.forEach(t => t.classList.remove('active'));
          if (isToday(new Date(dateBox.dataset.date))) {
            document.querySelector('.ent-tab[data-range="today"]').classList.add('active');
          }

          document.getElementById("entDateLabel").textContent = formatDisplayDate(dateBox.dataset.date);
          updateContextLabel(dateBox.dataset.date);
          fetchEntries({ date: dateBox.dataset.date });
        });
        dateContainer.appendChild(dateBox);
      }
      const todayBox = Array.from(dateContainer.children).find(box =>
        isToday(new Date(box.dataset.date))
      );
      if (todayBox) {
        todayBox.classList.add('active');
        document.getElementById("entDateLabel").textContent = formatDisplayDate(todayBox.dataset.date);
        updateContextLabel(todayBox.dataset.date);
        fetchEntries({ date: todayBox.dataset.date });
        setTimeout(() => {
          todayBox.scrollIntoView({
            behavior: "smooth",
            inline: "center",
            block: "nearest"
          });
        }, 100);
      }
      document.getElementById('prevDates').disabled = (offset + chunkSize >= maxOffset);
      document.getElementById('nextDates').disabled = (offset <= 0);
    }
    document.getElementById("prevDates").addEventListener("click", () => {
      if (offset + chunkSize < maxOffset) {
        offset += chunkSize;
        populateDates(offset);
      }
    });
    document.getElementById("nextDates").addEventListener("click", () => {
      if (offset > 0) {
        offset -= chunkSize;
        populateDates(offset);
      }
    });

    window.onload = () => {
      offset = 0; // Reset offset on load
      populateCategories();
      
      // Populate Year Picker
      const yearPicker = document.getElementById('yearPicker');
      const currentYear = new Date().getFullYear();
      for (let y = currentYear + 1; y >= currentYear - 10; y--) {
          yearPicker.add(new Option(y, y));
      }

      loadSummary();
      populateDates(offset); // ✅ required
      populateCategoryChips();
      formSection.classList.add('mode-income'); // Default state
      document.getElementById('addEntryText').textContent = 'Add Income';
      validateForm(); // Ensure button state is correct on load
      initBalanceChart(); // Initialize the new graph
    };

    // --- INSIGHT GENERATION ENGINE ---
    function updateInsightsUI(range, data, isComparing) {
      const insights = generateChartInsights(range, data, isComparing);
      const list = document.getElementById('chartInsightsList');
      list.innerHTML = '';

      if (insights.length === 0) {
        list.innerHTML = `<li class="insight-item" style="color: #9ca3af;">No specific insights for this period.</li>`;
        return;
      }

      insights.forEach(text => {
        const li = document.createElement('li');
        li.className = 'insight-item';
        li.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22C17.5 22 22 17.5 22 12S17.5 2 12 2 2 6.5 2 12s4.5 10 10 10z"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
                <span>${text}</span>
            `;
        list.appendChild(li);
      });
    }

    function generateChartInsights(range, data, isComparing) {
      const insights = [];
      const {
        current,
        previous,
        events
      } = data;
      const periodMap = {
        '1D': 'day',
        '1W': 'week',
        '1M': 'month',
        '1Y': 'year'
      };
      const periodName = periodMap[range] || 'period';

      // 1. Major Event Insights (Highest Priority)
      if (events && events.length > 0) {
        const sortedEvents = [...events].sort((a, b) => Math.abs(b.amount) - Math.abs(a.amount));
        const mainEvent = sortedEvents[0];
        if (mainEvent.type === 'peak') {
          insights.push(`Your balance increased significantly due to a <strong>${mainEvent.label}</strong> credit of <strong>${window.CURRENCY_SYMBOL}${mainEvent.amount.toLocaleString()}</strong>.`);
        } else {
          insights.push(`A notable dip was caused by a <strong>${mainEvent.label}</strong> payment of <strong>${window.CURRENCY_SYMBOL}${Math.abs(mainEvent.amount).toLocaleString()}</strong>.`);
        }
      }

      // 2. Comparison Insight
      if (isComparing && current.length > 0 && previous.length > 0) {
        const currentEnd = current[current.length - 1];
        const previousEnd = previous[previous.length - 1];
        const diff = currentEnd - previousEnd;

        if (Math.abs(diff) > 0) {
          const direction = diff > 0 ? 'higher' : 'lower';
          insights.push(`At the end of this ${periodName}, your balance is <strong>${window.CURRENCY_SYMBOL}${Math.abs(diff).toLocaleString()} ${direction}</strong> than the previous ${periodName}.`);
        }
      }

      // 3. Trend Insight
      if (current.length > 1) {
        const startVal = current[0];
        const endVal = current[current.length - 1];
        if (endVal > startVal && insights.length < 2) {
          insights.push(`Your balance shows a general <strong>upward trend</strong> over this ${periodName}.`);
        } else if (endVal < startVal && insights.length < 2) {
          insights.push(`Your balance shows a general <strong>downward trend</strong> over this ${periodName}.`);
        }
      }

      // 4. Stability Insight
      if (insights.length === 0) {
        const max = Math.max(...current);
        const min = Math.min(...current);
        // If fluctuation is less than 10% of the average, consider it stable.
        const avg = current.reduce((a, b) => a + b, 0) / current.length;
        if ((max - min) / avg < 0.1) {
          insights.push(`Your balance remained <strong>relatively stable</strong> during this period.`);
        }
      }

      return insights.slice(0, 3); // Max 3 insights
    }

    function isToday(date) {
      const today = new Date();
      return date.toDateString() === today.toDateString();
    }

    function formatDisplayDate(isoDate) {
      const date = new Date(isoDate);
      const day = date.toLocaleDateString("en-IN", {
        weekday: "short"
      });
      const dateNum = date.getDate().toString().padStart(2, '0');
      const month = date.toLocaleDateString("en-IN", {
        month: "long"
      });
      return `${dateNum} ${month}, ${day}`;
    }




    // --- Date Picker Logic (Transactions Table) ---
    document.getElementById('entDateFilter').addEventListener('click', () => {
      // Allow clicking even in Today mode to pick a specific date

      const modal = document.getElementById("calendarModal");
      const title = document.getElementById("modalTitle");
      const dateInput = document.getElementById("datePicker");
      const monthInput = document.getElementById("monthPicker");
      const yearInput = document.getElementById("yearPicker");

      // Hide all first
      dateInput.style.display = 'none';
      monthInput.style.display = 'none';
      yearInput.style.display = 'none';

      if (activeEntRange === 'month') {
          title.textContent = "Select Month & Year";
          monthInput.style.display = 'block';
          monthInput.value = currentFetchParams.month || new Date().toISOString().slice(0, 7);
      } else if (activeEntRange === 'year') {
          title.textContent = "Select Year";
          yearInput.style.display = 'block';
          yearInput.value = currentFetchParams.year || new Date().getFullYear();
      } else {
          // Covers 'day' and 'today'
          title.textContent = "Select Specific Date";
          dateInput.style.display = 'block';
          dateInput.value = currentFetchParams.date || new Date().toISOString().split('T')[0];
      }

      openModal();
    });

    let currentCenterDate = new Date();

    document.getElementById("prevDates").addEventListener("click", () => {
      if (offset + chunkSize < maxOffset) {
        offset += chunkSize;
        populateDates(offset);
      }
    });

    document.getElementById("nextDates").addEventListener("click", () => {
      if (offset > 0) {
        offset -= chunkSize;
        populateDates(offset);
      }
    });

    // --- Amount Formatting & Validation ---
    const amountInput = document.getElementById('amount');
    const addEntryBtn = document.getElementById('addEntry');

    function validateForm() {
      const rawValue = amountInput.value.replace(/,/g, '');
      const isValid = rawValue && !isNaN(rawValue) && parseFloat(rawValue) > 0;
      addEntryBtn.disabled = !isValid;
    }

    amountInput.addEventListener('input', (e) => {
      // Allow only numbers and decimals
      let value = e.target.value.replace(/[^0-9.]/g, '');

      // Prevent multiple decimals
      const parts = value.split('.');
      if (parts.length > 2) value = parts[0] + '.' + parts.slice(1).join('');

      // Format with commas (Indian numbering system approximation or standard)
      if (parts[0].length > 0) {
        parts[0] = Number(parts[0]).toLocaleString('en-IN');
      }

      e.target.value = parts.join('.');
      validateForm();
    });

    // Enter Key Submission
    ['amount', 'desc'].forEach(id => {
      document.getElementById(id).addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !addEntryBtn.disabled) {
          addEntryBtn.click();
          e.preventDefault(); // Prevent default form submission if any
        }
      });
    });

    document.getElementById("addEntry").addEventListener("click", () => {
      const type = typeInput.value;
      const category = document.getElementById("category").value;
      // Parse amount removing commas
      const amount = document.getElementById("amount").value.replace(/,/g, '').trim();
      const desc = document.getElementById("desc").value.trim();
      const editingId = document.getElementById('addEntry').dataset.editingId || null;
      const btnText = document.getElementById('addEntryText');
      if (!category || !amount) {
        alert("Please fill in all required fields.");
        return;
      }
      const entry = {
        type,
        category,
        amount: parseFloat(amount),
        description: desc,
        time: new Date().toLocaleString()
      };
      const logItem = document.createElement("li");
      logItem.textContent = `${entry.time} - ${type.toUpperCase()} - ${category}: ${window.CURRENCY_SYMBOL}${entry.amount} ${desc ? "(" + desc + ")" : ""}`;
      // document.getElementById("logList").appendChild(logItem); // Removed old list logic
      const params = new URLSearchParams({
        type,
        category,
        amount,
        description: desc,
      });
      if (editingId) {
        params.append('id', editingId);
      }
      const selectedDate = document.querySelector(".date-box.active")?.dataset.date;
      params.append("created_at", selectedDate);

      // Loading State
      const originalText = btnText.textContent;
      btnText.textContent = "Saving...";
      document.getElementById("addEntry").disabled = true;

      fetch("php/add_entry.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: params,
        })
        .then((res) => res.text())
        .then((msg) => {
          const type = msg.toLowerCase().includes('error') || msg.includes('❌') ? 'error' : 'success';
          showToast(msg, type);
          setTimeout(() => location.reload(), 1500);
        })
        .catch((err) => {
          console.error("Error:", err);
          alert("❌ Something went wrong while saving.");
          // Reset button on error
          btnText.textContent = originalText;
          document.getElementById("addEntry").disabled = false;
        });
    });

    function escapeHtml(text) {
      if (!text) return text;
      return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    // Pagination State
    let allRawEntries = [];
    let currentPage = 1;
    let rowsPerPage = 'all'; // '5', '10', ..., 'all'

    // Event Listener for Rows Per Page
    // Event Listener for Rows Per Page (Segmented Control)
    document.querySelectorAll('.rows-option').forEach(btn => {
      btn.addEventListener('click', (e) => {
        // Update visual state
        document.querySelectorAll('.rows-option').forEach(b => {
          b.classList.remove('active');
          b.setAttribute('aria-pressed', 'false');
        });
        e.currentTarget.classList.add('active');
        e.currentTarget.setAttribute('aria-pressed', 'true');

        // Logic
        rowsPerPage = e.currentTarget.dataset.value;
        currentPage = 1; 
        renderTable();
      });
    });

    function fetchEntries(params) {
      // Abort previous request if it exists
      if (currentFetchController) {
        currentFetchController.abort();
      }
      currentFetchController = new AbortController();
      const signal = currentFetchController.signal;
      
      currentFetchParams = params;
      const queryString = new URLSearchParams(params).toString();

      // Ensure we fetch all data from server so we can page client-side
      // We do NOT send limit/page params to server to get full dataset for the period
      fetch(`php/fetch_entries.php?${queryString}&limit=all`, { signal })
        .then(res => res.text())
        .then(text => {
          try {
            return JSON.parse(text);
          } catch (e) {
            console.error("Server Error (fetchEntriesByDate):", text);
            showToast("❌ Server Error. Check console.");
            throw new Error("Invalid JSON response from server");
          }
        })
        .then(data => {
          // data can be array (old) or {entries: [], pagination: {}} (new)
          allRawEntries = Array.isArray(data) ? data : (data.entries || []);
          
          renderFilterSummary(); // Update summary
          
          // Initial Filter & Render
          runFilterPipeline();
        })
        .catch(error => {
          if (error.name === 'AbortError') return; // Ignore aborted requests
          console.error("Error fetching entries by date:", error);
        });
    }

    function runFilterPipeline() {
        // 1. Filter
        let filtered = allRawEntries.filter(entry => {
            const amt = parseFloat(entry.amount);
            const typeMatch = selectedType === 'all' || entry.type === selectedType;
            const catMatch = selectedCategories.length === 0 || selectedCategories.includes(entry.category);
            const amtMatch = amt >= minAmount && amt <= maxAmount;
            return typeMatch && catMatch && amtMatch;
        });

        // 2. Sort
        filtered.sort((a, b) => {
             const dateA = new Date((a.created_at || '').replace(' ', 'T'));
             const dateB = new Date((b.created_at || '').replace(' ', 'T'));
             
             const valA = String(a.amount).replace(/,/g, '');
             const valB = String(b.amount).replace(/,/g, '');
             const amtA = parseFloat(valA) || 0;
             const amtB = parseFloat(valB) || 0;

             if (currentSort === 'date-asc') return dateA - dateB;
             if (currentSort === 'amount-desc') return amtB - amtA;
             if (currentSort === 'amount-asc') return amtA - amtB;
             return dateB - dateA; // Default date-desc
        });

        // Update global filtered data
        currentTableData = filtered; // Used for export
        currentPage = 1; // Always reset page on filter/sort change
        
        // 3. Render
        renderTable();
    }

    function renderTable() {
        const tableBody = document.getElementById("ent-table-body");
        const paginationInfo = document.getElementById("paginationInfo");
        tableBody.innerHTML = "";

        const totalItems = currentTableData.length;
        
        if (totalItems === 0) {
            tableBody.innerHTML = `
              <tr>
                <td colspan="4" style="text-align: center; padding: 60px 20px; color: #64748b;">
                    <div style="margin-bottom: 12px; opacity: 0.5;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div style="font-size: 1rem; font-weight: 500;">No transactions found</div>
                    <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 4px;">Try adjusting your filters.</div>
                </td>
              </tr>
            `;
            paginationInfo.textContent = "0 transactions";
            renderPaginationControls(0, 1, 1);
            return;
        }

        // Pagination Calculations
        let limit = rowsPerPage === 'all' ? totalItems : parseInt(rowsPerPage);
        const totalPages = Math.ceil(totalItems / limit);
        if (currentPage > totalPages) currentPage = totalPages;
        
        const start = (currentPage - 1) * limit;
        const end = Math.min(start + limit, totalItems);
        const pageData = currentTableData.slice(start, end);

        // Update Info Text
        paginationInfo.textContent = `Showing ${start + 1}-${end} of ${totalItems}`;

        // Render Rows
        let prevDate = null;
        let totalIncome = 0; // Stats for THIS PAGE (or should it be total? usually total context is better, but this loop is page only)
        // Note: Summary stats (Cards) are fetched separately via fetch_summary.php, so we don't need to calc totals here for the UI.

        pageData.forEach(entry => {
            // Fix for 05:30 AM issue: Handle date-only strings as local time
            let isoDateStr = entry.created_at.replace(' ', 'T');
            if (entry.created_at.indexOf(':') === -1) {
                isoDateStr += 'T00:00:00';
            }
            const entryDateObj = new Date(isoDateStr);
            const dateStr = entryDateObj.toLocaleDateString('en-IN');
            const timeStr = entryDateObj.toLocaleTimeString('en-IN', {
              hour: '2-digit',
              minute: '2-digit'
            });

            // Date Grouping Logic
            if (dateStr !== prevDate) {
                const groupRow = document.createElement("tr");
                groupRow.className = "date-group-header";
                groupRow.innerHTML = `<td colspan="4">${dateStr}</td>`;
                tableBody.appendChild(groupRow);
                prevDate = dateStr;
            }

            // Amount Formatting
            const isIncome = entry.type === 'income';
            const sign = isIncome ? '+' : '−';
            const amountClass = isIncome ? 'income' : 'expense';
            const formattedAmount = `${sign}${window.CURRENCY_SYMBOL}${parseFloat(entry.amount).toLocaleString()}`;

            // Remarks Formatting
            const remarksHtml = entry.description 
                ? escapeHtml(entry.description) 
                : '<span class="cell-remarks empty">No notes</span>';

            const row = document.createElement("tr");
            row.innerHTML = `
                <td>
                    <span class="cell-name-primary">${escapeHtml(entry.category)}</span>
                    <span class="cell-name-secondary">${timeStr}</span>
                </td>
                <td><span class="ent-badge ${entry.type}">${entry.type}</span></td>
                <td class="cell-amount ${amountClass}">${formattedAmount}</td>
                <td class="cell-remarks">${remarksHtml}</td>
            `;

            // Add click to edit
            row.addEventListener('click', (e) => {
              if (e.target.type === 'checkbox') return;
              typeInput.value = entry.type;
              populateCategories();
              setTimeout(() => {
                const item = document.querySelector(`.dropdown-item[data-value="${entry.category}"]`);
                if(item) selectCategory(entry.category, item);
              }, 100);
              document.getElementById('amount').value = entry.amount;
              document.getElementById('desc').value = entry.description || '';
              document.getElementById('addEntry').dataset.editingId = entry.id;
              document.getElementById('addEntryText').textContent = 'Update Entry';
              document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
            });

            tableBody.appendChild(row);
        });

        renderPaginationControls(totalPages, currentPage);
    }

    function renderPaginationControls(totalPages, current) {
        const container = document.getElementById("paginationControls");
        container.innerHTML = "";
        
        if (totalPages === 0) return;

        // Prev Button
        const prevBtn = document.createElement("button");
        prevBtn.className = "ent-page-btn";
        prevBtn.setAttribute("aria-label", "Previous Page");
        // Robust Chevron Left
        prevBtn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18 l -6 -6 6 -6"/></svg>`;
        prevBtn.disabled = current === 1;
        prevBtn.onclick = () => { if(currentPage > 1) { currentPage--; renderTable(); } };
        container.appendChild(prevBtn);

        // Page Numbers (Smart Logic)
        let pagesToShow = [];
        if (totalPages <= 7) {
            pagesToShow = Array.from({length: totalPages}, (_, i) => i + 1);
        } else {
             if (current <= 4) {
                 pagesToShow = [1, 2, 3, 4, 5, "...", totalPages];
             } else if (current >= totalPages - 3) {
                 pagesToShow = [1, "...", totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
             } else {
                 pagesToShow = [1, "...", current - 1, current, current + 1, "...", totalPages];
             }
        }

        pagesToShow.forEach(p => {
            const btn = document.createElement("button");
            btn.className = `ent-page-btn ${p === current ? 'active' : ''}`;
            btn.textContent = p;
            if (p === "...") {
                btn.disabled = true;
                btn.style.border = "none";
                btn.style.background = "transparent";
            } else {
                btn.onclick = () => { currentPage = p; renderTable(); };
            }
            container.appendChild(btn);
        });

        // Next Button
        const nextBtn = document.createElement("button");
        nextBtn.className = "ent-page-btn";
        nextBtn.setAttribute("aria-label", "Next Page");
        // Robust Chevron Right
        nextBtn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18 l 6 -6 -6 -6"/></svg>`;
        nextBtn.disabled = current === totalPages;
        nextBtn.onclick = () => { if(currentPage < totalPages) { currentPage++; renderTable(); } };
        container.appendChild(nextBtn);
    }

    function animateValue(id, start, end, duration) {
      const obj = document.getElementById(id);
      let startTimestamp = null;
      const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        obj.textContent = `${window.CURRENCY_SYMBOL}${value.toLocaleString()}`;
        if (progress < 1) {
          window.requestAnimationFrame(step);
        }
      };
      window.requestAnimationFrame(step);
    }

    function updateInsight(id, income, expense) {
      const badge = document.getElementById(id);
      if (!badge) return;

      const balance = income - expense;
      let text = "No Activity";
      let type = "";

      if (income === 0 && expense === 0) {
        text = "No Activity";
      } else if (balance >= 0) {
        const savingsRate = income > 0 ? Math.round((balance / income) * 100) : 0;

        // Qualitative Context
        let quality = "Positive";
        if (savingsRate >= 50) quality = "Exceptional";
        else if (savingsRate >= 30) quality = "Strong";
        else if (savingsRate >= 10) quality = "Healthy";

        text = `${quality} • ${savingsRate}% Saved`;
        type = "positive";
      } else {
        // Deficit Context
        text = "Deficit • Overspending";
        type = "negative";
      }

      badge.textContent = text;
      badge.className = "insight-badge"; // reset
      if (type) badge.classList.add(type);
    }

    function loadSummary() {
      fetch("php/fetch_summary.php")
        .then(res => res.text())
        .then(text => {
          try {
            return JSON.parse(text);
          } catch (e) {
            console.error("Server Error (loadSummary):", text);
            throw new Error("Invalid JSON response from server");
          }
        })
        .then(data => {
          console.log("SUMMARY DATA:", data);
          if (data.todayExpense !== undefined) {
            animateValue("today-exp", 0, data.todayExpense, 1000);
          }
          if (data.todayEarning !== undefined) {
            animateValue("today-inc", 0, data.todayEarning, 1000);
          }
          if (data.todayEarning !== undefined && data.todayExpense !== undefined) {
            animateValue("today-bal", 0, data.todayEarning - data.todayExpense, 1000);
            updateInsight("today-insight", data.todayEarning, data.todayExpense);
          }
          if (data.yearExpense !== undefined) {
            animateValue("year-exp", 0, data.yearExpense, 1000);
          }
          if (data.yearEarning !== undefined) {
            animateValue("year-inc", 0, data.yearEarning, 1000);
          }
          if (data.yearEarning !== undefined && data.yearExpense !== undefined) {
            animateValue("year-bal", 0, data.yearEarning - data.yearExpense, 1000);
            updateInsight("year-insight", data.yearEarning, data.yearExpense);
          }
          if (data.monthExpense !== undefined && data.monthEarning !== undefined) {
            animateValue("month-exp", 0, data.monthExpense, 1000);
            animateValue("month-inc", 0, data.monthEarning, 1000);
            animateValue("month-bal", 0, data.monthEarning - data.monthExpense, 1000);
            updateInsight("month-insight", data.monthEarning, data.monthExpense);
            renderMonthlyChart(data.monthEarning, data.monthExpense);
            if (Array.isArray(data.categoryExpense)) {
              const categories = data.categoryExpense.map(item => item.category);
              const values = data.categoryExpense.map(item => parseFloat(item.total));
              renderExpenseCategoryChart(categories, values);
            }
            if (Array.isArray(data.categoryIncome)) {
              const incomeCats = data.categoryIncome.map(item => item.category);
              const incomeVals = data.categoryIncome.map(item => parseFloat(item.total));
              renderIncomeCategoryChart(incomeCats, incomeVals);
            }
          }
        })
        .catch(err => {
          console.error("Error loading summary:", err);
        });
    }

    // --- Stock-Style Balance Chart ---
    function initBalanceChart() {
      const ctx = document.getElementById('balanceChart').getContext('2d');

      // Destroy existing chart if it exists to prevent canvas reuse errors
      if (window.balanceChart instanceof Chart) {
        window.balanceChart.destroy();
      }

      // 1. Create Soft Gradient
      const gradient = ctx.createLinearGradient(0, 0, 0, 400);
      gradient.addColorStop(0, 'rgba(20, 184, 166, 0.2)'); // Teal-500 (low opacity)
      gradient.addColorStop(1, 'rgba(20, 184, 166, 0)'); // Transparent

      // Popover Logic
      const popover = document.getElementById('chartPopover');
      const popLabel = document.getElementById('popLabel');
      const popValue = document.getElementById('popValue');
      const popDate = document.getElementById('popDate');

      function showPopover(context, eventData) {
        const {
          chart,
          element
        } = context;
        const rect = chart.canvas.getBoundingClientRect();

        // Calculate position relative to viewport
        const x = rect.left + element.x;
        const y = rect.top + element.y;

        popLabel.textContent = eventData.label;
        popValue.textContent = (eventData.amount > 0 ? '+' : '−') + window.CURRENCY_SYMBOL + Math.abs(eventData.amount).toLocaleString();
        popValue.className = 'popover-value ' + (eventData.amount > 0 ? 'pos' : 'neg');
        popDate.textContent = 'Tap to dismiss'; // Or actual date if available

        popover.style.left = `${x}px`;
        popover.style.top = `${y}px`;
        popover.style.display = 'block';

        // Simple dismiss logic on next click
        setTimeout(() => document.addEventListener('click', hidePopover, {
          once: true
        }), 10);
      }

      function hidePopover() {
        popover.style.display = 'none';
      }

      // Helper to create annotations from event data
      function createAnnotationsFromEvents(events, chartData) {
        const annotations = {};
        if (!events) return annotations;

        events.forEach((event, index) => {
          const dataIndex = chartData.labels.indexOf(event.x);
          if (dataIndex === -1) return;

          const yValue = chartData.datasets[0].data[dataIndex];

          // Point marker on the line
          annotations[`point-${index}`] = {
            type: 'point',
            xValue: event.x,
            yValue: yValue,
            backgroundColor: event.type === 'peak' ? '#10b981' : '#ef4444',
            radius: 5,
            borderColor: 'white',
            borderWidth: 2,
            drawTime: 'afterDatasetsDraw'
          };

          // Text label
          annotations[`label-${index}`] = {
            type: 'label',
            xValue: event.x,
            yValue: yValue,
            content: `${event.label} ${event.amount > 0 ? '+' : '−'}${window.CURRENCY_SYMBOL}${Math.abs(event.amount).toLocaleString()}`,
            font: {
              size: 11,
              weight: '600',
              family: 'sans-serif'
            },
            color: '#334155',
            backgroundColor: 'rgba(248, 250, 252, 0.85)', // Slate-50 with opacity
            padding: 6,
            cornerRadius: 4,
            yAdjust: event.type === 'peak' ? -25 : 25,
            // Subtle hover effect
            enter: (ctx) => {
              document.body.style.cursor = 'pointer';
              ctx.element.options.backgroundColor = 'rgba(248, 250, 252, 1)';
              return true;
            },
            leave: (ctx) => {
              document.body.style.cursor = 'default';
              ctx.element.options.backgroundColor = 'rgba(248, 250, 252, 0.85)';
              return true;
            },
            // Tap Interaction
            click: (ctx) => showPopover(ctx, event)
          };
        });
        return annotations;
      }

      // 2. Custom Plugin for Vertical Guide Line
      const verticalHoverLine = {
        id: 'verticalHoverLine',
        beforeDatasetsDraw: (chart) => {
          const {
            ctx,
            tooltip,
            chartArea: {
              top,
              bottom
            }
          } = chart;
          if (tooltip && tooltip._active && tooltip._active.length) {
            const activePoint = tooltip._active[0];
            const x = activePoint.element.x;
            ctx.save();
            ctx.beginPath();
            ctx.moveTo(x, top);
            ctx.lineTo(x, bottom);
            ctx.lineWidth = 1;
            ctx.strokeStyle = '#cbd5e1'; // Slate-300
            ctx.setLineDash([5, 5]); // Dashed guide line
            ctx.stroke();
            ctx.restore();
          }
        }
      };

      // 3. Mock Data Store with Previous Period Comparison
      // INJECTED FROM PHP:
      const chartData = <?php echo json_encode($analyticsData); ?>;

      // Default to Year
      const initialData = chartData['1Y'];
      const initialAnnotations = createAnnotationsFromEvents(initialData.events, {
        labels: initialData.labels,
        datasets: [{
          data: initialData.current
        }]
      });

      window.balanceChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: initialData.labels,
          datasets: [{
              label: 'Current',
              data: initialData.current,
              borderColor: '#0d9488', // Teal-700
              backgroundColor: gradient,
              borderWidth: 2.5,
              tension: initialData.tension,
              fill: true,
              pointRadius: 0,
              pointHoverRadius: 6,
              pointHoverBackgroundColor: '#ffffff',
              pointHoverBorderColor: '#0d9488',
              pointHoverBorderWidth: 3,
              borderJoinStyle: 'round'
            },
            {
              label: 'Previous',
              data: initialData.previous,
              borderColor: '#99f6e4', // Muted Teal-200
              borderDash: [5, 5], // Dashed line for comparison
              borderWidth: 2,
              fill: false, // No fill for comparison line
              tension: initialData.tension,
              pointRadius: 0,
              pointHoverRadius: 6,
              pointHoverBackgroundColor: '#ffffff',
              pointHoverBorderColor: '#99f6e4',
              pointHoverBorderWidth: 2,
              hidden: true // Initially hidden
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            annotation: {
              drawTime: 'afterDatasetsDraw',
              annotations: initialAnnotations
            },
            legend: {
              display: false
            }, // Hide legend
            tooltip: {
              enabled: true,
              backgroundColor: 'rgba(255, 255, 255, 0.95)', // High opacity white
              titleColor: '#64748b', // Muted slate for date
              bodyColor: '#0f172a', // Dark for value
              borderColor: '#e2e8f0', // Subtle border
              borderWidth: 1,
              padding: 12,
              cornerRadius: 8,
              displayColors: false, // Hide color box
              titleFont: {
                size: 12,
                weight: '500',
                family: 'sans-serif'
              },
              bodyFont: {
                size: 16,
                weight: '700',
                family: 'sans-serif'
              },
              footerFont: {
                size: 12,
                weight: '500',
                family: 'sans-serif'
              },
              footerColor: '#64748b',
              callbacks: {
                // We build the entire tooltip body here for unified display
                label: () => '', // Disable default label
                afterBody: function(tooltipItems) {
                  const current = tooltipItems.find(item => item.dataset.label === 'Current');
                  const previous = tooltipItems.find(item => item.dataset.label === 'Previous');

                  const formatCurrency = (val) => val.toLocaleString('en-IN', {
                    style: 'currency',
                    currency: 'INR',
                    maximumFractionDigits: 0
                  });

                  let lines = [`Current:  ${formatCurrency(current.parsed.y)}`];

                  if (previous) {
                    lines.push(`Previous: ${formatCurrency(previous.parsed.y)}`);
                  }
                  return lines;
                },
                footer: function(tooltipItems) {
                  if (tooltipItems.length < 2) return ''; // Only show diff if comparing
                  const current = tooltipItems.find(i => i.dataset.label === 'Current').parsed.y;
                  const previous = tooltipItems.find(i => i.dataset.label === 'Previous').parsed.y;
                  const diff = current - previous;
                  const sign = diff >= 0 ? '+' : '-';
                  return `Difference: ${sign}${Math.abs(diff).toLocaleString('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 })}`;
                }
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              }, // No vertical grid lines
              ticks: {
                color: '#94a3b8', // Muted gray labels
                font: {
                  size: 11
                }
              },
              border: {
                display: false
              } // No bold axis line
            },
            y: {
              grid: {
                color: '#f1f5f9', // Very light horizontal lines
                borderDash: [5, 5], // Dashed lines
                drawBorder: false
              },
              ticks: {
                color: '#94a3b8',
                font: {
                  size: 11
                },
                callback: function(value) {
                  return '₹' + (value >= 1000 ? (value / 1000).toFixed(1) + 'k' : value);
                }
              },
              border: {
                display: false
              }
            }
          }
        },
        plugins: [verticalHoverLine] // Register the vertical line plugin
      });

      // Time Range Button Logic
      const buttons = document.querySelectorAll('.time-btn');
      buttons.forEach(btn => {
        btn.onclick = (e) => {
          buttons.forEach(b => b.classList.remove('active'));
          e.target.classList.add('active');

          const range = e.target.dataset.range;
          const newData = chartData[range] || chartData['1Y'];

          if (window.balanceChart && newData) {
            window.balanceChart.data.labels = newData.labels;
            window.balanceChart.data.datasets[0].data = newData.current;
            window.balanceChart.data.datasets[1].data = newData.previous;
            window.balanceChart.data.datasets[0].tension = newData.tension;
            window.balanceChart.data.datasets[1].tension = newData.tension;
            // Update annotations
            window.balanceChart.options.plugins.annotation.annotations = createAnnotationsFromEvents(newData.events, {
              labels: newData.labels,
              datasets: [{
                data: newData.current
              }]
            });
            window.balanceChart.update();
            updateInsightsUI(range, newData, document.getElementById('compareToggle').checked);
          }
        };
      });

      // Comparison Toggle Logic
      const compareToggle = document.getElementById('compareToggle');
      compareToggle.addEventListener('change', (e) => {
        if (window.balanceChart) {
          window.balanceChart.setDatasetVisibility(1, e.target.checked); // Dataset at index 1 is 'Previous'
          window.balanceChart.update();
          const activeRange = document.querySelector('.time-btn.active').dataset.range;
          const currentData = chartData[activeRange];
          updateInsightsUI(activeRange, currentData, e.target.checked);
        }
      });
    }

    // --- Chart Styling & Config ---
    const chartCommonOptions = {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 800,
        easing: 'easeOutQuart'
      },
      plugins: {
        legend: {
          position: 'top',
          align: 'end',
          labels: {
            usePointStyle: true,
            pointStyle: 'circle',
            color: '#94a3b8',
            font: {
              size: 12,
              family: "sans-serif"
            },
            padding: 20
          }
        },
        tooltip: {
          backgroundColor: '#1e293b',
          titleColor: '#f8fafc',
          bodyColor: '#cbd5e1',
          borderColor: 'rgba(255, 255, 255, 0.1)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 8,
          displayColors: false
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#6b7280',
            font: {
              size: 11
            }
          }
        },
        y: {
          grid: {
            color: 'rgba(255, 255, 255, 0.05)',
            borderDash: [6, 6],
            drawBorder: false
          },
          ticks: {
            color: '#6b7280',
            font: {
              size: 11
            },
            padding: 10
          },
          beginAtZero: true
        }
      }
    };

    function renderMonthlyChart(income, expense) {
      const ctx = document.getElementById("incomeExpenseChart").getContext("2d");
      if (window.incomeExpenseChart instanceof Chart) {
        window.incomeExpenseChart.destroy(); // destroy old chart on reload
      }

      // Plugin to draw text in the center of the donut chart
      const centerTextPlugin = {
        id: 'centerText',
        afterDraw: (chart) => {
          const {
            ctx,
            data
          } = chart;
          const chartArea = chart.chartArea;
          if (!chartArea) return;

          const centerX = (chartArea.left + chartArea.right) / 2;
          const centerY = (chartArea.top + chartArea.bottom) / 2;

          const income = data.datasets[0].data[0] || 0;
          const expense = data.datasets[0].data[1] || 0;
          const netBalance = income - expense;

          // Main Value (Net Balance)
          ctx.save();
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          ctx.font = '600 1.75rem sans-serif';
          ctx.fillStyle = netBalance >= 0 ? '#059669' : '#dc2626';
          ctx.fillText('₹' + netBalance.toLocaleString('en-IN'), centerX, centerY - 8);

          // Sub-label
          ctx.font = '500 0.8rem sans-serif';
          ctx.fillStyle = '#64748b';
          ctx.fillText('Net Balance', centerX, centerY + 18);
          ctx.restore();
        }
      };

      window.incomeExpenseChart = new Chart(ctx, {
        type: "doughnut",
        data: {
          labels: ["Income", "Expense"],
          datasets: [{
            label: 'Monthly Overview',
            data: [income, expense],
            backgroundColor: [
              '#10b981', // Emerald-500 for Income
              '#e11d48' // Rose-600 for Expense
            ],
            borderColor: '#ffffff', // White border for separation
            borderWidth: 4,
            hoverOffset: 8,
            hoverBorderColor: '#f8fafc'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "80%", // Increase cutout for a thinner, more modern look
          animation: {
            animateScale: true,
            animateRotate: true
          },
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                usePointStyle: true,
                pointStyle: 'rectRounded',
                color: "#94a3b8",
                padding: 15,
                font: {
                  size: 12,
                  weight: 500
                }
              }
            },
            tooltip: {
              ...chartCommonOptions.plugins.tooltip,
              enabled: true
            } // Ensure tooltips are on
          }
        },
        plugins: [centerTextPlugin] // Register the custom plugin
      });
    }

    function renderExpenseCategoryChart(categories, values) {
      const ctx = document.getElementById("expenseCategoryChart").getContext("2d");
      if (window.expenseCategoryChart instanceof Chart) {
        window.expenseCategoryChart.destroy();
      }

      // 1. Visual Hierarchy: Highlight the top expense
      // Top expense gets a strong "Alert" color, others are muted
      const bgColors = values.map((_, i) => i === 0 ? '#e11d48' : '#fecdd3'); // Rose-600 vs Rose-200
      const hoverColors = values.map((_, i) => i === 0 ? '#be123c' : '#fda4af'); // Darker on hover

      window.expenseCategoryChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: categories,
          datasets: [{
            label: "Expenses",
            data: values,
            backgroundColor: bgColors,
            hoverBackgroundColor: hoverColors,
            borderRadius: 8,
            borderSkipped: false, // Modern rounded look
            barThickness: 'flex',
            maxBarThickness: 40,
            minBarLength: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: 1000,
            easing: 'easeOutQuart'
          },
          plugins: {
            legend: {
              display: false
            }, // Remove noise, title is enough
            tooltip: {
              backgroundColor: '#ffffff',
              titleColor: '#1e293b',
              bodyColor: '#64748b',
              borderColor: '#e2e8f0',
              borderWidth: 1,
              padding: 12,
              cornerRadius: 8,
              displayColors: false,
              callbacks: {
                label: (context) => '₹' + context.parsed.y.toLocaleString('en-IN'),
                afterLabel: (context) => context.dataIndex === 0 ? '⚠️ Highest Spend' : ''
              },
              titleFont: {
                size: 13,
                weight: 'bold',
                family: 'sans-serif'
              },
              bodyFont: {
                size: 13,
                family: 'sans-serif'
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: false,
                drawBorder: false
              },
              ticks: {
                color: '#64748b',
                font: {
                  size: 11,
                  weight: 500
                }
              }
            },
            y: {
              grid: {
                color: '#f1f5f9',
                borderDash: [4, 4],
                drawBorder: false
              },
              ticks: {
                color: '#94a3b8',
                font: {
                  size: 10
                },
                callback: (val) => val >= 1000 ? '₹' + (val / 1000).toFixed(0) + 'k' : val
              },
              border: {
                display: false
              }
            }
          }
        }
      });
    }

    function renderIncomeCategoryChart(categories, values) {
      const ctx = document.getElementById("incomeCategoryChart").getContext("2d");
      if (window.incomeCategoryChart instanceof Chart) {
        window.incomeCategoryChart.destroy();
      }

      // 1. Calculate Total for Percentage Share
      const totalIncome = values.reduce((a, b) => a + b, 0);

      // 2. Visual Hierarchy: Highlight primary income source
      // Primary gets strong Emerald, others get soft Emerald to show concentration
      const bgColors = values.map((_, i) => i === 0 ? '#10b981' : '#a7f3d0'); // Emerald-500 vs Emerald-200
      const hoverColors = values.map((_, i) => i === 0 ? '#059669' : '#6ee7b7');

      window.incomeCategoryChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: categories,
          datasets: [{
            label: "Income",
            data: values,
            backgroundColor: bgColors,
            hoverBackgroundColor: hoverColors,
            borderRadius: 8,
            borderSkipped: false,
            barThickness: 'flex',
            maxBarThickness: 40,
            minBarLength: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: {
            duration: 1000,
            easing: 'easeOutQuart'
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: '#ffffff',
              titleColor: '#1e293b',
              bodyColor: '#64748b',
              borderColor: '#e2e8f0',
              borderWidth: 1,
              padding: 12,
              cornerRadius: 8,
              displayColors: false,
              callbacks: {
                label: (context) => '₹' + context.parsed.y.toLocaleString('en-IN'),
                afterLabel: (context) => {
                  const pct = totalIncome > 0 ? (context.parsed.y / totalIncome * 100).toFixed(0) : 0;
                  return context.dataIndex === 0 ? `🏆 Primary (${pct}%)` : `Contribution: ${pct}%`;
                }
              },
              titleFont: {
                size: 13,
                weight: 'bold',
                family: 'sans-serif'
              },
              bodyFont: {
                size: 13,
                family: 'sans-serif'
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: false,
                drawBorder: false
              },
              ticks: {
                color: '#64748b',
                font: {
                  size: 11,
                  weight: 500
                }
              }
            },
            y: {
              grid: {
                color: '#f1f5f9',
                borderDash: [4, 4],
                drawBorder: false
              },
              ticks: {
                color: '#94a3b8',
                font: {
                  size: 10
                },
                callback: (val) => val >= 1000 ? '₹' + (val / 1000).toFixed(0) + 'k' : val
              },
              border: {
                display: false
              }
            }
          }
        }
      });
    }

    // --- Modal Accessibility & Logic ---
    const modal = document.getElementById("calendarModal");
    let lastFocusedElement;

    function openModal() {
      lastFocusedElement = document.activeElement;
      modal.style.display = "flex";
      // Small delay to allow display:flex to apply before adding class for transition
      requestAnimationFrame(() => {
        modal.classList.add("show");
        // Focus the visible input
        const visibleInput = modal.querySelector('input:not([style*="none"]), select:not([style*="none"])');
        if (visibleInput) visibleInput.focus();
      });
    }

    function closeModal() {
      modal.classList.remove("show");
      setTimeout(() => {
        modal.style.display = "none";
        if (lastFocusedElement) lastFocusedElement.focus();
      }, 200); // Match CSS transition duration
    }

    // Focus Trap
    modal.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeModal();
      }
      if (e.key === 'Tab') {
        const focusableContent = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        const firstFocusableElement = focusableContent[0];
        const lastFocusableElement = focusableContent[focusableContent.length - 1];

        if (e.shiftKey) { // Shift + Tab
          if (document.activeElement === firstFocusableElement) {
            lastFocusableElement.focus();
            e.preventDefault();
          }
        } else { // Tab
          if (document.activeElement === lastFocusableElement) {
            firstFocusableElement.focus();
            e.preventDefault();
          }
        }
      }
    });

    document.getElementById("openCalendar").addEventListener("click", () => {
      // Reset to default date picker view
      document.getElementById("modalTitle").textContent = "Select a date";
      document.getElementById("datePicker").style.display = "block";
      document.getElementById("monthPicker").style.display = "none";
      document.getElementById("yearPicker").style.display = "none";
      openModal();
    });
    document.getElementById("closeCalendar").addEventListener("click", () => {
      closeModal();
    });
    
    document.getElementById("goToDateBtn").addEventListener("click", () => {
      if (activeEntRange === 'month') {
          const val = document.getElementById("monthPicker").value;
          if (!val) return;
          currentFetchParams = { month: val };
          const [y, m] = val.split('-');
          const d = new Date(y, m - 1);
          document.getElementById("entDateLabel").textContent = d.toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });
          fetchEntries(currentFetchParams);
      } else if (activeEntRange === 'year') {
          const val = document.getElementById("yearPicker").value;
          if (!val) return;
          currentFetchParams = { year: val };
          document.getElementById("entDateLabel").textContent = val;
          fetchEntries(currentFetchParams);
      } else {
          // Fallback / Custom Date logic (covers 'day' and 'today')
          const selected = document.getElementById("datePicker").value;
          if (!selected) return;
          
          // If we were in 'today' mode, switch to 'day' mode visually
          if (activeEntRange === 'today') {
             document.querySelectorAll('.ent-tab[data-range]').forEach(t => t.classList.remove('active'));
             document.querySelector('.ent-tab[data-range="day"]').classList.add('active');
             activeEntRange = 'day';
          }

          document.getElementById("entDateLabel").textContent = formatDisplayDate(selected);
          fetchEntries({ date: selected });
          // Sync date strip
          const match = [...document.querySelectorAll(".date-box")].find(box => box.dataset.date === selected);
          if (match) {
            document.querySelectorAll(".date-box").forEach(d => d.classList.remove("active"));
            match.classList.add("active");
            match.scrollIntoView({ behavior: "smooth", inline: "center" });
          }
      }
      closeModal();
    });

    function renderCategoryBarChart(categories, values, type = "expense") {
      const ctx = document.getElementById("categoryBarChart").getContext("2d");
      if (window.categoryBarChart instanceof Chart) {
        window.categoryBarChart.destroy(); // avoid duplicates
      }
      window.categoryBarChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: categories,
          datasets: [{
            label: `${type === "income" ? "Top Income Sources" : "Top Expenses"}`,
            data: values,
            backgroundColor: type === "income" ? "#22c55e" : "#ef4444",
            borderRadius: 6
          }]
        },
        options: {
          responsive: true,
          scales: {
            x: {
              ticks: {
                color: "#cbd5e1"
              }
            },
            y: {
              beginAtZero: true,
              ticks: {
                color: "#cbd5e1"
              }
            }
          },
          plugins: {
            legend: {
              display: true,
              labels: {
                color: "#e2e8f0",
                font: {
                  size: 14
                }
              }
            }
          }
        }
      });
    }

    // --- INLINE FILTER SUMMARY LOGIC ---
    function renderFilterSummary() {
      const summaryContainer = document.getElementById('filterSummary');
      const activeFilters = [];

      // 1. Category
      if (selectedCategories.length > 0) {
        if (selectedCategories.length <= 3) {
            activeFilters.push(selectedCategories.join(", "));
        } else {
            activeFilters.push(selectedCategories.length + " Categories");
        }
      }

      // 2. Type
      if (selectedType && selectedType !== 'all') {
        activeFilters.push(selectedType.charAt(0).toUpperCase() + selectedType.slice(1));
      }

      // 3. Amount Range
      if (minAmount > 0 || maxAmount < 100000) {
        const formatK = (num) => num >= 1000 ? (num / 1000) + 'k' : num;
        activeFilters.push(`₹${formatK(minAmount)}–₹${formatK(maxAmount)}`);
      }

      if (activeFilters.length === 0) {
        summaryContainer.classList.remove('visible');
        summaryContainer.innerHTML = '';
        return;
      }

      let html = `<span class="filter-summary-label">Filtered by:</span>`;
      activeFilters.forEach((filter, index) => {
        html += `<span class="filter-summary-token">${filter}</span>`;
        if (index < activeFilters.length - 1) {
          html += `<span class="filter-summary-separator">·</span>`;
        }
      });
      html += `<span class="filter-summary-clear" id="inlineClearFilters">Clear</span>`;

      summaryContainer.innerHTML = html;
      summaryContainer.classList.add('visible');

      document.getElementById('inlineClearFilters').addEventListener('click', () => {
        document.getElementById('clearFiltersBtn').click();
      });
    }

    // --- REDESIGNED FILTER LOGIC ---
    function populateCategoryChips() {
      const container = document.getElementById("categoryChipsContainer");
      container.innerHTML = '';

      // STEP 1: Determine relevant categories based on selectedType
      let relevantCategories = [];
      if (selectedType === 'income') {
        relevantCategories = incomeGenres;
      } else if (selectedType === 'expense') {
        relevantCategories = expenseGenres;
      } else {
        // Show all unique categories
        relevantCategories = [...new Set([...incomeGenres, ...expenseGenres])];
      }

      // "All Categories" Chip (Visual reset for category selection)
      const allBtn = document.createElement("button");
      // Active if no specific categories are selected
      allBtn.className = `chip-modern ${selectedCategories.length === 0 ? 'active' : ''}`;
      allBtn.textContent = "All Categories";
      allBtn.dataset.category = "all";
      container.appendChild(allBtn);

      // Top 5 + More Logic
      const topCategories = relevantCategories.slice(0, 5);
      const remainingCategories = relevantCategories.slice(5);

      const createChip = (cat) => {
        const btn = document.createElement("button");
        // Check if this category is currently selected
        const isActive = selectedCategories.includes(cat);
        btn.className = `chip-modern ${isActive ? 'active' : ''}`;
        btn.textContent = cat;
        btn.dataset.category = cat;
        
        // Link type for internal logic (Step 1 requirement)
        if (incomeGenres.includes(cat)) btn.dataset.type = 'income';
        if (expenseGenres.includes(cat)) btn.dataset.type = 'expense';
        
        container.appendChild(btn);
      };

      topCategories.forEach(createChip);

      // "More" Button
      if (remainingCategories.length > 0) {
        const moreBtn = document.createElement("button");
        moreBtn.className = "chip-modern chip-more";
        moreBtn.textContent = `+ ${remainingCategories.length} More`;
        moreBtn.onclick = (e) => {
            e.preventDefault();
            moreBtn.remove();
            remainingCategories.forEach(createChip);
        };
        container.appendChild(moreBtn);
      }
    }
    document.getElementById("applyFiltersBtn").addEventListener("click", () => {
      fetchEntries(currentFetchParams);
      showToast("✅ Filters applied");
    });
    document.getElementById("clearFiltersBtn").addEventListener("click", () => {
      // Reset selected values
      selectedCategories = [];
      selectedType = "all";
      minAmount = 0;
      maxAmount = 100000;
      currentSort = 'date-desc';

      document.querySelectorAll(".segment-btn").forEach(b => b.classList.remove("active"));
      document.querySelector('.segment-btn[data-type="all"]').classList.add("active");

      // Reset sliders and labels
      document.getElementById("minAmount").value = 0;
      document.getElementById("maxAmount").value = 100000;
      document.getElementById("minVal").textContent = "0";
      document.getElementById("maxVal").textContent = "100000";

      // Reset Sort UI
      document.getElementById("sortLabel").textContent = "Sort: Date (Newest)";
      document.querySelectorAll(".sort-option").forEach(opt => opt.classList.remove("selected"));
      document.querySelector('.sort-option[data-sort="date-desc"]').classList.add("selected");

      // Reset Date to Today
      const today = new Date().toISOString().split("T")[0];
      currentFetchParams = { date: today };
      document.getElementById("entDateLabel").textContent = formatDisplayDate(today);

      // Reset UI
      populateCategoryChips(); // Re-render chips to default state
      renderFilterSummary();

      document.querySelectorAll(".date-box").forEach(d => d.classList.remove("active"));
      const todayBox = document.querySelector(`.date-box[data-date="${today}"]`);
      if (todayBox) todayBox.classList.add("active");
      document.querySelectorAll('.ent-tab[data-range]').forEach(t => t.classList.remove('active'));
      document.querySelector('.ent-tab[data-range="today"]').classList.add('active');
      
      activeEntRange = 'today';
      document.getElementById('entDateFilter').style.opacity = '0.5';
      document.getElementById('entDateFilter').style.cursor = 'default';

      fetchEntries(currentFetchParams);

      showToast("✅ Filters cleared");
    });

    // --- EXPORT FUNCTIONALITY ---
    async function exportToExcel() {
      if (!currentTableData || currentTableData.length === 0) {
        showToast("⚠️ No data to export", "error");
        return;
      }

      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Expense Report');

      // 1. Setup Columns
      worksheet.columns = [
        { key: 'date', width: 15 },
        { key: 'time', width: 12 },
        { key: 'category', width: 25 },
        { key: 'type', width: 12 },
        { key: 'amount', width: 18 },
        { key: 'desc', width: 40 },
      ];

      // 2. Header Section
      worksheet.mergeCells('A1:F1');
      const titleCell = worksheet.getCell('A1');
      titleCell.value = 'EXPENSE TRACKER REPORT';
      titleCell.font = { name: 'Arial', size: 16, bold: true };
      titleCell.alignment = { horizontal: 'center', vertical: 'middle' };

      // Metadata
      const reportType = activeEntRange.charAt(0).toUpperCase() + activeEntRange.slice(1);
      const period = document.getElementById("entDateLabel").textContent;
      const exportDate = new Date().toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' });

      const metaStyle = { name: 'Arial', size: 10, color: { argb: 'FF555555' } };
      worksheet.getCell('A2').value = `Report Type: ${reportType}`;
      worksheet.getCell('A2').font = metaStyle;
      worksheet.getCell('A3').value = `Period: ${period}`;
      worksheet.getCell('A3').font = metaStyle;
      worksheet.getCell('A4').value = `Exported On: ${exportDate}`;
      worksheet.getCell('A4').font = metaStyle;

      // 3. Summary Section
      let totalIncome = 0, totalExpense = 0;
      currentTableData.forEach(row => {
        const amt = parseFloat(row.amount);
        if (row.type === 'income') totalIncome += amt;
        else if (row.type === 'expense') totalExpense += amt;
      });
      const netBalance = totalIncome - totalExpense;

      // Summary Box Styling
      const summaryStartRow = 6;
      const boxBorder = { top: {style:'thin'}, left: {style:'thin'}, bottom: {style:'thin'}, right: {style:'thin'} };
      const boxFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF5F7FA' } };

      // Headers
      worksheet.getCell(`A${summaryStartRow}`).value = 'Label';
      worksheet.getCell(`B${summaryStartRow}`).value = 'Value';
      [worksheet.getCell(`A${summaryStartRow}`), worksheet.getCell(`B${summaryStartRow}`)].forEach(cell => {
        cell.font = { bold: true };
        cell.border = boxBorder;
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE0E0E0' } };
      });

      // Data
      const summaryData = [
        { label: 'Total Income', value: totalIncome, color: 'FF166534' }, // Green
        { label: 'Total Expense', value: totalExpense, color: 'FFDC2626' }, // Red
        { label: 'Net Balance', value: netBalance, color: netBalance >= 0 ? 'FF166534' : 'FFDC2626' }
      ];

      summaryData.forEach((item, idx) => {
        const r = summaryStartRow + 1 + idx;
        const labelCell = worksheet.getCell(`A${r}`);
        const valCell = worksheet.getCell(`B${r}`);
        
        labelCell.value = item.label;
        valCell.value = item.value;
        valCell.numFmt = '₹#,##0';
        
        labelCell.font = { bold: true };
        valCell.font = { bold: true, color: { argb: item.color } };
        
        [labelCell, valCell].forEach(c => { c.border = boxBorder; c.fill = boxFill; });
      });

      // 4. Transaction Table
      const tableHeadRowIdx = 11;
      const headerRow = worksheet.getRow(tableHeadRowIdx);
      headerRow.values = ['Date', 'Time', 'Category', 'Type', 'Amount (₹)', 'Description'];
      
      headerRow.eachCell(cell => {
        cell.font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF2E3440' } };
        cell.alignment = { horizontal: 'center', vertical: 'middle' };
        cell.border = boxBorder;
      });

      currentTableData.forEach((entry, idx) => {
        const rIdx = tableHeadRowIdx + 1 + idx;
        const row = worksheet.getRow(rIdx);
        
        // Parse Date manually to ensure "Wall Clock" time is preserved exactly in Excel.
        // We construct a Date object as UTC to prevent timezone offsets from shifting the displayed time.
        let datePart = entry.created_at.split(' ')[0];
        let timePart = entry.created_at.split(' ')[1] || '00:00:00';
        const [y, m, d] = datePart.split('-').map(Number);
        const [hr, min, sec] = timePart.split(':').map(Number);
        const dateObj = new Date(Date.UTC(y, m - 1, d, hr, min, sec));

        row.values = [
          dateObj,
          dateObj,
          entry.category,
          entry.type.charAt(0).toUpperCase() + entry.type.slice(1),
          parseFloat(entry.amount),
          entry.description || ''
        ];

        // Formatting
        row.getCell(1).numFmt = 'dd-mmm-yyyy';
        row.getCell(1).alignment = { horizontal: 'center' };
        
        row.getCell(2).numFmt = 'h:mm AM/PM';
        row.getCell(2).alignment = { horizontal: 'center' };
        
        row.getCell(3).alignment = { horizontal: 'left' };
        row.getCell(4).alignment = { horizontal: 'center' };
        
        row.getCell(5).numFmt = '₹#,##0';
        row.getCell(5).alignment = { horizontal: 'right' };
        row.getCell(5).font = { color: { argb: entry.type === 'income' ? 'FF166534' : 'FFDC2626' } };
        
        row.getCell(6).alignment = { wrapText: true, horizontal: 'left' };

        // Zebra Striping (Alternate rows light gray)
        if (idx % 2 !== 0) {
          row.eachCell({ includeEmpty: true }, cell => {
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF9FAFB' } };
          });
        }

        row.eachCell({ includeEmpty: true }, cell => {
          cell.border = boxBorder;
          cell.font = { ...cell.font, name: 'Arial', size: 11 };
        });
      });

      // 5. Enhancements
      worksheet.views = [{ state: 'frozen', xSplit: 0, ySplit: tableHeadRowIdx }];
      worksheet.autoFilter = {
        from: { row: tableHeadRowIdx, column: 1 },
        to: { row: tableHeadRowIdx, column: 6 }
      };

      // Save
      const buffer = await workbook.xlsx.writeBuffer();
      const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
      saveAs(blob, `ExpenseTracker_${activeEntRange}_${new Date().getTime()}.xlsx`);
    }

    document.getElementById('exportBtn').addEventListener('click', exportToExcel);

    // --- GLOBAL CLICK LISTENER FOR DYNAMIC ELEMENTS ---
    document.addEventListener("click", (e) => {
      // STEP 3: Category Selection Logic
      if (e.target.classList.contains("chip-modern")) {
        if (e.target.classList.contains("chip-more")) return; // Ignore 'More' button

        const cat = e.target.dataset.category;
        if (cat === 'all') {
            selectedCategories = [];
        } else {
            if (selectedCategories.includes(cat)) {
                selectedCategories = selectedCategories.filter(c => c !== cat);
            } else {
                selectedCategories.push(cat);
            }
        }
        
        // Update UI classes manually to avoid full re-render flicker
        document.querySelectorAll(".chip-modern").forEach(chip => {
            if (chip.dataset.category === 'all') {
                if (selectedCategories.length === 0) chip.classList.add("active");
                else chip.classList.remove("active");
            } else {
                if (selectedCategories.includes(chip.dataset.category)) chip.classList.add("active");
                else chip.classList.remove("active");
            }
        });
        
        renderFilterSummary();
      }

      // STEP 2: Type Toggle Logic
      if (e.target.classList.contains("segment-btn")) {
        document.querySelectorAll(".segment-btn").forEach(b => b.classList.remove("active"));
        e.target.classList.add("active");
        
        const newType = e.target.dataset.type;

        // Only update if type actually changed
        if (newType !== selectedType) {
            selectedType = newType;

            // Auto-clear invalid categories (Step 3 requirement)
            if (selectedType === 'income') {
                selectedCategories = selectedCategories.filter(c => incomeGenres.includes(c));
            } else if (selectedType === 'expense') {
                selectedCategories = selectedCategories.filter(c => expenseGenres.includes(c));
            }
            
            populateCategoryChips(); // Re-render chips for the new type
            renderFilterSummary();
        }
      }
    });

    // Amount slider sync
    document.getElementById("minAmount").addEventListener("input", (e) => {
      minAmount = parseInt(e.target.value);
      document.getElementById("minVal").textContent = minAmount;
    });
    document.getElementById("maxAmount").addEventListener("input", (e) => {
      maxAmount = parseInt(e.target.value);
      document.getElementById("maxVal").textContent = maxAmount;
    });

    // Amount Popover Logic
    const amountTrigger = document.getElementById("amountTrigger");
    const amountPopover = document.getElementById("amountPopover");
    
    amountTrigger.addEventListener("click", (e) => {
        e.stopPropagation();
        document.getElementById("sortPopover").classList.remove("show");
        document.getElementById("sortTrigger").classList.remove("active");
        amountPopover.classList.toggle("show");
        amountTrigger.classList.toggle("active");
    });
    document.addEventListener("click", (e) => {
        if (!amountPopover.contains(e.target) && !amountTrigger.contains(e.target)) {
            amountPopover.classList.remove("show");
            amountTrigger.classList.remove("active");
        }
    });

    // Sort Popover Logic
    const sortTrigger = document.getElementById("sortTrigger");
    const sortPopover = document.getElementById("sortPopover");
    const sortLabel = document.getElementById("sortLabel");
    const sortOptions = document.querySelectorAll(".sort-option");

    sortTrigger.addEventListener("click", (e) => {
        e.stopPropagation();
        document.getElementById("amountPopover").classList.remove("show");
        document.getElementById("amountTrigger").classList.remove("active");
        sortPopover.classList.toggle("show");
        sortTrigger.classList.toggle("active");
    });

    sortOptions.forEach(option => {
        option.addEventListener("click", (e) => {
            e.stopPropagation();
            sortOptions.forEach(opt => opt.classList.remove("selected"));
            option.classList.add("selected");
            currentSort = option.dataset.sort;
            
            let labelText = "Sort: ";
            if (currentSort === 'date-desc') labelText += "Date (Newest)";
            if (currentSort === 'date-asc') labelText += "Date (Oldest)";
            if (currentSort === 'amount-desc') labelText += "Amount (High to Low)";
            if (currentSort === 'amount-asc') labelText += "Amount (Low to High)";
            sortLabel.textContent = labelText;

            sortPopover.classList.remove("show");
            sortTrigger.classList.remove("active");
            fetchEntries(currentFetchParams);
        });
    });

    document.addEventListener("click", (e) => {
        if (!sortPopover.contains(e.target) && !sortTrigger.contains(e.target)) {
            sortPopover.classList.remove("show");
            sortTrigger.classList.remove("active");
        }
    });



    function showToast(message, type = "success", duration = 3000) {
      const container = document.getElementById("toast-container");
      if (!container) return;

      const toast = document.createElement("div");
      toast.className = `toast ${type}`;

      toast.innerHTML = `
        <span>${message}</span>
        <button aria-label="Close notification">&times;</button>
      `;

      container.appendChild(toast);

      const removeToast = () => {
        toast.style.animation = "fadeOut 0.3s ease forwards";
        setTimeout(() => toast.remove(), 300);
      };

      toast.querySelector("button").addEventListener("click", removeToast);
      setTimeout(removeToast, duration);
    }
  </script>
  <div id="toast-container" aria-live="polite" aria-atomic="true"></div>

</body>

</html>+