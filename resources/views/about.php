<?php 
use Illuminate\Support\Facades\Log;
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
                <div class="inner-content about">
                    <div class="about-block">
                        <div class="about-img">
                            <img src="images/quinn-scp.jpg" alt="Quinn in the scp facility, give her a laaff">
                        </div>
                        <div class="about-text">
                            <p class="about-img-label">(Courtsey of my brother, Lorne)</p>
                            <p><strong>Hi! I'm Quinn Miersma, the developer of this website.</strong></p><br>
                            <p>
                                Decaffeinated Games is a pet project of mine: a hobby to keep sane 
                                after graduating. The premise is a site that has a bunch of 
                                multiplayer word games + whatever I thought would be funny, 
                                with a visual layout that shares the vibe of 2005 Dunkin Donuts.
                            </p><br>
                            <p>
                                It's been a fun way to teach myself Laravel and Ajax, and it's 
                                gotten me more familiar with the LEMP stack.
                            </p><br>
                            <p>
                                <strong>I'm currently looking for a job!</strong> So if you or a company 
                                you know of is in need of someone super rad (me), please reach out!
                            </p><br>
                            <p><a href="https://github.com/Queeky" target="_blank">https://github.com/Queeky</a></p>
                            <p><a href="http://www.linkedin.com/in/quinn-miersma" target="_blank">http://www.linkedin.com/in/quinn-miersma</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        <?php showFoot(); ?>
    </body>
</html>