<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['id'];

// Include database configuration
require_once 'config.php';

$recipeName = '';
$recipeDescription = '';
$ingredients = '';
$instructions = '';
// Check form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize form data
    $recipeName = trim($_POST['recipeName']);
    $recipeName = substr($recipeName, 0, 50);

    $recipeDescription = trim($_POST['recipeDescription']);
    $recipeDescription = substr($recipeDescription, 0, 1000);

    $ingredients = trim($_POST['ingredients']);
    $ingredients = substr($ingredients, 0, 1000);

    $instructions = trim($_POST['instructions']);
    $instructions = substr($instructions, 0, 1000);

    $cookingTime = intval($_POST['cookingTime']);
    $categoryId = intval($_POST['category']);

    $imageSize = $_FILES['image']['size'];

    // Check for empty fields
    $errorMessages = [];

    if (empty($recipeName)) {
        $errorMessages['recipeName'] = "Recipe Name is required.";
    }

    if (empty($recipeDescription)) {
        $errorMessages['recipeDescription'] = "Recipe Description is required.";
    }

    if (empty($ingredients)) {
        $errorMessages['ingredients'] = "Ingredients are required.";
    }

    if (empty($instructions)) {
        $errorMessages['instructions'] = "Instructions are required.";
    }

    if (empty($cookingTime)) {
        $errorMessages['cookingTime'] = "Cooking Time is required.";
    }

    if (empty($categoryId)) {
        $errorMessages['category'] = "Category is required.";
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
        $errorMessages['image'] = "Error uploading image. Please try again.";
    } elseif ($_FILES['image']['size'] > 3000000 ) {
        $errorMessages['image'] = "Error: The uploaded image size should not exceed 3 MB.";
    }
    else {
        $image = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($extension, $allowedExtensions)) {
            $errorMessages['image'] = "Error: Only JPG, JPEG, and PNG files are allowed.";
        }
    }

    if (empty($errorMessages)) {
        // Check file upload
        if (isset($_FILES['image'])) {
            $image = $_FILES['image']['name'];
            $imageTmp = $_FILES['image']['tmp_name'];

            // Generate a unique filename for each user
            $userFolder = "uploads/user_$user_id/";
            $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $imagePath = $userFolder . uniqid('image_') . ".$extension";

            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (in_array($extension, $allowedExtensions)) {
                // Create the user's folder if it doesn't exist
                if (!file_exists($userFolder)) {
                    mkdir($userFolder, 0777, true);
                }
            
                // Compress the image and move it to the specified directory
                if ($extension === 'jpg' || $extension === 'jpeg') {
                    $src = imagecreatefromjpeg($imageTmp);
                    imagejpeg($src, $imagePath, 75); // 75 is the quality percentage
                } else if ($extension === 'png') {
                    $src = imagecreatefrompng($imageTmp);
                    imagepng($src, $imagePath, 7); // 7 is the compression level (0-9)
                } else {
                    $errorMessages['image'] = "Error: Only JPG, JPEG, and PNG files are allowed.";
                }
            
                if (!file_exists($imagePath)) {
                    $errorMessages['image'] = "Error moving file to uploads directory";
                }
            }
        }

        // Prepared statement for inserting recipe
        $insertRecipeQuery = "INSERT INTO recipes (title, description, image, cooking_time, category, ingredients, instructions, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertRecipeQuery);
        mysqli_stmt_bind_param($stmt, "ssssissi", $recipeName, $recipeDescription, $imagePath, $cookingTime, $categoryId, $ingredients, $instructions, $user_id);

        // Execute the query
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to the page of the added recipe
            header('Location: recipe_element.php?id=' . mysqli_insert_id($conn));
            exit;
        } else {
            $errorMessages['query'] = "Error adding recipe: " . mysqli_stmt_error($stmt);
        }
    }
}

// Fetch categories for the dropdown
$categoryQuery = "SELECT * FROM categories ORDER BY title";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = mysqli_fetch_all($categoryResult, MYSQLI_ASSOC);
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
    <div class="page" id="recipes">
        <header class="header">
            <?php include('includes/_nav.php'); ?>
            <section class="add-recipe">
                <h2>Add a Recipe</h2>

                <!-- Display error messages here -->
                <?php if(!empty($errorMessages)): ?>
                    <div class="error">
                        <ul>
                            <?php foreach ($errorMessages as $errorMessage): ?>
                                <li><?php echo $errorMessage; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="add_recipe.php" enctype="multipart/form-data">
                    <label for="recipeName">Recipe Name:</label>
                    <input type="text" id="recipeName" name="recipeName" class="input-field" required maxlength="50" value="<?php echo htmlspecialchars($recipeName, ENT_QUOTES, 'UTF-8'); ?>">


                    <label for="recipeDescription">Recipe Description:</label>
                    <textarea id="recipeDescription" name="recipeDescription" class="textarea-field" rows="4" maxlength="1000" required><?php echo htmlspecialchars($recipeDescription, ENT_QUOTES, 'UTF-8'); ?></textarea>

                    <label for="ingredients">Ingredients:</label>
                    <textarea id="ingredients" name="ingredients" class="textarea-field" rows="4" maxlength="1000" required><?php echo htmlspecialchars($ingredients, ENT_QUOTES, 'UTF-8'); ?></textarea>


                    <label for="instructions">Instructions:</label>
                    <textarea id="instructions" name="instructions" class="textarea-field" rows="4" maxlength="1000" required><?php echo htmlspecialchars($instructions, ENT_QUOTES, 'UTF-8'); ?></textarea>


                    <label for="image">Recipe Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" class="file-input" required>
                    
                    <label for="category">Category:</label>
                    <select id="category" name="category" class="input-field" required>
                        <option value="" label="Choose category">Choose category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>" label="<?php echo htmlspecialchars($category['title']); ?>" <?php echo $categoryId == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="cookingTime">Cooking Time (minutes):</label>
                    <input type="number" id="cookingTime" name="cookingTime" class="input-field" value="<?php echo htmlspecialchars($cookingTime, ENT_QUOTES, 'UTF-8'); ?>" min="0" max="255" required>


                    <button type="submit" class="submit-button">Submit recipe</button>
                </form>
            </section>
        </header>
    </div>
</body>
</html>
