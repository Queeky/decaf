<?php 
session_start(); 
use Illuminate\Support\Facades\Log;

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
        // (not really a point in having this session var, then)
        $_SESSION["STORY_TURN_LIMIT"] = $gameAvail[0]["STORY_TURN_LIMIT"]; 
        $_SESSION["PLAY_USER"] = ["username" => $_GET["user"], "turn" => 0, "host" => false]; 

        DB::insert("INSERT INTO PLAYER (PLAY_USER, GAME_ID, PLAY_SESSION) VALUES (?, ?, ?)", ["{$_GET["user"]}", $_SESSION["GAME_ID"], "{$_SESSION["SESSION_ID"]}"]); 
    }
}

if (isset($_POST["leave"])) {
    if ($_SESSION["PLAY_USER"]["host"]) {
        // Either change SQL to delete from multiple tables at once, 
        // or make a stored procedure

        // Removing all non-host players
        DB::delete("DELETE P FROM PLAYER P JOIN GAME G ON P.GAME_ID = G.GAME_ID WHERE P.GAME_ID = ? AND G.GAME_HOST != P.PLAY_USER AND G.GAME_SESSION != P.PLAY_SESSION", [$_SESSION["GAME_ID"]]); 
        // Making host independent of game (so no constraint error will throw)
        DB::update("UPDATE PLAYER SET GAME_ID = null WHERE GAME_ID = ?", [$_SESSION["GAME_ID"]]); 
        // TODO: Give players the option to remove story from db once done
        DB::update("UPDATE STORY SET GAME_ID = 1 WHERE GAME_ID = ?", [$_SESSION["GAME_ID"]]); 
        // DB::delete("DELETE FROM STORY WHERE GAME_ID = ?", [$_SESSION["GAME_ID"]]); 
        DB::delete("DELETE FROM GAME WHERE GAME_ID = ?", [$_SESSION["GAME_ID"]]);
        DB::delete("DELETE FROM PLAYER WHERE GAME_ID IS NULL");  

        Log::info("Host left game"); 
    } else { 
        DB::delete("DELETE FROM PLAYER WHERE PLAY_USER = ? AND PLAY_SESSION = ?", ["{$_SESSION["PLAY_USER"]["username"]}", "{$_SESSION["SESSION_ID"]}"]); 

        Log::info($_SESSION["PLAY_USER"]["username"] . " left game");
    } 

    unset($_SESSION["GAME_ID"], $_SESSION["GAME_KEY"], $_SESSION["GAME_PASS"], $_SESSION["STORY_TITLE"], $_SESSION["STORY_TEXT"], $_SESSION["STORY_TURN_LIMIT"], $_SESSION["PLAY_USER"]); 
} 

// make these else ifs? 
if (isset($gameId)) {
    $_SESSION["GAME_ID"] = $gameId[0]["@gameId := GAME_ID"]; 
    $_SESSION["GAME_KEY"] = $_POST["key"]; 
    $_SESSION["GAME_PASS"] = $_POST["pass"]; 
    $_SESSION["STORY_TITLE"] = $_POST["title"]; 
    $_SESSION["STORY_TEXT"] = $_POST["starter-text"]; 
    $_SESSION["STORY_TURN_LIMIT"] = $_POST["limit"]; 
    $_SESSION["PLAY_USER"] = ["username" => $_POST["user"], "turn" => 0, "host" => true]; 
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
                <?php  
                if (isset($limitMessage) && !isset($_POST["redo"])) {
                    showError($limitMessage); 
                }
                ?>
                <div class='upper-content' style='border-bottom: 0.2vw solid var(--blue1); padding: 0;'>
                    <?php
                    !((isset($_SESSION["GAME_KEY"])) && (isset($_SESSION["GAME_PASS"]))) ? showJoinOptions() : showGameInfo(); 
                    ?>
                </div>
                <div class='inner-content'>
                    <?php 
                        if ((isset($_GET["join"]))) {
                            showJoinForm(); 
                            // test(); 
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