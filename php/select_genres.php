<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Example predefined income genres
$incomeGenres = ["Salary", "Business", "Freelance", "Rental Income", "Investments"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select Income Sources</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <div class="form-container">
    <h2>Select Your Income Sources</h2>
    <form action="select_genres_process.php" method="POST">
      <?php foreach ($incomeGenres as $genre): ?>
        <label>
          <input type="checkbox" name="income_genres[]" value="<?= $genre ?>">
          <?= $genre ?>
        </label><br>
      <?php endforeach; ?>

      <input type="text" name="custom_income" placeholder="Add your own income source (optional)" />

      <button type="submit">Continue</button>
    </form>
  </div>
</body>
</html>
