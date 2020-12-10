<?php
namespace App;

use Codedge\Fpdf\Fpdf\Fpdf;

class PrintableLabelPdf extends Fpdf {

	protected $dataStruct = null;

	function __construct($input, $orientation='P', $unit='in', $size='Letter')
	{
		parent::__construct($orientation, $unit, $size);
		$this->setMargins(0, 0);
		$this->dataStruct = $input;
	}

	// Page header
	function Header() {
	}

	// Page footer
	function Footer() {
	}
	
	function generatePDF() {

		$this->setAutoPageBreak(true, 0.5);
		$this->AliasNbPages();

		$x = 0;
		$y = 0;
		
		foreach ($this->dataStruct['serial_image_array'] as $oneImage) {
			if (($x == 0) && ($y == 0)) {
				$this->AddPage();
			}
			$this->Image($oneImage, 
						$this->dataStruct['left_tolerance']+($this->dataStruct['grid_width']*$x), 
						$this->dataStruct['top_tolerance']+($this->dataStruct['grid_height']*$y), 
						$this->dataStruct['label_width']-0.0625, 
						$this->dataStruct['label_height']-0.0625, 
						"GIF");
			$x += 1;
			if ($x == $this->dataStruct['num_per_row']) {
				$y += 1;
				$x = 0;
			}
			if ($y == $this->dataStruct['num_per_col']) {
				$x = 0;
				$y = 0;
			}
		}
	}
}
?>