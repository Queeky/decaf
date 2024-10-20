<?php 
// session_start(); 
use Illuminate\Support\Facades\Log;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <?php 
        if (isset($message)) {
            echo "<p>{$message[rand(0, count($message) - 1)]}</p>";
        }
        ?>
        <div class='web-login'>
            <div>
                <p>Losers only</p>
                <form action="<?php echo route("loginPost"); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <label for="pass">Password: </label>
                    <input type="password" name="pass">
                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>
    </body>
</html>
