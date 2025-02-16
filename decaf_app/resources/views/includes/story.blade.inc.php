<?php 
use Illuminate\Support\Facades\Log;

function showJoinOptions() {
    echo "<div class='join-game'>"; 

    echo "<p><a href='#'>Join random game</a></p>"; 
    echo "<p>|</p>"; 
    echo "<p><a href='" . route("storyGet") . "?join=private'>Join private game</a></p>"; 
    echo "<p>|</p>"; 
    echo "<p><a href='" . route("storyGet") . "?join=host'>Host game</a></p>"; 

    echo "</div>"; 
}

function showGameInfo() {
    // To do order by most recent, would need PLAY_ID (and redo all composite keys)
    $players = DB::select("SELECT PLAY_USER FROM PLAYER WHERE GAME_ID = ?", [$_SESSION["GAME_ID"]]); 

    $players = json_decode(json_encode($players, true), true);

    echo "<div class='game-info'>"; 

    echo "<div class='word-limit'><div><p><strong>Word Limit: </strong>{$_SESSION["STORY_TURN_LIMIT"]}</p></div></div>"; 
    
    echo "<div class='room-key'><div><p><strong>Room Key: </strong>{$_SESSION["GAME_KEY"]}</p></div></div>"; 

    if ($_SESSION["PLAY_USER"]["host"]) {
        // Resetting style
        echo "<style>.game-info > .users-connected {grid-column-start: 1; grid-column-end: 4;} .game-info > h3 {grid-column-end: 4;}</style>";

        echo "<div class='room-key' style='border-left: 0.2vw solid var(--blue1);grid-column-start: 3; grid-column-end: 4;'><p><strong>Password: </strong>{$_SESSION["GAME_PASS"]}</p></div>";
    }

    echo "<div class='users-connected'><p><strong>Connected: </strong>"; 

    for ($player = 0; $player < count($players); $player++) {
        echo $players[$player]["PLAY_USER"]; 

        if (($player + 1) < count($players)) echo ", "; 
    }

    echo "</p></div>"; 

    echo "<h3>Story: {$_SESSION["STORY_TITLE"]}</h3>"; 

    echo "</div>"; 
}

function showJoinForm() {
    if ($_GET["join"] == "private") {
        echo "<form class='join-form' action='" . route("storyPost") . "' method='POST'>";
        echo csrf_field();  
        ?>
        <div>
            <div class='create-username'>
                <div>
                    <label for='join-user'>Create your username</label>
                    <input type='text' name='join-user'>
                </div>
            </div>
            <div class='outer-div'>
                <div class='inner-div'>
                    <label for='join-key'>Room Key:</label> 
                    <input type='text' name='join-key'>
                    <label for='join-pass'>Password:</label>
                    <input type='password' name='join-pass'>
                    <button type='submit'>Submit</button> 
                </div>
            </div>
        </div>
        <?php
        echo "</form>"; 
    } else if ($_GET["join"] == "host") {
        echo "<form class='join-form' action='" . route('storyPost') . "' method='POST'>";
        echo csrf_field();
        ?>
        <div>
            <div class='create-username'>
                <div>
                    <label for='host-user'>Create your username</label>
                    <input type='text' name='host-user'>
                </div>
            </div>
            <div class='outer-div'>
                <div class='inner-div'>
                    <label for='host-key'>Set Room Key:</label>
                    <input type='text' name='host-key'> 
                    <label for='host-pass'>Set Password:</label>
                    <input type='password' name='host-pass'> 
                    <label for='host-title'>Set Story Title:</label> 
                    <input type='text' name='host-title'> 
                    <label for='starter-text'>Begin the story:</label>
                    <textarea name='starter-text'>Once upon a time...</textarea> 
                    <label for='host-limit'>Set Word Limit:</label> 
                    <input type='number' name='host-limit' min='1' value='3'> 
                    <input type='hidden' name='session' value='<?php $_SESSION["SESSION_ID"] ?>'> 
                    <button type='submit'>Submit</button> 
                </div>
            </div>
        </div>
        <?php
        echo "</form>"; 
    }
}

function showGameMain() {
    // Need to eventually organize these by priority
    // So script isn't going through all of them all the time
    if ($_SESSION["GAME_ID"] == 1) {
        // Admin view
        echo "<div class='story-says'>"; 
        echo "<form action='" . route('storyPost') . "' method='POST'>"; 
        echo csrf_field(); 
        echo "<p>You are in the admin view</p>"; 
        echo "<p>Wow, there's nothing to see!</p>"; 
        echo "<button type='submit' class='leave-button' name='leave[user]' value={$_SESSION["PLAY_USER"]["username"]}>Leave Game</button>"; 
        echo "<input type='hidden' name='leave[id]' value={$_SESSION["GAME_ID"]}>"; 
        if ($_SESSION["PLAY_USER"]["host"]) {
            echo "<input type='hidden' name='leave[host]' value=true>";
        } 
        echo "</form>"; 
        echo "</div>"; 
    } else if ($_SESSION["GAME_RUN"] == 0) {
        if ($_SESSION["PLAY_USER"]["host"]) {
            echo "<div class='story-wait'>"; 
            echo "<form action='" . route('storyPost') . "' method='POST'>"; 
            echo csrf_field();
            echo "<img src='images/stupid-picture.png' alt='FREE ME'>"; 
            echo "<div>"; 
            echo "<button type='submit' name='start-game' value={$_SESSION["GAME_ID"]}>Start Game</button>"; 
            echo "</div>"; 
            echo "</form>"; 
            echo "</div>"; 
        } else {
            echo "<div class='waiting-turn'>";
            echo "<div>"; 
            echo "<div class='wait-box'>"; 
            echo "<p>Waiting for game to begin.</p>"; 
            echo "<img src='images/spongebob-waiting.gif' alt='Spongebob waiting'>";
            echo "</div>";  
            echo "<form action='" . route('storyPost') . "' method='POST' id='wait-game-form'>"; 
            // echo "<form action='story.php' method='POST' id='wait-game-form'>"; 
            echo csrf_field(); 
            echo "<input type='hidden' name='wait-game' value={$_SESSION["GAME_ID"]}>"; 
            // echo "<input type='hidden' name='wait-player' value={$_SESSION["PLAY_USER"]["username"]}>"; 
            echo "<button type='submit' class='leave-button' name='leave[user]' value={$_SESSION["PLAY_USER"]["username"]}>Leave Game</button>"; 
            echo "<input type='hidden' name='leave[id]' value={$_SESSION["GAME_ID"]}>";
            if ($_SESSION["PLAY_USER"]["host"]) {
                echo "<input type='hidden' name='leave[host]' value=true>";
            }  
            echo "</form>"; 
            echo "</div>"; 
            echo "</div>"; 
            ?>
            <script>
            function poll() {
                $.ajax({
                    type: 'POST',
                    url: 'story.php',
                    dataType: 'JSON',
                    data: $('#wait-game-form').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        document.getElementById("body").innerHTML = response.html; 
                        console.log("Waiting for game to begin");

                        // Temp solution maybe
                        if (!document.getElementById("wait-game-form")) {
                            location.replace("http://127.0.0.1:8000/story.php"); 
                        }
                    },
                    error: function () {
                        console.log("You goofed up somewhere, good luck finding where"); 
                    }
                });
            }

            $(document).ready(function () {
                setInterval(poll, 5000);
            }); 
        </script>
        <?php
        }
    } else if ($_SESSION["GAME_RUN"] == 1) {
        if ($_SESSION["PLAY_USER"]["turn"] != $_SESSION["GAME_TURN"]) {
            echo "<div class='waiting-turn'>"; 
            echo "<div>"; 
            echo "<div class='wait-box'>"; 
            echo "<p>Waiting for your turn.</p>"; 
            echo "<img src='images/cyberchase-hacker.gif' alt='Hacker from Cyberchase being electrocuted'>"; 
            echo "</div>"; 

            // echo "<form action='" . route('storyPost') . "' method='POST' id='wait-turn-form'>";  
            echo "<form action='" . route('storyPost') . "' method='POST' id='wait-turn-form'>"; 
            echo csrf_field(); 
            echo "<input type='hidden' name='wait-turn' value={$_SESSION["GAME_ID"]}>"; 
            echo "<input type='hidden' name='wait-player' value={$_SESSION["PLAY_USER"]["username"]}>"; 
            echo "<button type='submit' class='leave-button' name='leave[user]' value={$_SESSION["PLAY_USER"]["username"]}>Leave Game</button>"; 
            echo "<input type='hidden' name='leave[id]' value={$_SESSION["GAME_ID"]}>";
            if ($_SESSION["PLAY_USER"]["host"]) {
                echo "<input type='hidden' name='leave[host]' value=true>";
            }  
            echo "</form>"; 
            echo "</div>"; 
            echo "</div>";
            ?>
            <script>
            function poll() {
                $.ajax({
                    type: 'POST',
                    url: 'story.php',
                    dataType: 'JSON',
                    data: $('#wait-turn-form').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        document.getElementById("body").innerHTML = response.html; 
                        console.log("Waiting for player's turn"); 

                        // Temp solution maybe
                        if (!document.getElementById("wait-turn-form")) {
                            location.replace("http://127.0.0.1:8000/story.php"); 
                        }
                    },
                    error: function () {
                        console.log("You goofed up somewhere, good luck finding where"); 
                    }
                });
            }

            $(document).ready(function () {
                setInterval(poll, 5000);
            }); 
        </script>
        <?php
        } else {
            // Active game, player's turn
            $text = DB::select("SELECT SUBSTRING_INDEX((SELECT STORY_TEXT FROM STORY WHERE GAME_ID = ?), ' ', -?) AS STORY_TEXT; ", [$_SESSION["GAME_ID"], $_SESSION["STORY_TURN_LIMIT"]]);

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
            echo "<button type='submit' class='leave-button' name='leave[user]' value={$_SESSION["PLAY_USER"]["username"]}>Leave Game</button>"; 
            echo "<input type='hidden' name='leave[id]' value={$_SESSION["GAME_ID"]}>";
            if ($_SESSION["PLAY_USER"]["host"]) {
                echo "<input type='hidden' name='leave[host]' value=true>";
            }  
            echo "<input type='hidden' name='turn-limit' value={$_SESSION["STORY_TURN_LIMIT"]}>"; 
            echo "<input type='hidden' name='turn-range' value={$_SESSION["GAME_TURN_RANGE"]}>"; 
            echo "<input type='hidden' name='player-turn' value={$_SESSION["PLAY_USER"]["turn"]}>"; 
            echo "</form>"; 
            echo "</div>"; 
        }
    } 
}
?>