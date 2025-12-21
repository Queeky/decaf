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
                            <p>
                                <strong>First time playing Consequences?</strong><br><br>
                                Consequences is an RNG game of your own creation and requires all the tactical skill of UNO. If you're bad at strategy but can write stupid things fast, this game is for you.
                            </p>
                            <div class="wrapper-1">
                                <div class="wrapper-2 wrapper-left">
                                    <p>
                                        <strong>1ST HALF: CREATE EVENTS</strong><br><br>
                                        Creating an event is a 3-turn process. First turn, you create a prompt and two possible responses.<br><br>
                                        <strong>Write a prompt:</strong> The phone rings<br>
                                        <strong>Write response #1:</strong> Answer it<br>
                                        <strong>Write response #2:</strong> Ignore it<br><br>

                                        The event is then passed off to another player, and <i>you</i> get someone else's prompt and responses. Now you can add a <strong>positive</strong> consequence to response #1 and a <strong>negative</strong> consequence to response #2.<br><br>

                                        <strong><u>Response #1: Answer it</u></strong><br>
                                        <strong>Write a positive consequence:</strong><br><br>

                                        <strong><u>Response #2: Ignore it</u></strong><br>
                                        <strong>Write a negative consequence:</strong>
                                    </p>
                                </div>
                                <p>
                                    <strong>2ND HALF: PLAY EVENTS</strong><br><br>
                                    
                                </p>
                            </div>
                        </div>
                   <?php } ?>
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        <?php showFoot(); ?>
    </body>
</html>
