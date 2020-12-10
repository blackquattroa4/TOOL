<?php
namespace App\Helpers;

class EmailHelper
{
	/**
	 * given an addressees string
	 * return array of parsed recipients
	 */
	public static function parseAddressee($addresseeInString)
	{
    if (preg_match('/^\s*$/', $addresseeInString)) {
      return [];
    }

		$addressees = explode(",", $addresseeInString);
		$result = preg_replace("/^([^<]*)(<)([^>]*)(>)(\s)*$/i", "$3", $addressees);
		return $result;
  }
}

?>
