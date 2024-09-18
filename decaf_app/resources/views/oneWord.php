<?php 
session_start(); 

$gameAvail = null; 

if ((isset($_GET["key"])) && (isset($_GET["pass"]))) {
    $gameAvail = DB::table("GAME")
            ->select("GAME.GAME_ID", "GAME.GAME_KEY", "STORY.STORY_TITLE", "STORY.STORY_TEXT", "STORY.STORY_TURN_LIMIT")
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
        $_SESSION["STORY_TITLE"] = $gameAvail[0]["STORY_TITLE"]; 
        $_SESSION["STORY_TEXT"] = $gameAvail[0]["STORY_TEXT"]; 
        // ^^ This will need to update every turn
        $_SESSION["STORY_TURN_LIMIT"] = $gameAvail[0]["STORY_TURN_LIMIT"]; 
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
    </head>
    <body>
        <?php 
        include_once("includes/headNav.inc.php"); 
        include_once("includes/bars.inc.php"); 
        include_once("includes/oneWord.inc.php"); 

        showHead(); 
        showNav(); 
        ?>
        <div class='full-page'>
            <?php showLeft(); ?>
            <div class='content'>
                <div class='upper-content' style='border-bottom: 0.2vw solid var(--blue1); padding: 0;'>
                    <?php
                    !$gameAvail ? showJoinOptions() : showGameInfo(); 
                    ?>
                </div>
                <div class='inner-content'>
                    <?php 
                        if ((isset($_GET["join"])) && $_GET["join"] == "private") {
                            echo "<form class='join-form' action='oneWord.php' method='GET'>"; 
                            echo "<div>"; 
                            echo "<label for='key'>Room Key:</label>"; 
                            echo "<input type='text' name='key'>"; 
                            echo "<label for='pass'>Password:</label>"; 
                            echo "<input type='text' name='pass'>"; 
                            echo "<button type='submit'>Submit</button>"; 
                            echo "</div>"; 
                            echo "</form>"; 
                        } else if ($gameAvail) {

                        }
                    ?>
                </div>
            </div>
            <?php showRight(); ?>
        </div>
        <footer>
            Hey there
            <div> Icons made by <a href="https://www.flaticon.com/authors/creatype" title="Creatype"> Creatype </a> from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a></div>
        </footer>
    </body>
</html>