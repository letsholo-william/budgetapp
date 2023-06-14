<?php
require_once 'db_connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$username = $password = $confirm_password = $email = $firstname = $surname = $country = $cellphone = $employment_status = $tax_percentage = "";
$username_err = $password_err = $confirm_password_err = $email_err = $firstname_err = $surname_err = $country_err = $cellphone_err = $employment_status_err = $tax_percentage_err = "";

$pdo = db_connect(); // Initialize PDO connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([trim($_POST["username"])]);
        if ($stmt->rowCount() > 0) {
            $username_err = "This username is already taken.";
        } else {
            $username = trim($_POST["username"]);
        }
        unset($stmt);
    }
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } elseif (!preg_match('/[a-z]/', $_POST["password"])) {
        $password_err = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/[A-Z]/', $_POST["password"])) {
        $password_err = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/\d/', $_POST["password"])) {
        $password_err = "Password must contain at least one number.";
    } elseif (!preg_match('/[^a-zA-Z\d]/', $_POST["password"])) {
        $password_err = "Password must contain at least one special character.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["firstname"]))) {
        $firstname_err = "Please enter your first name.";
    } else {
        $firstname = trim($_POST["firstname"]);
    }

    if (empty(trim($_POST["surname"]))) {
        $surname_err = "Please enter your surname.";
    } else {
        $surname = trim($_POST["surname"]);
    }

    if (empty(trim($_POST["country"]))) {
        $country_err = "Please enter your country.";
    } else {
        $country = trim($_POST["country"]);
    }

    if (empty(trim($_POST["cellphone"]))) {
        $cellphone_err = "Please enter your cellphone number.";
    } else {
        $cellphone = trim($_POST["cellphone"]);
    }

    if (empty(trim($_POST["employment_status"]))) {
        $employment_status_err = "Please select your employment status.";
    } else {
        $employment_status = trim($_POST["employment_status"]);
    }

    // Validate tax percentage if employed
    if ($employment_status === 'employed') {
        if (!isset($_POST["tax_percentage"]) || empty(trim($_POST["tax_percentage"]))) {
            $tax_percentage_err = "Please enter the tax percentage.";
        } elseif (!is_numeric(trim($_POST["tax_percentage"]))) {
            $tax_percentage_err = "Tax percentage must be a numeric value.";
        } else {
            $tax_percentage = trim($_POST["tax_percentage"]);
        }
    }

    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err) && empty($firstname_err) && empty($surname_err) && empty($country_err) && empty($cellphone_err) && empty($employment_status_err) && empty($tax_percentage_err)) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, firstname, surname, country, cellphone, employment_status, tax_percentage, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $email, $firstname, $surname, $country, $cellphone, $employment_status, $tax_percentage]);

        if ($stmt->rowCount() > 0) {
            echo "User added successfully.";
        } else {
            echo "Error adding user.";
        }

        header("Location: login.php");
        exit;
    }

    unset($stmt);
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            text-align: center;
        }

        .logo img {
            width: 100px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"],
        input[type="reset"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"].bg-primary {
            background-color: #007bff;
        }

        input[type="reset"].bg-danger {
            background-color: #dc3545;
        }

        .login-link {
            margin-top: 20px;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .logo img {
            width: 300px;
            height: auto;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
        <img src="https://drive.google.com/uc?id=1JMaA_8Ej4i93psml3TASnbe1MA6E4GbL" alt="Logo">
        </div>
        <h2>Register</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>
            <div class="form-group">
                <label for="surname">Surname</label>
                <input type="text" id="surname" name="surname" required>
            </div>
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" required>
            </div>
            <div class="form-group">
                <label for="cellphone">Cellphone</label>
                <input type="text" id="cellphone" name="cellphone" required>
            </div>
            <div class="form-group">
                <label for="employment_status">Employment Status</label>
                <select id="employment_status" name="employment_status" required>
                    <option value="">Select</option>
                    <option value="employed">Employed</option>
                    <option value="unemployed">Unemployed</option>
                    <option value="self_employed">Self-Employed</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tax_percentage">Tax Percentage</label>
                <input type="text" id="tax_percentage" name="tax_percentage">
            </div>
            <input type="submit" value="Register" class="bg-primary">
            <input type="reset" value="Reset" class="bg-danger">
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>

</html>
