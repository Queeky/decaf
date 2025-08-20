<?php 
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
        if ($msg) {
            echo "<p>{$msg[rand(0, count($msg) - 1)]}</p>";
        } 
        ?>
        <div class='web-login'>
            <div>
                <p>Losers only</p>
                <form action="<?php echo route('login'); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <label for="login-pass">Password: </label>
                    <input type="password" name="login-pass">
                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>
    </body>
</html>
