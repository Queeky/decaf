<?php 
session_start(); 
use Illuminate\Support\Facades\Log;

$get = null; 
if (isset($adminRead)) {
    $get = $adminRead;
    $_SESSION["STORY_TITLE"] = $get["STORY_TITLE"]; 
}  

// If game exists, sets SESSION
if (isset($avail)) {
    $avail = $avail[0]; 

    $_SESSION["GAME_ID"] = $avail["GAME_ID"]; 
    $_SESSION["GAME_KEY"] = $avail["GAME_KEY"]; 
    $_SESSION["GAME_PASS"] = isset($avail["GAME_PASS"]) ? $avail["GAME_PASS"] : " "; 
    $_SESSION["GAME_RUN"] = $avail["GAME_RUN"]; 
    $_SESSION["GAME_TURN"] = $avail["GAME_TURN"]; 
    $_SESSION["STORY_TITLE"] = $avail["STORY_TITLE"]; 
    $_SESSION["STORY_TEXT"] = $avail["STORY_TEXT"]; 
    // ^^ This will need to update every turn 
    // (not really a point in having this session var, then)
    $_SESSION["STORY_TURN_LIMIT"] = $avail["STORY_TURN_LIMIT"]; 
    $_SESSION["PLAY_USER"] = ["username" => $joinUser, "turn" => 0, "host" => false]; 

    DB::insert("INSERT INTO PLAYER (PLAY_USER, GAME_ID, PLAY_SESSION) VALUES (?, ?, ?)", ["{$joinUser}", $_SESSION["GAME_ID"], "{$_SESSION["SESSION_ID"]}"]); 
}

if (isset($gameId)) {
    $_SESSION["GAME_ID"] = $gameId[0]["@gameId := GAME_ID"]; 
    $_SESSION["GAME_KEY"] = ($_POST["make-public"] == "n") ? $_POST["host-key"] : "RANDOM"; 
    $_SESSION["GAME_PASS"] = ($_POST["make-public"] == "n") ? $_POST["host-pass"] : " "; 
    $_SESSION["GAME_RUN"] = 0; 
    $_SESSION["GAME_TURN"] = 1; 
    $_SESSION["STORY_TITLE"] = $_POST["host-title"]; 
    // $_SESSION["STORY_TEXT"] = $_POST["starter-text"]; 
    $_SESSION["STORY_TURN_LIMIT"] = $_POST["host-limit"]; 
    $_SESSION["PLAY_USER"] = ["username" => $_POST["host-user"], "turn" => 0, "host" => true]; 

    Log::info("Story created! --> GAME #" . $_SESSION["GAME_ID"]); 
} else if (isset($turns)) {
    // Better way to index this without having to loop until getting to specific row?
    // Index directly?
    foreach ($turns as $turn) {
        if (($turn["PLAY_USER"] == $_SESSION["PLAY_USER"]["username"]) && ($turn["PLAY_SESSION"] == $_SESSION["SESSION_ID"])) {
            $_SESSION["PLAY_USER"]["turn"] = $turn["PLAY_TURN"]; 
            break; 
        }
    }

    $_SESSION["GAME_RUN"] = 1; 
    $_SESSION["GAME_TURN_RANGE"] = $turns[count($turns) - 1]["PLAY_TURN"]; 
}

// This cannot be in the includes folder! Will not run otherwise!
if (isset($gameTurn)) {
    $_SESSION["GAME_TURN"] = $gameTurn; 
} 

if (isset($newTurn)) {
    $_SESSION["GAME_TURN"] = $newTurn; 
}

$storyComplete = ["STORY_ID" => 333, "STORY_TITLE" => "Fake Story", "STORY_TEXT" => "This is a lot of fake text lalalalalalal I need to know how to style this page. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. This is a lot of fake text lalalalalalal I need to know how to style this page. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum"]; 
$host = true; 

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
        <title>Homepage</title>
        <link rel="stylesheet" href="css/style.css">
        <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
        <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
        <?php 
        // Checking login
        if (!isset($_SESSION["LOGIN_SUCCESS"])) {
            echo "<meta http-equiv='refresh' content='0; url=http://127.0.0.1:8000/login.php'>"; 
        }
        ?>
    </head>
    <body id="body">
        <?php 
        include_once("includes/headNavFoot.inc.php"); 
        include_once("includes/bars.inc.php"); 
        include_once("includes/story.blade.inc.php"); 

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
                    !((isset($_SESSION["GAME_KEY"])) && (isset($_SESSION["GAME_PASS"]))) ? showJoinOptions() : showGameInfo(); 
                    ?>
                </div>
                <div class='inner-content story-content'>
                    <?php 
                        if ((isset($_SESSION["GAME_KEY"])) && (isset($_SESSION["GAME_PASS"]))) {
                            showGameMain($get); 
                        } else if (isset($_GET["join"])) {
                            showJoinForm(); 
                        } else if (isset($storyComplete)) { 
                            unset($_SESSION["GAME_ID"], $_SESSION["GAME_KEY"], $_SESSION["GAME_PASS"], $_SESSION["GAME_RUN"], $_SESSION["GAME_TURN"], $_SESSION["STORY_TITLE"], $_SESSION["STORY_TURN_LIMIT"], $_SESSION["PLAY_USER"]); 
                        ?>
                            <div class='story-complete'>
                                <div>
                                    <h3><?php echo $storyComplete["STORY_TITLE"]; ?></h3>
                                    <div class='wrapper'>
                                        <p><?php echo $storyComplete["STORY_TEXT"]; ?></p>
                                        <form action="<?php route('storyPost') ?>" method="POST">
                                            <?php if ($host) { ?>
                                                    <button type='submit' name='delete-story' value=<?php echo $storyComplete["STORY_ID"]; ?>>Delete</button>
                                                    <button type='submit' name='publish-story' value=<?php echo $storyComplete["STORY_ID"]; ?>>Publish</button>
                                                    <p>Want your story on the home page? <strong>Click "Publish" to show off your masterpiece.</strong></p>
                                            <?php } else { ?>
                                                <a href="story.php">Leave</a>
                                            <?php } ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class='game-instruct'>
                                <p>
                                    <strong>First time playing?</strong><br><br>
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