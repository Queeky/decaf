<?php 
function showSplash() {
    // ADD LIMIT LATER -- should only select ONE splash row
    $splash = DB::table("SPLASH")
                ->join("LINK", "SPLASH.LINK_ID", "=", "LINK.LINK_ID")
                ->select("SPLASH.SPLASH_TEXT", "LINK.LINK_TEXT", "LINK.LINK_URL")
                ->get();

    // $splash->transform(function ($item) {
    //     return (array)$item; 
    // });

    $splash = json_decode(json_encode($splash, true), true);

    echo str_replace("[LINK]", "<a target='_blank' href='{$splash[0]["LINK_URL"]}'>{$splash[0]["LINK_TEXT"]}</a>", "<p class='splash'>{$splash[0]["SPLASH_TEXT"]}.</p>"); 
}

function showMain() {
    echo "<div class='main'>"; 
    echo "<div>"; 

    echo "<h3>LATEST STORY</h3>";
    echo "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Orci eu lobortis elementum nibh tellus. Orci sagittis eu volutpat odio. Purus sit amet luctus venenatis lectus magna fringilla. Aenean sed adipiscing diam donec adipiscing tristique risus nec. Leo integer malesuada nunc vel risus commodo viverra maecenas accumsan. Felis bibendum ut tristique et. Justo laoreet sit amet cursus sit amet dictum sit. Porttitor massa id neque aliquam vestibulum morbi blandit cursus risus. Vel elit scelerisque mauris pellentesque. Faucibus purus in massa tempor nec feugiat nisl pretium fusce. Sit amet venenatis urna cursus eget nunc scelerisque viverra. Sapien eget mi </p>";  

    echo "</div>"; 
    echo "</div>"; 
}

function showSide() {
    echo "<div class='side'>"; 

    // Will have foreach loop here
    echo "<div class='daily-vocab'>"; 
    echo "<h3>WORD OF THE DAY</h3>"; 
    echo "<div>"; 
    echo "<p class='word'>LYCANTHROPY</p>"; 
    echo "<p class='word-type'>noun</p>"; 
    echo "<p class='word-def'>A delusion that one has become a wolf; the assumption of the form and characteristics of a wolf held to be possible by witchcraft or magic.</p>"; 

    echo "<ul class='synonyms'>"; 
    echo "<li>Synonyms:</li>"; 
    echo "<li><a href=''>zoanthropy</a></li>"; 
    echo "<li><a href=''>zoanthropy</a></li>"; 
    echo "<li><a href=''>zoanthropy</a></li>"; 
    echo "<li><a href=''>zoanthropy</a></li>"; 
    echo "</ul>"; 

    echo "<ul class='antonyms'>"; 
    echo "<li>Antonyms:</li>"; 
    echo "<li><a href=''>zoanthropy</a></li>"; 
    echo "<li><a href=''>zoanthropy</a></li>"; 
    echo "<li><a href=''>zoanthropy</a></li>"; 
    echo "<li><a href=''>zoanthropy</a></li>"; 
    echo "</ul>"; 

    echo "</div>"; 
    echo "</div>"; 

    echo "</div>"; 
}
?>