<?php
function showHead() {
    echo "<header>"; 

    echo "<img src='images/logoBlue.png'>"; 
    echo "<div class='header-triangle'></div>"; 
    echo "<form action='index.php' method='GET'>"; 
    echo "<input class='search-bar' type='text'>"; 
    echo "<a type='submit' href='index.php'><img src='images/search-icon.png'></a>"; 
    echo "</form>"; 

    echo "</header>"; 
}

function showNav() {
    echo "<nav>"; 

    echo "<ul>"; 
    echo "<li><a href='index.php'>HOME</a></li>";
    echo "<li><a href='index.php'>ABOUT</a></li>";
    echo "<li><a href='index.php'>MADLIBS</a></li>";
    echo "<li><a href='oneWord.php'>1-WORD</a></li>";
    echo "<li><a href='index.php'>COLORING BOOK</a></li>";
    echo "</ul>"; 

    echo "</nav>"; 
}
?>