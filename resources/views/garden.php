<?php 
use Illuminate\Support\Facades\Log;
if (!session("KEY") && isset($_COOKIE["key"])) session(["KEY" => $_COOKIE["key"]]); 
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>About</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <?php 
        include_once("includes/headNavFoot.php"); 
        include_once("includes/bars.php"); 
        include_once("includes/garden.php");

        showHead('$n .= ($x + $y);'); 
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
                <div class="inner-content garden">
                    <div class="garden-wrapper">
                    </div>
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        <?php showFoot(); ?>
        <script type="text/javascript" src="js/generate-garden.js"></script>
    </body>
</html>