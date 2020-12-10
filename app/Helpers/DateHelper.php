<?php
namespace App\Helpers;

use Request;

class DateHelper
{
	public static function guiToDbDate($date)
	{
		$locale = Request::server('HTTP_ACCEPT_LANGUAGE');
		$locale = str_replace("-", "_", substr($locale, 0, 5));
		$formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
		$unixtime = $formatter->parse($date);
		return date("Y-m-d", $unixtime);
	}

	public static function dbToGuiDate($date)
	{
		$locale = Request::server('HTTP_ACCEPT_LANGUAGE');
		$locale = str_replace("-", "_", substr($locale, 0, 5));
		$formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
		$formatter->setPattern(preg_replace('/(?<!y)yy(?!y)/', 'yyyy', $formatter->getPattern()));
		//$dateObj = new \DateTime($date);
		$dateObj = date_create($date);
		if (!$dateObj) {
			return null;
		}
		return $formatter->format($dateObj->getTimestamp());
	}

	public static function guiDatePattern()
	{
		$locale = Request::server('HTTP_ACCEPT_LANGUAGE');
		$locale = str_replace("-", "_", substr($locale, 0, 5));
		$formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
		return $formatter->getPattern();   //  M/d/yy
	}

	public static function maxGuiDate($dates, $convertToDbDate = false)
	{
		if (count($dates) == 0) {
			return false;
		}

		$result = date("Y-m-d", max(array_map(function($theDate) {
				return strtotime(self::guiToDbDate($theDate));
			}, $dates)));

		if ($convertToDbDate) {
			return $result;
		}

		return self::dbToGuiDate($result);
	}
}

?>
