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

// Function to insert expense into the database
function insertExpense($name, $amount, $budgetId)
{
    global $conn;

    $sql = "INSERT INTO expenses (name, amount, created_at, budget_id)
            VALUES ('$name', '$amount', NOW(), '$budgetId')";

    if ($conn->query($sql) === true) {
        return true;
    } else {
        return false;
    }
}

// Close database connection
$conn->close();
?>
