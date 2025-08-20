<?php 
function showLeft() {
    ?>
    <div class='left-bar'>
        <img src='images/bubbles.gif' alt='Carbonation bubbles'>
    </div>
    <?php
}

function showRight() {
    $json = json_decode(file_get_contents("json/link.json"), true);
    ?>
    <div class='right-bar'>
        <ul class='links'>
            <?php for ($i = 0; $i < count($json["rightbar-link"]["text"]); $i++) {
                echo "<li><a target='_blank' href='{$json["rightbar-link"]["url"][$i]}'>{$json["rightbar-link"]["text"][$i]}</a></li>"; 
            } ?>
        </ul>
    </div>
    <?php 
}
?>