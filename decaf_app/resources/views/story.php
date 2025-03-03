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
    $_SESSION["GAME_PASS"] = $avail["GAME_PASS"]; 
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

if (isset($leftGame)) {
    unset($_SESSION["GAME_ID"], $_SESSION["GAME_KEY"], $_SESSION["GAME_PASS"], $_SESSION["GAME_RUN"], $_SESSION["GAME_TURN"], $_SESSION["STORY_TITLE"], $_SESSION["STORY_TEXT"], $_SESSION["STORY_TURN_LIMIT"], $_SESSION["PLAY_USER"]); 
}

if (isset($gameId)) {
    $_SESSION["GAME_ID"] = $gameId[0]["@gameId := GAME_ID"]; 
    $_SESSION["GAME_KEY"] = $_POST["host-key"]; 
    $_SESSION["GAME_PASS"] = $_POST["host-pass"]; 
    $_SESSION["GAME_RUN"] = 0; 
    $_SESSION["GAME_TURN"] = 1; 
    $_SESSION["STORY_TITLE"] = $_POST["host-title"]; 
    $_SESSION["STORY_TEXT"] = $_POST["starter-text"]; 
    $_SESSION["STORY_TURN_LIMIT"] = $_POST["host-limit"]; 
    $_SESSION["PLAY_USER"] = ["username" => $_POST["host-user"], "turn" => 0, "host" => true]; 
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
                        } else {
                            ?>
                            <div class='game-instruct'>
                                <p>
                                    <strong>First time playing?</strong><br><br>
                                    In Run-On Story, the goal is for you (and a group of friends) to collaborate on a story. The catch is that, each turn, you can only see the story's final few words and must add more based on the limited amount you know. <br><br>
                                    You can either build a cohesive tale with context clues and teamwork, or you can make something really stupid. 
                                </p>
                                <p>
                                    Every game begins with some starter text and a word limit of the host's choosing. Here's what a few turns may look like. <br><br>
                                    <strong>Word Limit: </strong>
                                    3 <br>
                                    <strong>Story: </strong> 
                                    I failed my driver's license exam, so I had to <br>
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
                                    <strong>Story: </strong>
                                    I failed my driver's license exam, so I had to walk my cat. I really love to eat sand.
                                </p>
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