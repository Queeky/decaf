<?php 
use Illuminate\Support\Facades\Log;
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
        include_once("includes/headNavFoot.php"); 
        include_once("includes/bars.php"); 
        include_once("includes/index.php"); 

        showHead(); 
        showNav(); 
        ?>
        <div class='full-page'>
            <?php showLeft(); ?>
            <div class='content'>
                <?php  
                if (session("PLAYER.HOST")) {
                    showError("Your people need you, captain! (You are currently <strong>hosting a game</strong>.)"); 
                } else if (session("PLAYER") && !session("PLAYER.HOST")) {
                    showError("You're coming back... right? (You are currently <strong>in a game</strong>.)"); 
                }
                ?>
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
        <?php showFoot(); ?>
    </body>
</html>