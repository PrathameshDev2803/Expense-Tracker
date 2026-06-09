<?php
include 'php/auth_check.php';
include_once 'php/db.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Expense Genres</title>
  <link rel="stylesheet" href="css/menu.css" />
</head>

<body>
  <div class="container">
    <h1>Select Your Expense Types</h1>

    <div class="genres">
      <div class="genre-card" data-genre="Groceries">
        <img src="images/groceries.png" alt="Groceries" />
        <span>Groceries</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Bills">
        <img src="images/bills.png" alt="Bills" />
        <span>Bills</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="EMI">
        <img src="images/emi.png" alt="EMI" />
        <span>EMI</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Subscriptions">
        <img src="images/subscriptions.png" alt="Subscriptions" />
        <span>Subscriptions</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Shopping">
        <img src="images/shopping.png" alt="Shopping" />
        <span>Shopping</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Travel">
        <img src="images/travel.png" alt="Travel" />
        <span>Travel</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Healthcare">
        <img src="images/healthcare.png" alt="Healthcare" />
        <span>Healthcare</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Education">
        <img src="images/education.png" alt="Education" />
        <span>Education</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Insurance">
        <img src="images/insurance.png" alt="Insurance" />
        <span>Insurance</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Diningout">
        <img src="images/diningout.png" alt="Dining Out" />
        <span>Dining Out</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Transportation">
        <img src="images/transportation.png" alt="Transportation" />
        <span>Transportation</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Investments">
        <img src="images/investmentsexpense.png" alt="Investments" />
        <span>Investments</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Pets">
        <img src="images/pets.png" alt="Pets" />
        <span>Pets</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Entertainment">
        <img src="images/entertainment.png" alt="Entertainment" />
        <span>Entertainment</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Householdsupplies">
        <img src="images/householdsupplies.png" alt="HouseHold Supplies" />
        <span>HouseHold Supplies</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Gifts">
        <img src="images/gifts.png" alt="Gifts" />
        <span>Gifts</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Childcare">
        <img src="images/childcare.png" alt="Childcare" />
        <span>Childcare</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Fitness">
        <img src="images/fitness.png" alt="Fitness" />
        <span>Fitness</span>
        <div class="checkmark">✔</div>
      </div>

      <div class="genre-card" data-genre="Charity">
        <img src="images/charity.png" alt="Charity" />
        <span>Charity</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Personal Care">
        <img src="images/personal care.png" alt="Personal Care" />
        <span>Personal Care</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Alcohol Smoking">
        <img src="images/alocohol smoking.png" alt="Alcohol Smoking" />
        <span>Alcohol Smoking</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Emergency">
        <img src="images/emergency.png" alt="Emergency" />
        <span>Emergency</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Maintainence">
        <img src="images/maintainence.png" alt="Maintainence" />
        <span>Maintainence</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Taxes">
        <img src="images/taxes.png" alt="Taxes" />
        <span>Taxes</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Clothing">
        <img src="images/clothing.png" alt="Clothing" />
        <span>Clothing</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Vacation">
        <img src="images/vacation.png" alt="Vacation" />
        <span>Vacation</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="genre-card" data-genre="Fuel">
        <img src="images/fuel.png" alt="Fuel" />
        <span>Fuel</span>
        <div class="checkmark">✔</div>
      </div>
      <div class="custom-genre-section">
        <div class="custom-flex">
          <img src="images/custom.png" alt="Custom Icon" style="width:60px; height:60px;" />
          <span>Custom</span>
        </div>
        <div class="custom-input-row">
          <input type="text" id="customExpenseInput" placeholder="Enter custom expense" />
          <button id="addCustomExpense">+</button>
        </div>
      </div>
      <div id="customExpenseContainer" class="genres"></div>

    </div>
    <button class="next">Finish</button>
  </div>

  <div id="toast-container" class="toast-container"></div>
  <!-- Toast Logic -->
  <script>
    function showToast(message, type = 'success', duration = 3000) {
      const container = document.getElementById('toast-container');
      
      // Create toast element
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      
      // Icon SVG based on type
      let iconSvg = '';
      if (type === 'success') {
        iconSvg = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>`;
      } else {
        iconSvg = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
      }

      toast.innerHTML = `
        <div class="toast-icon">${iconSvg}</div>
        <div class="toast-content">
          <div class="toast-title">${type === 'success' ? 'Success' : 'Attention'}</div>
          <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      `;

      container.appendChild(toast);

      // Auto remove
      setTimeout(() => {
        toast.classList.add('toast-exit');
        toast.addEventListener('transitionend', () => {
          toast.remove();
        });
      }, duration);
    }

    const genreCards = document.querySelectorAll('.genre-card');
    const customInput = document.getElementById('customExpenseInput');
    const addCustomBtn = document.getElementById('addCustomExpense');
    const customContainer = document.getElementById('customExpenseContainer');
    const nextButton = document.querySelector('.next');


    genreCards.forEach(card => {
      card.addEventListener('click', () => {
        card.classList.toggle('selected');

      });
    })

    addCustomBtn.addEventListener('click', () => {
      const value = customInput.value.trim();
      if (!value) {
        showToast("Enter a custom expense!", "error");
        return;
      }
      const card = document.createElement('div');
      card.className = 'genre-card selected';
      card.dataset.genre = value;


      card.innerHTML = `
    <button class="remove-btn" title="Remove">X</button>
    <img src="images/custom.png" alt="${value}" />
    <span>${value}</span>
    <div class="checkmark">✔</div>
  `;
      card.addEventListener('click', e => {
        if (!e.target.classList.contains('remove-btn')) {
          card.classList.toggle('selected');

        }
      });

      card.querySelector('.remove-btn').addEventListener('click', e => {
        e.stopPropagation();
        card.remove();
      });

      customContainer.appendChild(card);
      customInput.value = "";

    })

    nextButton.addEventListener('click', () => {
      const selectedGenres = [];
      const allGenreCards = document.querySelectorAll('.genre-card');

      allGenreCards.forEach(card => {
        if (card.classList.contains('selected')) {
          selectedGenres.push(card.dataset.genre);
        }
      });

      if (selectedGenres.length === 0) {
        showToast("Please select at least one expense type.", "error");
        return;
      }

      // Save to localStorage
      localStorage.setItem("selectedExpenseGenres", JSON.stringify(selectedGenres));

      // Retrieve income genres from localStorage
      const incomeGenres = JSON.parse(localStorage.getItem("selectedIncomeGenres") || "[]");

      // Ensure income genres exist, otherwise redirect back
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
          console.log(data);
          showToast("✅ Your expense selections are saved!", "success");
          
          // Delay redirect to let user see the toast
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