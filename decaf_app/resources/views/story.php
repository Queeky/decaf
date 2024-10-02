<?php 
session_start(); 


// NOTE: make this a controller at some point
// If GAME_ID is already set, won't run this 
// (prevents re-adding user after hard refresh)
if ((isset($_GET["key"])) && (isset($_GET["pass"])) && (isset($_GET["user"])) && (!isset($_SESSION["GAME_ID"]))) {
    $gameAvail = DB::table("GAME")
            ->select("GAME.GAME_ID", "GAME.GAME_KEY", "GAME.GAME_PASS", "STORY.STORY_TITLE", "STORY.STORY_TEXT", "STORY.STORY_TURN_LIMIT")
            ->join("STORY", "GAME.GAME_ID", "=", "STORY.GAME_ID")
            ->where("GAME_KEY", $_GET["key"])
            ->where("GAME_PASS", $_GET["pass"])
            ->get();

    $gameAvail = json_decode(json_encode($gameAvail, true), true);
    // var_dump($gameAvail); 

    // Storing results in SESSION vars
    if ($gameAvail) {
        $_SESSION["GAME_ID"] = $gameAvail[0]["GAME_ID"]; 
        $_SESSION["GAME_KEY"] = $gameAvail[0]["GAME_KEY"]; 
        $_SESSION["GAME_PASS"] = $gameAvail[0]["GAME_PASS"]; 
        $_SESSION["STORY_TITLE"] = $gameAvail[0]["STORY_TITLE"]; 
        $_SESSION["STORY_TEXT"] = $gameAvail[0]["STORY_TEXT"]; 
        // ^^ This will need to update every turn
        $_SESSION["STORY_TURN_LIMIT"] = $gameAvail[0]["STORY_TURN_LIMIT"]; 
        $_SESSION["PLAY_USER"] = $_GET["user"]; 

        DB::insert("INSERT INTO PLAYER (PLAY_USER, GAME_ID) VALUES (?, ?)", ["{$_GET["user"]}", $_SESSION["GAME_ID"]]); 
    }
}

// Change this GET to POST when changing form method
if (isset($_POST["leave"])) {
    DB::delete("DELETE FROM PLAYER WHERE PLAY_USER = ? AND GAME_ID = ?", ["{$_SESSION["PLAY_USER"]}", $_SESSION["GAME_ID"]]); 

    unset($_SESSION["GAME_ID"], $_SESSION["GAME_KEY"], $_SESSION["GAME_PASS"], $_SESSION["STORY_TITLE"], $_SESSION["STORY_TEXT"], $_SESSION["STORY_TURN_LIMIT"]);  
} 
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
        include_once("includes/headNavFoot.inc.php"); 
        include_once("includes/bars.inc.php"); 
        include_once("includes/story.blade.inc.php"); 

        showHead(); 
        showNav(); 
        ?>
        <div class='full-page'>
            <?php showLeft(); ?>
            <div class='content'>
                <div class='upper-content' style='border-bottom: 0.2vw solid var(--blue1); padding: 0;'>
                    <?php
                    !((isset($_SESSION["GAME_KEY"])) && (isset($_SESSION["GAME_PASS"]))) ? showJoinOptions() : showGameInfo(); 
                    ?>
                </div>
                <div class='inner-content'>
                    <?php 
                        if ((isset($_GET["join"]))) {
                            showJoinForm(); 
                        } else if ((isset($_SESSION["GAME_KEY"])) && (isset($_SESSION["GAME_PASS"]))) {
                            showGameMain(); 
                        }
                    ?>
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        <?php showFoot(); ?>
    </body>
</html>