<?php
namespace App\Helpers;

use Storage;

class S3DownloadHelper
{
	public static function toLocal($s3Path)
	{
		$theStream = Storage::disk('s3')->getDriver()->readStream($s3Path);
		$tempName = str_random(24);
		Storage::disk('local')->put($tempName, $theStream, 'public');
		return Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix() . $tempName;
	}
}
?>
