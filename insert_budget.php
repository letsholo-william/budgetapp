<?php
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
function insertBudget($amount, $taxRate)
{
    global $conn;

    $sql = "INSERT INTO budget (amount, tax_rate, created_at)
            VALUES ('$amount', '$taxRate', NOW())";

    if ($conn->query($sql) === true) {
        return $conn->insert_id; // Return the generated budget ID
        echo $sql;
    } else {
        return $conn->error; // Return the database error message
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve budget amount and tax rate from the POST request
    $budgetAmount = $_POST["amount"];
    $taxRate = $_POST["tax_rate"];

    // Insert the budget into the database
    $insertedBudgetId = insertBudget($budgetAmount, $taxRate);
    if (is_numeric($insertedBudgetId)) {
        echo "Budget inserted successfully. ID: " . $insertedBudgetId;
    } else {
        echo "An error occurred while setting the budget: " . $insertedBudgetId;
    }
}

// Close database connection
$conn->close();
?>
