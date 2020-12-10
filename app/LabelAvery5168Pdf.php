<?php
namespace App;

use Codedge\Fpdf\Fpdf\Fpdf;

class LabelAvery5168Pdf extends PrintableLabelPdf {

	function __construct($serial_image_array) {
		parent::__construct([
				'serial_image_array' => $serial_image_array,
				'left_tolerance' => 0.5,
				'top_tolerance' => 0.5 + 0.03125,  // + 1/32" for tolerance
				'num_per_row' => 2,
				'num_per_col' => 2,
				'grid_width' => 4 - 0.0078125,  // - 1/128" adjustment
				'grid_height' => 5 + 0.001953125,   // + 1/512" adjustment
				'label_width' => 3.5,
				'label_height' => 5 + 0.001953125,   // + 1/512" adjustment
			]);
	}

}
?>