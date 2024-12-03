<!DOCTYPE html>
<html>
    <head>
        <title>Reverse Ajax please work I'm begging you</title>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
        <link rel="stylesheet" href="css/style.css">
        <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
    </head>
    <body>
        <?php 
        // include_once("includes/test.inc.php"); 
        ?>
        <script>
            function poll() {
                $.ajax({
                    type: 'POST',
                    url: 'test.php',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        document.getElementById("test-container").innerHTML = response.html; 
                        console.log("working"); 
                    },
                    error: function () {
                        console.log("You goofed up somewhere, good luck finding where"); 
                    }
                });
            }

            $(document).ready(function () {
                setInterval(poll, 5000);
            }); 

        </script>
        <?php 
        if (isset($x)) {
            echo "<br>This also got sent, yay<br><br>"; 
            // var_dump($x); 
            echo "<strong>{$x["TEST_TEXT"]}</strong>"; 
        }
        ?>
        <div id="test-container">
            <div>Hey there cowboy, please don't refresh</div>
            <div>Image for testing below!</div>
            <img src="images/mantis_1.jpg" alt="Mantis just chilling" style="width: 20vm;">
            <!-- <form action="test.php" method="POST" id="myform">
                <input type="hidden" name="fakeInput" value="cranberries">
            </form> -->
        </div>
        
    </body>
</html>