<?php 
function showLeft() {
    echo "<div class='left-bar'>"; 

    echo "<img src='images/bubbles.gif' alt='Carbonation bubbles'>"; 

    echo "</div>"; 
}

function showRight() {
    $json = json_decode(file_get_contents("json/link.json"), true); 

    echo "<div class='right-bar'>"; 
    echo "<ul class='links'>"; 

    for ($i = 0; $i < count($json["rightbar-link"]["text"]); $i++) {
        echo "<li><a target='_blank' href='{$json["rightbar-link"]["url"][$i]}'>{$json["rightbar-link"]["text"][$i]}</a></li>"; 
    }

    echo "</ul>"; 
    echo "</div>"; 
}
?>