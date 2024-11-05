<?php
session_start();
// Include the database configuration file
require_once 'config.php';

// Include pagination functions
require_once 'pagination.php';

// Define the number of items per page
$itemsPerPage = 3;

// Get the current page number from the query string and ensure it's an integer
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = ($page !== false && $page > 0) ? $page : 1;

// Calculate the offset for the query
$offset = ($page - 1) * $itemsPerPage;

// Check if category ID is provided in the URL and ensure it's an integer
$categoryID = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$categoryID = ($categoryID !== false && $categoryID > 0) ? $categoryID : 0; // Set a default value

// Get the total number of recipes in the specified category
$totalRecipesQuery = "SELECT COUNT(*) as total FROM recipes WHERE category = ?";
$stmtTotal = mysqli_prepare($conn, $totalRecipesQuery);
mysqli_stmt_bind_param($stmtTotal, "i", $categoryID);
mysqli_stmt_execute($stmtTotal);
$resultTotal = mysqli_stmt_get_result($stmtTotal);
$totalRecipes = mysqli_fetch_assoc($resultTotal)['total'];

// Set $totalRecipes to at least 1 if no recipes are found
$totalRecipes = max(1, $totalRecipes);

// Calculate the total number of pages
$totalPages = ceil($totalRecipes / $itemsPerPage);

// Fetch recipes from the database using prepared statements to prevent SQL injection
$query = "SELECT * FROM recipes WHERE category = ? ORDER BY title ASC LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iii", $categoryID, $offset, $itemsPerPage);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recipes = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
    <div class="page">
        <header class="header">
            <?php include('includes/_nav.php'); ?>
            <div class="categories-recipe-group">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="categories-recipe">
                        <img src="<?php echo htmlspecialchars($recipe['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($recipe['title'] . ' - ' . $recipe['description'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="categories-recipe-info">
                            <h1><?php echo htmlspecialchars(mb_strlen($recipe['title'], 'UTF-8') > 30 ? mb_substr($recipe['title'], 0, 30, 'UTF-8') . '...' : $recipe['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                            <p><?php echo htmlspecialchars(mb_strlen($recipe['description'], 'UTF-8') > 30 ? mb_substr($recipe['description'], 0, 30, 'UTF-8') . '...' : $recipe['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p>Cooking Time: <?php echo htmlspecialchars($recipe['cooking_time'], ENT_QUOTES, 'UTF-8'); ?> minutes</p>
                            <a href="recipe_element.php?id=<?php echo htmlspecialchars($recipe['id'], ENT_QUOTES, 'UTF-8'); ?>" class="recipe-link">Get Recipe</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </header>

        <?php
        // Include pagination
        echo generatePaginationLinks($totalPages, $page, $categoryID);
        ?>
    </div>
</body>
</html>
