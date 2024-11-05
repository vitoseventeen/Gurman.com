<?php
// Start the session at the beginning
session_start();

// Include the database configuration file
require_once 'config.php';

// Check if recipe ID is set in the URL
if (!isset($_GET['id'])) {
    // Redirect to an error page or handle accordingly
    header('Location: error.php');
    exit;
}

// Get the recipe ID from the URL
$recipeId = intval($_GET['id']);

// Fetch recipe details from the database
$recipeQuery = "SELECT * FROM recipes WHERE id = ?";
$stmt = mysqli_prepare($conn, $recipeQuery);
mysqli_stmt_bind_param($stmt, "i", $recipeId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if recipe exists
if ($recipe = mysqli_fetch_assoc($result)) {
    // Get the recipe details
    $description = htmlspecialchars($recipe['description']);
    $ingredients = htmlspecialchars($recipe['ingredients']);
    $instructions = htmlspecialchars($recipe['instructions']);
    $title = htmlspecialchars($recipe['title']);
} else {
    // Redirect to an error page or handle accordingly
    header('Location: index.php');
    exit;
}

$commentsQuery = "SELECT * FROM comments WHERE recipe_id = ?";
$stmt = mysqli_prepare($conn, $commentsQuery);
mysqli_stmt_bind_param($stmt, "i", $recipeId);
mysqli_stmt_execute($stmt);
$commentsResult = mysqli_stmt_get_result($stmt);

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
    <div class="page" id="recipe-details">
        <header class="header">
            <?php include('includes/_nav.php'); ?>
            <div class="recipe">
                <div class="recipe-container">
                    <img src="<?php echo $recipe['image']; ?>" alt="<?php echo $recipe['title']; ?>" class="recipe-img">
                    <div class="recipe-details">
                        <h1><?php echo $title ?></h1>
                        <p><?php echo $description; ?></p>

                        <h2>Ingredients:</h2>
                        <p><?php echo $ingredients; ?></p>

                        <h2>Instructions:</h2>
                        <p><?php echo $instructions; ?></p>

                        <h2>Cooking Time: <?php echo $recipe['cooking_time']; ?> minutes</h2>
                        <?php if (isset($_SESSION['id']) && $_SESSION['id'] == $recipe['user_id']): ?>
                            <a href="edit_recipe.php?id=<?php echo $recipeId; ?>" class="edit-link">Edit</a>
                            <a href="delete_recipe.php?id=<?php echo $recipeId; ?>" class="delete-link">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>

                <section class="comments">
                    <h2>Comments</h2>
                        <?php
                        // Check if there are any comments for this recipe    
                        if (isset($_GET['error']) && $_GET['error'] == 'invalid_comment') {
                            echo '<div id="result">Error: Invalid comment. Please make sure it is not empty and does not exceed 1000 characters.</div>';
                        }
                        // Display all comments for this recipe
                        while ($comment = mysqli_fetch_assoc($commentsResult)) {
                            $commentText = htmlspecialchars($comment['comment_text']);
                            $commentUsername = htmlspecialchars($comment['username']);
                            $commentDate = date('m.d.Y H:i:s', strtotime($comment['created_at'])); // Convert timestamp to a readable date
                        
                            echo '<div class="comment-container">
                                    <div class="comment-header">
                                        <span class="comment-username">' . $commentUsername . '</span>
                                        <span class="comment-date">' . $commentDate . '</span>
                                    </div>
                                    <div class="comment-text">
                                        ' . $commentText . '
                                    </div>
                                  </div>';
                        }
                        // Check if the user is logged in
                        if (isset($_SESSION['username'])) {
                            // Display the comment form for logged-in users
                            echo '<form class="comment-form" action="post_comment.php" method="POST">
                                <label for="comment">Add your comment:</label>
                                <input type="hidden" name="recipe_id" value="' . htmlspecialchars($recipeId, ENT_QUOTES, 'UTF-8') . '">
                                <textarea name="comment" id="comment" placeholder="Write your comment here" class="comment-textarea"></textarea>
                                <button type="submit" class="submit-button">Submit Comment</button>
                            </form>';
                        } else {
                            // Display the login link for non-logged-in users
                            echo '<div class="comment-container"> 
                            <div class="comment-header"> 
                            To leave a comment, please <a href="login.php">login</a>.
                            </div>
                            </div>';
                        }
                        ?>
                </section>
            </div>
        </header>
    </div>
</body>

</html>
