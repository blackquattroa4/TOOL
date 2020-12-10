<?php

namespace App;

use Codedge\Fpdf\Fpdf\Fpdf;

class OutstandingTransactablePdf extends Fpdf {

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
		foreach ($this->dataStruct['data'] as $key => $items) {
			foreach ($items['items'] as $entity) {
				$this->Cell(100,10,$entity['title'],0,0,'');
				$this->Ln(10);
				foreach ($entity['items'] as $item) {
					$this->Cell(110,10,'',0,0,'');
					$this->Cell(40,10,$item['title'],0,0,'R');
					$this->Cell(40,10,$item['amount'],0,0,'R');
					$this->Ln(10);
				}
				$this->Cell(110,10,'',0,0,'');
				$this->Cell(40,10,\Lang::get('forms.Subtotal', [], 'en'),0,0,'R');
				$this->Cell(40,10,$entity['amount'],'T',0,'R');
				$this->Ln(10);
			}
			$this->Ln(10);
			$this->Cell(110,10,'',0,0,'');
			$this->Cell(40,10,\Lang::get('finance.Total', [], 'en'),0,0,'R');
			$this->Cell(40,10,$items['total'],0,0,'R');
			$this->Ln(10);
		}
	}

	// Page header
	function Header() {
		$this->Image(url("company_logo.png"),10,6,52,13,"PNG"); // Logo
		$this->Cell(145); // Move to the right
		$this->SetFont('Arial','B',15); // Arial bold 15
		$this->setTextColor(0,0,0);
		$this->setFillColor(255,255,0);

		$titleX = $this->getX();
		$titleY = $this->getY();
		$this->MultiCell(42,7, $this->dataStruct['title'],0,'C'); // Title
		$this->setXY($titleX, $titleY);
		$this->Ln(20);
		$this->Cell(70,10,\Lang::get('forms.Entity', [], 'en'),0,0,'C');
		$this->Cell(40,10,'',0,0,'');
		$this->Cell(40,10,\Lang::get('forms.Reference', [], 'en'),0,0,'C');
		$this->Cell(40,10,\Lang::get('finance.Amount', [], 'en'),0,0,'C');
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
