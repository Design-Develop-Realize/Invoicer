<?php
/**
 * manage_invoice.php - Invoice
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

//first lets check to see if we have a client selected or an invoice selected
if(isset($_GET['client']) && $_GET['client'] != '')
{
    
}    
?>