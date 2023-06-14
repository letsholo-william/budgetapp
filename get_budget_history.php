<?php
session_start(); // Start the session

// Check if the user is authenticated and the user ID is set in the session
if (!isset($_SESSION['user_id'])) {
    die("User not authenticated"); // Replace with appropriate error handling
}

function connectToDatabase()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "budget_tracker";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f5f5f5;
                }

                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #fff;
                    border-radius: 5px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                }

                h1 {
                    color: #dc3545;
                }

                p {
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>Error</h1>
                <p>Connection failed: " . $conn->connect_error . "</p>
            </div>
        </body>
        </html>");
    }

    return $conn;
}

function getBudgetHistory($user_id)
{
    $conn = connectToDatabase();

    // Prepare and bind the user ID as a parameter in the query
    $query = "
        SELECT b.id, b.amount, b.tax_rate, b.created_at,
               e.id AS expense_id, e.name, e.amount, e.created_at AS expense_created_at
        FROM budgets AS b
        LEFT JOIN expenses AS e ON b.id = e.budget_id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC, e.created_at ASC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        die("Query execution failed: " . $stmt->error . "<br>Query: " . $query . "<br>User ID: " . $user_id);
    }

    // Prepare an array to hold the budget history data
    $budgetHistory = array();

    // Bind the result variables
    $stmt->bind_result(
        $budgetId,
        $budgetAmount,
        $budgetTaxRate,
        $budgetCreatedAt,
        $expenseId,
        $expenseName,
        $expenseAmount,
        $expenseCreatedAt
    );

    // Iterate through the result and organize the data
    while ($stmt->fetch()) {
        // If the budget hasn't been added to the budget history array yet, create a new entry
        if (!isset($budgetHistory[$budgetId])) {
            $budgetHistory[$budgetId] = array(
                'id' => $budgetId,
                'amount' => $budgetAmount,
                'tax_rate' => $budgetTaxRate,
                'created_at' => $budgetCreatedAt,
                'expenses' => array(),
                'remaining_budget' => $budgetAmount // Initialize remaining budget with budget amount
            );
        }

        // If the current row has an expense, add it to the corresponding budget's expenses array
        if ($expenseId !== null) {
            $budgetHistory[$budgetId]['expenses'][] = array(
                'id' => $expenseId,
                'name' => $expenseName,
                'amount' => $expenseAmount,
                'created_at' => $expenseCreatedAt
            );

            // Deduct the expense amount from the remaining budget
            $budgetHistory[$budgetId]['remaining_budget'] -= $expenseAmount;
        }
    }

    $stmt->close();
    $conn->close();

    return $budgetHistory;
}

$user_id = $_SESSION['user_id'];

// Fetch the budget history data from the database
$budgetHistory = getBudgetHistory($user_id);

// Render the HTML page to display the budget history and expenses
?>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #dc3545;
        }

        p {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Budget Summary</h1>

        <?php if (count($budgetHistory) > 0): ?>
            <?php foreach ($budgetHistory as $budget): ?>
                <h2>Budget ID: <?php echo $budget['id']; ?></h2>
                <p>Amount: R<?php echo $budget['amount']; ?></p>
                <p>Tax Rate: <?php echo $budget['tax_rate']; ?>%</p>
                <p>Remaining Budget: R<?php echo $budget['remaining_budget']; ?></p>

                <h3>Expenses:</h3>
                <?php if (count($budget['expenses']) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budget['expenses'] as $expense): ?>
                                <tr>
                                    <td><?php echo $expense['name']; ?></td>
                                    <td>R<?php echo $expense['amount']; ?></td>
                                    <td><?php echo $expense['created_at']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No expenses found for this budget.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No budget history available.</p>
        <?php endif; ?>
    </div>
</body>
</html>
