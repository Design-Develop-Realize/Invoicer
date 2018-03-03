<?php
/**
 * generate_invoice.php - Invoice
 *
 * @author    Marc Towler <marc.towler@designdeveloprealize.com>
 *
 * @copyright 2016 - Design Develop Realize
 */
namespace Invoice;

//define('EURO', chr(128) );
define('EURO', chr(044));
define('EURO_VAL', 6.55957 );

include_once('../vendor/autoload.php');
use Invoice\Library;

$pdf = new Library\Invoice( 'P', 'mm', 'A4' );
$pdf->AddPage();
$pdf->add_company( "http://pairofmarks.com/Assets/Images/ddrlogoconc1.png",
    "Design Develop Realize",
    "6 Long Row\n" .
    "Horsforth, LS18 5AA\n".
    "07725 918193\n");
$pdf->fact_dev( "Estimate", "TEMP" );
//$pdf->temporaire( "Temporary Estimate" );
$pdf->addDate( "03/12/2003");
$pdf->addClient("OSM");
$pdf->addPageNumber("1");
$pdf->addClientAdresse("OSM\nJohn Beckman\n35265 Willow Ave.\nClarksburg CA  95612");
$pdf->addReglement("PayPal");
$pdf->addEcheance("03/12/2003");
$pdf->addNumTVA("FR888777666");
$pdf->addReference("hmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm");
$cols=array( "ITEM"    => 26,
    "DESCRIPTION"  => 78,
    "QUANTITY"     => 22,
    "UNIT COST"      => 26,
    "LINE TOTAL" => 30,
    "VAT"          => 8 );
$pdf->addCols( $cols);
$cols=array( "ITEM"    => "L",
    "DESCRIPTION"  => "L",
    "QUANTITY"     => "C",
    "UNIT COST"      => "R",
    "LINE TOTAL" => "R",
    "VAT"          => "C" );
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols);

$y    = 109;
$line = array( "ITEM"    => "Website Maintenance",
    "DESCRIPTION"  => "Recurring Website Maintenance",
    "QUANTITY"     => "1",
    "UNIT COST"      => "400.00",
    "LINE TOTAL" => "400.00",
    "VAT"          => "1" );
$size = $pdf->addLine( $y, $line );
$y   += $size + 2;

$line = array( "ITEM"    => ".NET Hosting",
    "DESCRIPTION"  => "Setup or Renewal of Windows .NET hosting account",
    "QUANTITY"     => "1",
    "UNIT COST"      => "85.00",
    "LINE TOTAL" => "85.00",
    "VAT"          => "1" );
$size = $pdf->addLine( $y, $line );
$y   += $size + 2;

$pdf->addCadreTVAs();

// invoice = array( "px_unit" => value,
//                  "qte"     => qte,
//                  "tva"     => code_tva );
// tab_tva = array( "1"       => 19.6,
//                  "2"       => 5.5, ... );
// params  = array( "RemiseGlobale" => [0|1],
//                      "discount_tva"     => [1|2...],  // {la discount s'applique sur ce code TVA}
//                      "discount"         => value,     // {Amount of Discount}
//                      "discount_percent" => percent,   // {discount percentage on the amount of vat}
//                  "FraisPort"     => [0|1],
//                      "portTTC"        => value,     // amount of vat shipping costs
//                                                     // par defaut la TVA = 19.6 %
//                      "portHT"         => value,     // amount of vat shipping costs
//                      "portTVA"        => tva_value, // vat value to be applied tot he net amount
//                  "depositExige" => [0|1],
//                      "deposit"         => value    // amount of deposit (TTC)
//                      "deposit_percent" => percent  // percentage of deposit (TTC)
//                  "Note" => "texte"              // text
$tot_prods = array( array ( "px_unit" => 600, "qte" => 1, "tva" => 1 ),
    array ( "px_unit" =>  10, "qte" => 1, "tva" => 1 ));
$tab_tva = array( "1"       => 19.6,
    "2"       => 5.5);
$params  = array( "discountGlobale" => 1,
    "discount_tva"     => 1,       // {This discount applies to VAT}
    "discount"         => 0,       // {discount amount}
    "discount_percent" => 10,      // {percentage discount on the amound of VAT}
    "FraisPort"     => 1,
    "portTTC"        => 10,      // amount of vat shipping costs
    // par defaut la TVA = 19.6 %
    "portHT"         => 0,       // amount of vat shipping costs
    "portTVA"        => 19.6,    // vat value to be applied to the net amount
    "depositExige" => 1,
    "deposit"         => 0,     // deposit amount (TTC)
    "deposit_percent" => 15,    // percentage of deposit (TTC)
    "Note" => "With a deposit, please" );

$pdf->addTVAs( $params, $tab_tva, $tot_prods);
$pdf->addCadreEurosFrancs();
$pdf->Output();