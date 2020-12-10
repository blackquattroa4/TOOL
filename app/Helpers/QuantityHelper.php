<?php
namespace App\Helpers;

class QuantityHelper
{
	public static function getHtmlAttribute($forJavaScript = false)
	{
		$format = env("APP_QUANTITY_FORMAT");
		$zero = sprintf($format, 0);
		$idx = strpos($zero, ".");
		$step = "1";

		if ($idx !== false) {
			$lastDigit = strlen($zero) - 1;
			$step = pow(10, ($idx - $lastDigit));
		}

		return [
				'zero' => $zero,
				'step' => $step,
			];
	}
}
?>
