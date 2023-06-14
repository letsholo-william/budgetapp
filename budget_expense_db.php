<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budget_tracker";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to insert budget into the database
function insertBudget($user_id, $amount, $taxRate)
{
    global $conn;

    // Prepare the SQL statement with placeholders
    $stmt = $conn->prepare("INSERT INTO budgets (user_id, amount, tax_rate, created_at) VALUES (?, ?, ?, NOW())");

    // Bind the parameters to the placeholders
    $stmt->bind_param("ddd", $user_id, $amount, $taxRate);

    if ($stmt->execute()) {
        return $stmt->insert_id; // Return the generated budget ID
    } else {
        echo "Error inserting budget: " . $stmt->error;
        return false;
    }
}

// Function to insert expense into the database
function insertExpense($budgetId, $name, $amount)
{
    global $conn;

    // Prepare the SQL statement with placeholders
    $stmt = $conn->prepare("INSERT INTO expenses (budget_id, name, amount, created_at) VALUES (?, ?, ?, NOW())");

    if ($stmt) {
        // Bind the parameters to the placeholders
        $stmt->bind_param("iss", $budgetId, $name, $amount);

        if ($stmt->execute()) {
            return true;
        } else {
            echo "Error executing statement: " . $stmt->error;
            return false;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
        return false;
    }
}

// Check if "Set Budget" form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["set_budget"])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get values from the form submission
    $amount = $_POST["budgetAmount"];
    $taxRate = $_POST["taxRate"];

    // Insert budget into the database
    $budgetId = insertBudget($user_id, $amount, $taxRate);

    if ($budgetId) {
        // Display the success message as an alert using JavaScript
        echo '<script>alert("Budget set successfully!"); 
        setTimeout(function() { window.location.href = "budget.php"; }, 1000);</script>';
        exit(); // Use exit() to stop further execution
    } else {
        echo "Error setting budget!";
    }
}

// Check if "Add Expense" form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_expense"])) {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get values from the form submission
    $expenseName = $_POST["expense_name"];
    $expenseAmount = $_POST["expense_amount"];
    $budgetId = $_POST["budget_id"];

    // Insert expense for the budget with ID $budgetId
    $expenseInserted = insertExpense($budgetId, $expenseName, $expenseAmount);

    if ($expenseInserted) {
        echo '<script>alert("Expense added successfully!"); 
        setTimeout(function() { window.location.href = "budget.php"; }, 1000);</script>';
        exit(); // Use exit() to stop further execution
    } else {
        echo '<script>alert("Error adding expense!"); 
        setTimeout(function() { window.location.href = "budget.php"; }, 1000);</script>';
        exit(); // Use exit() to stop further execution
    }
}

// Close database connection
$conn->close();
?>
