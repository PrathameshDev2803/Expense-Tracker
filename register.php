<?php
session_start();
include 'php/db.php';

if (isset($_SESSION["user_id"])) {
    $id = $_SESSION["user_id"];
    $check = $conn->query("SELECT id FROM users WHERE id = '$id'");
    if ($check && $check->num_rows > 0) {
        header("Location: main.php");
        exit();
    } else {
        session_unset();
        session_destroy();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Start Tracking - Expense Tracker</title>
  <meta name="description" content="Access your financial dashboard.">
  <link rel="stylesheet" href="css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  
  <div class="unified-surface">
    <div class="product-wrapper">
      
      <!-- CARD 1: Product Preview -->
      <section class="card product-card">
        <div class="product-preview-content">
          <h2 class="preview-title">Total visibility.</h2>
          <p class="preview-text">Your financial overview, simplified.</p>
          
          <div class="dashboard-mockup">
            <div class="mock-sidebar">
              <div class="mock-pill active"></div>
              <div class="mock-pill"></div>
              <div class="mock-pill"></div>
            </div>
            <div class="mock-body">
              <div class="mock-row-top">
                <div class="mock-block"></div>
                <div class="mock-avatar"></div>
              </div>
              <div class="mock-stats">
                  <div class="stat-card">
                     <div class="stat-label">Spent</div>
                     <div class="stat-val">$2,450</div>
                     <div class="stat-bar"><div class="fill"></div></div>
                  </div>
                  <div class="stat-card">
                     <div class="stat-label">Budget</div>
                     <div class="stat-val">$3,000</div>
                     <div class="stat-bar"><div class="fill"></div></div>
                  </div>
              </div>
              <div class="mock-list">
                  <div class="list-item">
                     <div class="icon-sq"></div>
                     <div class="text-lines"><div class="ln lg"></div><div class="ln sm"></div></div>
                  </div>
                  <div class="list-item">
                     <div class="icon-sq"></div>
                     <div class="text-lines"><div class="ln lg"></div><div class="ln sm"></div></div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- CARD 2: Registration -->
      <main class="card register-card">
        <div class="register-content">
          <header class="module-header">
            <h1>Create your account</h1>
            <p>Access your personal financial dashboard.</p>
          </header>

          <form id="register-form" class="product-form">
            <div class="module-group">
              <label for="username">Username</label>
              <input type="text" id="username" name="username" required autocomplete="username" />
              <div id="username-error" class="field-error" style="display: none;"></div>
            </div>

            <div class="module-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" required autocomplete="email" />
              <div id="email-error" class="field-error" style="display: none;"></div>
            </div>

            <div class="module-grid">
               <div class="module-group">
                 <label for="password">Password</label>
                 <div class="input-wrapper">
                   <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8" title="Must be at least 8 characters, with 1 uppercase letter and 1 special character." placeholder="Min 8 chars, 1 Upper, 1 Special" />
                   <button type="button" class="password-toggle-btn" data-target="password" aria-label="Toggle password">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                   </button>
                 </div>
                 <div id="password-error" class="field-error" style="display: none;"></div>
               </div>
               <div class="module-group">
                 <label for="confirm_password">Confirm</label>
                 <div class="input-wrapper">
                   <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" />
                   <div class="validation-status">
                      <svg class="icon-valid" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                   </div>
                 </div>
                 <div id="confirm_password-error" class="field-error" style="display: none;"></div>
               </div>
            </div>

            <button type="submit" class="btn-product" id="submit-btn">
              Create Account
            </button>

            <div class="module-footer">
               <span>Already have an account? <a href="login.php">Log in</a></span>
            </div>
          </form>
        </div>
      </main>

    </div>
  </div>

  <div id="toast-container" class="toast-container"></div>

  <script src="js/auth.js"></script>
</body>
</html>