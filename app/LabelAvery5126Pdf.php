<?php
namespace App;

use Codedge\Fpdf\Fpdf\Fpdf;

class LabelAvery5126Pdf extends PrintableLabelPdf {

	function __construct($serial_image_array) {
		parent::__construct([
				'serial_image_array' => $serial_image_array,
				'left_tolerance' => 0.0625,
				'top_tolerance' => 0.0625,
				'num_per_row' => 1,
				'num_per_col' => 2,
				'grid_width' => 8.375,
				'grid_height' => 5.4375,
				'label_width' => 8.375,
				'label_height' => 5.4375,
			]);
	}

}
?>