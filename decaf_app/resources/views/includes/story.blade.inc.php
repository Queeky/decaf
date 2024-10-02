<?php 
function showJoinOptions() {
    echo "<div class='join-game'>"; 

    echo "<p><a href='#'>Join random game</a></p>"; 
    echo "<p>|</p>"; 
    echo "<p><a href='" . route("storyGet") . "?join=private'>Join private game</a></p>"; 
    echo "<p>|</p>"; 
    echo "<p><a href='#'>Host game</a></p>"; 

    echo "</div>"; 
}

function showGameInfo() {
    $players = DB::select("SELECT PLAY_USER FROM PLAYER WHERE GAME_ID = ?", [$_SESSION["GAME_ID"]]); 

    $players = json_decode(json_encode($players, true), true);

    echo "<div class='game-info'>"; 

    echo "<div class='word-limit'><p><strong>Word Limit: </strong>{$_SESSION["STORY_TURN_LIMIT"]}</p></div>"; 
    echo "<div class='users-connected'><p><strong>Connected: </strong>"; 

    for ($player = 0; $player < count($players); $player++) {
        echo $players[$player]["PLAY_USER"]; 

        if (($player + 1) < count($players)) echo ", "; 
    }

    echo "</p></div>"; 
    echo "<div class='room-key'><p><strong>Room Key: </strong>{$_SESSION["GAME_KEY"]}</p></div>"; 

    echo "<h3>Story: {$_SESSION["STORY_TITLE"]}</h3>"; 

    echo "</div>"; 
}

function showJoinForm() {
    if ($_GET["join"] == "private") {
        echo "<form class='join-form' action='" . route("storyGet") . "' method='GET'>"; 
        echo "<div>"; 
        echo "<div class='create-username'>"; 
        echo "<div>"; 
        echo "<label for='user'>Create your username</label>"; 
        echo "<input type='text' name='user'>"; 
        echo "</div>"; 
        echo "</div>"; 
        echo "<div class='outer-div'>"; 
        echo "<div class='inner-div'>"; 
        echo "<label for='key'>Room Key:</label>"; 
        echo "<input type='text' name='key'>"; 
        echo "<label for='pass'>Password:</label>"; 
        echo "<input type='password' name='pass'>"; 
        echo "<button type='submit'>Submit</button>"; 
        echo "</div>"; 
        echo "</div>"; 
        echo "</div>"; 
        echo "</form>"; 
    }
}

function showGameMain() {
    // Edit this later to let last n amount of words/chars
    $text = DB::table("STORY")
                ->select("STORY_TEXT")
                ->get();

    $text = json_decode(json_encode($text, true), true); 

    // Setting up a random placeholder (suggestion text)
    $json = json_decode(file_get_contents("json/placeholder.json"), true); 
    $range1 = count($json["placeholder"]["first"]) - 1; 
    $range2 = count($json["placeholder"]["second"]) - 1; 

    $placeholder = $json["placeholder"]["first"][rand(0, $range1)] . " " . $json["placeholder"]["second"][rand(0, $range2)]; 

    echo "<div class='story-says'>"; 
    echo "<p><strong>The story says: </strong>{$text[0]["STORY_TEXT"]}</p>"; 
    echo "<form action='" . route('storyPost') . "' method='POST'>"; 
    echo csrf_field(); 
    echo "<textarea name='new-text'>{$placeholder}</textarea>"; 
    echo "<button type='submit' name='game-id' value='{$_SESSION["GAME_ID"]}'>Submit</button>"; 
    echo "<button type='submit' name='redo' value=true>Redo</button>"; 
    echo "<button type='submit' class='leave-button' name='leave' value=true>Leave Game</button>"; 
    echo "</form>"; 
    echo "</div>"; 
}
?>