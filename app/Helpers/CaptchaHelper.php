<?php
namespace App\Helpers;

class CaptchaHelper
{
	/*
	 * find next sequence
	 */
	public static function convertStringToImage($string)
	{
		$width = strlen($string)*9+10
		$im = imagecreate($width, 20); // image size ??x20px
		imagecolorallocate($im, 255, 255, 255); // background white
		$text_color = imagecolorallocate($im, 0, 0, 0); // text color black

		imagestring($im, 5, 5, 2, $string, $text_color); // append string to image

		$im = imagescale($im, $width*2);

		return $im;
	}
}

?>
