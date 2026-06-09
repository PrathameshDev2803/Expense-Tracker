<!-- /php/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Expense Tracker</title>
  <link rel="stylesheet" href=".css//style.css">
</head>
<body>
  <div class="form-container">
    <h2>Register</h2>
    <form action="register_process.php" method="POST">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Create Account</button>
      <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
  </div>
</body>
</html>
