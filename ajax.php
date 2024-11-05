<?php
/**
 * Include the database configuration file.
 */
require_once('config.php');

/**
 * Validate the entered username
 *
 * @param mysqli $conn     MySQLi connection object
 * @param string $username The entered username
 *
 * @return string          Error message, if any
 */
function validateUsername($conn, $username) {
    $username_err = "";

    if (empty(trim($username))) {
        $username_err = "Please enter a username.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = trim($username);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $username_err = "This username is already taken.";
                }
            } else {
                $username_err = "Oops! Something went wrong. Please try again later.";
            }

            $stmt->close();
        }
    }

    return $username_err;
}

/**
 * Validate the entered password
 *
 * @param string $password The entered password
 *
 * @return string          Error message, if any
 */
function validatePassword($password) {
    $password_err = "";

    if (empty(trim($password))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($password)) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }

    return $password_err;
}

/**
 * Validate the entered confirm password
 *
 * @param string $password The entered password
 * @param string $cpassword The entered confirm password
 *
 * @return string           Error message, if any
 */
function validateConfirmPassword($password, $cpassword) {
    $cpassword_err = "";

    if (empty(trim($cpassword))) {
        $cpassword_err = "Please confirm password.";
    } elseif ($password != $cpassword) {
        $cpassword_err = "Password did not match.";
    }

    return $cpassword_err;
}

/**
 * Register a new user
 *
 * @param mysqli $conn     MySQLi connection object
 * @param string $username The entered username
 * @param string $password The entered password
 *
 * @return array           Array with error messages, if any
 */
function registerUser($conn, $username, $password) {
    $username_err = validateUsername($conn, $username);
    $password_err = validatePassword($password);
    $cpassword_err = validateConfirmPassword($password, $_POST["cpassword"]);

    if (empty($username_err) && empty($password_err) && empty($cpassword_err)) {
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_username, $param_password);

            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_BCRYPT);

            if ($stmt->execute()) {
                header("location: login.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            $stmt->close();
        }
    }

    return [
        'username_err' => $username_err,
        'password_err' => $password_err,
        'cpassword_err' => $cpassword_err
    ];
}

// Process registration when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = registerUser($conn, $_POST["username"], $_POST["password"]);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>
