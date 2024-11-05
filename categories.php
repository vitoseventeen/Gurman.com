<?php
session_start();
// Include the database configuration file
require_once 'config.php';

// Include pagination functions
require_once 'pagination.php';

// Define the number of items per page
$itemsPerPage = 4;

// Get the current page number from the query string and ensure it's an integer
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = ($page !== false && $page > 0) ? $page : 1;

// Get the current category ID from the query string
$categoryID = isset($_GET['category_id']) ? $_GET['category_id'] : null;

// Calculate the offset for the query
$offset = ($page - 1) * $itemsPerPage;

// Fetch categories from the database using prepared statements
$query = "SELECT * FROM categories ORDER BY title ASC LIMIT ?, ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $offset, $itemsPerPage);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$categories = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get the total number of categories
$totalCategoriesQuery = "SELECT COUNT(*) as total FROM categories";
$totalCategoriesResult = mysqli_query($conn, $totalCategoriesQuery);
$totalCategories = mysqli_fetch_assoc($totalCategoriesResult)['total'];

// Set $totalCategories to at least 1 if no categories are found
$totalCategories = max(1, $totalCategories);

// Calculate the total number of pages
$totalPages = ceil($totalCategories / $itemsPerPage);
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
    <div class="page" id="categories">
        <header class="header">
            <?php include('includes/_nav.php'); ?>
            <div class="categories">
                <?php 
                // Initialize $categoryID
                $categoryID = null;

                foreach ($categories as $category): ?>
                    <div class="element-category">
                        <img src="<?php echo htmlspecialchars($category['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        <h1>
                            <?php echo htmlspecialchars($category['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h1>
                        <p>
                            <?php echo htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <?php
                            // Set $categoryID to the current category ID
                            $categoryID = $category['id'];
                        ?>
                        <a href="recipe_category.php?category_id=<?php echo htmlspecialchars($categoryID, ENT_QUOTES, 'UTF-8'); ?>&page=1" class="category-link">Read More</a>
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
