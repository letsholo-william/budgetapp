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

// Function to generate the summary and remaining budget file
function generateSummaryFile()
{
    global $conn;

    // Get the user ID from the session
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    $user_id = $_SESSION['user_id'];

    // Retrieve the budget and expense data for the user from the database
    $budgetQuery = $conn->prepare("SELECT amount, tax_rate FROM budgets WHERE user_id = ?");
    $budgetQuery->bind_param("i", $user_id);
    $budgetQuery->execute();
    $budgetResult = $budgetQuery->get_result();

    $expenseQuery = $conn->prepare("SELECT name, amount FROM expenses WHERE budget_id = (SELECT MAX(id) FROM budgets WHERE user_id = ?)");
    $expenseQuery->bind_param("i", $user_id);
    $expenseQuery->execute();
    $expenseResult = $expenseQuery->get_result();

    // Prepare the summary content
    $summaryContent = "Summary of Budget and Expenses:\n\n";

    if ($budgetResult->num_rows > 0) {
        // Retrieve the budget details
        $budgetData = $budgetResult->fetch_assoc();
        $budgetAmount = $budgetData['amount'];
        $taxRate = $budgetData['tax_rate'];

        $summaryContent .= "Budget Amount: R" . $budgetAmount . "\n";
        $summaryContent .= "Tax Rate: " . $taxRate . "%\n\n";
    }

    if ($expenseResult->num_rows > 0) {
        // Retrieve the expenses details
        $summaryContent .= "Expenses:\n";

        while ($expenseData = $expenseResult->fetch_assoc()) {
            $expenseName = $expenseData['name'];
            $expenseAmount = $expenseData['amount'];

            $summaryContent .= "- " . $expenseName . ": R" . $expenseAmount . "\n";
        }
    }

    // Calculate the remaining budget
    $expenseResult->data_seek(0); // Reset the result pointer to the beginning
    $expenseAmounts = $expenseResult->fetch_all(MYSQLI_ASSOC);
    $expenseTotal = 0;
    foreach ($expenseAmounts as $expense) {
        $expenseTotal += $expense['amount'];
    }
    $remainingBudget = $budgetAmount - $expenseTotal;
    $summaryContent .= "\nRemaining Budget: R" . $remainingBudget . "\n";

    // Set the appropriate headers for file download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="budget_summary.txt"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output the file content
    echo $summaryContent;
    exit;
}

// Call the generateSummaryFile function when the file is accessed
generateSummaryFile();

// Close database connection
$conn->close();
?>
