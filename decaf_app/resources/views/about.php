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
        <?php 
        // Checking login
        if (!isset($_SESSION["LOGIN_SUCCESS"])) {
            echo "<meta http-equiv='refresh' content='0; url=http://127.0.0.1:8000/login.php'>"; 
        }
        ?>
    </head>
    <body>
        <?php 
        include_once("includes/headNavFoot.inc.php"); 
        include_once("includes/bars.inc.php"); 

        showHead(); 
        showNav(); 
        ?>
        <div class='full-page'>
            <?php showLeft(); ?>
            <div class='content'>
                <?php  
                if (isset($_SESSION["PLAY_USER"]) && $_SESSION["PLAY_USER"]["host"]) {
                    showError("Your people need you, captain! (You are currently <strong>hosting a game</strong>.)"); 
                } else if (isset($_SESSION["PLAY_USER"])) {
                    showError("You're coming back... right? (You are currently <strong>in a game</strong>.)"); 
                }
                ?>
                <div style="display: flex; justify-content: center; align-items: center; height: 35vw;">
                    <img src="images/me.jpg" alt="" style="width: 20vw;">
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        <?php showFoot(); ?>
    </body>
</html>