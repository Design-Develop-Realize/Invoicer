<?php
/**
 * add_invoice.php - Invoice
 *
 * @author    Marc Towler <marc.towler@designdeveloprealize.com>
 *
 * @copyright 2016 - Design Develop Realize
 */
namespace Invoice;

include_once('../vendor/autoload.php');
use Invoice\Library;

$config = new Library\Config();
$user   = new Library\User();

session_start();

if(!$user->is_authenticated())
{
    header('Location: login.php');
}
if(!$user->is_staff())
{
    header('Location: login.php');
}
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
