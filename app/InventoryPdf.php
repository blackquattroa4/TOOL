<?php

namespace App;

use App\Helpers\BarcodeHelper;
use Codedge\Fpdf\Fpdf\Fpdf;

class InventoryPdf extends Fpdf {

	// structure to hold data
	protected $dataStruct = null;

	function __construct($input, $orientation='P', $unit='mm', $size='A4')
	{
		parent::__construct($orientation, $unit, $size);

		$this->dataStruct = $input;

		// generate PDF
		$this->setAutoPageBreak(true, 20);
		$this->AliasNbPages();
		$this->AddPage();
		$pageN = $this->PageNo();
		$firstX = $this->getX();
		$firstY = $this->getY();
		foreach ($this->dataStruct['data'] as $content) {
			$this->Cell(80,10,$content['sku'],0,0,'');
			$this->Cell(70,10,$content['location'],0,0,'');
			$this->Cell(40,10,$content['balance'],0,0,'R');
			$this->Ln(10);
		}
	}

	// Page header
	function Header() {
		$this->Image(url('company_logo.png'),10,6,52,13,"PNG"); // Logo
		$this->Cell(145); // Move to the right
		$this->SetFont('Arial','B',15); // Arial bold 15
		$this->setTextColor(0,0,0);
		$this->setFillColor(255,255,0);

		$titleX = $this->getX();
		$titleY = $this->getY();
		$this->MultiCell(42,7,$this->dataStruct['title'],0,'C'); // Title
		$this->setXY($titleX, $titleY);
		$this->Ln(20);
		$this->Cell(70,10,\Lang::get('forms.SKU', [], 'en'),0,0,'C');
		$this->Cell(10,10,'',0,0,'');
		$this->Cell(60,10,\Lang::get('forms.Location', [], 'en'),0,0,'');
		$this->Cell(10,10,'',0,0,'');
		$this->Cell(40,10,\Lang::get('forms.Balance', [], 'en'),0,0,'C');
		$this->Ln(10);
	}

	// Page footer
	function Footer() {
		$this->SetFont('Times','',12);
		$this->setFillColor(255,255,0);

		// Position at 1.5 cm from bottom
		$this->SetY(-15);

		$this->SetFont('Arial','I',8); // Arial italic 8
		$this->Cell(0,5,"Page"." ".$this->PageNo().'/{nb}',0,0,'C'); // Page number

	}

}
?>
