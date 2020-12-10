<?php
namespace App\Helpers;

use App\Parameter;

class ParameterHelper
{
	/*
	 * find next sequence
	 */
	public static function getNextSequence($key)
	{
		$param = Parameter::where('key', $key)->lockForUpdate()->first();
		$title = unserialize($param->value);
		$titleLength = strlen($title);
		$title = sprintf("%0".$titleLength."d", ($title + 1));
		$param->value = serialize($title);
		$param->save();
		return $title;
	}

	public static function getValue($key)
	{
		$param = Parameter::where('key', $key)->first();
		return is_null($param) ? null : unserialize($param->value);
	}
}

?>
