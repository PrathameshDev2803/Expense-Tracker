<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Predefined expense categories
$expenseGenres = ["Groceries", "Bills", "EMI", "Subscriptions", "Transport", "Shopping"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select Expense Categories</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <div class="form-container">
    <h2>Select Your Expense Categories</h2>
    <form action="select_expenses_process.php" method="POST">
      <?php foreach ($expenseGenres as $genre): ?>
        <label>
          <input type="checkbox" name="expense_genres[]" value="<?= $genre ?>">
          <?= $genre ?>
        </label><br>
      <?php endforeach; ?>

      <input type="text" name="custom_expense" placeholder="Add your own expense category (optional)" />

      <button type="submit">Finish Setup</button>
    </form>
  </div>
</body>
</html>
