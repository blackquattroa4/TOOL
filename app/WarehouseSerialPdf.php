<?php

namespace App;

use App\Helpers\BarcodeHelper;
use Codedge\Fpdf\Fpdf\Fpdf;

class WarehouseSerialPdf extends Fpdf {

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
		foreach ($this->dataStruct['detail'] as $detail) {
			$this->Cell(50,12, $detail['sku'], count($detail['serial'])?'T':'TB',0,'L',0);
			$lineX = $this->getX();
			$lineY = $this->getY();
			$this->Cell(120,12, "", count($detail['serial'])?'T':'TB',0,'L',0);
			$this->setXY($lineX, $lineY);
			$this->MultiCell(120, 6, $detail['description'],0,'L');
			$this->setXY($lineX + 120, $lineY);
			$this->Cell(20,12, $detail['quantity'], count($detail['serial'])?'T':'TB', 0, 'R', 0);
			$this->ln(12);
			if ($lastLine = count($detail['serial'])) {
				foreach ($detail['serial'] as $line => $oneRange) {
					$this->Cell(50,6,  ($line === 0) ? "Serial # " : "", ($lastLine - 1 == $line) ? 'B' : '', 0, 'R', 0);
					$this->Cell(120,6, $oneRange, ($lastLine - 1 == $line) ? 'B' : '', 0, 'L', 0);
					$this->Cell(20,6, "", ($lastLine - 1 == $line) ? 'B' : '', 0, 'L', 0);
					$this->ln(6);
				}
			}
		}
	}

	// Page header
	function Header() {
		$this->Image(url('company_logo.png'),10,6,52,13, "PNG"); // Logo
		$this->Cell(145); // Move to the right
		$this->SetFont('Arial','B',15); // Arial bold 15
		$this->setTextColor(0,0,0);
		$this->setFillColor(255,255,0);

		$this->Image(BarcodeHelper::generateBarcode128($this->dataStruct['increment'], 2, 17), 155, 16, 40, 10, 'GIF');
		//$this->Image(BarcodeHelper::generateBarcode128($this->dataStruct['customer'], 2, 17), 155, 16+11, 40, 10, 'GIF');

		$titleX = $this->getX();
		$titleY = $this->getY();
		$this->MultiCell(42,7,ucfirst($this->dataStruct['type']).' order',0,'C'); // Title
		$this->setXY($titleX, $titleY);
		$this->Ln(10);
		$partyX = $this->getX();
		$partyY = $this->getY();
		$this->SetFont('Times','',12);
		$this->MultiCell(70,5,$this->dataStruct['company_address'],0,'L');
		$this->setXY($partyX, $partyY);
		//$this->Cell(145);
		//$this->Cell(50,10,"" /*$this->dataStruct['customer']*/,0,0,'C');
		$this->Ln(3);
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
		$this->setXY($partyX, $partyY+20);
		$this->Cell(50,8, "Date",1,0,'C',true);
		$this->Cell(50,8, "Staff",1,0,'C',true);
		$shiptoX = $this->getX();
		$shiptoY = $this->getY();
		$this->Cell(90,8, "Address",1,0,'C',true);
		$this->ln(8);
		$this->Cell(50,8, $this->dataStruct['date'],1,0,'C',0);
		$this->Cell(50,8, $this->dataStruct['staff'],1,0,'C',0);
		$this->ln(8);
		$this->Cell(50,8, "Reference",1,0,'C',true);
		$this->Cell(50,8, "Via",1,0,'C',true);
		$this->ln(8);
		$this->Cell(50,8, $this->dataStruct['reference'],1,0,'C',0);
		$this->Cell(50,8, $this->dataStruct['via'],1,0,'C',0);
		$this->Ln(8);
		$this->setXY($shiptoX, $shiptoY+8);
		$this->Cell(90,24, '',1,0,'C',0);
		$this->setXY($shiptoX, $shiptoY+8);
		$this->MultiCell(90,5, $this->dataStruct['external_address'],0,'L');
		//$this->setXY($shiptoX, $shiptoY);
		//$this->MultiCell(95,5, 'shipping',0,'L');
		$this->setXY($shiptoX, $shiptoY+20);
		$this->Ln(15);
		$this->Cell(50,8, 'SKU',1,0,'C',true);
		$this->Cell(120,8, 'Description',1,0,'C',true);
		$this->Cell(20,8, 'Quantity',1,0,'C',true);
		$this->Ln(8);

		/*
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
		$this->Cell(47, 8, $this->dataStruct['palletize'], 1, 0, 'C');
		$this->Cell(48, 8, $this->dataStruct['transportation'], 1, 0, 'C');
		$this->Cell(48, 8, $this->dataStruct['container_load'], 1, 0, 'C');
		$this->Cell(47, 8, $this->dataStruct['bl_release'], 1, 0, 'C');
		$this->Ln(11);
		*/

		// print column header
		/*
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
		*/
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
