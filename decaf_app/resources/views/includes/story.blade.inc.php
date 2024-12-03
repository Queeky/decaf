<?php 
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

    echo "<div class='word-limit'><p><strong>Word Limit: </strong>{$_SESSION["STORY_TURN_LIMIT"]}</p></div>"; 
    echo "<div class='users-connected'><p><strong>Connected: </strong>"; 

    for ($player = 0; $player < count($players); $player++) {
        echo $players[$player]["PLAY_USER"]; 

        if (($player + 1) < count($players)) echo ", "; 
    }

    echo "</p></div>"; 
    echo "<div class='room-key'><p><strong>Room Key: </strong>{$_SESSION["GAME_KEY"]}</p></div>"; 

    if ($_SESSION["PLAY_USER"]["host"]) {
        // Resetting style
        echo "<style>.game-info > .users-connected {width: 35%;}</style>"; 

        echo "<div class='room-key' style='border-left: 0.2vw solid var(--blue1);'><p><strong>Password: </strong>{$_SESSION["GAME_PASS"]}</p></div>";
    }

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
    } else if ($_GET["join"] == "host") {
        echo "<form class='join-form' action='" . route('storyPost') . "' method='POST'>";
        echo csrf_field();
        echo "<div>"; 
        echo "<div class='create-username'>"; 
        echo "<div>"; 
        echo "<label for='user'>Create your username</label>"; 
        echo "<input type='text' name='user'>"; 
        echo "</div>"; 
        echo "</div>"; 
        echo "<div class='outer-div'>"; 
        echo "<div class='inner-div'>"; 
        echo "<label for='key'>Set Room Key:</label>"; 
        echo "<input type='text' name='key'>"; 
        echo "<label for='pass'>Set Password:</label>"; 
        echo "<input type='password' name='pass'>"; 
        echo "<label for='title'>Set Story Title:</label>"; 
        echo "<input type='text' name='title'>"; 
        echo "<label for='starter-text'>Begin the story:</label>"; 
        echo "<textarea name='starter-text'>Once upon a time...</textarea>"; 
        echo "<label for='limit'>Set Word Limit:</label>"; 
        echo "<input type='number' name='limit'>"; 
        echo "<input type='hidden' name='session' value='{$_SESSION["SESSION_ID"]}'>"; 
        echo "<button type='submit'>Submit</button>"; 
        echo "</div>"; 
        echo "</div>"; 
        echo "</div>"; 
        echo "</form>"; 
    }
}

function test() {
    echo "TIME: " . time(); 
    header("Refresh:0");
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
        echo "<button type='submit' class='leave-button' name='leave' value=true>Leave Game</button>"; 
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
            echo "<div class='waiting-turn'><p>Waiting for game to begin.</p></div>";
            echo "<form action='" . route('storyPost') . "' method='POST' id='wait-game-form'>"; 
            echo csrf_field(); 
            echo "<input type='hidden' name='wait-game' value={$_SESSION["GAME_ID"]}>"; 
            echo "<button type='submit' class='leave-button' name='leave' value=true>Leave Game</button>"; 
            echo "</form>"; 
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
                        // console.log(response.html); 
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
            echo "<div class='waiting-turn'><p>Waiting for your turn.</p></div>"; 

            echo "<form action='" . route('storyPost') . "' method='POST' id='wait-turn-form'>"; 
            echo csrf_field(); 
            echo "<input type='hidden' name='wait-turn' value={$_SESSION["GAME_ID"]}>"; 
            echo "<button type='submit' class='leave-button' name='leave' value=true>Leave Game</button>"; 
            echo "</form>"; 

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
                        console.log("Waiting for player turn"); 
                        // console.log(response.html); 
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

            // var_dump($text); 

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
            echo "<input type='hidden' name='turn-limit' value={$_SESSION["STORY_TURN_LIMIT"]}>"; 
            echo "<input type='hidden' name='turn-range' value={$_SESSION["GAME_TURN_RANGE"]}>"; 
            echo "<input type='hidden' name='player-turn' value={$_SESSION["PLAY_USER"]["turn"]}>"; 
            echo "</form>"; 
            echo "</div>"; 
        }
    } 
}
?>