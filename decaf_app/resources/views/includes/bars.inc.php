<?php 
function showLeft() {
    echo "<div class='left-bar'>"; 

    // Will be a foreach loop later
    // that takes 4? of the "highest scoring" images (or recent)
    // for ($i = 0; $i < 3; $i++) {
    //     echo "<div><img src='images/lord_lobster.png' alt='Lobster evangelist'></div>"; 
    // }

    echo "<div><img src='images/mantis_1.jpg' alt='Mantis headshot'></div>"; 
    echo "<div><img src='images/saiki_1.jpg' alt='Saiki staring in disbelief and disappointment'></div>"; 
    echo "<div><img src='images/lord_lobster.png' alt='Lobster evangelist'></div>"; 
    echo "<div><img src='images/transformerwiki_19.png' alt='Image from transformers comic'></div>"; 

    echo "</div>"; 
}

function showRight() {
    echo "<div class='right-bar'>"; 

    echo "<ul class='links'>"; 

    // Also a foreach loop will be here
    echo "<li><a target='_blank' href='https://classicreload.com/the-lost-treasures-of-infocom-volume-i.html'>The Lost Treasures of Infocom</a></li>"; 
    echo "<li><a target='_blank' href='https://archive.org/'>Internet Archive</a></li>"; 
    echo "<li><a target='_blank' href='https://libgen.li/'>Library Genesis</a></li>"; 
    echo "<li><a target='_blank' href='https://www.caffeineinformer.com/death-by-caffeine'>Caffeine Calculator</a></li>"; 
    echo "<li><a target='_blank' href='fakenamegenerator.com'>Fake Name Generator</a></li>"; 
    echo "<li><a target='_blank' href='teleworm.us'>Teleworm</a></li>"; 
    echo "<li><a target='_blank' href='https://www.dafont.com/'>DaFont</a></li>";  
    echo "<li><a target='_blank' href='https://pastebin.com/'>Paste Bin</a></li>";
    echo "<li><a target='_blank' href='https://pointerpointer.com/'>Pointer Pointer</a></li>";  
    echo "<li><a target='_blank' href='https://pinfruit.com/'>pinfruit</a></li>"; 

    echo "</ul>"; 

    echo "</div>"; 
}
?>