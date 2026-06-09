<?php
include 'php/auth_check.php';
include_once 'php/db.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Genre Selection</title>
  <link rel="stylesheet" href="css/menu.css" />
</head>
<body>
  <div class="container">
    <h1>Select Your Income Sources</h1>
    <div class="genres">
      <div class="genre-card" data-genre="Salary">
        <img src="images/salary.png" alt="Salary" />
        <span>Salary</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Business">
        <img src="images/business.png" alt="Business" />
        <span>Business</span>
      </div>
      <div class="genre-card" data-genre="Freelancing">
        <img src="images/freelancing.png" alt="Freelancing" />
        <span>Freelancing</span>
      </div>
      <div class="genre-card" data-genre="Investments">
        <img src="images/investments.png" alt="Investments" />
        <span>Investments</span>
      </div>
      <div class="genre-card" data-genre="Rental Income">
        <img src="images/rental.png" alt="Rental Income" />
        <span>Rental Income</span>
      </div>
      <div class="genre-card" data-genre="Side Hustles">
        <img src="images/side hustles.png" alt="Side Hustles" />
        <span>Side Hustles</span>
      </div>
      <div class="genre-card" data-genre="Dividend">
        <img src="images/dividend.png" alt="Dividend" />
        <span>Dividend</span>
      </div>
      <div class="genre-card" data-genre="Intrest Income">
        <img src="images/intrest income.png" alt="Intrest Income" />
        <span>Intrest Income</span>
      </div>
      <div class="genre-card" data-genre="Pension">
        <img src="images/pension.png" alt="Pension" />
        <span>Pension</span>
      </div>
      <div class="genre-card" data-genre="Digital Assets">
        <img src="images/digital assets.png" alt="Digital Assets" />
        <span>Digital Assets</span>
      </div>
      <!-- Custom Genre Section (Full Width) -->
      <div class="custom-genre-section" style="grid-column: 1 / -1; width: 100%; display: flex; flex-direction: column; align-items: center; margin-top: 20px; padding: 10px;">
        <div style="display: flex; gap: 10px; width: 100%; max-width: 300px;">
          <input type="text" id="customIncomeInput" placeholder="Enter custom source" style="flex: 1; padding: 10px; border-radius: 5px; border: none; outline: none;" />
          <button id="addCustomGenre" style="width: 40px; height: 40px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1.5rem; font-weight: bold; padding: 0;">+</button>
        </div>
      </div>
      <!-- Container for dynamically added custom genres -->
      <div id="customIncomeContainer" style="grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; width: 100%;"></div>
    </div>
    <button class="next">Next</button>
  </div>
<script>
  const genreCards = document.querySelectorAll('.genre-card');
  const nextButton = document.querySelector('.next');
  const customInput = document.getElementById('customIncomeInput');
  const addCustomBtn = document.getElementById('addCustomGenre');
  const customContainer = document.getElementById('customIncomeContainer');

  // Toggle selection for normal cards
  genreCards.forEach(card => {
    card.addEventListener('click', () => {
      card.classList.toggle('selected');
    });
  });

  // Handle Adding Custom Genre
  addCustomBtn.addEventListener('click', () => {
    const value = customInput.value.trim();
    if (!value) {
      alert("Please enter a custom income source.");
      return;
    }

    const card = document.createElement('div');
    card.className = 'genre-card selected'; // Auto-select new custom genres
    card.dataset.genre = value;
    card.innerHTML = `
      <img src="images/custom.png" alt="${value}" />
      <span>${value}</span>
      <div class="checkmark">✔</div>
      <button class="remove-btn" style="position: absolute; top: 5px; right: 5px; background: rgba(255,0,0,0.7); color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
    `;
    
    // Remove functionality
    card.querySelector('.remove-btn').addEventListener('click', (e) => {
      e.stopPropagation();
      card.remove();
    });

    customContainer.appendChild(card);
    customInput.value = ''; // Clear input
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
