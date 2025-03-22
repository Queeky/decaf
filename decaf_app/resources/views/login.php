<?php 
session_start(); 
// FOR TESTING
if (!isset($_SESSION["LOGIN_ADDRESS"])) {
    $_SESSION["LOGIN_ADDRESS"] = "127.0.0.1:8000"; 
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="css/style.css">
        <?php 
        // Checking login
        if (isset($loginTrue)) {
            $_SESSION["LOGIN_SUCCESS"] = true; 

            echo "<meta http-equiv='refresh' content='0; url=http://{$_SESSION["LOGIN_ADDRESS"]}/index.php'>"; 
        }
        ?>
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
