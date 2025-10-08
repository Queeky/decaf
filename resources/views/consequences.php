<?php 
use Illuminate\Support\Facades\Log;

if (isset($err)) {
    switch ($err["errCode"]) {
        case "JP": 
            $_GET["join"] = "private"; 
            break; 
        case "JH": 
            $_GET["join"] = "host"; 
            break; 
        case "JR": 
            $_GET["join"] = "random"; 
            break; 
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Consequences</title>
        <link rel="stylesheet" href="css/style.css">
        <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
        <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
    </head>
    <body id="body">
        <?php 
        include_once("includes/headNavFoot.php"); 
        include_once("includes/bars.php");
        include_once("includes/game.php");
        include_once("includes/consequences.php");

        $game = new Game("consequences", $hostFormData); 

        showHead(); 
        showNav(); 
        ?>
        <div class="full-page">
            <?php showLeft(); ?>
            <div class="content">
                <?php  
                if (isset($limitMessage) && !isset($_POST["redo"])) {
                    showError($limitMessage); 
                } else if (isset($err)) {
                    showError($err["errMsg"]); 
                }
                ?>
                <div class='upper-content' style='border-bottom: 0.2vw solid var(--blue1); padding: 0;'>
                    <?php
                    !(session("GAME.KEY") && session("GAME.PASS")) ? $game->showJoinOptions() : $game->showGameInfo(); 
                    ?>
                </div>
                <div class='inner-content consequences-content'>
                    <?php if (session("GAME.KEY") && session("GAME.PASS")) {
                        $game->showGameMain(); 
                    } else if (isset($_GET["join"])) {
                        $game->showJoinForm(); 
                    } else { ?>
                        <div class='game-instruct'>
                            <p>Put something here.</p>
                        </div>
                   <?php } ?>
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        
    </body>
</html>
