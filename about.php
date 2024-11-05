<?php
session_start();
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
    <div class="page" id="about"> 
        <header class="header">
            <?php include('includes/_nav.php'); ?>
            <section class="content">
                <div class="intro-text">
                    <h1>
                        <span>Welcome to Gurman.com, your go-to spot for all things delicious!</span>
                    </h1>
                </div>
                    <div class="about-text">
                        <p> We've been around forever, making sure you have the yummiest recipes and foodie finds at your fingertips. Picture a place where tasty dishes meet easy-peasy cooking—yeah, that's us! </p>
                        <p>Gurman.com is like a virtual kitchen buddy. Whether you're a kitchen pro or just starting, we've got your back with recipes that are both classic and cool. Click around, and you'll discover a world of flavors waiting to be tasted.</p>
                        <p>We're not just a website; we're your culinary sidekick. Thanks a bunch for joining us on this tasty adventure. Let's keep the good times rolling in the kitchen!</p>
                        Today is:
                        <?php
                            echo date("m.d.Y");      
                        ?>
                    </div>
                    <div class="about-text">
                        <h2>Documentation</h2>
                        <ul>
                            <li><a href="doc/index.html">Generated documentation from source code</a></li>
                            <li><a href="https://docs.google.com/document/d/1HIqjQyvW5XRQE81uxHkBmNGjK-YUwPJJ7jpXPQc4A2g/edit?usp=sharing">Assigned task</a></li>
                            <li><a href="https://docs.google.com/document/d/1Q7QrmDA87XfhzZIav1kmXbkrx0uDikJY2XCXX5i3ApY/edit?usp=sharing">Product documentation</a></li>
                            <li><a href="https://docs.google.com/document/d/1aJrmsuCxoUjkk-zVymb6_pK3T0xHALzNRx6dqvpDnbY/edit?usp=sharing">Programming documentation</a></li>
                        </ul>
                        
                    </div>

            </section>
        </header>
        <footer class="footer">
            <p> Created by Vitalii Stepaniuk &copy; 2024</p>
        </footer>
</div>
</body>
</html>