<?php
class Game {
    public $type; 
    public $getUrl; 
    public $postUrl; 
    public $scriptUrl; 
    public $hostFormData; 

    public function __construct($type, $hostFormData = null) {
        $this->type = $type; 
        $this->getUrl = "{$type}Get"; 
        $this->postUrl = "{$type}Post"; 
        $this->scriptUrl = "<script> var url = '{$type}'; </script>";
        $this->hostFormData = $hostFormData; 
    }

    public function showJoinOptions() { ?>
        <div class='join-game'>
            <p><a href='<?php route("{$this->getUrl}"); ?>?join=random'>Join random game</a></p>
            <p>|</p>
            <p><a href='<?php route("{$this->getUrl}"); ?>?join=private'>Join private game</a></p>
            <p>|</p>
            <p><a href='<?php route("{$this->getUrl}"); ?>?join=host'>Host game</a></p>
        </div>
    <?php }

    public function showJoinForm() {
        if ($_GET["join"] == "private" || $_GET["join"] == "random") { ?>
            <form class='join-form' action='<?php route("{$this->postUrl}") ?>' method='POST'>
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
            <form class='join-form host-form' action='<?php route("{$this->postUrl}") ?>' method='POST'>
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
                            <div class='host-form-radio'>
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
                            <?php if ($this->hostFormData) $this->hostFormData(); ?>
                        </div>
                    </div>
                </div>
            </form>
        <?php }
    }

    public function showGameInfo() {
        // To do order by most recent, would need PLAY_ID (and redo all composite keys)
        $players = DB::select("SELECT PLAY_USER FROM PLAYER WHERE GAME_ID = ?", [session("GAME.ID")]); 
        $players = json_decode(json_encode($players, true), true);
        ?>
        <div class='game-info'>
            <?php if ($this->type == "story") { ?>
                <div class='word-limit'>
                    <div>
                        <p><strong>Word Limit: </strong><?php echo session("STORY.TURN_LIMIT"); ?></p>
                    </div>
                </div>
            <?php } else if ($this->type == "consequences") { ?>
                <div class='event-counter'>
                    <div>
                        <p><strong>Events you created: </strong><?php echo session("CONSEQUENCE.EVENT_CREATED"); ?>/<?php echo session("CONSEQUENCE.EVENT_TOTAL"); ?></p>
                    </div>
                </div>
            <?php } ?>
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
            <?php if ($this->type == "story") { ?>
                <h3>Story: <?php echo session("STORY.TITLE"); ?></h3>
            <?php } else { ?>
                <h3><?php echo session("PLAYER.NAME"); ?>'s Game</h3>
            <?php } ?>
        </div>
    <?php }

    public function showGameMain() {
        if (session("GAME.RUN") == 0 && session("GAME.ID") != 1) {
            // Game waiting to run
            if (!session("PLAYER.HOST")) {
                $this->waitGame(); 
            } else {
                $this->waitGameHost(); 
            }
        } else if (session("GAME.RUN") == 1) {
            // Game is running
            if (session("PLAYER.TURN") != session("GAME.TURN")) {
                $this->waitTurn(); 
            } else if ($this->type == "story") { 
                $this->playTurnStory(); 
            }
        } else if ($this->type == "story" && !isset($readText) && session("GAME.KEY") == env("ADMIN_KEY")) {
            $this->viewAdminStory(); 
        } else if (isset($readText) && session("GAME.ID") == 1) { ?>
            <div class='admin-view read'>
                <p><?php echo $readText; //Intelliphense is mad but it works I swear ?></p>
            </div>
        <?php }
    }

    private function waitGame() { ?>
        <div class='waiting-turn'>
            <div>
                <div class='wait-box'>
                    <p>Waiting for game to begin.</p>
                    <img src='images/spongebob-waiting.gif' alt='Spongebob waiting'>
                </div>
                <form action="<?php route("{$this->postUrl}") ?>" method='POST' id='wait-game-form'>
                    <?php echo csrf_field(); ?>
                    <input type='hidden' name='wait-game' value=<?php echo session("GAME.ID"); ?>>
                    <button type='submit' class='leave-button' name='leave' value='<?php echo session("GAME.ID"); ?>'>Leave Game</button>
                </form>
            </div>
        </div>
        <?php echo $this->scriptUrl; ?>
        <script>
            var formId = "wait-game-form"; 
            var successMsg = "Waiting for game to begin"; 
            var errorMsg = "wait-game polling has failed"; 
        </script>
        <script type="text/javascript" src="js/game-poll.js"></script>
    <?php }

    private function waitGameHost() { ?>
        <div class='waiting-turn'>
            <div>
                <div class='wait-box'>
                    <p>Waiting for game to begin.</p>
                    <img src='images/wordgirl-becky.gif' alt='Wordgirl dancing'>
                </div>
                <form action="<?php route("{$this->postUrl}") ?>" method='POST' id='wait-host-form'>
                    <?php echo csrf_field(); ?>
                    <input type='hidden' name='wait-game' value=<?php echo session("GAME.ID"); ?>>
                    <button type='submit' class='leave-button start-button' name='start-game' value=<?php echo session("GAME.ID"); ?>>Start Game</button>
                    <button type='submit' class='leave-button' name='leave' value='<?php echo session("GAME.ID"); ?>'>Leave Game</button>
                </form>
            </div>
        </div>
        <?php echo $this->scriptUrl; ?>
        <script>
            var formId = "wait-host-form"; 
            var successMsg = "Waiting for you to start the game"; 
            var errorMsg = "wait-host polling has failed"; 
        </script>
        <script type="text/javascript" src="js/game-poll.js"></script>
    <?php }

    private function waitTurn() { ?>
        <div class='waiting-turn'>
            <div>
                <div class='wait-box'>
                    <p>Waiting for your turn.</p>
                    <img src='images/cyberchase-hacker.gif' alt='Hacker from Cyberchase being electrocuted'>
                </div>
                <form action='<?php route("{$this->postUrl}") ?>' method='POST' id='wait-turn-form'>
                    <?php echo csrf_field(); ?>
                    <input type='hidden' name='wait-turn' value=<?php echo session("GAME.ID"); ?>>
                    <button type='submit' class='leave-button' name='leave' value='<?php echo session("GAME.ID"); ?>'>Leave Game</button>
                </form>
            </div>
        </div>
        <?php echo $this->scriptUrl; ?>
        <script>
            var formId = "wait-turn-form"; 
            var successMsg = "Waiting for player's turn"; 
            var errorMsg = "wait-turn polling has failed"; 
        </script>
        <script type="text/javascript" src="js/game-poll.js"></script>
    <?php }

    // Active game, player's turn
    private function playTurnStory() {
        $text = DB::select("SELECT SUBSTRING_INDEX((SELECT STORY_TEXT FROM STORY WHERE GAME_ID = ?), ' ', -?) AS STORY_TEXT; ", [session("GAME.ID"), session("STORY.TURN_LIMIT")]);
        $text = json_decode(json_encode($text, true), true)[0];

        // Setting up a random placeholder (suggestion text)
        $json = json_decode(file_get_contents("json/placeholder.json"), true); 
        $range1 = count($json["placeholder"]["first"]) - 1; 
        $range2 = count($json["placeholder"]["second"]) - 1; 

        $placeholder = $json["placeholder"]["first"][rand(0, $range1)] . $json["placeholder"]["second"][rand(0, $range2)]; ?>
        <div class='story-says'>
            <p><strong>The story says: </strong><?php echo $text["STORY_TEXT"]; ?></p>
            <form action="<?php route("{$this->postUrl}") ?>" method='POST'>
                <?php echo csrf_field(); ?>
                <textarea name='new-text'><?php echo $placeholder; ?></textarea>
                <button type='submit' name='game-id' value=<?php echo session("GAME.ID"); ?>>Submit</button>
                <button type='submit' name='redo' value=true>Redo</button>
                <button type='submit' class='leave-button' name='leave' value='<?php echo session("GAME.ID") ?>'>Leave Game</button>
            </form>
        </div>
    <?php }

    private function viewAdminStory() {
        $completed = DB::select("SELECT STORY_ID, LEFT(STORY_TITLE, 30) AS STORY_TITLE, LEFT(STORY_TEXT, 90) AS STORY_TEXT, STORY_TURN_LIMIT FROM STORY WHERE GAME_ID = 1 ORDER BY STORY_ID DESC"); 
        $completed = json_decode(json_encode($completed, true), true);

        $active = DB::select("SELECT STORY_ID, LEFT(STORY_TITLE, 30) AS STORY_TITLE, LEFT(STORY_TEXT, 90) AS STORY_TEXT, STORY_TURN_LIMIT FROM STORY WHERE GAME_ID != 1 ORDER BY STORY_ID DESC"); 
        $active = json_decode(json_encode($active, true), true);
        ?>
        <div class='admin-stories'>
            <div class='completed'>
                <h3>COMPLETED STORIES</h3>
                <?php foreach ($completed as $c) { ?>
                    <div>
                        <form class='delete' action="<?php route("{$this->postUrl}") ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type='submit' name='admin-delete' value=<?php echo $c["STORY_ID"]; ?>>DEL</button>
                        </form>
                        <p class='title'><?php echo $c["STORY_TITLE"]; ?></p>
                        <p class='limit'><?php echo $c["STORY_TURN_LIMIT"]; ?></p>
                        <p class='text'><?php echo $c["STORY_TEXT"]; ?></p>
                        <form class='read-more' action="<?php route("{$this->getUrl}") ?>" method="GET">
                            <button type='submit' name='read-more' value=<?php echo $c["STORY_ID"]; ?>>READ</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <div class='active'>
                <h3>ACTIVE STORIES</h3>
                <?php foreach ($active as $a) { ?>
                    <div>
                        <form class='delete' action="<?php route("{$this->postUrl}") ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type='button'>DEL</button>
                        </form>
                        <p class='title'><?php echo $a["STORY_TITLE"]; ?></p>
                        <p class='limit'><?php echo $a["STORY_TURN_LIMIT"]; ?></p>
                        <p class='text'><?php echo $a["STORY_TEXT"]; ?></p>
                        <form class='read-more' action="<?php route("{$this->getUrl}") ?>" method="GET">
                            <button type='submit' name='read-more' value=<?php echo $a["STORY_ID"]; ?>>READ</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <div class='story-says admin'>
                <form action="<?php route("{$this->postUrl}") ?>" method='POST'>
                    <?php echo csrf_field(); ?>
                    <button type='submit' class='leave-button' name='leave' value='<?php echo session("GAME.ID"); ?>'>Leave Admin View</button>
                </form>
            </div>
        </div>
    <?php }
}
?>