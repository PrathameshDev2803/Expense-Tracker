<!-- /php/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Expense Tracker</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <div class="form-container">
    <h2>Login</h2>
    <form action="login_process.php" method="POST">
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <p>New user? <a href="register.php">Create an account</a></p>
    </form>
  </div>
</body>
</html>
