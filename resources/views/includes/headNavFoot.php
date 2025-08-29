<?php
function showHead() {
    if (!session("SESSION_ID")) session(["SESSION_ID" => session()->getId()]); 
    ?>
    <header>
        <img src='images/logoBlue.png'>
        <div class='header-triangle'></div>
        <form action='<?php echo route('index'); ?>' method='GET'>
            <input class='search-bar' type='text'>
            <a type='submit'><img src='images/search-icon.png'></a>
        </form>
    </header>
    <?php
}

function showNav() {
    ?>
    <nav>
        <ul>
            <li><a href='<?php echo route('index'); ?>'>HOME</a></li>
            <li><a href='<?php echo route('about'); ?>'>ABOUT</a></li>
            <!-- <li><a href='index.php'>MADLIBS</a></li> -->
            <li><a href='<?php echo route('storyGet'); ?>'>RUN-AWAY</a></li>
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
            </p>
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