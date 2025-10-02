<?php
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

function showGameInfo($gameType) {
    // To do order by most recent, would need PLAY_ID (and redo all composite keys)
    $players = DB::select("SELECT PLAY_USER FROM PLAYER WHERE GAME_ID = ?", [session("GAME.ID")]); 
    $players = json_decode(json_encode($players, true), true);
    ?>
    <div class='game-info'>
        <div class='word-limit'>
            <div>
                <p><strong>Word Limit: </strong><?php echo session("STORY.TURN_LIMIT"); ?></p>
            </div>
        </div>
        <div class='room-key'>
            <div>
                <p><strong>Room Key: </strong><?php echo session("GAME.KEY"); ?></p>
            </div>
        </div>
        <?php if (session("PLAYER.HOST")) { ?>
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
                <p><strong>Password: </strong><?php echo session("GAME.PASS"); ?></p>
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
        <h3>Story: <?php echo session("STORY.TITLE"); ?></h3>
    </div>
    <?php
}

function showJoinForm($gameType) {
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
    } else if ($_GET["join"] == "host") { 
        $keyDefault = ""; 
        $chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 

        for ($i = 0; $i < rand(4, 9); $i++) {
            $keyDefault = $keyDefault . substr($chars, rand(0, strlen($chars) - 1), 1); 
        }
    ?>
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
                        <label for='host-key'>Set Room Key: <br><span style='color: var(--blue3);'>(You can change the preset key.)</span></label>
                        <input type='text' name='host-key' value='<?php echo $keyDefault; ?>'> 
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
                        <?php
                            switch($gameType) {
                                case "story": ?>
                                        <label for='host-title'>Set Story Title:</label> 
                                        <input type='text' name='host-title'> 
                                        <label for='starter-text'>Begin the story:</label>
                                        <textarea name='starter-text'>Once upon a time...</textarea> 
                                        <label for='host-limit'>Set Word Limit:</label> 
                                        <input type='number' name='host-limit' min='1' value='3'> 
                                        <input type='hidden' name='session' value='<?php echo session("SESSION_ID"); ?>'> 
                                        <button type='submit'>Submit</button> 
                                    <?php break; 
                                case "consequences": ?>
                                    <?php break; 
                            }
                        ?>
                    </div>
                </div>
            </div>
        </form>
    <?php }
}
?>