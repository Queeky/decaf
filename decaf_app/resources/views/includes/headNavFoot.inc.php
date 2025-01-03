<?php
function showHead() {
    if (!isset($_SESSION["SESSION_ID"])) $_SESSION["SESSION_ID"] = session_id(); 

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
    echo "<li><a href='about.php'>ABOUT</a></li>";
    echo "<li><a href='index.php'>MADLIBS</a></li>";
    echo "<li><a href='story.php'>1-WORD</a></li>";
    // echo "<li><a href='index.php'>COLORING BOOK</a></li>";
    echo "</ul>"; 

    echo "</nav>"; 
}

function showFoot() {
    ?>
    <footer>
        <div id='bug-form'>
            <p>
                <strong>Bugs? In <i>my</i> website? It's more likely than you think!</strong><br>
                Hey beta testers -- if you find the site behaving strangely or flat out breaking, please let me know! In the form below, describe the issue and any steps I may need to replicate the problem. Muchas gracias. 
            </p>
            <form action="index.php" method="POST">
                <div>
                    <label for="bug-name">Name (can be fake):</label>
                    <input type="text" name='bug-name'>
                </div>
                <div class='bug-msg'>
                    <label for="bug-msg">Your message:</label>
                    <textarea name="bug-msg" id="bug-msg" placeholder="Describe bug here"></textarea>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
        <div id='icons'>
        Icons made by <a href='https://www.flaticon.com/authors/creatype' title='Creatype'> Creatype </a> from <a href='https://www.flaticon.com/' title='Flaticon'>www.flaticon.com</a>
        </div>
    </footer>
    <?php
}

function showError($message) {
    echo "<div class='error-message'>"; 
    echo "<p>{$message}</p>"; 
    echo "</div>"; 
}
?>