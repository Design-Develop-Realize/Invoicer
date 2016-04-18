<?php
namespace Invoice;

include_once('../vendor/autoload.php');
use Invoice\Library;

$config = new Library\Config();
$user   = new Library\User();

session_start();
?>
<html>
    <head>
        <title>
            <?php echo $config->getName(); ?>
        </title>
    </head>
    <body>
        <div class="header">
            <h1>
                Welcome to <?php echo $config->getName(); ?>
            </h1>
        </div>
        <div class="content">
            <p>
<?php
if(!$user->is_authenticated())
{
    echo("                If you are a current client of " . $config->getSettings('company') . " then please <a href='login.php'>login</a>.\n");
} else {
    if($user->is_client())
    {

    } elseif($user->is_staff()) {
        echo("                Welcome back staff member!");
    }
}
?>
            </p>
        </div>
    </body>
</html>
