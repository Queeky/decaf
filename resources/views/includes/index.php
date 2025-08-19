<?php 
function showSplash() {
    $json = json_decode(file_get_contents("json/splash.json"), true); 
    $index = rand(0, count($json["splash"]["full-text"]) - 1); 

    echo str_replace("[LINK]", "<a target='_blank' href='{$json["splash"]["url"][$index]}'>{$json["splash"]["hyperlink-text"][$index]}</a>", "<p class='splash'>{$json["splash"]["full-text"][$index]}</p>"); 
}

function showMain() {
    // Make these two queries one? Left or right join?
    $featured = DB::select("SELECT STORY_ID, LEFT(STORY_TITLE, 70) AS STORY_TITLE, LEFT(STORY_TEXT, 210) AS STORY_TEXT FROM STORY WHERE STORY_PUBLISH = 1 ORDER BY RAND() LIMIT 1"); 
    $stories = DB::select("SELECT STORY_ID, LEFT(STORY_TITLE, 70) AS STORY_TITLE, LEFT(STORY_TEXT, 210) AS STORY_TEXT FROM STORY WHERE STORY_PUBLISH = 1 ORDER BY STORY_ID DESC"); 

    $featured = json_decode(json_encode($featured, true), true);
    $stories = json_decode(json_encode($stories, true), true);
    ?>
    <div class='main'>
        <div class='featured'>
            <?php if ($featured) { ?>
                <h3>FEATURED STORY | <?php echo $featured[0]["STORY_TITLE"]; ?></h3>
                <p><?php echo $featured[0]["STORY_TEXT"]; ?></p>
                <form class='read-more' action="<?php route('storyGet') ?>" method="GET">
                    <button type='submit' name='read-more' value=<?php echo $featured[0]["STORY_ID"]; ?>>READ</button>
                </form>
            <?php } else { ?>
                <h3>FEATURED STORY | NONE FOUND</h3>
                <p>No stories are currently published.</p>
            <?php } ?>
        </div>
        <div>
            <h3>ALL STORIES (NEWEST TO OLDEST)</h3>
            <div class='all-stories'>
                <?php foreach ($stories as $story) { ?>
                    <div class='story-item'>
                        <p class='title'><?php echo $story["STORY_TITLE"]; ?></p>
                        <p class='text'><?php echo $story["STORY_TEXT"]; ?></p>
                        <form class='read-more' action="<?php route('storyGet') ?>" method="GET">
                            <button type='submit' name='read-more' value=<?php echo $story["STORY_ID"]; ?>>READ</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
}

function showSide() {
    ?>
    <div class='side'>
        <div class='daily-vocab'>
            <h3>WORD OF THE DAY</h3>
            <div>
                <p class='word'>LYCANTHROPY</p>
                <p class='word-type'>noun</p>
                <p class='word-def'>A delusion that one has become a wolf; the assumption of the form and characteristics of a wolf held to be possible by witchcraft or magic.</p>
                <ul class='synonyms'>
                    <li>Synonyms:</li>
                    <li><a href=''>zoanthropy</a></li>
                    <li><a href=''>zoanthropy</a></li>
                    <li><a href=''>zoanthropy</a></li>
                    <li><a href=''>zoanthropy</a></li>
                </ul>
                <ul class='antonyms'>
                    <li>Antonyms:</li>
                    <li><a href=''>zoanthropy</a></li>
                    <li><a href=''>zoanthropy</a></li>
                    <li><a href=''>zoanthropy</a></li>
                    <li><a href=''>zoanthropy</a></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}
?>