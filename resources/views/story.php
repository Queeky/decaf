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
        <title>Freewrite</title>
        <link rel="stylesheet" href="css/style.css">
        <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
        <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
    </head>
    <body id="body">
        <?php 
        include_once("includes/headNavFoot.php"); 
        include_once("includes/bars.php"); 
        include_once("includes/game.php"); 
        include_once("includes/story.php");  

        $game = new Game("story", $hostFormData, $playTurn, $viewAdmin); 

        showHead(); 
        showNav(); 
        ?>
        <div class='full-page'>
            <?php showLeft(); ?>
            <div class='content'>
                <?php  
                if (isset($limitMessage) && !isset($_POST["redo"])) {
                    showError($limitMessage); 
                } else if (isset($err)) {
                    showError($err["errMsg"]); 
                }
                ?>
                <div class='upper-content story-upper-content' style='border-bottom: 0.2vw solid var(--blue1); padding: 0;'>
                    <?php
                    !(session("STORY.ID")) ? $game->showJoinOptions() : $game->showGameInfo(); 
                    ?>
                </div>
                <div class='inner-content story-content'>
                    <?php 
                        if (session("STORY_COMPLETE")) { ?>
                            <div class='story-complete'>
                                <div>
                                    <h3><?php echo session("STORY_COMPLETE.STORY_TITLE"); ?></h3>
                                    <div class='wrapper'>
                                        <p><?php echo session("STORY_COMPLETE.STORY_TEXT"); ?></p>
                                        <form action="<?php route('storyPost') ?>" method="POST">
                                        <?php 
                                        echo csrf_field(); 
                                        ?>
                                            <?php if (session("PLAYER.HOST")) { ?>
                                                    <button type='submit' name='leave-story-result' value=true>Delete</button>
                                                    <button type='submit' name='publish-story' value=<?php echo session("STORY_COMPLETE.STORY_ID"); ?>>Publish</button>
                                                    <button type='submit' name='file-download' value=true>Download</button>
                                                    <p>Want your story on the home page? <strong>Click "Publish" to show off your masterpiece.</strong></p>
                                            <?php } else { ?>
                                                <!-- <a href="story.php">Leave</a> -->
                                                <button type='submit' name='leave-story-result' value=true>Leave</button>
                                                <button type='submit' name='file-download' value=true>Download</button>
                                            <?php } ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php } else if (session("STORY.ID")) {
                            $game->showGameMain(); 
                        } else if (isset($_GET["join"])) {
                            $game->showJoinForm(); 
                        } else { ?>
                            <div class='game-instruct'>
                                <p>
                                    <strong>First time playing Freewrite?</strong><br><br>
                                    Everyone in a Freewrite game helps build a story. The catch is, each turn, you can only see the story's final few words and must add more based on limited context.<br><br>
                                    Want to tag-team smash poetry with your mom? You can do that. Want to write fanfiction with strangers on the internet? Even better. You don't even have to be literate, all you need is a keyboard and a dream. 
                                </p>
                                <div class='wrapper-1'>
                                    <p>
                                        Every game begins with some starter text and a word limit of the host's choosing. Here's what a few turns may look like. -->
                                    </p>
                                    <div class='wrapper-2 wrapper-right'>
                                        <p>
                                            <strong>Word Limit: </strong>
                                            3 <br>
                                            <strong>Starter Text: </strong> 
                                            I failed my driver's license exam, so I had to 
                                        </p>
                                        <p>
                                            <strong class='player-1'>What player #1 sees: </strong>
                                            I had to <br>
                                            <strong class='player-1'>What player #1 writes: </strong>
                                            walk my cat. <br>
                                            <strong class='player-2'>What player #2 sees: </strong>
                                            walk my cat. <br>
                                            <strong class='player-2'>What player #2 writes: </strong>
                                            I really love <br>
                                            <strong class='player-3'>What player #3 sees: </strong>
                                            I really love <br>
                                            <strong class='player-3'>What player #3 writes: </strong>
                                            to eat sand. <br>
                                        </p>
                                        <p>
                                            <strong>Story: </strong>
                                            I failed my driver's license exam, so I had to walk my cat. I really love to eat sand.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        <?php showFoot(); ?>
    </body>
</html>