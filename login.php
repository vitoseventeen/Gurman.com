<?php
/**
 * Start the session at the beginning of your script.
 */
session_start();

/**
 * Include the database configuration file.
 */
require_once 'config.php';

/**
 * Check if the user is already logged in. If yes, redirect them to the profile page.
 */
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: profile.php");
    exit;
}

/**
 * Define variables and initialize with empty values.
 */
$username = $password = "";
$err = "";

/**
 * Function to validate the username.
 *
 * @param string $input The username input to be validated.
 * @return string The sanitized and validated username.
 */
function validateUsername($input) {
    return empty(trim($input)) ? "Please enter your username." : trim($input);
}

/**
 * Function to validate the password.
 *
 * @param string $input The password input to be validated.
 * @return string The sanitized and validated password.
 */
function validatePassword($input) {
    return empty(trim($input)) ? "Please enter your password." : trim($input);
}

/**
 * Function to perform the login process.
 *
 * @param mysqli $conn The database connection object.
 * @param string $username The username provided during login.
 * @param string $password The password provided during login.
 * @return string|null Returns an error message if login fails, null otherwise.
 */
function login($conn, $username, $password) {
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $existingUsername, $hashed_password);

        if ($stmt->fetch()) {
            if (password_verify($password, $hashed_password)) {
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $existingUsername;
                header("location: profile.php");
                exit();
            } else {
                return "Invalid username or password.";
            }
        } else {
            return "Invalid username or password.";
        }

        $stmt->close();
    } else {
        return "Oops! Something went wrong. Please try again later.";
    }
}

/**
 * Process form data when the form is submitted.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = validateUsername($_POST["username"]);
    $password = validatePassword($_POST["password"]);

    if (empty($err)) {
        $err = login($conn, $username, $password);
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html> 
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/print.css" media="print">
    <link rel="icon" href="img/siteicon.ico" type="image/x-icon">
    <title>Gurman.com</title>
</head>
<body>
    <div class="page" id="login">
        <header class="header">
            <?php include('includes/_nav.php'); ?>
            <div class="form-container">
                <div class="form-box">
                    <form action="login.php" method="post" name="formFill" id="logForm">
                        <h2>Login</h2>
                        <div id="result"></div>
                        <div class="input-box">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>">
                            <span class="error" id="username-error"></span>
                        </div>
                        <div class="input-box">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" placeholder="Password" value="<?php echo htmlspecialchars($password); ?>">
                            <span class="error" id="password-error"></span>
                        </div>
                        <input type="submit" class="btn" value="Login">
                        <div class="log">
                            <a href="register.php">Register</a>
                        </div>
                    </form>
                </div>
            </div>
        </header>
    </div>

    <!-- Include your script file -->
    <script src="js/log_validation_ajax.js"></script>
</body>
</html>
