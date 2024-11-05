<?php
/**
 * Include the database configuration file.
 */
require_once 'config.php';

/**
 * Function to check if the username is available.
 *
 * @param string $username The username to be checked for availability.
 * @return bool Returns true if the username is available, false otherwise.
 */
function isUsernameAvailable($username) {
    global $conn;

    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    return $stmt->num_rows === 0;
}

/**
 * Function to check password strength.
 *
 * @param string $password The password to be checked for strength.
 * @return bool Returns true if the password is strong, false otherwise.
 */
function isPasswordStrong($password) {
    // Use a regex pattern to check password strength
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/';
    return preg_match($pattern, $password);
}

/**
 * Handle AJAX requests.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
        echo json_encode(["available" => isUsernameAvailable($username)]);
    }

    if (isset($_POST["password"])) {
        $password = $_POST["password"];
        echo json_encode(["strong" => isPasswordStrong($password)]);
    }
}
?>
