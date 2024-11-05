<?php
    echo '<nav class="nav"> 
        <a href="index.php" class="nav_logo">
            <img src="img/icon.ico" alt="Gurman.com Logo">
            Gurman.com
        </a>                 
        <ul class="nav_items"> 
            <li class="nav_item"> 
                <a href="categories.php" class="nav_link">Categories</a> 
                <a href="add_recipe.php" class="nav_link">Add a Recipe</a>  
                <a href="about.php" class="nav_link">About us</a> 
            </li> 
        </ul>';

    if(isset($_SESSION["loggedin"]) === true){
        // User is logged in, show profile and logout buttons
        echo '<form action="profile.php">';
        echo '<button class="button">My Profile</button>';
        echo '</form>';
        echo '<form action="logout.php">';
        echo '<button class="button">Logout</button>';
        echo '</form>';
    } else {
        // User is not logged in, show sign in button
        echo '<form action="register.php">';
        echo '<button class="button">Sign Up</button>';
        echo '</form>';
    }

    echo '</nav>';
?>
