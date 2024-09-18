<?php 
session_start(); 
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Homepage</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <?php 
        include_once("includes/headNav.inc.php"); 
        include_once("includes/bars.inc.php"); 
        include_once("includes/index.inc.php"); 

        showHead(); 
        showNav(); 
        ?>
        <div class='full-page'>
            <?php showLeft(); ?>
            <div class='content'>
                <div class='upper-content'>
                    <?php showSplash(); ?>
                </div>
                <div class='inner-content'>
                    <?php 
                    showMain();
                    showSide(); 
                    ?>
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        <footer>
            Hey there
            <div> Icons made by <a href="https://www.flaticon.com/authors/creatype" title="Creatype"> Creatype </a> from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a></div>
        </footer>
    </body>
</html>