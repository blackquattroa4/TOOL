<?php

namespace App;

use Codedge\Fpdf\Fpdf\Fpdf;

class FinancialStatementPdf extends Fpdf {

	// structure to hold data
	protected $dataStruct = null;

	private $statementIdx = null;

	function __construct($input, $orientation='P', $unit='mm', $size='A4')
	{
		parent::__construct($orientation, $unit, $size);

		$this->dataStruct = $input;
		$this->statementIdx = key($input);

		// generate PDF
		$this->setAutoPageBreak(true, 20);
		$this->AliasNbPages();
		foreach ($this->dataStruct['data'] as $key => $statement) {
			$this->statementIdx = $key;
			$this->AddPage();
			foreach ($statement['items'] as $item) {
				$this->Cell(70,10,$item['title'],0,0,'L');
				$this->Ln(10);
				foreach ($item['items'] as $subItem) {
					$this->Cell(10,10,'',0,0,'');
					$this->Cell(90,10,$subItem['title'],0,0,'');
					$this->Cell(50,10,'',0,0,'');
					$this->Cell(40,10,$subItem['amount'],0,0,'R');
					$this->Ln(10);
				}
				$this->Cell(110,10,'',0,0,'C');
				$this->Cell(40,10,trans('forms.Subtotal'),0,0,'R');
				$this->Cell(40,10,$item['amount'],'T',0,'R');
				$this->Ln(10);
			}
		}
	}

	// Page header
	function Header() {
		$this->Image(url("company_logo.png"),10,6,52,13,"PNG"); // Logo
		$this->Cell(90); // Move to the right
		$this->SetFont('Arial','B',15); // Arial bold 15
		$this->setTextColor(0,0,0);
		$this->setFillColor(255,255,0);

		$titleX = $this->getX();
		$titleY = $this->getY();
		$this->MultiCell(100,7,$this->dataStruct['data'][$this->statementIdx]['title']."\n".$this->dataStruct['data'][$this->statementIdx]['date'],0,'R'); // Title
		$this->setXY($titleX, $titleY);
		$this->Ln(20);
		$this->Cell(70,10,\Lang::get('forms.Description', [], 'en'),'B',0,'C');
		$this->Cell(80,10,'','B',0,'');
		$this->Cell(40,10,\Lang::get('finance.Amount', [], 'en'),'B',0,'C');
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
