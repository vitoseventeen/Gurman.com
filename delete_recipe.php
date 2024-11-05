<?php
// Start session
session_start();

// Check if user is authenticated
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// Include database configuration
require_once 'config.php';

// Check if recipe ID is set in the URL
if (!isset($_GET['id'])) {
    // Redirect to an error page or handle accordingly
    header('Location: error.php');
    exit;
}

// Get the recipe ID from the URL
$recipeId = intval($_GET['id']);

// Check if the logged-in user is the owner of the recipe
$userId = $_SESSION['id'];
$ownershipCheckResult = checkRecipeOwnership($conn, $recipeId, $userId);

if (!$ownershipCheckResult['success']) {
    // Redirect to an error page or handle accordingly
    header('Location: error.php');
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

// Delete the recipe and associated comments
$deletionResult = deleteRecipe($conn, $recipeId);

if ($deletionResult['success']) {
    // Recipe deleted successfully, commit transaction
    mysqli_commit($conn);
    // Redirect to a confirmation page or home page
    header('Location: index.php');
    exit;
} else {
    // Handle deletion error
    echo "Error deleting recipe: " . $deletionResult['error'];
    // Rollback transaction
    mysqli_rollback($conn);
}

/**
 * Function to check recipe ownership
 *
 * @param mysqli $conn      MySQLi connection object
 * @param int    $recipeId  Recipe ID
 * @param int    $userId    User ID
 *
 * @return array            Array with success status and error message if applicable
 */
function checkRecipeOwnership($conn, $recipeId, $userId) {
    $checkOwnershipQuery = "SELECT user_id FROM recipes WHERE id = ?";
    $stmt = mysqli_prepare($conn, $checkOwnershipQuery);
    mysqli_stmt_bind_param($stmt, "i", $recipeId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($recipe = mysqli_fetch_assoc($result)) {
        if ($recipe['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'User does not own the recipe'];
        } else {
            return ['success' => true];
        }
    } else {
        return ['success' => false, 'error' => 'Recipe not found'];
    }
}

/**
 * Function to delete recipe and associated comments
 *
 * @param mysqli $conn      MySQLi connection object
 * @param int    $recipeId  Recipe ID
 *
 * @return array            Array with success status and error message if applicable
 */
function deleteRecipe($conn, $recipeId) {
    // Delete comments associated with the recipe
    $deleteCommentsQuery = "DELETE FROM comments WHERE recipe_id = ?";
    $deleteCommentsStmt = mysqli_prepare($conn, $deleteCommentsQuery);
    mysqli_stmt_bind_param($deleteCommentsStmt, "i", $recipeId);

    if (!mysqli_stmt_execute($deleteCommentsStmt)) {
        return ['success' => false, 'error' => 'Error deleting comments: ' . mysqli_stmt_error($deleteCommentsStmt)];
    }

    // Delete the recipe
    $deleteRecipeQuery = "DELETE FROM recipes WHERE id = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteRecipeQuery);
    mysqli_stmt_bind_param($deleteStmt, "i", $recipeId);

    if (!mysqli_stmt_execute($deleteStmt)) {
        return ['success' => false, 'error' => 'Error deleting recipe: ' . mysqli_stmt_error($deleteStmt)];
    }

    return ['success' => true];
}
?>