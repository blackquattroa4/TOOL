<?php
namespace App\Helpers;

class HtmlSelectHelper
{
	/*
	 * find next sequence
	 */
	public static function getWorkHourOptions()
	{
		static $result = "";

		if (empty($result)) {
			$startTimeInSecond = strtotime("1970-01-01 08:00:00");

			foreach (range($startTimeInSecond, $startTimeInSecond+(9*60*60), 30 * 60) as $second) {
				$result .= "<option value=\"" . date("H:i:s", $second) . "\">" . date("H:i A", $second) . "</option>";
			}
		}

		return $result;

	}
}

?>
