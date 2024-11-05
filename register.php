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
 * Check if the user is already logged in.
 */
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Redirect them to the profile page
    header("location: profile.php");
    exit;
}

/**
 * Define variables and initialize with empty values.
 */
$username = $password = $cpassword = "";
$username_err = $password_err = $cpassword_err = "";

/**
 * Function to validate username.
 *
 * @param string $input_username The input username to be validated.
 * @param mysqli $conn The database connection object.
 */
function validateUsername($input_username, $conn) {
    global $username_err, $username;

    if (empty(trim($input_username))) {
        $username_err = "Please enter a username.";
        // Reset password variables
        $password = $cpassword = "";
        $password_err = $cpassword_err = "";
    } else {
        $input_username = trim($input_username);

        // Check if the username has any illegal characters
        if (preg_match('/[^A-Za-z0-9_\-]/', $input_username)) {
            $username_err = "Username can only contain letters, numbers, underscores, and hyphens.";
        } elseif (strlen($input_username) > 40) {
            $username_err = "Username must not exceed 40 characters.";
        } else {
            // Prepare a select statement
            $sql = "SELECT id FROM users WHERE username = ?";

            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("s", $param_username);

                // Set parameters
                $param_username = $input_username;

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    // Store result
                    $stmt->store_result();

                    if ($stmt->num_rows == 1) {
                        $username_err = "This username is already taken.";
                    } else {
                        $username = $input_username;
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        }
    }
}

/**
 * Function to validate password.
 *
 * @param string $input_password The input password to be validated.
 */
function validatePassword($input_password) {
    global $password_err, $password;

    if (empty(trim($input_password))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($input_password)) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($input_password);
    }
}

/**
 * Function to validate confirm password.
 *
 * @param string $input_cpassword The input confirm password to be validated.
 */
function validateConfirmPassword($input_cpassword) {
    global $cpassword_err, $password, $cpassword;

    if (empty(trim($input_cpassword))) {
        $cpassword_err = "Please confirm password.";
    } else {
        $cpassword = trim($input_cpassword);
        if (empty($password_err) && ($password != $cpassword)) {
            $cpassword_err = "Password did not match.";
        }
    }
}

/**
 * Processing form data when the form is submitted.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    validateUsername($_POST["username"], $conn);
    validatePassword($_POST["password"]);
    validateConfirmPassword($_POST["cpassword"]);

    // Check input errors before inserting into the database
    if (empty($username_err) && empty($password_err) && empty($cpassword_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ss", $param_username, $param_password);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_BCRYPT); // Creates a password hash

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect the user to the welcome page
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $stmt->insert_id; // Get the ID of the newly created account
                $_SESSION["username"] = $username; // The username that was just registered

                // Use ob_start() to buffer the output
                ob_start();

                // Redirect user to the welcome page
                header("location: profile.php");

                // Use ob_end_flush() to send the output and turn off output buffering
                ob_end_flush();
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
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
    <div class="page" id="register">
        <header class="header">
            <?php include('includes/_nav.php'); ?>
            <div class="form-container">
                <div class="form-box">
                    <form action="register.php" method="post" name="formFill" id="regForm">
                        <h2>Registration</h2>
                        <p id="result"></p>
                        <div class="input-box">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>">
                            <span class="error"><?php echo htmlspecialchars($username_err); ?></span>
                        </div>
                        <div class="input-box">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" placeholder="Password" value="<?php echo htmlspecialchars($password); ?>">
                            <span class="error"><?php echo htmlspecialchars($password_err); ?></span>
                        </div>
                        <div class="input-box">
                            <label for="cpassword">Confirm password:</label>
                            <input type="password" name="cpassword" id="cpassword" placeholder="Confirm password" value="<?php echo htmlspecialchars($cpassword); ?>">
                            <span class="error"><?php echo htmlspecialchars($cpassword_err); ?></span>
                        </div>
                        <input type="submit" class="btn" value="Register">
                        <div class="log">
                            <a href="login.php">Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </header>
    </div>
    <script src="js/reg_validation_ajax.js"></script>
</body>
</html>
