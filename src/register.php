<?php
/**
 * register.php - Invoice
 *
 * @author    Marc Towler <marc.towler@designdeveloprealize.com>
 * @copyright 2016 - Design Develop Realize
 */
namespace Invoice;

include_once('../vendor/autoload.php');
use Invoice\Library\Config;
use Invoice\Library\User;

$config = new Config();
$user   = new User();


if(array_key_exists('register', $_POST))
{
    $user->register($_POST);
}
?>
<html>
<head>
    <title>
        <?php echo $config->getName(); ?>
    </title>

    <link rel="stylesheet" href="Assets/css/style.css">
</head>
<body>
<section class="container">
    <div class="login">
        <h1>Register for <?php echo $config->getName(); ?></h1>
        <form method="post">
            <p><input type="text" name="user" value="" placeholder="Username"></p>
            <p><input type="password" name="password" value="" placeholder="Password"></p>
            <p><input type="text" name="email" placeholder="Email"></p>
            <p><input type="text" name="name" placeholder="Your Name"></p>
            <p class="submit"><input type="submit" name="register" value="Register"></p>
        </form>
    </div>
</section>
</body>
</html>
