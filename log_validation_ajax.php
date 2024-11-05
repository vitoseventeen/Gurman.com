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
 * Function to validate the username.
 *
 * @param string $username The username to be validated.
 * @return bool Returns true if the username is valid, false otherwise.
 */
function validateUsername($username) {
    $usernameRegex = '/^(?=.*[A-z]).{6,}$/';
    return preg_match($usernameRegex, $username);
}

/**
 * Function to validate the password.
 *
 * @param string $password The password to be validated.
 * @return bool Returns true if the password is valid, false otherwise.
 */
function validatePassword($password) {
    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z]).{6,}$/';
    return preg_match($passwordRegex, $password);
}

/**
 * Function to log in a user.
 *
 * @param mysqli $conn The database connection object.
 * @param string $username The username provided during login.
 * @param string $password The password provided during login.
 */
function loginUser($conn, $username, $password) {
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($id, $existingUsername, $hashed_password);
        
        if ($stmt->fetch()) {
            if (password_verify($password, $hashed_password)) {
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $existingUsername;
                echo 'success';
            } else {
                echo 'Username or password is incorrect.';
            }
        } else {
            echo 'Username or password is incorrect.';
        }
        
        $stmt->close();
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
}

/**
 * Process form data when the form is submitted.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    if (!validateUsername($username)) {
        echo 'Username must be at least six characters.';
    } elseif (!validatePassword($password)) {
        echo 'Password must be at least six characters and contain both uppercase and lowercase letters.';
    } else {
        loginUser($conn, $username, $password);
    }
    
    // Close connection
    $conn->close();
} else {
    echo 'Invalid request';
}
?>
