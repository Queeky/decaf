<?php 
use Illuminate\Support\Facades\Log;

function showJoinOptions() {
    ?>
    <div class='join-game'>
        <p><a href='<?php route('storyGet'); ?>?join=random'>Join random game</a></p>
        <p>|</p>
        <p><a href='<?php route('storyGet'); ?>?join=private'>Join private game</a></p>
        <p>|</p>
        <p><a href='<?php route('storyGet'); ?>?join=host'>Host game</a></p>
    </div>
    <?php
}

function showGameInfo() {
    // To do order by most recent, would need PLAY_ID (and redo all composite keys)
    $players = DB::select("SELECT PLAY_USER FROM PLAYER WHERE GAME_ID = ?", [$_SESSION["GAME_ID"]]); 

    $players = json_decode(json_encode($players, true), true);
    ?>
    <div class='game-info'>
        <div class='word-limit'>
            <div>
                <p><strong>Word Limit: </strong><?php echo $_SESSION["STORY_TURN_LIMIT"]; ?></p>
            </div>
        </div>
        <div class='room-key'>
            <div>
                <p><strong>Room Key: </strong><?php echo $_SESSION["GAME_KEY"]; ?></p>
            </div>
        </div>
        <?php if ($_SESSION["PLAY_USER"]["host"]) { ?>
            <style>
                /* Resetting style if host */
                .game-info > .users-connected {
                    grid-column-start: 1; 
                    grid-column-end: 4;
                } 
                .game-info > h3 {
                    grid-column-end: 4;
                }
            </style>
            <div class='room-key' style='border-left: 0.2vw solid var(--blue1);grid-column-start: 3; grid-column-end: 4;'>
                <p><strong>Password: </strong><?php echo $_SESSION["GAME_PASS"]; ?></p>
            </div>
        <?php } ?>
        <div class='users-connected'>
            <p><strong>Connected: </strong>
                <?php
                for ($player = 0; $player < count($players); $player++) {
                    echo $players[$player]["PLAY_USER"]; 
            
                    if (($player + 1) < count($players)) echo ", "; 
                }
                ?>
            </p>
        </div>
        <h3>Story: <?php echo $_SESSION["STORY_TITLE"]; ?></h3>
    </div>
    <?php
}

function showJoinForm() {
    if ($_GET["join"] == "private" || $_GET["join"] == "random") { ?>
        <form class='join-form' action='<?php route('storyPost') ?>' method='POST'>
        <?php echo csrf_field(); ?>
            <div>
                <div class='create-username'>
                    <div>
                        <label for='join-user'>Create your username</label>
                        <input type='text' name='join-user'>
                    </div>
                </div>
                <div class='outer-div'>
                    <div class='inner-div'>
                        <?php if ($_GET["join"] == "private") { ?>
                            <label for='join-key'>Room Key:</label> 
                            <input type='text' name='join-key'>
                            <label for='join-pass'>Password:</label>
                            <input type='password' name='join-pass'>
                        <?php } else { ?>
                            <input type='hidden' name='join-public' value=true>
                            <p>
                                Once you submit your username, you will join a randomly-selected public game. Have fun!
                            </p><br>
                        <?php } ?>
                        <button type='submit'>Submit</button> 
                    </div>
                </div>
            </div>
        </form>
    <?php
    } else if ($_GET["join"] == "host") { ?>
        <form class='join-form host-form' action='<?php route('storyPost') ?>' method='POST'>
        <?php echo csrf_field(); ?>
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
                        <label class='host-pass-label' for='host-pass'>Set Password:</label>
                        <input class='host-pass' type='password' name='host-pass'> 
                        <div class='public-private'>
                            <div>
                                <input type="radio" id='choice-public' name='make-public' value='y'>
                                <label for="choice-public">Public</label>
                            </div>
                            <div>
                                <input type="radio" id='choice-private' name='make-public' value='n'>
                                <label for="choice-private">Private</label>
                            </div>
                        </div>
                        <p class='radio-msg'>
                            Making your game public will allow random players to join. If you choose public, <strong>your game will not have a room key or password.</strong>
                        </p>
                        <label for='host-title'>Set Story Title:</label> 
                        <input type='text' name='host-title'> 
                        <label for='starter-text'>Begin the story:</label>
                        <textarea name='starter-text'>Once upon a time...</textarea> 
                        <label for='host-limit'>Set Word Limit:</label> 
                        <input type='number' name='host-limit' min='1' value='3'> 
                        <input type='hidden' name='session' value='<?php echo $_SESSION["SESSION_ID"]; ?>'> 
                        <button type='submit'>Submit</button> 
                    </div>
                </div>
            </div>
        </form>
    <?php }
}

function showGameMain($get) {
    if ($_SESSION["GAME_RUN"] == 0 && $_SESSION["GAME_ID"] != 1) { 
        // Game waiting to run
        if (!$_SESSION["PLAY_USER"]["host"]) { ?>
            <div class='waiting-turn'>
                <div>
                    <div class='wait-box'>
                        <p>Waiting for game to begin.</p>
                        <img src='images/spongebob-waiting.gif' alt='Spongebob waiting'>
                    </div>
                    <form action="<?php route('storyPost') ?>" method='POST' id='wait-game-form'>
                        <?php echo csrf_field(); ?>
                        <input type='hidden' name='wait-game' value=<?php echo $_SESSION["GAME_ID"]; ?>>
                        <input type='hidden' name='wait-player' value='<?php echo $_SESSION["PLAY_USER"]["username"]; ?>'>
                        <button type='submit' class='leave-button' name='leave[user]' value='<?php echo $_SESSION["PLAY_USER"]["username"]; ?>'>Leave Game</button>
                        <input type='hidden' name='leave[id]' value=<?php echo $_SESSION["GAME_ID"]; ?>>
                        <?php if ($_SESSION["PLAY_USER"]["host"]) {
                            echo "<input type='hidden' name='leave[host]' value=true>";
                        } ?>
                    </form>
                </div>
            </div>
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
        <?php } else { ?>
            <div class='waiting-turn'>
                <div>
                    <div class='wait-box'>
                        <p>Waiting for game to begin.</p>
                        <img src='images/wordgirl-becky.gif' alt='Wordgirl dancing'>
                    </div>
                    <form action="<?php route('storyPost') ?>" method='POST' id='wait-host-form'>
                        <?php echo csrf_field(); ?>
                        <input type='hidden' name='wait-game' value=<?php echo $_SESSION["GAME_ID"]; ?>>
                        <input type="hidden" name='wait-host' value=true>
                        <button type='submit' class='leave-button start-button' name='start-game' value=<?php echo $_SESSION["GAME_ID"]; ?>>Start Game</button>
                        <button type='submit' class='leave-button' name='leave[user]' value='<?php echo $_SESSION["PLAY_USER"]["username"]; ?>'>Leave Game</button>
                        <input type='hidden' name='leave[id]' value=<?php echo $_SESSION["GAME_ID"]; ?>>
                        <input type='hidden' name='leave[host]' value=true>
                    </form>
                </div>
            </div>
            <script>
            function poll() {
                $.ajax({
                    type: 'POST',
                    url: 'story.php',
                    dataType: 'JSON',
                    data: $('#wait-host-form').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        document.getElementById("body").innerHTML = response.html; 
                        console.log("Waiting for game to begin");

                        // Temp solution maybe
                        if (!document.getElementById("wait-host-form")) {
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
        <?php }
    } else if ($_SESSION["GAME_RUN"] == 1) {
        // Game is running
        if ($_SESSION["PLAY_USER"]["turn"] != $_SESSION["GAME_TURN"]) {
            ?>
            <div class='waiting-turn'>
                <div>
                    <div class='wait-box'>
                        <p>Waiting for your turn.</p>
                        <img src='images/cyberchase-hacker.gif' alt='Hacker from Cyberchase being electrocuted'>
                    </div>
                    <form action='<?php route('storyPost') ?>' method='POST' id='wait-turn-form'>
                        <?php echo csrf_field(); ?>
                        <input type='hidden' name='wait-turn' value=<?php echo $_SESSION["GAME_ID"]; ?>>
                        <input type='hidden' name='wait-player' value='<?php echo $_SESSION["PLAY_USER"]["username"]; ?>'>
                        <button type='submit' class='leave-button' name='leave[user]' value='<?php echo $_SESSION["PLAY_USER"]["username"]; ?>'>Leave Game</button>
                        <input type='hidden' name='leave[id]' value=<?php echo $_SESSION["GAME_ID"]; ?>>
                        <?php if ($_SESSION["PLAY_USER"]["host"]) {
                            echo "<input type='hidden' name='leave[host]' value=true>";
                        } ?>  
                    </form>
                </div>
            </div>
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
            ?>
            <div class='story-says'>
                <p><strong>The story says: </strong><?php echo $text[0]["STORY_TEXT"]; ?></p>
                <form action="<?php route('storyPost') ?>" method='POST'>
                    <?php echo csrf_field(); ?>
                    <textarea name='new-text'><?php echo $placeholder; ?></textarea>
                    <button type='submit' name='game-id' value=<?php echo $_SESSION["GAME_ID"]; ?>>Submit</button>
                    <button type='submit' name='redo' value=true>Redo</button>
                    <button type='submit' class='leave-button' name='leave[user]' value='<?php echo $_SESSION["PLAY_USER"]["username"]; ?>'>Leave Game</button>
                    <input type='hidden' name='leave[id]' value=<?php echo $_SESSION["GAME_ID"]; ?>>
                    <?php if ($_SESSION["PLAY_USER"]["host"]) {
                        echo "<input type='hidden' name='leave[host]' value=true>";
                    } ?>
                    <input type='hidden' name='turn-limit' value=<?php echo $_SESSION["STORY_TURN_LIMIT"]; ?>>
                    <input type='hidden' name='turn-range' value=<?php echo $_SESSION["GAME_TURN_RANGE"]; ?>>
                    <input type='hidden' name='player-turn' value='<?php echo $_SESSION["PLAY_USER"]["turn"]; ?>'>
                </form>
            </div>
        <?php }
    } else if (!$get && $_SESSION["GAME_KEY"] == "WODAHS") { 
        // Admin view
        $completed = DB::select("SELECT STORY_ID, STORY_TITLE, LEFT(STORY_TEXT, 70) AS STORY_TEXT, STORY_TURN_LIMIT FROM STORY WHERE GAME_ID = 1 ORDER BY STORY_ID DESC"); 
        $completed = json_decode(json_encode($completed, true), true);

        $active = DB::select("SELECT STORY_ID, STORY_TITLE, LEFT(STORY_TEXT, 70) AS STORY_TEXT, STORY_TURN_LIMIT FROM STORY WHERE GAME_ID != 1 ORDER BY STORY_ID DESC"); 
        $active = json_decode(json_encode($active, true), true);
        ?>
        <div class='admin-stories'>
            <div class='completed'>
                <h3>COMPLETED STORIES</h3>
                <?php foreach ($completed as $c) { ?>
                    <div>
                        <form class='delete' action="<?php route('storyPost') ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type='submit' name='admin-delete' value=<?php echo $c["STORY_ID"]; ?>>DEL</button>
                        </form>
                        <p class='title'><?php echo $c["STORY_TITLE"]; ?></p>
                        <p class='limit'><?php echo $c["STORY_TURN_LIMIT"]; ?></p>
                        <p class='text'><?php echo $c["STORY_TEXT"]; ?></p>
                        <form class='read-more' action="<?php route('storyGet') ?>" method="GET">
                            <button type='submit' name='admin-read' value=<?php echo $c["STORY_ID"]; ?>>READ</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <div class='active'>
                <h3>ACTIVE STORIES</h3>
                <?php foreach ($active as $a) { ?>
                    <div>
                        <form class='delete' action="<?php route('storyPost') ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type='button'>DEL</button>
                        </form>
                        <p class='title'><?php echo $a["STORY_TITLE"]; ?></p>
                        <p class='limit'><?php echo $a["STORY_TURN_LIMIT"]; ?></p>
                        <p class='text'><?php echo $a["STORY_TEXT"]; ?></p>
                        <form class='read-more' action="<?php route('storyGet') ?>" method="GET">
                            <button type='submit' name='admin-read' value=<?php echo $a["STORY_ID"]; ?>>READ</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <div class='story-says admin'>
                <form action="<?php route('storyPost') ?>" method='POST'>
                    <?php echo csrf_field(); ?>
                    <button type='submit' class='leave-button' name='leave[user]' value='<?php echo $_SESSION["PLAY_USER"]["username"]; ?>'>Leave Admin View</button>
                    <input type='hidden' name='leave[id]' value=<?php echo $_SESSION["GAME_ID"]; ?>>
                </form>
            </div>
        </div>
    <?php } else if ($get && $_SESSION["GAME_ID"] == 1) { ?>
        <div class='admin-view read'>
            <p><?php echo $get["STORY_TEXT"]; ?></p>
        </div>
    <?php }
}
?>