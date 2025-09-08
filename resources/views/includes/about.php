<?php 
function showAbout() {
    ?>
    <div class="about-img">
        <img src="images/quinn-scp.jpg" alt="Quinn in the scp facility, give her a laaff">
    </div>
    <div class="about-text">
        <p class="about-img-label">(Courtsey of my brother, Lorne)</p>
        <p><strong>Hi! I'm Quinn Miersma, the developer of this website.</strong></p><br>
        <p>
            Decaffeinated Games is a pet project of mine: a hobby to keep sane 
            after graduating. The premise is a site that has a bunch of 
            multiplayer word games + whatever I thought would be funny, 
            with a visual layout that shares the vibe of 2005 Dunkin Donuts.
        </p><br>
        <p>
            It's been a fun way to teach myself Laravel and Ajax, and it's 
            gotten me more familiar with the LEMP stack.
        </p><br>
        <p>
            <strong>I'm currently looking for a job!</strong> So if you or a company 
            you know of is in need of someone super rad (me), please reach out!
        </p><br>
        <p><a href="https://github.com/Queeky" target="_blank">https://github.com/Queeky</a></p>
        <p><a href="http://www.linkedin.com/in/quinn-miersma" target="_blank">http://www.linkedin.com/in/quinn-miersma</a></p>
    </div>
    <?php
}

function showCharacter() {
    ?>
    // Characters will be chosen at random
    <div class="about-img">
        <img src="#" alt="#">
    </div>
    <div class="about-text">
        <p class="about-img-label">(I will put something here eventually)</p>
        <p><strong>Character text</strong> right here.</p><br>
    </div>
    <?php
}
?>