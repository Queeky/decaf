<?php 
use Illuminate\Support\Facades\Log;

$hostFormData = function() { ?>
    <div class="host-form-radio">
    <div>
        <input type="radio" name="conseq-round-length" id="choice-super-speedy" value="1">
        <label for="choice-super-speedy">Super Speedy (1 Round)</label>
    </div>
    <div>
        <input type="radio" name="conseq-round-length" id="choice-speedy" value="2">
        <label for="choice-speedy">Speedy (2 Rounds)</label>
    </div>
    <div>
        <input type="radio" name="conseq-round-length" id="choice-average" value="3">
        <label for="choice-super-speedy">Average (3 Rounds)</label>
    </div>
    <div>
        <input type="radio" name="conseq-round-length" id="choice-flowstate" value="5">
        <label for="choice-flowstate">Flowstate (5 Rounds)</label>
    </div>
    <div>
        <input type="radio" name="conseq-round-length" id="choice-marathon" value="10">
        <label for="choice-marathon">Marathon (10 Rounds)</label>
    </div>
    </div>
<?php }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Consequences</title>
        <link rel="stylesheet" href="css/style.css">
        <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
        <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
    </head>
    <body id="body">
        <?php 
        include_once("includes/headNavFoot.php"); 
        include_once("includes/bars.php");

        showHead(); 
        showNav(); 
        ?>
        <div class="full-page">
            <?php showLeft(); ?>
            <div class="content">
                <?php  
                if (isset($limitMessage) && !isset($_POST["redo"])) {
                    showError($limitMessage); 
                } else if (isset($err)) {
                    showError($err["errMsg"]); 
                }
                ?>
                <div class='upper-content' style='border-bottom: 0.2vw solid var(--blue1); padding: 0;'>
                    <?php
                    !(session("GAME.KEY") && session("GAME.PASS")) ? showJoinOptions() : showGameInfo(); 
                    ?>
                </div>
            </div>

        </div>
        
    </body>
</html>
