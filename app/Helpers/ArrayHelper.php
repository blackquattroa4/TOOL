<?php
namespace App\Helpers;

class ArrayHelper
{
	public static function union($arrays)
	{
		$result = [];
		foreach ($arrays as $oneArray) {
			$result = array_merge(array_intersect($result, $oneArray), array_diff($result, $oneArray), array_diff($oneArray, $result));
		}
		return $result;
	}

	/**
	 * array of associative array to be transposed
	 **/
	public static function transpose($arrays)
	{
		$result = [];

		$keys = array_keys($arrays);
		foreach ($arrays[$keys[0]] as $k => $v) {  // only iterate first "row"
		    $result[] = array_combine($keys, array_column($arrays, $k));  // store each "column" as an associative "row"
		}

		return $result;
	}
}
?>
