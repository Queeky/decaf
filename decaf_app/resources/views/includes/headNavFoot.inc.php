<?php
function showHead() {
    if (!isset($_SESSION["SESSION_ID"])) $_SESSION["SESSION_ID"] = session_id(); 
    ?>
    <header>
        <img src='images/logoBlue.png'>
        <div class='header-triangle'></div>
        <form action='index.php' method='GET'>
            <input class='search-bar' type='text'>
            <a type='submit' href='index.php'><img src='images/search-icon.png'></a>
        </form>
    </header>
    <?php
}

function showNav() {
    ?>
    <nav>
        <ul>
            <li><a href='index.php'>HOME</a></li>
            <li><a href='about.php'>ABOUT</a></li>
            <!-- <li><a href='index.php'>MADLIBS</a></li> -->
            <li><a href='story.php'>BLUE RASPBERRY</a></li>
            <!-- <li><a href='index.php'>COLORING BOOK</a></li> -->
        </ul>
    </nav>
    <?php
}

function showFoot() {
    ?>
    <footer>
        <div id='bug-form'>
            <p>
                <strong>Bugs? In <i>my</i> website? It's more likely than you think!</strong><br>
                Hey beta testers -- if you find the site behaving strangely or flat out breaking, please let me know! In the form below, describe the issue and any steps I may need to replicate the problem. Muchas gracias. 
            </p>
            <form action="#" method="GET">
                <input type="hidden" name='READ' value='form doesnt work rn lol whoopsie'>
                <div>
                    <label for="bug-name">Name (can be fake):</label>
                    <input type="text" name='bug-name'>
                </div>
                <div class='bug-msg'>
                    <!-- Remove null values when I get this working! -->
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
    ?>
    <div class='error-message'>
        <p><?php echo $message; ?></p>
    </div>
    <?php
}
?>