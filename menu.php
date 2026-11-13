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
  <link rel="stylesheet" href="<?= asset('css/menu.css') ?>" />
</head>
<body class="income-selection-page genre-selection-page">
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

    <!-- Custom Source Section - Compact Balanced Capsule -->
    <section class="custom-genre-section" aria-label="Add custom income source">
      <div class="custom-input-group">
        <div class="input-leading-icon" aria-hidden="true">
          <!-- Pencil / Edit Icon -->
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
          </svg>
        </div>
        <div class="input-field-wrapper">
          <input type="text" id="customIncomeInput" placeholder="Enter custom source..." aria-label="Custom income source name" autocomplete="off" />
          <div class="char-reveal-layer" aria-hidden="true"></div>
          <div class="living-cursor-aura" aria-hidden="true"></div>
        </div>
        <div class="input-divider" aria-hidden="true"></div>
        <button type="button" id="addCustomGenre" class="btn-add-custom add-source-btn" aria-label="Add custom source">
          <svg class="plus-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          <span>Add</span>
        </button>
      </div>
    </section>

    <!-- Action Section - Prominent Centered Next Button -->
    <div class="action-section">
      <button type="button" class="next" aria-label="Continue to expense selection">
        <span>Next</span>
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
  const genresContainer = document.querySelector('.genres');
  const genreCards = document.querySelectorAll('.genre-card');
  const nextButton = document.querySelector('.next');
  const customInput = document.getElementById('customIncomeInput');
  const addCustomBtn = document.getElementById('addCustomGenre');

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

  // Handle Adding Custom Genre with Seamless Grid Insertion
  let isAdding = false;

  function addCustomSource() {
    if (isAdding) return;

    const value = customInput.value.trim();
    if (!value) {
      showToast("Please enter a custom income source.", "error");
      customInput.focus();
      return;
    }

    // Check for duplicates across all static and custom cards in the grid
    const existingCards = Array.from(genresContainer.querySelectorAll('.genre-card'));
    const isDuplicate = existingCards.some(c => (c.dataset.genre || '').trim().toLowerCase() === value.toLowerCase());
    if (isDuplicate) {
      showToast(`"${value}" is already in your income sources.`, "error");
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

  // On next button click
  nextButton.addEventListener('click', () => {
    const selectedGenres = [];

    // Select all selected cards (both static and dynamic)
    document.querySelectorAll('.genre-card.selected').forEach(card => {
      selectedGenres.push(card.dataset.genre);
    });

    if (selectedGenres.length === 0) {
      showToast("Please select at least one income source.", "error");
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
