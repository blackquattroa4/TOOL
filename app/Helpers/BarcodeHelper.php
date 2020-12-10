<?php
namespace App\Helpers;

use Barcodebakery\Barcode\BCGcode128;
use Barcodebakery\Barcode\BCGDrawing;
use Barcodebakery\Barcode\BCGupca;
use Barcodebakery\Barcode\BCGColor;
use Barcodebakery\Barcode\BCGLabel;

class BarcodeHelper
{
  public static function generateBarcode128($text, $scale, $thickness, $title=null) {
		$drawException = null;
		try {
			$code = new BCGcode128();  // default scale 1, thickness 30, foreground black, background white, font 5,
			$code->setScale($scale); // Resolution
			$code->setThickness($thickness); // Thickness
			//$code->setForegroundColor($color_black); // Color of bars
			//$code->setBackgroundColor($color_white); // Color of spaces
			$code->setFont(7);
			if (!empty($title)) {
				$code->addLabel(new BCGLabel($title, null, BCGLabel::POSITION_TOP, BCGLabel::ALIGN_CENTER));
			}
			$code->parse($text); // Text
		} catch(Exception $exception) {
			$drawException = $exception;
		}
		// create a temporary file
		$filename = tempnam(sys_get_temp_dir(), 'code128_');
		// Here is the list of the arguments
		// 1 - Filename (empty : display on screen)
		// 2 - Background color
		$drawing = new BCGDrawing($filename, new BCGColor(255, 255, 255));
		if($drawException) {
			$drawing->drawException($drawException);
		} else {
			$drawing->setBarcode($code);
			$drawing->draw();
		}
		$drawing->finish(BCGDrawing::IMG_FORMAT_GIF);
		return $filename;
	}

	public static function generateBarcodeUPCA($text, $scale, $thickness, $title=null) {
		$drawException = null;
		try {
			$code = new BCGupca();  // default scale 1, thickness 30, foreground black, background white, font 5,
			$code->setScale($scale); // Resolution
			$code->setThickness($thickness); // Thickness
			//$code->setForegroundColor($color_black); // Color of bars
			//$code->setBackgroundColor($color_white); // Color of spaces
			$code->setFont(7);
			if (!empty($title)) {
				$code->addLabel(new BCGLabel($title, null, BCGLabel::POSITION_TOP, BCGLabel::ALIGN_CENTER));
			}
			$code->parse($text); // Text
		} catch(Exception $exception) {
			$drawException = $exception;
		}
		// create a temporary file
		$filename = tempnam(sys_get_temp_dir(), 'upc');
		// Here is the list of the arguments
		// 1 - Filename (empty : display on screen)
		// 2 - Background color
		$drawing = new BCGDrawing($filename, new BCGColor(255, 255, 255));
		if($drawException) {
			$drawing->drawException($drawException);
		} else {
			$drawing->setBarcode($code);
			$drawing->draw();
		}
		$drawing->finish(BCGDrawing::IMG_FORMAT_GIF);
		return $filename;
	}
}
?>
