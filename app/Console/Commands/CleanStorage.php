<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanStorage extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'storage:declutter ' .
							'{hour}: grace period in hour';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'de-clutter storage/app directory';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */

	CONST DIRECTORY_TO_IGNORE = [
		".",
		".."
	];

	CONST FILE_TO_IGNORE = [
		".gitignore"
	];

	private $expiration = null;

	private function handleFile($path, $filename)
	{
		if (!in_array($filename, self::FILE_TO_IGNORE)) {
			// if file is greater than N hours old, delete it.
			if (filemtime($path . DIRECTORY_SEPARATOR . $filename) < $this->expiration) {
				unlink($path . DIRECTORY_SEPARATOR . $filename);
			}
		}
	}

	private function handleDirectory($path, $filename)
	{
		$files = scandir($path . DIRECTORY_SEPARATOR . $filename);
		foreach ($files as $file) {
			if (is_dir($path . DIRECTORY_SEPARATOR . $filename . DIRECTORY_SEPARATOR . $file)) {
				if (!in_array($file, self::DIRECTORY_TO_IGNORE)) {
					$this->handleDirectory($path . DIRECTORY_SEPARATOR . $filename, $file);
				}
			} else {
				$this->handleFile($path . DIRECTORY_SEPARATOR . $filename, $file);
			}
		}
	}

	public function handle()
	{
		$this->expiration = time() - $this->argument('hour') * 60 * 60;
		$this->handleDirectory(storage_path(), 'app');
	}
}
