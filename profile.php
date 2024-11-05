<?php
// Start the session at the beginning of your script
session_start();

// Include the database configuration file
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to login page or display a message
    header("location: login.php");
    exit();
}

// Define variables and initialize with empty values
$new_username = $new_username_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username form was submitted
    if (!empty($_POST["new_username"])) {
        // Validate new username
        if (empty(trim($_POST["new_username"]))) {
            $new_username_err = "Please enter a new username.";
        } else {
            $new_username = trim($_POST["new_username"]);

            // Check if the username has any illegal characters
            if (preg_match('/[^A-Za-z0-9_\-]/', $new_username)) {
                $new_username_err = "Username can only contain letters, numbers, underscores, and hyphens.";
            } elseif (strlen($new_username) > 20) {
                $new_username_err = "Username must not exceed 20 characters.";
            } elseif (strlen($new_username) < 6) {
                $new_username_err = "Username must be at least 6 characters.";
            } else {
                // Check if the username is already taken
                $sql_check_username = "SELECT id FROM users WHERE username = ?";

                if ($stmt_check_username = $conn->prepare($sql_check_username)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt_check_username->bind_param("s", $param_username_check);

                    // Set parameters
                    $param_username_check = $new_username;

                    // Attempt to execute the prepared statement
                    if ($stmt_check_username->execute()) {
                        // Store result
                        $stmt_check_username->store_result();

                        if ($stmt_check_username->num_rows > 0) {
                            $new_username_err = "This username is already taken.";
                        } else {
                            // Prepare an update statement
                            $sql_update_username = "UPDATE users SET username = ? WHERE id = ?";

                            if ($stmt_update_username = $conn->prepare($sql_update_username)) {
                                // Bind variables to the prepared statement as parameters
                                $stmt_update_username->bind_param("si", $param_username_update, $param_id_update);

                                // Set parameters
                                $param_username_update = $new_username;
                                $param_id_update = $_SESSION["id"]; // Get the ID of the current user from the session

                                // Attempt to execute the prepared statement
                                if ($stmt_update_username->execute()) {
                                    // Username updated successfully. Update the username stored in the session
                                    $_SESSION["username"] = $new_username;
                                
                                    // Redirect to the same page to prevent form resubmission
                                    header("location: profile.php");
                                    exit();
                                } else {
                                    echo "Oops! Something went wrong. Please try again later.";
                                }

                                // Close statement
                                $stmt_update_username->close();
                            }
                        }
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }

                    // Close statement
                    $stmt_check_username->close();
                }
            }
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
    <div class="page" id="profile">
        <header class="header">
            <nav class="nav">
                <?php include('includes/_nav.php'); ?>
            </nav>
        </header>

        <!-- Section for user profile -->
        <div id="profile-container">
            <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>

            <!-- Form for changing username -->
            <form action="profile.php" method="post">
                <h2><label for="new-username" id="new-username-label">New Username:</label></h2>
                <input type="text" id="new-username" name="new_username" placeholder="Enter new username">
                <p class="error"><?php echo $new_username_err; ?></p>

                <button id="username-button" type="submit">Change Username</button>
            </form>

        </div>
    </div>
    <script src="js/reg_validation.js"></script>
</body>
</html>
