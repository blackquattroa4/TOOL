<?php
namespace App\Helpers;

class SerialRangeHelper
{
	/*
	public static function formatRangeHelper($begin, $end) {
		if (strcasecmp($begin, $end) == 0) {
			return $begin;
		} else {
			if (strcasecmp($begin, $end) < 0) {
				return $begin . " - " . $end;
			} else {
				return $end . " - " . $begin;
			}
		}
		return null;
	}
	*/

	public static function difference($candidate, $base) {
		if (strlen($candidate) > strlen($base)) { return 999999999999; }
		if (strlen($candidate) < strlen($base)) { return -999999999999; }
		if (strcasecmp($candidate, $base) == 0) { return 0; }
		$cmn = strlen($candidate);
		for ($idx = strlen($candidate)-1; $idx >= 0; $idx--) {
			if (strcasecmp(substr($candidate, 0, $idx), substr($base, 0, $idx)) == 0) { $cmn = $idx; break; }
		}
		return intval(substr($candidate, $cmn)) - intval(substr($base, $cmn));
	}

	// assuming $ranges is sorted.
	public static function insertIntoVicinityRange($serial, &$ranges) {
		$lastEnd = "";
		foreach ($ranges as $idx => $range) {
			if ((self::difference($serial, $lastEnd) > 1) && (self::difference($serial, $range['start']) < -1)) {
				// inject element
				array_splice($ranges, $idx, 0, [['start' => $serial, 'end' => $serial]]);
				return $idx;
			} else if (self::difference($serial, $range['start']) == -1) {
				$ranges[$idx]['start'] = $serial;
				return $idx;
			} else if (self::difference($serial, $range['end']) == 1) {
				$ranges[$idx]['end'] = $serial;
				return $idx;
			}
		}
		return array_push($ranges, ['start' => $serial, 'end' => $serial]) - 1;
	}

	public static function formatRange($serialLots, $mode='') {
		$result = [];
		foreach ($serialLots as $serial) {
			$idx = self::insertIntoVicinityRange($serial, $result);
			// check with neighboring range to see if merge is necessary
			if ($idx > 0) {
				// merge with previous
				if (self::difference($result[$idx - 1]['end'], $result[$idx]['start']) == -1) {
					$result[$idx - 1]['end'] = $result[$idx]['end'];
					array_splice($result, $idx, 1);
				}
			}
			if ($idx < (count($result) - 1)) {
				// merge with next element
				if (self::difference($result[$idx]['end'], $result[$idx + 1]['start']) == -1) {
					$result[$idx + 1]['start'] = $result[$idx]['start'];
					array_splice($result, $idx, 1);
				}
			}
		}
		if ($mode == 'text') {
			foreach ($result as $idx => $useless) {
				if ($result[$idx]['start'] == $result[$idx]['end']) {
					$result[$idx] = $result[$idx]['start'];
				} else {
					$result[$idx] = $result[$idx]['start'] . " - " . $result[$idx]['end'];
				}
			}
		}
		return $result;
	}
}
?>
