<?php
namespace Invoice;

include_once('../vendor/autoload.php');
use Invoice\Library;

$config = new Library\config();
$user   = new Library\User();

session_start();

//Quick check to see if the user is already logged in
if($user->is_authenticated())
{
    header('Location: index.php');
}

if(array_key_exists('login', $_POST)) {
    $username = trim($_POST['user']);
    $password = trim($_POST['password']);

    if($user->login($username, $password))
    {
        header('Location: index.php');
    } else {
        echo "error";
    }
}
?>
<html>
    <head>
        <title>
            <?php echo $config->getName(); ?>
        </title>

        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
    <section class="container">
        <div class="login">
            <h1>Login to <?php echo $config->getName(); ?></h1>
            <form method="post">
                <p><input type="text" name="user" value="" placeholder="Username or Email"></p>
                <p><input type="password" name="password" value="" placeholder="Password"></p>
                <p class="remember_me">
                    <label>
                        <input type="checkbox" name="remember_me" id="remember_me">
                        Remember me on this computer
                    </label>
                </p>
                <p class="submit"><input type="submit" name="login" value="Login"></p>
            </form>
        </div>

        <div class="login-help">
            <p>Forgot your password? <a href="forgot.php">Click here to reset it</a>.</p>
        </div>
    </section>
    </body>
</html>