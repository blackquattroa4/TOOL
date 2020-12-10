<?php

namespace App;

use App\Helpers\BarcodeHelper;
use Codedge\Fpdf\Fpdf\Fpdf;

class SalesFormPdf extends Fpdf {

	// from sales_headers in database
	protected $dataStruct = null;

	function __construct($input, $orientation='P', $unit='mm', $size='A4')
	{
		parent::__construct($orientation, $unit, $size);

		$this->dataStruct = $input;

		// generate PDF
		$this->setAutoPageBreak(true, 30);
		$this->AliasNbPages();
		$this->AddPage();
		$pageN = $this->PageNo();
		$firstX = $this->getX();
		$firstY = $this->getY();
		foreach ($this->dataStruct['line'] as $key => $useless) {
			$quantityX = $this->getX();
			$quantityY = $this->getY();
			$this->Cell(10,15,"",'0',0,'R');
			$this->Ln(15);
			if ($pageN == $this->PageNo()) {
				$this->setXY($quantityX, $quantityY);
			} else {
				$this->setXY($firstX, $firstY);
				$pageN = $this->PageNo();
			}
			$this->setAutoPageBreak(false);
			$this->Cell(15,15,$this->dataStruct['quantity'][$key],'0B',0,'R');
			$productX = $this->getX();
			$productY = $this->getY();
			$this->setXY($productX, $productY+2);
			$this->MultiCell(40,5,$this->dataStruct['product'][$key],0,'L');
			$this->setXY($productX, $productY);
			$this->Cell(40,15,"",'0B',0,'R');
			$descriptionX = $this->getX();
			$descriptionY = $this->getY();
			$this->setXY($descriptionX, $descriptionY);
			$this->MultiCell(65,5,$this->dataStruct['description'][$key],0,'L');
			$this->setXY($descriptionX, $descriptionY);
			$this->Cell(65,15,"",'0B',0,'L');
			$this->Cell(30,15,$this->dataStruct['unitprice'][$key],'0B',0,'R');
			$subtotalX = $this->getX();
			$subtotalY = $this->getY();
			$this->MultiCell(35,5,"\n".$this->dataStruct['subtotal'][$key]."\n".(empty($this->dataStruct['discount'][$key]) ? "  " : "Disc ".$this->dataStruct['disctype'][$key].$this->dataStruct['discount'][$key]),'0B','R');
			$this->setXY($subtotalX, $subtotalY);
			$this->Cell(35,15,"",0,0,'L');
			$this->Cell(5,15,($this->dataStruct['taxable'][$key]==1 ? "T" : ""),'0B',0,'R');
			$this->Ln();
			$this->setAutoPageBreak(true, 30);
		}
		if (!preg_match("/^quote$/i", $this->dataStruct['type'])) {
			$this->Cell(5,25,'',0,0);      // use these 2 commands to figure out if we still
			$this->Ln();                   // have at least 2.0cm space left in the page
			$this->SetY(-55);  // Position at 5.5 cm from bottom
			$this->Cell(120);
			$this->MultiCell(70,5,"Untaxed subtotal"." " . $this->dataStruct['untaxed_subtotal']."\n"."Taxed subtotal"." " .$this->dataStruct['taxed_subtotal']."\n"."Tax amount"." ". $this->dataStruct['tax_amount']."\n"."Total"." ".$this->dataStruct['grand_total'], 0, 'R');
		}
	}

	// Page header
	function Header() {
		$this->Image(url('company_logo.png'),10,6,52,13,"PNG"); // Logo
		$this->Cell(145); // Move to the right
		$this->SetFont('Arial','B',15); // Arial bold 15
		$this->setTextColor(0,0,0);
		$this->setFillColor(255,255,0);

		$this->Image(BarcodeHelper::generateBarcode128($this->dataStruct['increment'], 2, 17), 155, 16, 40, 10, 'GIF');
		$this->Image(BarcodeHelper::generateBarcode128($this->dataStruct['customer'], 2, 17), 155, 16+11, 40, 10, 'GIF');

		$titleX = $this->getX();
		$titleY = $this->getY();
		$this->MultiCell(42,7,$this->dataStruct['type'],0,'C'); // Title
		$this->setXY($titleX, $titleY);
		$this->Ln(10);
		$partyX = $this->getX();
		$partyY = $this->getY();
		$this->SetFont('Times','',12);
		$this->MultiCell(70,5,$this->dataStruct['company_address'],0,'L');
		$this->setXY($partyX, $partyY);
		//$this->Cell(145);
		//$this->Cell(50,10,"" /*$this->dataStruct['customer']*/,0,0,'C');
		$this->Ln();
		//$this->SetFont('Arial','',12); // Arial 12
		//$this->setFillColor(255,255,255);
		//$this->Cell(38, 15, '', 1, 0, '', true);
		//$this->Cell(38, 15, '', 1, 0, '', true);
		//$this->Cell(38, 15, '', 1, 0, '', true);
		//$this->Cell(38, 15, '', 1, 0, '', true);
		//$this->Cell(38, 15, '', 1, 0, '', true);
		//$this->Ln();
		$this->SetFont('Times','',12);
		$this->setFillColor(255,255,0);
		$this->setXY($partyX, $partyY+30);
		$this->Cell(95,8, "Bill to",1,0,'C',true);
		$this->Cell(95,8, "Ship to",1,0,'C',true);
		$this->Ln();
		$billtoX = $this->getX();
		$billtoY = $this->getY();
		$this->Cell(95,20,'',1,0,'C');
		$shiptoX = $this->getX();
		$shiptoY = $this->getY();
		$this->Cell(95,20,'',1,0,'C');
		$this->setXY($billtoX, $billtoY);
		$this->MultiCell(95,5, $this->dataStruct['billing'],0,'L');
		$this->setXY($shiptoX, $shiptoY);
		$this->MultiCell(95,5, $this->dataStruct['shipping'],0,'L');
		$this->setXY($billtoX, $billtoY+20);
		$this->Ln(3);

		$this->Cell(47, 8, "Order date", 1, 0, 'C', true);
		$this->Cell(48, 8, "Sales", 1, 0, 'C', true);
		$this->Cell(48, 8, "Delivery date", 1, 0, 'C', true);
		$this->Cell(47, 8, "Reference", 1, 0, 'C', true);
		$this->Ln(8);
		$this->Cell(47, 8, $this->dataStruct['inputdate'], 1, 0, 'C');
		$this->Cell(48, 8, $this->dataStruct['staff'], 1, 0, 'C');
		$this->Cell(48, 8, $this->dataStruct['delivery_date'], 1, 0, 'C');
		$this->Cell(47, 8, $this->dataStruct['reference'], 1, 0, 'C');
		$this->Ln(8);

		$this->Cell(47, 8, "Payment term", 1, 0, 'C', true);
		$this->Cell(48, 8, "Incoterm", 1, 0, 'C', true);
		$this->Cell(48, 8, "Tax rate", 1, 0, 'C', true);
		$this->Cell(47, 8, "Ship via", 1, 0, 'C', true);
		$this->Ln(8);
		$this->Cell(47, 8, $this->dataStruct['payment'], 1, 0, 'C');
		$this->Cell(48, 8, $this->dataStruct['incoterm'], 1, 0, 'C');
		$this->Cell(48, 8, $this->dataStruct['tax_rate'], 1, 0, 'C');
		$this->Cell(47, 8, $this->dataStruct['via'], 1, 0, 'C');
		$this->Ln(8);

		$this->Cell(47, 8, "Pallet", 1, 0, 'C', true);
		$this->Cell(48, 8, "Transportation", 1, 0, 'C', true);
		$this->Cell(48, 8, "Container", 1, 0, 'C', true);
		$this->Cell(47, 8, "B/L release", 1, 0, 'C', true);
		$this->Ln(8);
		$this->Cell(47, 8, $this->dataStruct['palletized'], 1, 0, 'C');
		$this->Cell(48, 8, $this->dataStruct['transportation'], 1, 0, 'C');
		$this->Cell(48, 8, $this->dataStruct['container_load'], 1, 0, 'C');
		$this->Cell(47, 8, $this->dataStruct['bl_release'], 1, 0, 'C');
		$this->Ln(11);

		// print column header
		$this->SetFont('Times','',12);
		$this->setFillColor(255,255,0);
		$this->Cell(15,10, "Quantity",1,0,'C',true);
		$this->Cell(40,10, "Product",1,0,'C',true);
		$this->Cell(65,10, "Description",1,0,'C',true);
		$this->Cell(30,10, "Unit price",1,0,'C',true);
		$this->Cell(35,10, "Subtotal",1,0,'C',true);
		$this->Cell(5,10, "Tax",1,0,'C',true);
		$this->Ln();

		switch($this->dataStruct['status']) {
			case 'closed':
				$this->Image(public_path('images/stamp_closed.png'),60,20);
				break;
			case 'void':
				$this->Image(public_path('images/stamp_void.png'),60,20);
				break;
		}
	}

	// Page footer
	function Footer() {
		$this->SetFont('Times','',12);
		$this->setFillColor(255,255,0);

		// Position at 3.0 cm from bottom
		$this->SetY(-30);

		$this->Cell(30,15,"Notes",1,0,'C',true);
		$notesX = $this->getX();
		$notesY = $this->getY();
		$this->Cell(160,15,'',1,0,'C');
		$this->Ln();
		$pageNumberX = $this->getX();
		$pageNumberY = $this->getY();
		$this->setXY($notesX, $notesY);
		$this->MultiCell(160, 5, $this->dataStruct['notes'], 0, 'L');
		$this->setXY($pageNumberX, $pageNumberY);
		$this->SetFont('Arial','I',8); // Arial italic 8
		$this->Cell(0,5,"Page"." ".$this->PageNo().'/{nb}',0,0,'C'); // Page number

	}

}
?>
