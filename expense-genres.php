<?php
include_once __DIR__ . '/php/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Select Expense Types - Expense Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/menu.css') ?>" />
</head>
<body class="expense-selection-page genre-selection-page">
  <!-- Decorative Ambient Glows -->
  <div class="ambient-glow glow-top-left" aria-hidden="true"></div>
  <div class="ambient-glow glow-top-right" aria-hidden="true"></div>
  <div class="ambient-glow glow-bottom-left" aria-hidden="true"></div>
  <div class="ambient-glow glow-bottom-right" aria-hidden="true"></div>
  <div class="ambient-bubble bubble-tr" aria-hidden="true"></div>
  <div class="ambient-bubble bubble-bl" aria-hidden="true"></div>

  <main class="container">
    <!-- Header Section with icon badge, heading, subtitle, and progress bar -->
    <header class="header-section">
      <div class="header-badge" aria-hidden="true">
        <!-- Wallet / Expense Icon matching reference design -->
        <svg class="badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
          <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
          <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
        </svg>
      </div>
      <h1 class="main-heading">Select Your <span class="highlight-blue">Expense Types</span></h1>
      <p class="subtitle">Choose all the expense categories that apply to you.</p>

      <!-- Step Progress Indicator: Step 2 of 3 -->
      <div class="progress-bar-container" role="progressbar" aria-valuenow="2" aria-valuemin="1" aria-valuemax="3" aria-label="Step 2 of 3">
        <span class="progress-segment active"></span>
        <span class="progress-segment active"></span>
        <span class="progress-segment"></span>
      </div>
    </header>

    <!-- Expense Type Cards Grid -->
    <div class="genres" role="group" aria-label="Expense type selection cards">
      <!-- 1. Groceries -->
      <div class="genre-card" data-genre="Groceries" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Groceries">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/groceries.png" alt="Groceries" />
        </div>
        <span class="primary-label">Groceries</span>
        <span class="secondary-pill">Groceries</span>
      </div>

      <!-- 2. Bills -->
      <div class="genre-card" data-genre="Bills" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Bills">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/bills.png" alt="Bills" />
        </div>
        <span class="primary-label">Bills</span>
        <span class="secondary-pill">Bills</span>
      </div>

      <!-- 3. EMI -->
      <div class="genre-card" data-genre="EMI" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="EMI">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/emi.png" alt="EMI" />
        </div>
        <span class="primary-label">EMI</span>
        <span class="secondary-pill">EMI</span>
      </div>

      <!-- 4. Subscriptions -->
      <div class="genre-card" data-genre="Subscriptions" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Subscriptions">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/subscriptions.png" alt="Subscriptions" />
        </div>
        <span class="primary-label">Subscriptions</span>
        <span class="secondary-pill">Subscriptions</span>
      </div>

      <!-- 5. Shopping -->
      <div class="genre-card" data-genre="Shopping" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Shopping">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/shopping.png" alt="Shopping" />
        </div>
        <span class="primary-label">Shopping</span>
        <span class="secondary-pill">Shopping</span>
      </div>

      <!-- 6. Travel -->
      <div class="genre-card" data-genre="Travel" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Travel">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/travel.png" alt="Travel" />
        </div>
        <span class="primary-label">Travel</span>
        <span class="secondary-pill">Travel</span>
      </div>

      <!-- 7. Healthcare -->
      <div class="genre-card" data-genre="Healthcare" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Healthcare">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/healthcare.png" alt="Healthcare" />
        </div>
        <span class="primary-label">Healthcare</span>
        <span class="secondary-pill">Healthcare</span>
      </div>

      <!-- 8. Education -->
      <div class="genre-card" data-genre="Education" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Education">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/education.png" alt="Education" />
        </div>
        <span class="primary-label">Education</span>
        <span class="secondary-pill">Education</span>
      </div>

      <!-- 9. Insurance -->
      <div class="genre-card" data-genre="Insurance" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Insurance">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/insurance.png" alt="Insurance" />
        </div>
        <span class="primary-label">Insurance</span>
        <span class="secondary-pill">Insurance</span>
      </div>

      <!-- 10. Dining Out -->
      <div class="genre-card" data-genre="Diningout" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Dining Out">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/diningout.png" alt="Dining Out" />
        </div>
        <span class="primary-label">Dining Out</span>
        <span class="secondary-pill">Dining Out</span>
      </div>

      <!-- 11. Transportation -->
      <div class="genre-card" data-genre="Transportation" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Transportation">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/transportation.png" alt="Transportation" />
        </div>
        <span class="primary-label">Transportation</span>
        <span class="secondary-pill">Transportation</span>
      </div>

      <!-- 12. Investments -->
      <div class="genre-card" data-genre="Investments" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Investments">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/investmentsexpense.png" alt="Investments" />
        </div>
        <span class="primary-label">Investments</span>
        <span class="secondary-pill">Investments</span>
      </div>

      <!-- 13. Pets -->
      <div class="genre-card" data-genre="Pets" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Pets">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/pets.png" alt="Pets" />
        </div>
        <span class="primary-label">Pets</span>
        <span class="secondary-pill">Pets</span>
      </div>

      <!-- 14. Entertainment -->
      <div class="genre-card" data-genre="Entertainment" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Entertainment">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/entertainment.png" alt="Entertainment" />
        </div>
        <span class="primary-label">Entertainment</span>
        <span class="secondary-pill">Entertainment</span>
      </div>

      <!-- 15. Household Supplies -->
      <div class="genre-card" data-genre="Householdsupplies" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Household Supplies">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/householdsupplies.png" alt="Household Supplies" />
        </div>
        <span class="primary-label">Household Supplies</span>
        <span class="secondary-pill">Household Supplies</span>
      </div>

      <!-- 16. Gifts -->
      <div class="genre-card" data-genre="Gifts" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Gifts">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/gifts.png" alt="Gifts" />
        </div>
        <span class="primary-label">Gifts</span>
        <span class="secondary-pill">Gifts</span>
      </div>

      <!-- 17. Childcare -->
      <div class="genre-card" data-genre="Childcare" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Childcare">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/childcare.png" alt="Childcare" />
        </div>
        <span class="primary-label">Childcare</span>
        <span class="secondary-pill">Childcare</span>
      </div>

      <!-- 18. Fitness -->
      <div class="genre-card" data-genre="Fitness" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Fitness">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/fitness.png" alt="Fitness" />
        </div>
        <span class="primary-label">Fitness</span>
        <span class="secondary-pill">Fitness</span>
      </div>

      <!-- 19. Charity -->
      <div class="genre-card" data-genre="Charity" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Charity">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/charity.png" alt="Charity" />
        </div>
        <span class="primary-label">Charity</span>
        <span class="secondary-pill">Charity</span>
      </div>

      <!-- 20. Personal Care -->
      <div class="genre-card" data-genre="Personal Care" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Personal Care">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/personal care.png" alt="Personal Care" />
        </div>
        <span class="primary-label">Personal Care</span>
        <span class="secondary-pill">Personal Care</span>
      </div>

      <!-- 21. Alcohol Smoking -->
      <div class="genre-card" data-genre="Alcohol Smoking" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Alcohol Smoking">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/alocohol smoking.png" alt="Alcohol Smoking" />
        </div>
        <span class="primary-label">Alcohol Smoking</span>
        <span class="secondary-pill">Alcohol Smoking</span>
      </div>

      <!-- 22. Emergency -->
      <div class="genre-card" data-genre="Emergency" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Emergency">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/emergency.png" alt="Emergency" />
        </div>
        <span class="primary-label">Emergency</span>
        <span class="secondary-pill">Emergency</span>
      </div>

      <!-- 23. Maintenance -->
      <div class="genre-card" data-genre="Maintainence" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Maintenance">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/maintainence.png" alt="Maintenance" />
        </div>
        <span class="primary-label">Maintenance</span>
        <span class="secondary-pill">Maintenance</span>
      </div>

      <!-- 24. Taxes -->
      <div class="genre-card" data-genre="Taxes" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Taxes">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/taxes.png" alt="Taxes" />
        </div>
        <span class="primary-label">Taxes</span>
        <span class="secondary-pill">Taxes</span>
      </div>

      <!-- 25. Clothing -->
      <div class="genre-card" data-genre="Clothing" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Clothing">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/clothing.png" alt="Clothing" />
        </div>
        <span class="primary-label">Clothing</span>
        <span class="secondary-pill">Clothing</span>
      </div>

      <!-- 26. Vacation -->
      <div class="genre-card" data-genre="Vacation" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Vacation">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/vacation.png" alt="Vacation" />
        </div>
        <span class="primary-label">Vacation</span>
        <span class="secondary-pill">Vacation</span>
      </div>

      <!-- 27. Fuel -->
      <div class="genre-card" data-genre="Fuel" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Fuel">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/fuel.png" alt="Fuel" />
        </div>
        <span class="primary-label">Fuel</span>
        <span class="secondary-pill">Fuel</span>
      </div>
    </div>

    <!-- Custom Expense Section - Compact Balanced Capsule -->
    <section class="custom-genre-section" aria-label="Add custom expense category">
      <div class="custom-input-group">
        <div class="input-leading-icon" aria-hidden="true">
          <!-- Pencil / Edit Icon -->
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
          </svg>
        </div>
        <div class="input-field-wrapper">
          <input type="text" id="customExpenseInput" placeholder="Enter custom expense..." aria-label="Custom expense category name" autocomplete="off" />
          <div class="char-reveal-layer" aria-hidden="true"></div>
          <div class="living-cursor-aura" aria-hidden="true"></div>
        </div>
        <div class="input-divider" aria-hidden="true"></div>
        <button type="button" id="addCustomExpense" class="btn-add-custom add-source-btn" aria-label="Add custom expense">
          <svg class="plus-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          <span>Add</span>
        </button>
      </div>
    </section>

    <!-- Action Section - Prominent Centered Finish Button -->
    <div class="action-section">
      <button type="button" class="next" aria-label="Finish and save selections">
        <span>Finish</span>
        <!-- Crisp right arrow SVG icon -->
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </button>
    </div>
  </main>

  <div id="toast-container" class="toast-container" aria-live="polite"></div>

<script>
  const genresContainer = document.querySelector('.genres');
  const genreCards = document.querySelectorAll('.genre-card');
  const nextButton = document.querySelector('.next');
  const customInput = document.getElementById('customExpenseInput');
  const addCustomBtn = document.getElementById('addCustomExpense');

  // Enhanced Modern Light Theme Toast Notification
  function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    let iconSvg = '';
    let titleText = 'Success';

    if (type === 'success') {
      titleText = 'Success';
      iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="9 12 11.5 14.5 15.5 9.5"></polyline></svg>`;
    } else if (type === 'error') {
      titleText = 'Attention';
      iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`;
    } else if (type === 'warning') {
      titleText = 'Warning';
      iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
    } else {
      titleText = 'Information';
      iconSvg = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
    }

    toast.innerHTML = `
      <div class="toast-icon">${iconSvg}</div>
      <div class="toast-content">
        <div class="toast-title">${titleText}</div>
        <div class="toast-message">${message}</div>
      </div>
      <button type="button" class="toast-close" aria-label="Close notification">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    `;

    function dismiss() {
      if (toast._isDismissing) return;
      toast._isDismissing = true;
      toast.classList.add('toast-exit');
      toast.addEventListener('transitionend', () => toast.remove(), { once: true });
      setTimeout(() => toast.remove(), 300);
    }

    toast.querySelector('.toast-close').addEventListener('click', dismiss);
    container.appendChild(toast);

    setTimeout(dismiss, duration);
  }

  // Center incomplete final row on 4-column desktop layout
  function updateGridRowCentering() {
    if (!genresContainer || window.innerWidth < 1024) return;
    const cards = Array.from(genresContainer.querySelectorAll('.genre-card'));
    cards.forEach(c => c.style.gridColumnStart = '');
    const remainder = cards.length % 4;
    if (remainder === 2 && cards.length > 2) {
      // 2 cards in last row: center across columns 2 & 3
      cards[cards.length - 2].style.gridColumnStart = '2';
    }
  }

  // FLIP (First, Last, Invert, Play) Grid Reflow Animation
  function flipReflow(container, mutationFn) {
    const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (isReducedMotion || !container) {
      const res = mutationFn();
      updateGridRowCentering();
      return res;
    }

    // 1. FIRST: Record starting bounding rects of all existing cards
    const existingCards = Array.from(container.querySelectorAll('.genre-card'));
    const firstRects = new Map();
    existingCards.forEach(card => {
      firstRects.set(card, card.getBoundingClientRect());
    });

    // 2. Perform DOM mutation (insert or remove)
    const result = mutationFn();

    // Update row centering before measuring final positions
    updateGridRowCentering();

    // 3. LAST: Measure new positions and invert
    existingCards.forEach(card => {
      if (!card.isConnected) return;
      const first = firstRects.get(card);
      if (!first) return;

      const last = card.getBoundingClientRect();
      const dx = first.left - last.left;
      const dy = first.top - last.top;

      if (dx !== 0 || dy !== 0) {
        // 4. INVERT
        card.style.transform = `translate(${dx}px, ${dy}px)`;
        card.style.transition = 'none';

        // 5. PLAY: 450ms cubic-bezier(0.22, 1, 0.36, 1)
        requestAnimationFrame(() => {
          card.style.transition = 'transform 450ms cubic-bezier(0.22, 1, 0.36, 1)';
          card.style.transform = '';
        });

        const onTransitionEnd = () => {
          card.style.transition = '';
          card.removeEventListener('transitionend', onTransitionEnd);
        };
        card.addEventListener('transitionend', onTransitionEnd);
      }
    });

    return result;
  }

  // Toggle selection state
  function toggleSelection(card) {
    const isSelected = card.classList.toggle('selected');
    card.setAttribute('aria-checked', isSelected ? 'true' : 'false');
    card.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
  }

  // Bind click & keyboard events to card
  function bindCardEvents(card) {
    card.addEventListener('click', (e) => {
      if (e.target.closest('.remove-btn')) return;
      toggleSelection(card);
    });

    card.addEventListener('keydown', (e) => {
      if (e.key === ' ' || e.key === 'Enter') {
        e.preventDefault();
        toggleSelection(card);
      }
    });
  }

  // Bind events for initial static cards
  genreCards.forEach(bindCardEvents);
  updateGridRowCentering();
  window.addEventListener('resize', updateGridRowCentering);

  // Remove custom source with smooth fade-and-collapse animation
  function removeCustomSource(card) {
    const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (isReducedMotion) {
      card.remove();
      updateGridRowCentering();
      return;
    }

    card.style.transition = 'opacity 200ms ease, transform 200ms cubic-bezier(0.4, 0, 1, 1)';
    card.style.opacity = '0';
    card.style.transform = 'scale(0.92)';
    card.style.pointerEvents = 'none';

    setTimeout(() => {
      flipReflow(genresContainer, () => {
        card.remove();
      });
    }, 200);
  }

  // Handle Adding Custom Expense Category with Seamless Grid Insertion
  let isAdding = false;

  function addCustomSource() {
    if (isAdding) return;

    const value = customInput.value.trim();
    if (!value) {
      showToast("Please enter a custom expense category.", "error");
      customInput.focus();
      return;
    }

    // Check for duplicates across all static and custom cards in the grid
    const existingCards = Array.from(genresContainer.querySelectorAll('.genre-card'));
    const isDuplicate = existingCards.some(c => (c.dataset.genre || '').trim().toLowerCase() === value.toLowerCase());
    if (isDuplicate) {
      showToast(`"${value}" is already in your expense categories.`, "error");
      customInput.focus();
      return;
    }

    isAdding = true;

    // 1. Subtle pressed state on Add button (scale 0.98), prevent duplicate submissions
    addCustomBtn.classList.add('btn-pressed');
    setTimeout(() => {
      addCustomBtn.classList.remove('btn-pressed');
      isAdding = false;
    }, 300);

    // Sanitize display value
    const div = document.createElement('div');
    div.textContent = value;
    const sanitized = div.innerHTML;

    // 2. Create new card matching existing design system with entrance animation
    const card = document.createElement('div');
    card.className = 'genre-card card-entering'; // Starts with card-entering animation
    card.dataset.genre = value;
    card.setAttribute('role', 'checkbox');
    card.setAttribute('aria-checked', 'true');
    card.setAttribute('aria-pressed', 'true');
    card.setAttribute('tabindex', '0');
    card.setAttribute('aria-label', value);

    card.innerHTML = `
      <div class="selection-indicator" aria-hidden="true">
        <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
        </svg>
      </div>
      <button type="button" class="remove-btn" aria-label="Remove ${sanitized}" title="Remove ${sanitized}">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
      <div class="checkmark" style="display:none;">✔</div>
      <div class="illustration-wrapper">
        <img src="images/custom.png" alt="${sanitized}" />
      </div>
      <span class="primary-label">${sanitized}</span>
      <span class="secondary-pill">Custom</span>
    `;

    // Remove button listener (separate from selection toggle)
    card.querySelector('.remove-btn').addEventListener('click', (e) => {
      e.stopPropagation();
      e.preventDefault();
      removeCustomSource(card);
    });

    bindCardEvents(card);

    // 3. Insert directly into existing grid with 450ms FLIP reflow
    flipReflow(genresContainer, () => {
      genresContainer.appendChild(card);
      return card;
    });

    // 4. Smooth 150-200ms transition into selected appearance
    requestAnimationFrame(() => {
      card.classList.add('selected');
    });

    // Remove entry animation class once 500ms animation completes
    setTimeout(() => {
      card.classList.remove('card-entering');
    }, 520);

    // Clear input and reset typing states
    customInput.value = '';
    prevInputValue = '';
    if (customGroup) {
      customGroup.classList.remove('has-value', 'is-typing');
    }
    if (cursorAura) {
      cursorAura.classList.remove('active-pulse');
    }
    updateCaretAura(false);

    // Return focus without unnecessary window scroll
    customInput.focus({ preventScroll: true });

    // Smoothly bring card into view only if outside viewport
    const cardRect = card.getBoundingClientRect();
    if (cardRect.bottom > window.innerHeight) {
      card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  addCustomBtn.addEventListener('click', addCustomSource);
  customInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      addCustomSource();
    }
  });

  // Premium Typing Experience: Living Cursor + Character Reveal + Focus Aura
  const customGroup = customInput ? customInput.closest('.custom-input-group') : null;
  const revealLayer = customGroup ? customGroup.querySelector('.char-reveal-layer') : null;
  const cursorAura = customGroup ? customGroup.querySelector('.living-cursor-aura') : null;

  let typingTimer = null;
  let prevInputValue = customInput ? customInput.value : '';
  let measureCanvas = null;

  function measureTextWidth(text) {
    if (!measureCanvas) {
      measureCanvas = document.createElement('canvas');
    }
    const ctx = measureCanvas.getContext('2d');
    const style = window.getComputedStyle(customInput);
    ctx.font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
    return ctx.measureText(text).width;
  }

  function updateCaretAura(pulse = false) {
    if (!cursorAura || !customInput) return;
    const caretPos = customInput.selectionStart || 0;
    const textBefore = customInput.value.substring(0, caretPos);
    const leftPos = measureTextWidth(textBefore) - customInput.scrollLeft;

    cursorAura.style.left = `${Math.max(0, leftPos)}px`;

    if (pulse && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      cursorAura.classList.remove('active-pulse');
      void cursorAura.offsetWidth; // Force reflow to replay animation
      cursorAura.classList.add('active-pulse');
    }
  }

  if (customInput && customGroup) {
    // Responsive Focus Aura on actual input focus
    customInput.addEventListener('focus', () => {
      updateCaretAura(false);
    });

    customInput.addEventListener('blur', () => {
      customGroup.classList.remove('is-typing');
      if (cursorAura) {
        cursorAura.classList.remove('active-pulse');
      }
    });

    // Caret tracking on selection / arrow navigation / scroll
    ['keyup', 'click', 'select'].forEach(evt => {
      customInput.addEventListener(evt, () => {
        updateCaretAura(false);
      });
    });

    customInput.addEventListener('scroll', () => {
      updateCaretAura(false);
    });

    // Coordinated typing event: Character Reveal + Living Cursor Pulse + Subtle Aura Glow
    customInput.addEventListener('input', (e) => {
      const currentVal = customInput.value;
      const isReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      // Update has-value state for Add button
      if (currentVal.trim().length > 0) {
        customGroup.classList.add('has-value');
      } else {
        customGroup.classList.remove('has-value');
      }

      // 1. Responsive Focus Aura: subtle glow pulse while actively typing
      customGroup.classList.add('is-typing');
      clearTimeout(typingTimer);
      typingTimer = setTimeout(() => {
        customGroup.classList.remove('is-typing');
      }, 200);

      // 2. Smooth Character Reveal (only for single typed characters; never on paste or delete)
      const caretPos = customInput.selectionStart || 0;
      const isSingleCharAdd = currentVal.length === prevInputValue.length + 1 &&
        (e.inputType === 'insertText' || (!e.inputType && e.data));

      if (isSingleCharAdd && !isReducedMotion && revealLayer) {
        const charIdx = Math.max(0, caretPos - 1);
        const typedChar = currentVal[charIdx] || e.data;

        // Animate newly typed non-whitespace glyph
        if (typedChar && typedChar.trim()) {
          const textBefore = currentVal.substring(0, charIdx);
          const charLeft = measureTextWidth(textBefore) - customInput.scrollLeft;

          if (charLeft >= 0 && charLeft <= customInput.clientWidth) {
            const charSpan = document.createElement('span');
            charSpan.className = 'char-reveal-particle';
            charSpan.textContent = typedChar;
            charSpan.style.left = `${charLeft}px`;
            revealLayer.appendChild(charSpan);

            setTimeout(() => {
              charSpan.remove();
            }, 110);
          }
        }
      }

      // 3. Living Cursor: subtle blue caret pulse at exact cursor coordinates
      updateCaretAura(true);

      prevInputValue = currentVal;
    });
  }

  // On Finish button click
  nextButton.addEventListener('click', () => {
    const selectedGenres = [];

    // Select all selected cards (both static and custom)
    document.querySelectorAll('.genre-card.selected').forEach(card => {
      selectedGenres.push(card.dataset.genre);
    });

    if (selectedGenres.length === 0) {
      showToast("Please select at least one expense type.", "error");
      return;
    }

    // Save to localStorage
    localStorage.setItem("selectedExpenseGenres", JSON.stringify(selectedGenres));

    // Retrieve income genres from localStorage
    const incomeGenres = JSON.parse(localStorage.getItem("selectedIncomeGenres") || "[]");

    // Ensure income genres exist, otherwise redirect back to menu.php
    if (incomeGenres.length === 0) {
      showToast("No income sources found. Please go back and select them.", "error");
      setTimeout(() => {
        window.location.href = "menu.php";
      }, 2000);
      return;
    }

    // Save to Backend
    fetch("php/save_expense_genres.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        genres: selectedGenres,
        income_genres: incomeGenres
      })
    })
    .then(res => {
      if (!res.ok) throw new Error("Network response was not OK");
      return res.json();
    })
    .then(data => {
      showToast("Your expense selections are saved!", "success");
      setTimeout(() => {
        window.location.href = "main.php";
      }, 1500);
    })
    .catch(err => {
      console.error("Fetch error:", err);
      showToast("Error saving preferences. Please try again.", "error");
    });
  });
</script>
</body>
</html>
