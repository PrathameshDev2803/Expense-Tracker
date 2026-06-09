<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="description" content="Take control of your finances with Expense Tracker. Simple, secure, and clear expense tracking." />
  <title>Expense Tracker - Financial Control, Simplified</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/start.css" />
</head>
<body>
  <main class="hero-container">
    <div class="hero-content">
      <div class="text-group">
        <h1 class="hero-headline">Financial Control,<br>Simplified.</h1>
        <p class="hero-subtext">
          Gain clarity over your spending. Track expenses, visualize your habits, and reach your financial goals with confidence and ease.
        </p>
      </div>

      <div class="cta-group">
        <a href="register.php" class="btn btn-primary" id="btn-start">Start Tracking Expenses</a>
        
        <div class="secondary-action">
          <span>Already have an account?</span>
          <a href="login.php" class="link-secondary" id="link-login">Log in</a>
        </div>
      </div>
    </div>

    <!-- Visual Cues / Decorative Elements -->
    <div class="visual-preview" aria-hidden="true">
      <div class="preview-backdrop-gradient"></div>
      
      <div class="expense-card card-1">
        <div class="card-icon icon-coffee">☕</div>
        <div class="card-info">
          <span class="card-title">Morning Coffee</span>
          <span class="card-meta">Today, 8:30 AM</span>
        </div>
        <span class="card-amount">-$4.50</span>
      </div>

      <div class="expense-card card-2">
        <div class="card-icon icon-grocery">🛒</div>
        <div class="card-info">
          <span class="card-title">Whole Foods Market</span>
          <span class="card-meta">Yesterday</span>
        </div>
        <span class="card-amount">-$124.80</span>
      </div>

      <div class="expense-card card-3">
        <div class="card-icon icon-subscription">🎵</div>
        <div class="card-info">
          <span class="card-title">Spotify Premium</span>
          <span class="card-meta">Monthly Sub</span>
        </div>
        <span class="card-amount">-$11.99</span>
      </div>
    </div>
  </main>
</body>
</html>
