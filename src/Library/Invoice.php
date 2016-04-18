<?php
/**
 * Invoice.php - Invoice
 *
 * @author    Marc Towler <marc.towler@designdeveloprealize.com>
 *
 * @copyright 2016 - Design Develop Realize
 */
namespace Invoice\Library;

use FPDF;
use Invoice\Library;


class Invoice extends FPDF
{
    private $_columns = '';
    private $_format  = '';
    private $_angle   = 0;
    private $_config;

    /**
     * Invoice constructor.
     *
     * @param string $orientation either Portrait "P" or Landscape "L"
     * @param string $unit        measurement units
     * @param string $size        The page size
     */
    public function __construct($orientation, $unit, $size)
    {
        parent::__construct($orientation, $unit, $size);

        $this->_config = new Library\Config();
    }

    /**
     * @param integer $x The X value on a grid layout
     * @param integer $y The y value on a grid layout
     * @param integer $w The width
     * @param integer $h The height
     * @param integer $r
     * @param string  $style
     */
    public function rounded_rect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    /**
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     * @param $x3
     * @param $y3
     */
    private function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1 * $this->k, ($h - $y1) * $this->k,
            $x2 * $this->k, ($h - $y2) * $this->k, $x3 * $this->k, ($h - $y3) * $this->k));
    }

    /**
     * @param     $angle
     * @param int $x
     * @param int $y
     */
    function Rotate($angle, $x=-1, $y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->_angle!=0)
            $this->_out('Q');
        $this->_angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function _endpage()
    {
        if($this->_angle!=0)
        {
            $this->_angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

// public functions
    function sizeOfText( $texte, $largeur )
    {
        $index    = 0;
        $nb_lines = 0;
        $loop     = TRUE;
        while ( $loop )
        {
            $pos = strpos($texte, "\n");
            if (!$pos)
            {
                $loop  = FALSE;
                $ligne = $texte;
            }
            else
            {
                $ligne  = substr( $texte, $index, $pos);
                $texte = substr( $texte, $pos+1 );
            }
            $length = floor( $this->GetStringWidth( $ligne ) );
            $res = 1 + floor( $length / $largeur) ;
            $nb_lines += $res;
        }
        return $nb_lines;
    }

// Company
    function add_company( $img = '', $nom, $address )
    {
        $x1 = 10;
        $y1 = 8;
        //Positionnement en bas
        $this->SetXY( $x1, $y1 );
        $this->SetFont('Arial','B',12);
        $length = $this->GetStringWidth( $this->_config->getSettings('company') );
        $this->Cell($length, 2, $this->Image($img));
        $this->SetXY($x1, $y1 + 25);
        $this->Cell( $length, 2, $nom);
        $this->SetXY( $x1, $y1 + 29 );
        $this->SetFont('Arial','',10);
        $length = $this->GetStringWidth( $address );
        //Company Information
        $lignes = $this->sizeOfText( $address, $length) ;
        $this->MultiCell($length, 4, $address);
    }

// Label and number of invoice/estimate
    function fact_dev( $libelle, $num )
    {
        $r1  = $this->w - 80;
        $r2  = $r1 + 68;
        $y1  = 6;
        $y2  = $y1 + 2;
        $mid = ($r1 + $r2 ) / 2;

        //$texte  = $libelle . " IN " . $this->_config->getSettings('currency') . " No : " . $num;
        $texte = $libelle . " IN $ No: " . $num;
        $szfont = 12;
        $loop   = 0;

        while ( $loop == 0 )
        {
            $this->SetFont( "Arial", "B", $szfont );
            $sz = $this->GetStringWidth( $texte );
            if ( ($r1+$sz) > $r2 )
                $szfont --;
            else
                $loop ++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->rounded_rect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY( $r1+1, $y1+2);
        $this->Cell($r2-$r1 -1,5, $texte, 0, 0, "C" );
    }

// Estimate
    function addDevis( $numdev )
    {
        $string = sprintf("DEV%04d",$numdev);
        $this->fact_dev( "Devis", $string );
    }

// Invoice
    function addFacture( $numfact )
    {
        $string = sprintf("FA%04d",$numfact);
        $this->fact_dev( "Facture", $string );
    }

    function addDate( $date )
    {
        $r1  = $this->w - 61;
        $r2  = $r1 + 30;
        $y1  = 17;
        $y2  = $y1 ;
        $mid = $y1 + ($y2 / 2);
        $this->rounded_rect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line( $r1, $mid, $r2, $mid);
        $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
        $this->SetFont( "Arial", "B", 10);
        $this->Cell(10,5, "DATE", 0, 0, "C");
        $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
        $this->SetFont( "Arial", "", 10);
        $this->Cell(10,5,$date, 0,0, "C");
    }

    function addClient( $ref )
    {
        $r1  = $this->w - 31;
        $r2  = $r1 + 19;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->rounded_rect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line( $r1, $mid, $r2, $mid);
        $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
        $this->SetFont( "Arial", "B", 10);
        $this->Cell(10,5, "CLIENT", 0, 0, "C");
        $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
        $this->SetFont( "Arial", "", 10);
        $this->Cell(10,5,$ref, 0,0, "C");
    }

    function addPageNumber( $page )
    {
        $r1  = $this->w - 80;
        $r2  = $r1 + 19;
        $y1  = 17;
        $y2  = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->rounded_rect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line( $r1, $mid, $r2, $mid);
        $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
        $this->SetFont( "Arial", "B", 10);
        $this->Cell(10,5, "PAGE", 0, 0, "C");
        $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
        $this->SetFont( "Arial", "", 10);
        $this->Cell(10,5,$page, 0,0, "C");
    }

// Client address
    function addClientAdresse( $address )
    {
        $r1     = $this->w - 80;
        $r2     = $r1 + 68;
        $y1     = 40;
        $this->SetXY( $r1, $y1);
        $this->MultiCell( 60, 4, $address);
    }

// Mode of payment
    function addReglement( $mode )
    {
        $r1  = 10;
        $r2  = $r1 + 60;
        $y1  = 80;
        $y2  = $y1+10;
        $mid = $y1 + (($y2-$y1) / 2);
        $this->rounded_rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
        $this->Line( $r1, $mid, $r2, $mid);
        $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
        $this->SetFont( "Arial", "B", 10);
        $this->Cell(10,4, "PAYMENT METHOD", 0, 0, "C");
        $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1 + 5 );
        $this->SetFont( "Arial", "", 10);
        $this->Cell(10,5,$mode, 0,0, "C");
    }

// Expiry date
    function addEcheance( $date )
    {
        $r1  = 80;
        $r2  = $r1 + 40;
        $y1  = 80;
        $y2  = $y1+10;
        $mid = $y1 + (($y2-$y1) / 2);
        $this->rounded_rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
        $this->Line( $r1, $mid, $r2, $mid);
        $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
        $this->SetFont( "Arial", "B", 10);
        $this->Cell(10,4, "DUE DATE", 0, 0, "C");
        $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
        $this->SetFont( "Arial", "", 10);
        $this->Cell(10,5,$date, 0,0, "C");
    }

// VAT number
    function addNumTVA($tva)
    {
        $this->SetFont( "Arial", "B", 10);
        $r1  = $this->w - 80;
        $r2  = $r1 + 70;
        $y1  = 80;
        $y2  = $y1+10;
        $mid = $y1 + (($y2-$y1) / 2);
        $this->rounded_rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
        $this->Line( $r1, $mid, $r2, $mid);
        $this->SetXY( $r1 + 16 , $y1+1 );
        $this->Cell(40, 4, "VAT Number", '', '', "C");
        $this->SetFont( "Arial", "", 10);
        $this->SetXY( $r1 + 16 , $y1+5 );
        $this->Cell(40, 5, $tva, '', '', "C");
    }

    function addReference($ref)
    {
        $this->SetFont( "Arial", "", 10);
        $length = $this->GetStringWidth( "Reference: " . $ref );
        $r1  = 10;
        $r2  = $r1 + $length;
        $y1  = 92;
        $y2  = $y1+5;
        $this->SetXY( $r1 , $y1 );
        $this->Cell($length,4, "Reference: " . $ref);
    }

    function addCols( $tab )
    {
        //global $this->_columns;

        $r1  = 10;
        $r2  = $this->w - ($r1 * 2) ;
        $y1  = 100;
        $y2  = $this->h - 50 - $y1;
        $this->SetXY( $r1, $y1 );
        $this->Rect( $r1, $y1, $r2, $y2, "D");
        $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
        $colX = $r1;
        //$this->_columns = $tab;
        $this->_columns = $tab;
        while ( list( $lib, $pos ) = each ($tab) )
        {
            $this->SetXY( $colX, $y1+2 );
            $this->Cell( $pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line( $colX, $y1, $colX, $y1+$y2);
        }
    }

    function addLineFormat( $tab )
    {
        while ( list( $lib, $pos ) = each ($this->_columns) )
        {
            if ( isset( $tab["$lib"] ) )
                $this->_format[ $lib ] = $tab["$lib"];
        }
    }

    function lineVert( $tab )
    {
        reset( $this->_columns );
        $maxSize=0;
        while ( list( $lib, $pos ) = each ($this->_columns) )
        {
            $texte = $tab[ $lib ];
            $longCell  = $pos -2;
            $size = $this->sizeOfText( $texte, $longCell );
            if ($size > $maxSize)
                $maxSize = $size;
        }
        return $maxSize;
    }

// add a line to the invoice/estimate
    /*    $ligne = array( "REFERENCE"    => $prod["ref"],
                          "DESIGNATION"  => $libelle,
                          "QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
                          "P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
                          "MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
                          "TVA"          => $prod["tva"] );
    */
    function addLine( $ligne, $tab )
    {
        $ordonnee     = 10;
        $maxSize      = $ligne;

        reset( $this->_columns );
        while ( list( $lib, $pos ) = each ($this->_columns) )
        {
            $longCell  = $pos -2;
            $texte     = $tab[ $lib ];
            $length    = $this->GetStringWidth( $texte );
            $tailleTexte = $this->sizeOfText( $texte, $length );
            $formText  = $this->_format[ $lib ];
            $this->SetXY( $ordonnee, $ligne-1);
            $this->MultiCell( $longCell, 4 , $texte, 0, $formText);
            if ( $maxSize < ($this->GetY()  ) )
                $maxSize = $this->GetY() ;
            $ordonnee += $pos;
        }
        return ( $maxSize - $ligne );
    }

    function addNote($Note)
    {
        $this->SetFont( "Arial", "", 10);
        $length = $this->GetStringWidth( "Note : " . $Note );
        $r1  = 10;
        $r2  = $r1 + $length;
        $y1  = $this->h - 45.5;
        $y2  = $y1+5;
        $this->SetXY( $r1 , $y1 );
        $this->Cell($length,4, "Note : " . $Note);
    }

    function addCadreTVAs()
    {
        $this->SetFont( "Arial", "B", 8);
        $r1  = 10;
        $r2  = $r1 + 120;
        $y1  = $this->h - 40;
        $y2  = $y1+20;
        $this->rounded_rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
        $this->Line( $r1, $y1+4, $r2, $y1+4);
        $this->Line( $r1+5,  $y1+4, $r1+5, $y2); // avant BASES HT
        $this->Line( $r1+27, $y1, $r1+27, $y2);  // avant discount
        $this->Line( $r1+43, $y1, $r1+43, $y2);  // avant MT TVA
        $this->Line( $r1+63, $y1, $r1+63, $y2);  // avant % TVA
        $this->Line( $r1+75, $y1, $r1+75, $y2);  // avant PORT
        $this->Line( $r1+91, $y1, $r1+91, $y2);  // avant TOTAUX
        $this->SetXY( $r1+9, $y1);
        $this->Cell(10,4, "BASES HT");
        $this->SetX( $r1+27 );
        $this->Cell(10,4, "DISCOUNT");
        $this->SetX( $r1+48 );
        $this->Cell(10,4, "MT VAT");
        $this->SetX( $r1+63 );
        $this->Cell(10,4, "VAT %");
        $this->SetX( $r1+78 );
        $this->Cell(10,4, "PORT");
        $this->SetX( $r1+100 );
        $this->Cell(10,4, "TOTALS");
        $this->SetFont( "Arial", "B", 6);
        $this->SetXY( $r1+93, $y2 - 8 );
        $this->Cell(6,0, "H.T.   :");
        $this->SetXY( $r1+93, $y2 - 3 );
        $this->Cell(6,0, "T.V.A. :");
    }

    function addCadreEurosFrancs()
    {
        $r1  = $this->w - 70;
        $r2  = $r1 + 60;
        $y1  = $this->h - 40;
        $y2  = $y1+20;
        $this->rounded_rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
        $this->Line( $r1+20,  $y1, $r1+20, $y2); // avant EUROS
        $this->Line( $r1+20, $y1+4, $r2, $y1+4); // Sous Euros & Francs
        $this->Line( $r1+38,  $y1, $r1+38, $y2); // Entre Euros & Francs
        $this->SetFont( "Arial", "B", 8);
        $this->SetXY( $r1+22, $y1 );
        $this->Cell(15,4, "EUROS", 0, 0, "C");
        $this->SetFont( "Arial", "", 8);
        $this->SetXY( $r1+42, $y1 );
        $this->Cell(15,4, "FRANCS", 0, 0, "C");
        $this->SetFont( "Arial", "B", 6);
        $this->SetXY( $r1, $y1+5 );
        $this->Cell(20,4, "TOTAL TTC", 0, 0, "C");
        $this->SetXY( $r1, $y1+10 );
        $this->Cell(20,4, "DEPOSIT", 0, 0, "C");
        $this->SetXY( $r1, $y1+15 );
        $this->Cell(20,4, "NET A PAYER", 0, 0, "C");
    }

// remplit les cadres TVA / Totaux et la Note
// params  = array( "discountGlobale" => [0|1],
//                      "discount_tva"     => [1|2...],  // {la discount s'applique sur ce code TVA}
//                      "discount"         => value,     // {montant de la discount}
//                      "discount_percent" => percent,   // {pourcentage de discount sur ce montant de TVA}
//                  "FraisPort"     => [0|1],
//                      "portTTC"        => value,     // montant des frais de ports TTC
//                                                     // par defaut la TVA = 19.6 %
//                      "portHT"         => value,     // montant des frais de ports HT
//                      "portTVA"        => tva_value, // valeur de la TVA a appliquer sur le montant HT
//                  "depositExige" => [0|1],
//                      "deposit"         => value    // montant de l'DEPOSIT (TTC)
//                      "deposit_percent" => percent  // pourcentage d'DEPOSIT (TTC)
//                  "Note" => "texte"              // texte
// tab_tva = array( "1"       => 19.6,
//                  "2"       => 5.5, ... );
// invoice = array( "px_unit" => value,
//                  "qte"     => qte,
//                  "tva"     => code_tva );
    function addTVAs( $params, $tab_tva, $invoice )
    {
        $this->SetFont('Arial','',8);

        reset ($invoice);
        $px = array();
        while ( list( $k, $prod) = each( $invoice ) )
        {
            $tva = $prod["tva"];
            @ $px[$tva] += $prod["qte"] * $prod["px_unit"];
        }

        $prix     = array();
        $totalHT  = 0;
        $totalTTC = 0;
        $totalTVA = 0;
        $y = 261;
        reset ($px);
        natsort( $px );
        while ( list($code_tva, $articleHT) = each( $px ) )
        {
            $tva = $tab_tva[$code_tva];
            $this->SetXY(17, $y);
            $this->Cell( 19,4, sprintf("%0.2F", $articleHT),'', '','R' );
            if ( $params["discountGlobale"]==1 )
            {
                if ( $params["discount_tva"] == $code_tva )
                {
                    $this->SetXY( 37.5, $y );
                    if ($params["discount"] > 0 )
                    {
                        if ( is_int( $params["discount"] ) )
                            $l_discount = $params["discount"];
                        else
                            $l_discount = sprintf ("%0.2F", $params["discount"]);
                        $this->Cell( 14.5,4, $l_discount, '', '', 'R' );
                        $articleHT -= $params["discount"];
                    }
                    else if ( $params["discount_percent"] > 0 )
                    {
                        $rp = $params["discount_percent"];
                        if ( $rp > 1 )
                            $rp /= 100;
                        $rabais = $articleHT * $rp;
                        $articleHT -= $rabais;
                        if ( is_int($rabais) )
                            $l_discount = $rabais;
                        else
                            $l_discount = sprintf ("%0.2F", $rabais);
                        $this->Cell( 14.5,4, $l_discount, '', '', 'R' );
                    }
                    else
                        $this->Cell( 14.5,4, "ErrorRem", '', '', 'R' );
                }
            }
            $totalHT += $articleHT;
            $totalTTC += $articleHT * ( 1 + $tva/100 );
            $tmp_tva = $articleHT * $tva/100;
            $a_tva[ $code_tva ] = $tmp_tva;
            $totalTVA += $tmp_tva;
            $this->SetXY(11, $y);
            $this->Cell( 5,4, $code_tva);
            $this->SetXY(53, $y);
            $this->Cell( 19,4, sprintf("%0.2F",$tmp_tva),'', '' ,'R');
            $this->SetXY(74, $y);
            $this->Cell( 10,4, sprintf("%0.2F",$tva) ,'', '', 'R');
            $y+=4;
        }

        if ( $params["FraisPort"] == 1 )
        {
            if ( $params["portTTC"] > 0 )
            {
                $pTTC = sprintf("%0.2F", $params["portTTC"]);
                $pHT  = sprintf("%0.2F", $pTTC / 1.196);
                $pTVA = sprintf("%0.2F", $pHT * 0.196);
                $this->SetFont('Arial','',6);
                $this->SetXY(85, 261 );
                $this->Cell( 6 ,4, "HT : ", '', '', '');
                $this->SetXY(92, 261 );
                $this->Cell( 9 ,4, $pHT, '', '', 'R');
                $this->SetXY(85, 265 );
                $this->Cell( 6 ,4, "TVA : ", '', '', '');
                $this->SetXY(92, 265 );
                $this->Cell( 9 ,4, $pTVA, '', '', 'R');
                $this->SetXY(85, 269 );
                $this->Cell( 6 ,4, "TTC : ", '', '', '');
                $this->SetXY(92, 269 );
                $this->Cell( 9 ,4, $pTTC, '', '', 'R');
                $this->SetFont('Arial','',8);
                $totalHT += $pHT;
                $totalTVA += $pTVA;
                $totalTTC += $pTTC;
            }
            else if ( $params["portHT"] > 0 )
            {
                $pHT  = sprintf("%0.2F", $params["portHT"]);
                $pTVA = sprintf("%0.2F", $params["portTVA"] * $pHT / 100 );
                $pTTC = sprintf("%0.2F", $pHT + $pTVA);
                $this->SetFont('Arial','',6);
                $this->SetXY(85, 261 );
                $this->Cell( 6 ,4, "HT : ", '', '', '');
                $this->SetXY(92, 261 );
                $this->Cell( 9 ,4, $pHT, '', '', 'R');
                $this->SetXY(85, 265 );
                $this->Cell( 6 ,4, "TVA : ", '', '', '');
                $this->SetXY(92, 265 );
                $this->Cell( 9 ,4, $pTVA, '', '', 'R');
                $this->SetXY(85, 269 );
                $this->Cell( 6 ,4, "TTC : ", '', '', '');
                $this->SetXY(92, 269 );
                $this->Cell( 9 ,4, $pTTC, '', '', 'R');
                $this->SetFont('Arial','',8);
                $totalHT += $pHT;
                $totalTVA += $pTVA;
                $totalTTC += $pTTC;
            }
        }

        $this->SetXY(114,266.4);
        $this->Cell(15,4, sprintf("%0.2F", $totalHT), '', '', 'R' );
        $this->SetXY(114,271.4);
        $this->Cell(15,4, sprintf("%0.2F", $totalTVA), '', '', 'R' );

        $params["totalHT"] = $totalHT;
        $params["TVA"] = $totalTVA;
        $depositTTC=0;
        if ( $params["depositExige"] == 1 )
        {
            if ( $params["deposit"] > 0 )
            {
                $depositTTC=sprintf ("%.2F", $params["deposit"]);
                if ( strlen ($params["Note"]) == 0 )
                    $this->addNote( "deposit de $depositTTC Euros exigé à la commande.");
                else
                    $this->addNote( $params["Note"] );
            }
            else if ( $params["deposit_percent"] > 0 )
            {
                $percent = $params["deposit_percent"];
                if ( $percent > 1 )
                    $percent /= 100;
                $depositTTC=sprintf("%.2F", $totalTTC * $percent);
                $percent100 = $percent * 100;
                if ( strlen ($params["Note"]) == 0 )
                    $this->addNote( "deposit de $percent100 % (soit $depositTTC Euros) exigé à la commande." );
                else
                    $this->addNote( $params["Note"] );
            }
            else
                $this->addNote( "Drôle d'DEPOSIT !!! " . $params["Note"]);
        }
        else
        {
            if ( strlen ($params["Note"]) > 0 )
                $this->addNote( $params["Note"] );
        }
        $re  = $this->w - 50;
        $rf  = $this->w - 29;
        $y1  = $this->h - 40;
        $this->SetFont( "Arial", "", 8);
        $this->SetXY( $re, $y1+5 );
        $this->Cell( 17,4, sprintf("%0.2F", $totalTTC), '', '', 'R');
        $this->SetXY( $re, $y1+10 );
        $this->Cell( 17,4, sprintf("%0.2F", $depositTTC), '', '', 'R');
        $this->SetXY( $re, $y1+14.8 );
        $this->Cell( 17,4, sprintf("%0.2F", $totalTTC - $depositTTC), '', '', 'R');
        $this->SetXY( $rf, $y1+5 );
        $this->Cell( 17,4, sprintf("%0.2F", $totalTTC * EURO_VAL), '', '', 'R');
        $this->SetXY( $rf, $y1+10 );
        $this->Cell( 17,4, sprintf("%0.2F", $depositTTC * EURO_VAL), '', '', 'R');
        $this->SetXY( $rf, $y1+14.8 );
        $this->Cell( 17,4, sprintf("%0.2F", ($totalTTC - $depositTTC) * EURO_VAL), '', '', 'R');
    }

// add a watermark (temporary estimate, DUPLICATA...)
// call this method first
    function temporaire( $texte )
    {
        $this->SetFont('Arial','B',50);
        $this->SetTextColor(203,203,203);
        $this->Rotate(45,55,190);
        $this->Text(55,190,$texte);
        $this->Rotate(0);
        $this->SetTextColor(0,0,0);
    }
}