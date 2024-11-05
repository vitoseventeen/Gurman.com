<?php
session_start();
include('config.php'); // Include the file with the database configuration

// Database query to retrieve the latest 4 recipes
$sql = "SELECT * FROM recipes ORDER BY created_at DESC LIMIT 4";
$result = mysqli_query($conn, $sql);

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
    <div class="page" id="index"> 
        <header class="header"> 
            <?php include('includes/_nav.php'); ?>
            
            <section class="content">
                <div class="intro-text">
                    <h1>
                        <span>Unlock Flavorful Adventures:<br>Your Recipe Journey Begins Here!</span>
                    </h1>
                </div>
            
                <div class="last-recipes">
                    <h1>Last Recipes:</h1>
                </div>
                <div class="element-recipe-group">
                    <?php
                    // Output recipes
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="element-recipe">';
                        echo '<img src="' . htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . '">';
                    
                        // Checking the length of the title
                        $title = mb_strlen($row['title'], 'UTF-8') > 20 ? mb_substr($row['title'], 0, 20, 'UTF-8') . '...' : $row['title'];
                    
                        echo '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
                        echo '<p>Cooking Time: ' . htmlspecialchars($row['cooking_time'], ENT_QUOTES, 'UTF-8') . ' minutes</p>';
                        echo '<a href="recipe_element.php?id=' . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . '" class="recipe-link">Get Recipe</a>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </section>
        </header> 
    </div> 
</body> 
</html>
