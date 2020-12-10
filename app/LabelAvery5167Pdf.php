<?php
namespace App;

use Codedge\Fpdf\Fpdf\Fpdf;

class LabelAvery5167Pdf extends PrintableLabelPdf {

	function __construct($serial_image_array) {
		parent::__construct([
				'serial_image_array' => $serial_image_array,
				'left_tolerance' => 0.375,
				'top_tolerance' => 0.5 + 0.03125,  // + 1/32" for tolerance
				'num_per_row' => 4,
				'num_per_col' => 20,
				'grid_width' => 2.0625 - 0.0078125,  // - 1/128" adjustment
				'grid_height' => 0.5 + 0.001953125,   // + 1/512" adjustment
				'label_width' => 1.75,
				'label_height' => 0.5 + 0.001953125,   // + 1/512" adjustment
			]);
	}

}
?>