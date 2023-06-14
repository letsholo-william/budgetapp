<?php
// Start the session and check if the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the expenses from the database for the logged-in user
$expenses = fetchExpensesFromDatabase($user_id);

// Function to fetch expenses from the database based on user ID
function fetchExpensesFromDatabase($user_id) {
    // Connect to your database (example: MySQLi)
    $mysqli = new mysqli("localhost", "root", "", "budget_tracker");

    // Check for connection errors
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
        // You can handle the connection error as per your application's requirements
        exit;
    }

    // Prepare and execute the SQL query to fetch expenses for the user
    $query = "SELECT * FROM expenses WHERE id = ?";
    $stmt = $mysqli->prepare($query);

    // Check for query preparation errors
    if (!$stmt) {
        echo "Error preparing query: " . $mysqli->error;
        // You can handle the query preparation error as per your application's requirements
        exit;
    }

    // Bind the ID parameter
    $stmt->bind_param("i", $user_id);

    // Check for binding errors
    if (!$stmt) {
        echo "Error binding parameters: " . $stmt->error;
        // You can handle the parameter binding error as per your application's requirements
        exit;
    }

    // Execute the query
    $stmt->execute();

    // Check for query execution errors
    if ($stmt->errno) {
        echo "Error executing query: " . $stmt->error;
        // You can handle the query execution error as per your application's requirements
        exit;
    }

    // Get the result set
    $result = $stmt->get_result();

    // Fetch expenses as an associative array
    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }

    // Close the statement and database connection
    $stmt->close();
    $mysqli->close();

    // Return the fetched expenses
    return $expenses;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <title>Budget App</title>
  <style>
    /* Set up page styles */
    body {
      background-color: #f2f2f2;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 960px;
      margin: 0 auto;
      padding: 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-gap: 20px;
    }

    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }

    .alert {
      padding: 20px;
      background-color: #f44336;
      color: white;
      border-radius: 5px;
      margin-bottom: 20px;
    }

    nav {
      background-color: #333;
      color: #fff;
      padding: 10px;
      grid-column: span 2;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    nav ul {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    nav ul li {
      margin-right: 10px;
    }

    nav a {
      text-decoration: none;
      color: #fff;
      transition: color 0.3s ease;
    }

    nav a:hover {
      color: #f2f2f2;
    }

    form {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      grid-column: span 2;
    }

    input[type="text"],
    input[type="number"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: none;
      border-radius: 5px;
      box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
    }

    input[type="submit"] {
      background-color: #333;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
      background-color: #555;
    }

    .expense-section {
      margin-bottom: 20px;
    }

    #expenses-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .expense-item {
      background-color: #fff;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 5px;
      box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .expense-item span {
      flex: 1;
    }

    .expense-item button {
      background-color: #f44336;
      color: #fff;
      border: none;
      padding: 5px 10px;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .expense-item button:hover {
      background-color: #d32f2f;
    }

    #remaining-budget {
      font-size: 24px;
      font-weight: bold;
      color: #333;
      text-align: center;
      grid-column: span 2;
      margin-bottom: 20px;
    }

    .danger {
      color: red;
      font-weight: bold;
    }

    /* Media queries */
    @media screen and (max-width: 800px) {
      .container {
        grid-template-columns: 1fr;
      }

      nav ul {
        flex-direction: column;
      }

      .menu-icon {
        display: block;
      }

      #remaining-budget {
        grid-column: auto;
      }
    }
  
    /* Animation styles */
    @keyframes fadeIn {
      0% {
        opacity: 0;
      }
      100% {
        opacity: 1;
      }
    }

    /* Add animation to elements */
    h1,
    form,
    .expense-item {
      animation: fadeIn 0.5s ease-in-out;
    }
    .expense-section {
    position: relative;
    text-align: center;
    margin-bottom: 20px;
  }

  .expense-section img {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    margin: auto;
    max-width: 100%;
    max-height: 100%;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    animation: slideIn 1s forwards;
    animation-delay: 4s;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(-100%);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
  </style>
</head>

<body>

<nav>
  <div class="container">
    <div class="menu-icon">&#9776;</div>
    <ul>
      <li><a href="get_budget_history.php">Budget Summary</a></li>
      <li><a href="download_summary.php">Share</a></li>
      <li><a href="logout.php">Logout</a></li>
      <li><a href="#">About</a></li>
    </ul>
  </div>
</nav>

  <div class="container">
    <div>
      <h1>Employee User: Budget Tracker</h1>
      <!-- Set Budget Form -->
<form method="POST" action="budget_expense_db.php">
    <label for="budgetAmount">Budget Amount:</label>
    <input type="number" name="budgetAmount" id="budgetAmount" required>

    <label for="taxRate">Tax Rate:</label>
    <input type="number" name="taxRate" id="taxRate" required>

    <button type="submit" name="set_budget">Set Budget</button>
</form>

<!-- Add Expense Form -->
<form method="POST" action="budget_expense_db.php">
    <input type="hidden" name="budget_id" value="1">

    <label for="expense_name">Expense Name:</label>
    <input type="text" name="expense_name" id="expense_name" required>

    <label for="expense_amount">Expense Amount:</label>
    <input type="number" name="expense_amount" id="expense_amount" required>

    <button type="submit" name="add_expense">Add Expense</button>
</form>
</div>
    </div>
  </div>

  <script>
  // Get references to the elements
  const budgetForm = document.querySelector("#budget-form");
  const budgetInput = document.querySelector("#budget-input");
  const taxInput = document.querySelector("#tax-input");
  const expenseForm = document.querySelector("#expense-form");
  const expenseNameInput = document.querySelector("#expense-name-input");
  const expenseAmountInput = document.querySelector("#expense-amount-input");
  const expensesList = document.querySelector(".expense-section ul");
  const remainingBudget = document.querySelector("#remaining-budget");
  const menuIcon = document.querySelector(".menu-icon");
  const menuItems = document.querySelector("nav ul");
  const myBudgetLink = document.querySelector('nav ul li:nth-child(2) a');

  // Initialize budget and expenses array
  let budget = 0;
  let taxRate = 0;
  let expenses = [];
  let remainingBudgetAmount = 0;
  let isBudgetSet = false;
  let expenses = <?php echo json_encode($expenses); ?>;

  // Initialize formatter for currency formatting
  const formatter = new Intl.NumberFormat("en-ZA", {
    style: "currency",
    currency: "ZAR",
  });

  // Function to calculate remaining budget
  const calculateRemainingBudget = () => {
    const taxedBudget = budget - (budget * (taxRate / 100));
    remainingBudgetAmount = taxedBudget - calculateTotalExpenses();
    remainingBudget.textContent = formatter.format(remainingBudgetAmount);

    if (remainingBudgetAmount < 0) {
      remainingBudget.classList.add("danger");
    } else {
      remainingBudget.classList.remove("danger");
    }
  };

  // Function to calculate total expenses
  const calculateTotalExpenses = () => {
    let totalExpenses = 0;
    expenses.forEach((expense) => {
      totalExpenses += expense.amount;
    });
    return totalExpenses;
  };

  // Function to render expenses
  const renderExpenses = () => {
    expensesList.innerHTML = "";
    expenses.forEach((expense) => {
      const expenseItem = document.createElement("li");
      expenseItem.className = "expense-item";
      expenseItem.innerHTML = `
        <span>${expense.name}</span>
        <span>${formatter.format(expense.amount)}</span>
        <button class="delete-expense" data-id="${expense.id}">X</button>
      `;
      expensesList.appendChild(expenseItem);
    });
  };

  // Function to show alert message for a specified duration at the top
  const showAlert = (message, duration = 3000) => {
    const alertElement = document.createElement("div");
    alertElement.className = "alert";
    alertElement.textContent = message;
    document.body.insertBefore(alertElement, document.body.firstChild);

    setTimeout(() => {
      alertElement.remove();
    }, duration);
  };
  // Event listener for budget form submission
budgetForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const enteredBudget = parseFloat(budgetInput.value);
  const enteredTaxRate = parseFloat(taxInput.value);
  if (
    isNaN(enteredBudget) ||
    enteredBudget <= 0 ||
    isNaN(enteredTaxRate) ||
    enteredTaxRate < 0
  ) {
    showAlert("Please enter a valid budget and tax rate.");
    return;
  }

  if (isBudgetSet) {
    showAlert("Budget already set. To update, please delete the existing budget first.");
    return;
  }

  // Insert the budget into the database
  fetch("budget_expense_db.php", {
    method: "POST",
    body: JSON.stringify({
      budgetAmount: enteredBudget,
      taxRate: enteredTaxRate,
    }),
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        isBudgetSet = true;
        budget = enteredBudget;
        taxRate = enteredTaxRate;
        calculateRemainingBudget(); // Update the remaining budget display
        showAlert("Budget set successfully.", "success");
      } else {
        showAlert("An error occurred while setting the budget. Please try again.");
      }
    })
    .catch(() => {
      showAlert("An error occurred while setting the budget. Please try again.");
    });
});
// Event listener for expense form submission
expenseForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const enteredExpenseName = expenseNameInput.value;
  const enteredExpenseAmount = parseFloat(expenseAmountInput.value);
  if (enteredExpenseName.trim().length === 0 || isNaN(enteredExpenseAmount) || enteredExpenseAmount <= 0) {
    showAlert("Please enter a valid expense name and amount.");
    return;
  }

  if (!isBudgetSet) {
    showAlert("Please set a budget first.");
    return;
  }

  // Insert the expense into the database
  fetch("budget_expense_db.php", {
    method: "POST",
    body: JSON.stringify({
      expenseName: enteredExpenseName,
      expenseAmount: enteredExpenseAmount,
    }),
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        remainingBudgetAmount -= enteredExpenseAmount;
        remainingBudget.textContent = formatter.format(remainingBudgetAmount);
        showAlert("Expense added successfully.", "success");
        renderExpenses(); // Optional: Refresh the expenses list after adding a new expense
      } else {
        showAlert("An error occurred while adding the expense. Please try again.");
      }
    })
    .catch(() => {
      showAlert("An error occurred while adding the expense. Please try again.");
    });
});
  // Add click event listener to the "My Budget" link
  myBudgetLink.addEventListener('click', () => {
  // Show the budget summary section
  document.getElementById('budget-summary').style.display = 'block';

  // Calculate and display the budget summary information
  const totalBudget = formatter.format(budget);
  const totalExpenses = formatter.format(calculateTotalExpenses());
  const remainingBudget = formatter.format(remainingBudgetAmount);

  document.getElementById('total-budget').textContent = `Total Budget: ${totalBudget}`;
  document.getElementById('total-expenses').textContent = `Total Expenses: ${totalExpenses}`;
  document.getElementById('remaining-budget').textContent = `Remaining Budget: ${remainingBudget}`;
});

  // Event listener for deleting an expense
  expensesList.addEventListener("click", (e) => {
    if (e.target.classList.contains("delete-expense")) {
      const expenseId = parseInt(e.target.dataset.id);
      expenses = expenses.filter((expense) => expense.id !== expenseId);
      renderExpenses();
      calculateRemainingBudget();
    }
  });
  function showBudgetSummary() {
  document.getElementById('budget-summary').style.display = 'block';

  // Retrieve and display the budget summary information
  const totalBudget = formatter.format(budget);
  const totalExpenses = formatter.format(calculateTotalExpenses());
  const remainingBudget = formatter.format(remainingBudgetAmount);

  document.getElementById('total-budget').textContent = `Total Budget: ${totalBudget}`;
  document.getElementById('total-expenses').textContent = `Total Expenses: ${totalExpenses}`;
  document.getElementById('remaining-budget').textContent = `Remaining Budget: ${remainingBudget}`;
}
function sendBudgetByEmail() {
  if (!isBudgetSet) {
    showAlert("Please set a budget first.");
    return;
  }

  // Send a request to the server to initiate the email sending process
  fetch("send_budget_email.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      budgetAmount: budget,
      taxRate: taxRate,
      expenses: expenses,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Budget sent via email.", "success");
      } else {
        showAlert("An error occurred while sending the budget via email. Please try again.");
      }
    })
    .catch(() => {
      showAlert("An error occurred while sending the budget via email. Please try again.");
    });
}

  // Event listener for toggling the menu
  menuIcon.addEventListener("click", () => {
    menuItems.classList.toggle("show");
  });
</script>
</body>
</html>
