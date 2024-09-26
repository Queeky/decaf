<?php 
function showJoinOptions() {
    echo "<div class='join-game'>"; 

    echo "<p><a href='#'>Join random game</a></p>"; 
    echo "<p>|</p>"; 
    echo "<p><a href='oneWord.php?join=private'>Join private game</a></p>"; 
    echo "<p>|</p>"; 
    echo "<p><a href='#'>Host game</a></p>"; 

    echo "</div>"; 
}

function showGameInfo() {
    echo "<div class='game-info'>"; 

    echo "<div class='word-limit'><p><strong>Word Limit: </strong>{$_SESSION["STORY_TURN_LIMIT"]}</p></div>"; 
    echo "<div class='users-connected'><p><strong>Connected: </strong>0</p></div>"; 
    echo "<div class='room-key'><p><strong>Room Key: </strong>{$_SESSION["GAME_KEY"]}</p></div>"; 

    echo "<h3>Story: {$_SESSION["STORY_TITLE"]}</h3>"; 

    echo "</div>"; 
}

function showJoinForm() {
    if ($_GET["join"] == "private") {
        echo "<form class='join-form' action='oneWord.php' method='GET'>"; 
        echo "<div>"; 
        echo "<label for='key'>Room Key:</label>"; 
        echo "<input type='text' name='key'>"; 
        echo "<label for='pass'>Password:</label>"; 
        echo "<input type='password' name='pass'>"; 
        echo "<button type='submit'>Submit</button>"; 
        echo "</div>"; 
        echo "</form>"; 
    }
}

function showGameMain() {
    $text = DB::table("STORY")
                ->select("STORY_TEXT")
                ->get();

    $text = json_decode(json_encode($text, true), true); 

    echo "<div class='story-says'>"; 
    echo "<p><strong>The story says: </strong>{$text[0]["STORY_TEXT"]}</p>"; 
    echo "</div>"; 
}
        
?>