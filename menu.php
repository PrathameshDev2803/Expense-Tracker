<?php
include_once __DIR__ . '/php/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Select Income Sources - Expense Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/menu.css" />
</head>
<body class="income-selection-page">
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
        <!-- Stack of coins icon matching reference design -->
        <svg class="badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <ellipse cx="12" cy="7" rx="8" ry="3.5" />
          <path d="M4 7v4.5c0 1.93 3.58 3.5 8 3.5s8-1.57 8-3.5V7" />
          <path d="M4 11.5v4.5c0 1.93 3.58 3.5 8 3.5s8-1.57 8-3.5v-4.5" />
        </svg>
      </div>
      <h1 class="main-heading">Select Your <span class="highlight-blue">Income Sources</span></h1>
      <p class="subtitle">Choose all the income sources that apply to you.</p>

      <!-- Step Progress Indicator -->
      <div class="progress-bar-container" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3" aria-label="Step 1 of 3">
        <span class="progress-segment active"></span>
        <span class="progress-segment"></span>
        <span class="progress-segment"></span>
      </div>
    </header>

    <!-- Income Source Cards Grid -->
    <div class="genres" role="group" aria-label="Income source selection cards">
      <!-- 1. Salary -->
      <div class="genre-card" data-genre="Salary" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Salary">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/salary.png" alt="Salary" />
        </div>
        <span class="primary-label">Salary</span>
        <span class="secondary-pill">Salary</span>
      </div>

      <!-- 2. Business -->
      <div class="genre-card" data-genre="Business" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Business">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/business.png" alt="Business" />
        </div>
        <span class="primary-label">Business</span>
        <span class="secondary-pill">Business</span>
      </div>

      <!-- 3. Freelancing -->
      <div class="genre-card" data-genre="Freelancing" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Freelancing">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/freelancing.png" alt="Freelancing" />
        </div>
        <span class="primary-label">Freelancing</span>
        <span class="secondary-pill">Freelancing</span>
      </div>

      <!-- 4. Investments -->
      <div class="genre-card" data-genre="Investments" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Investments">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/investments.png" alt="Investments" />
        </div>
        <span class="primary-label">Investments</span>
        <span class="secondary-pill">Investments</span>
      </div>

      <!-- 5. Rental Income -->
      <div class="genre-card" data-genre="Rental Income" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Rental Income">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/rental.png" alt="Rental Income" />
        </div>
        <span class="primary-label">Rental Income</span>
        <span class="secondary-pill">Rental Income</span>
      </div>

      <!-- 6. Side Hustles -->
      <div class="genre-card" data-genre="Side Hustles" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Side Hustles">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/side hustles.png" alt="Side Hustles" />
        </div>
        <span class="primary-label">Side Hustles</span>
        <span class="secondary-pill">Side Hustles</span>
      </div>

      <!-- 7. Dividend -->
      <div class="genre-card" data-genre="Dividend" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Dividend">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/dividend.png" alt="Dividend" />
        </div>
        <span class="primary-label">Dividend</span>
        <span class="secondary-pill">Dividend</span>
      </div>

      <!-- 8. Interest Income -->
      <div class="genre-card" data-genre="Interest Income" data-alias="Intrest Income" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Interest Income">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/intrest income.png" alt="Interest Income" />
        </div>
        <span class="primary-label">Interest Income</span>
        <span class="secondary-pill">Interest Income</span>
      </div>

      <!-- 9. Pension -->
      <div class="genre-card" data-genre="Pension" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Pension">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/pension.png" alt="Pension" />
        </div>
        <span class="primary-label">Pension</span>
        <span class="secondary-pill">Pension</span>
      </div>

      <!-- 10. Bitcoin / Digital Assets -->
      <div class="genre-card" data-genre="Digital Assets" data-alias="Bitcoin" role="checkbox" aria-checked="false" aria-pressed="false" tabindex="0" aria-label="Bitcoin Digital Assets">
        <div class="selection-indicator" aria-hidden="true">
          <svg class="check-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3.5 8.5 6.5 11.5 12.5 4.5" />
          </svg>
        </div>
        <div class="checkmark" style="display:none;">✔</div>
        <div class="illustration-wrapper">
          <img src="images/digital assets.png" alt="Bitcoin" />
        </div>
        <span class="primary-label">Bitcoin</span>
        <span class="secondary-pill">Digital Assets</span>
      </div>
    </div>

    <!-- Custom Source Section -->
    <section class="custom-genre-section" aria-label="Add custom income source">
      <div class="custom-input-group">
        <input type="text" id="customIncomeInput" placeholder="Enter custom source..." aria-label="Custom income source name" />
        <button type="button" id="addCustomGenre" aria-label="Add custom source">
          <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          <span>Add</span>
        </button>
      </div>
    </section>

    <!-- Container for dynamically added custom genres -->
    <div id="customIncomeContainer" class="custom-income-container" role="group" aria-label="Custom income sources"></div>

    <!-- Action Section -->
    <div class="action-section">
      <button type="button" class="next" aria-label="Continue to expense selection">
        <span>Next</span>
        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18" aria-hidden="true">
          <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </main>

<script>
  const genreCards = document.querySelectorAll('.genre-card');
  const nextButton = document.querySelector('.next');
  const customInput = document.getElementById('customIncomeInput');
  const addCustomBtn = document.getElementById('addCustomGenre');
  const customContainer = document.getElementById('customIncomeContainer');

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

  // Handle Adding Custom Genre
  function addCustomSource() {
    const value = customInput.value.trim();
    if (!value) {
      alert("Please enter a custom income source.");
      return;
    }

    // Sanitize display value
    const div = document.createElement('div');
    div.textContent = value;
    const sanitized = div.innerHTML;

    const card = document.createElement('div');
    card.className = 'genre-card selected'; // Auto-select new custom genres
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
      <div class="checkmark" style="display:none;">✔</div>
      <button type="button" class="remove-btn" aria-label="Remove ${sanitized}">&times;</button>
      <div class="illustration-wrapper">
        <img src="images/custom.png" alt="${sanitized}" />
      </div>
      <span class="primary-label">${sanitized}</span>
      <span class="secondary-pill">Custom</span>
    `;

    // Remove functionality
    card.querySelector('.remove-btn').addEventListener('click', (e) => {
      e.stopPropagation();
      card.remove();
    });

    bindCardEvents(card);
    customContainer.appendChild(card);
    customInput.value = '';
    customInput.focus();
  }

  addCustomBtn.addEventListener('click', addCustomSource);
  customInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      addCustomSource();
    }
  });

  // On next button click
  nextButton.addEventListener('click', () => {
    const selectedGenres = [];

    // Select all selected cards (both static and dynamic)
    document.querySelectorAll('.genre-card.selected').forEach(card => {
      selectedGenres.push(card.dataset.genre);
    });

    if (selectedGenres.length === 0) {
      alert("Please select at least one income source.");
      return;
    }

    localStorage.setItem("selectedIncomeGenres", JSON.stringify(selectedGenres));

    // Save to Backend
    fetch("php/save_income_genres.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          genres: selectedGenres
        })
      })
      .then(() => {
        window.location.href = "expense-genres.php";
      })
      .catch(err => {
        console.error("Error saving income genres:", err);
        window.location.href = "expense-genres.php";
      });
  });
</script>
</body>
</html>
