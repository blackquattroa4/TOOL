<?php
namespace App\Helpers;

class DataSetHelper
{
	public static function checkDataSetValue($record, $column, $value)
	{
		$oldVal = $record->{$column};
		if (!empty($oldVal)) {
			$oldVal = explode(',', $oldVal);
			return (array_search($value, $oldVal) !== false);
		}
		return false;
	}

	public static function addDataSetValue($record, $column, $value)
	{
		$oldVal = $record->{$column};
		if (empty($oldVal)) {
			$record->update([ $column => $value ]);
		} else {
			$oldVal = explode(',', $oldVal);
			array_push($oldVal, $value);
			$record->update([ $column => implode(',',  $oldVal) ]);
		}
	}

	public static function removeDataSetValue($record, $column, $value)
	{
		$oldVal = $record->{$column};
		if (!empty($oldVal)) {
			$oldVal = explode(',', $oldVal);
			$oldKey = array_search($value, $oldVal);
			if ($oldKey !== false) {
				unset($oldVal[$oldKey]);
				$record->update([ $column => implode(',', $oldVal) ]);
			}
		}
	}
}

?>
