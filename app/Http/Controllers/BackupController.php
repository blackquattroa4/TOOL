<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Auth;
use App\User;
use App\Helpers\HistoryHelper;
use App\Http\Requests;
use Storage;

class BackupController extends Controller
{
	public function createBackup(Request $request)
	{
		return view()->first(generateTemplateCandidates('backup.input'), [
					'source' => [
						'title' => trans('tool.Create backup'),
						'post_url' => '/' . $request->path(),
						'action' => trans('tool.Backup')
					]
				]);
	}

	public function createBackupPost(Request $request)
	{
		$pseudoUser = env("DB_USERNAME");
		$pseudoPass = env("DB_PASSWORD");
		$dbServer = env("DB_HOST");
		$dbPort = env("DB_PORT");
		$database = env("DB_DATABASE");

		// backup file
		$backupfile = "backup-" . date("YmdHis") . ".sql";
		// spawn a shell command to execute backup
		$backupPath = Storage::getDriver()->getAdapter()->getPathPrefix() . $backupfile;
		$socket = false; //preg_match('/^localhost/i', $dbServer);
		$tableStruct = $request->input('tablestruct');
		$lockTable = $request->input('withlock');
		exec("mysqldump --user=" . $pseudoUser . " --password=" . $pseudoPass . (($socket !== false) ? " --socket=" : " --host=") . $dbServer . (empty($tableStruct) ? " --no-create-info " : "") . (empty($lockTable) ? " --skip-add-locks" : "") . " --databases " . $database . " > " . $backupPath . " &");

		return view()->first(generateTemplateCandidates('backup.progress'), [
					'source' => [
						'title' => trans('tool.Backup in progress'),
						'backupfile' => $backupfile,
						'action' => trans('tool.Download')
					]
				]);
	}

	public function reportBackupProgress(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		return filesize(Storage::getDriver()->getAdapter()->getPathPrefix() . $request->input('backupfile'));
	}

	public function downloadBackup($hash, Request $request)
	{
		$fileName = 'backup-' . $hash . '.sql';
		if (!file_exists(Storage::getDriver()->getAdapter()->getPathPrefix() . $fileName)) {
			return redirect(HistoryHelper::goBackPages(3))->with('alert-warning', trans('messages.Backup already downloaded.  Please re-backup.'));;
		}
		return response()->download(Storage::getDriver()->getAdapter()->getPathPrefix() . $fileName, $fileName)->deleteFileAfterSend(true);
	}
}
