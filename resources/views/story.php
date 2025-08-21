<?php 
use Illuminate\Support\Facades\Log;
// var_dump(session("GAME")); 

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
        default: 
            break; 
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Run-Away Story</title>
        <link rel="stylesheet" href="css/style.css">
        <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
        <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
    </head>
    <body id="body">
        <?php 
        include_once("includes/headNavFoot.php"); 
        include_once("includes/bars.php"); 
        include_once("includes/story.blade.php");  

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
                <div class='upper-content' style='border-bottom: 0.2vw solid var(--blue1); padding: 0;'>
                    <?php
                    !(session("GAME.KEY") && session("GAME.PASS")) ? showJoinOptions() : showGameInfo(); 
                    ?>
                </div>
                <div class='inner-content story-content'>
                    <?php 
                        if (session("GAME.KEY") && session("GAME.PASS")) {
                            showGameMain(); 
                        } else if (isset($_GET["join"])) {
                            showJoinForm(); 
                        } else if (session("STORY_COMPLETE")) { ?>
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
                                                    <button type='submit' name='delete-story' value=<?php echo session("STORY_COMPLETE.STORY_ID"); ?>>Delete</button>
                                                    <button type='submit' name='publish-story' value=<?php echo session("STORY_COMPLETE.STORY_ID"); ?>>Publish</button>
                                                    <p>Want your story on the home page? <strong>Click "Publish" to show off your masterpiece.</strong></p>
                                            <?php } else { ?>
                                                <!-- <a href="story.php">Leave</a> -->
                                                <button type='submit' name='leave-story-result' value=true>Leave</button>
                                            <?php } ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class='game-instruct'>
                                <p>
                                    <strong>First time playing Run-Away Story?</strong><br><br>
                                    The goal is for you and your team to collaborate on a story. The catch is that, each turn, you can only see the story's final few words and must add more based on the limited amount you know. <br><br>
                                    Freewrite with friends (or strangers), build a cohesive storyline, or create something really stupid. 
                                </p>
                                <div class='wrapper-1'>
                                    <p>
                                        Every game begins with some starter text and a word limit of the host's choosing. Here's what a few turns may look like. -->
                                    </p>
                                    <div class='wrapper-2'>
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