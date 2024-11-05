<?php
session_start();

require_once 'config.php';

// Check if user is authenticated
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['id'];

$recipe_id = intval($_GET['id']);

// Fetch the recipe data along with the user_id
$recipeQuery = "SELECT id, title, description, image, cooking_time, category, ingredients, instructions, user_id FROM recipes WHERE id = ?";
$stmt = mysqli_prepare($conn, $recipeQuery);
mysqli_stmt_bind_param($stmt, "i", $recipe_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recipe = mysqli_fetch_assoc($result);

// Check if the recipe with the given ID exists
if (!$recipe) {
    // Redirect to a page indicating that the recipe does not exist
    header('Location: index.php');
    exit;
}

// Check if the current user is the creator of the recipe
if ($recipe['user_id'] !== $user_id) {
    // Redirect to a page indicating unauthorized access
    header('Location: login.php');
    exit;
}

// Initialize variables to store form values and error messages
$enteredValues = [
    'recipeName' => '',
    'recipeDescription' => '',
    'ingredients' => '',
    'instructions' => '',
    'cookingTime' => '',
    'category' => ''
];

$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize form data
    $enteredValues['recipeName'] = trim($_POST['recipeName']);
    $enteredValues['recipeName'] = substr($enteredValues['recipeName'], 0, 50);

    $enteredValues['recipeDescription'] = trim($_POST['recipeDescription']);
    $enteredValues['recipeDescription'] = substr($enteredValues['recipeDescription'], 0, 1000);

    $enteredValues['ingredients'] = trim($_POST['ingredients']);
    $enteredValues['ingredients'] = substr($enteredValues['ingredients'], 0, 1000);

    $enteredValues['instructions'] = trim($_POST['instructions']);
    $enteredValues['instructions'] = substr($enteredValues['instructions'], 0, 1000);

    $enteredValues['cookingTime'] = intval($_POST['cookingTime']);
    $enteredValues['category'] = intval($_POST['category']);

    // Check for empty fields
    if (empty($enteredValues['recipeName'])) {
        $errorMessages['recipeName'] = "Recipe Name is required.";
    }

    if (empty($enteredValues['recipeDescription'])) {
        $errorMessages['recipeDescription'] = "Recipe Description is required.";
    }

    if (empty($enteredValues['ingredients'])) {
        $errorMessages['ingredients'] = "Ingredients are required.";
    }

    if (empty($enteredValues['instructions'])) {
        $errorMessages['instructions'] = "Instructions are required.";
    }

    if (empty($enteredValues['cookingTime'])) {
        $errorMessages['cookingTime'] = "Cooking Time is required.";
    }

    if (empty($enteredValues['category'])) {
        $errorMessages['category'] = "Category is required.";
    }


    // Check if a new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image = $_FILES['image']['name'];
        $imageTmp = $_FILES['image']['tmp_name'];

        // Check if the uploaded file size exceeds 3 MB
        if ($_FILES['image']['size'] > 3000000) {
            $errorMessages['image'] = "Error: The uploaded image size should not exceed 3 MB.";
        }

        // Generate a unique filename for each user
        $userFolder = "uploads/user_$user_id/";
        $extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $imagePath = $userFolder . uniqid('image_') . ".$extension";

        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($extension, $allowedExtensions)) {
            $errorMessages['image'] = "Error: Only JPG, JPEG, and PNG files are allowed.";
        }

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
            imagepng($src, $imagePath, 7); // 7 is the compression level (no compression)
        } else {
            $errorMessages['image'] = "Error: Only JPG, JPEG, and PNG files are allowed.";
        }

        imagedestroy($src); // Free up memory

        if (!file_exists($imagePath)) {
            $errorMessages['image'] = "Error moving file to uploads directory";
        }
    } else {
        // No new image uploaded, keep the existing image path
        $imagePath = $recipe['image'];
    }


    if (empty($errorMessages)) {
        // Escape and sanitize data for SQL insertion
        $escapedRecipeName = mysqli_real_escape_string($conn, $enteredValues['recipeName']);
        $escapedRecipeDescription = mysqli_real_escape_string($conn, $enteredValues['recipeDescription']);
        $escapedIngredients = mysqli_real_escape_string($conn, $enteredValues['ingredients']);
        $escapedInstructions = mysqli_real_escape_string($conn, $enteredValues['instructions']);

        // Prepared statement for updating recipe
        $updateRecipeQuery = "UPDATE recipes SET title=?, description=?, image=?, cooking_time=?, category=?, ingredients=?, instructions=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $updateRecipeQuery);
        mysqli_stmt_bind_param($stmt, "ssssissi", $escapedRecipeName, $escapedRecipeDescription, $imagePath, $enteredValues['cookingTime'], $enteredValues['category'], $escapedIngredients, $escapedInstructions, $recipe_id);

        // Execute the query
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to the page of the updated recipe
            header('Location: recipe_element.php?id=' . $recipe_id);
            exit;
        } else {
            $errorMessages['query'] = "Error updating recipe: " . mysqli_stmt_error($stmt);
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
                <h2>Edit a Recipe</h2>

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

                <form method="post" enctype="multipart/form-data">
                    <label for="recipeName">Recipe Name:</label>
                    <input type="text" id="recipeName" name="recipeName" class="input-field" required maxlength="50" value="<?php echo htmlspecialchars($enteredValues['recipeName'] ?: $recipe['title']); ?>">

                    <label for="recipeDescription">Recipe Description:</label>
                    <textarea id="recipeDescription" name="recipeDescription" class="textarea-field" rows="4" maxlength="1000" required><?php echo htmlspecialchars($enteredValues['recipeDescription'] ?: $recipe['description']); ?></textarea>

                    <label for="ingredients">Ingredients:</label>
                    <textarea id="ingredients" name="ingredients" class="textarea-field" rows="4" maxlength="1000" required><?php echo htmlspecialchars($enteredValues['ingredients'] ?: $recipe['ingredients']); ?></textarea>

                    <label for="instructions">Instructions:</label>
                    <textarea id="instructions" name="instructions" class="textarea-field" rows="4" maxlength="1000" required><?php echo htmlspecialchars($enteredValues['instructions'] ?: $recipe['instructions']); ?></textarea>

                    <label for="image">Recipe Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" class="file-input">

                    <label for="category">Category:</label>
                    <select id="category" name="category" class="input-field" required>
                        <option value="" disabled selected>Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($enteredValues['category'] == $category['id'] || $recipe['category'] == $category['id']) ? 'selected' : ''; ?>><?php echo $category['title']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="cookingTime">Cooking Time (minutes):</label>
                    <input type="number" id="cookingTime" name="cookingTime" class="input-field" required min="0" max="255" value="<?php echo htmlspecialchars($enteredValues['cookingTime'] ?: $recipe['cooking_time']); ?>" min="0" max="255" required>

                    <button type="submit" class="submit-button">Submit recipe</button>
                </form>

            </section>
        </header>
    </div>
</body>
</html>
