<?php
/**
 * forgot.php - Invoice
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

if(isset($_SESSION['loggedin']))
{
    $user->logout();
}

if(array_key_exists('update', $_POST))
{
    $hashedpassword = $user->password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $con = $config->database();
        $stmt = $con->prepare('UPDATE users SET password = :password WHERE userid = :id');
        $stmt->execute(array(
            ':password' => $hashedpassword,
            ':id' => $_POST['uid']
        ));

        header('Location: index.php');
    } catch(\PDOException $e) {
        //need to do something here
    }

    try {
        $con = $config->database();
        $stmt = $con->prepare('DELETE FROM tmp_key WHERE userid = :id');
        $stmt->execute(array(':id' => $_POST['uid']));
    } catch(\PDOException $e) {
        echo '<p class="error">There was an issue deleting the temp key</p>';
    }
} elseif(array_key_exists('reset', $_POST))
{
    //password reset
    if($user->password_reset($_POST))
    {
        echo('<p class="notice">Email sent</p>');
    }

} elseif(isset($_GET['key']))
{
    //a key has been passed for a reset
        $return = $user->check_key($_GET['key']);
        $uid = $return['userid'];
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
                <h1><?php echo $config->getName(); ?> Set new password</h1>
                <form method="post">
                    <p><input type="password" name="password" value="" placeholder="Password"></p>
                    <p><input type="password" name="password_check" value="" placeholder="Password Check"></p>
                    <p><input type="hidden" name="uid" value="<?php $uid; ?>"></p>
                    <p class="submit"><input type="submit" name="update" value="Reset"></p>
                </form>
            </div>
        </section>
        </body>
        </html>
<?php
} else {
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
        <h1><?php echo $config->getName(); ?> Forgotten Password</h1>
        <form method="post">
            <p><input type="text" name="user" value="" placeholder="Username or Email"></p>
            <p class="submit"><input type="submit" name="reset" value="Reset"></p>
        </form>
    </div>
</section>
</body>
</html>
<?php } ?>