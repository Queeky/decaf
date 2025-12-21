<?php 
$hostFormData = function() { ?>
    <label for='host-title'>Set Story Title:</label> 
    <input type='text' name='host-title'> 
    <label for='starter-text'>Begin the story:</label>
    <textarea name='starter-text'>Once upon a time...</textarea> 
    <label for='host-limit'>Set Word Limit:</label> 
    <input type='number' name='host-limit' min='1' value='3'> 
    <input type='hidden' name='session' value='<?php echo session("SESSION_ID"); ?>'> 
    <button type='submit'>Submit</button> 
<?php }; 

$playTurn = function() {
    $text = DB::select("SELECT SUBSTRING_INDEX((SELECT STORY_TEXT FROM STORY WHERE GAME_ID = ?), ' ', -?) AS STORY_TEXT; ", [session("GAME.ID"), session("STORY.TURN_LIMIT")]);
    $text = json_decode(json_encode($text, true), true)[0];

    // Setting up a random placeholder (suggestion text)
    $json = json_decode(file_get_contents("json/placeholder.json"), true); 
    $range1 = count($json["placeholder"]["first"]) - 1; 
    $range2 = count($json["placeholder"]["second"]) - 1; 

    $placeholder = $json["placeholder"]["first"][rand(0, $range1)] . $json["placeholder"]["second"][rand(0, $range2)]; ?>
    <div class='story-says'>
        <p><strong>The story says: </strong><?php echo $text["STORY_TEXT"]; ?></p>
        <form action="<?php route('storyPost') ?>" method='POST'>
            <?php echo csrf_field(); ?>
            <textarea name='new-text'><?php echo $placeholder; ?></textarea>
            <button type='submit' name='game-id' value=<?php echo session("GAME.ID"); ?>>Submit</button>
            <button type='submit' name='redo' value=true>Redo</button>
            <button type='submit' class='leave-button' name='leave' value='<?php echo session("GAME.ID") ?>'>Leave Game</button>
        </form>
    </div>
<?php };

$viewAdmin = function() {
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
                    <form class='delete' action="<?php route('storyPost') ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type='submit' name='admin-delete' value=<?php echo $c["STORY_ID"]; ?>>DEL</button>
                    </form>
                    <p class='title'><?php echo $c["STORY_TITLE"]; ?></p>
                    <p class='limit'><?php echo $c["STORY_TURN_LIMIT"]; ?></p>
                    <p class='text'><?php echo $c["STORY_TEXT"]; ?></p>
                    <form class='read-more' action="<?php route('storyGet') ?>" method="GET">
                        <button type='submit' name='read-more' value=<?php echo $c["STORY_ID"]; ?>>READ</button>
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
                        <button type='submit' name='read-more' value=<?php echo $a["STORY_ID"]; ?>>READ</button>
                    </form>
                </div>
            <?php } ?>
        </div>
        <div class='story-says admin'>
            <form action="<?php route('storyPost') ?>" method='POST'>
                <?php echo csrf_field(); ?>
                <button type='submit' class='leave-button' name='leave' value='<?php echo session("GAME.ID"); ?>'>Leave Admin View</button>
            </form>
        </div>
    </div>
<?php };
?>