<?php

namespace App;

use App\Facades\OcrService as Ocr;
use App\Helpers\S3DownloadHelper;
use App\SearchableKeyword;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Storage;

class Document extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title', 'original_version', 'version', 'creator_id', 'file_path', 'file_size', 'file_type', 'file_name', 'valid', 'notes', 'ocr_scanned',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public function creator()
	{
		return $this->hasOne('\App\User', 'id', 'creator_id');
	}

	public function permission($matrix = null)
	{
		if ($matrix) {  // write operation
			// delete all with this documents
			DB::delete("delete from document_permission where original_document_id = " . $this['original_version']);
			foreach ($matrix as $category => $ids) {
				$query = "insert into document_permission (original_document_id, accessor_type, accessor_id, permission_read, permission_update, permission_delete, created_at, updated_at) values (" . $this['original_version'] . ", '$category', ";
				foreach ($ids as $id => $permissions) {
					$query1 = $query . $id . ", " . (isset($permissions['read']) ? "1" : "0") . ", " . (isset($permissions['update']) ? "1" : "0") . ", " . (isset($permissions['delete']) ? "1" : "0") . ", utc_timestamp(), utc_timestamp())";
					DB::insert($query1);
				}
			}
			return $this;
		}

		// read operation
		$matrix = [ ];
		foreach (DB::select("select * from document_permission where original_document_id = " . $this['original_version']) as $result) {
			if (!isset($matrix[$result->accessor_type])) {
				$matrix[$result->accessor_type] = [];
			}
			$matrix[$result->accessor_type][$result->accessor_id] = [];
			if ($result->permission_read) {
				$matrix[$result->accessor_type][$result->accessor_id]['read'] = "on";
			}
			if ($result->permission_update) {
				$matrix[$result->accessor_type][$result->accessor_id]['update'] = "on";
			}
			if ($result->permission_delete) {
				$matrix[$result->accessor_type][$result->accessor_id]['delete'] = "on";
			}
		};
		return $matrix;
	}

	public function canView($user)
	{
		$result = DB::select("select id from document_permission where original_document_id=" . $this['original_version'] . " and permission_read = 1 and ((accessor_type='users' and accessor_id=" . $user['id'] . ") or (accessor_type='roles' and accessor_id in (select role_id from role_user where user_id = " . $user['id'] . ")))");
		return (count($result) > 0) || ($this['creator_id'] == $user['id']);
	}

	public function canUpdate($user)
	{
		$result = DB::select("select id from document_permission where original_document_id=" . $this['original_version'] . " and permission_update = 1 and ((accessor_type='users' and accessor_id=" . $user['id'] . " ) or (accessor_type='roles' and accessor_id in (select role_id from role_user where user_id = " . $user['id'] . ")))");
		return (count($result) > 0) || ($this['creator_id'] == $user['id']);
	}

	public function canDelete($user)
	{
		$result = DB::select("select id from document_permission where original_document_id=" . $this['original_version'] . " and permission_delete = 1 and ((accessor_type='users' and accessor_id=" . $user['id'] . ") or (accessor_type='roles' and accessor_id in (select role_id from role_user where user_id = " . $user['id'] . ")))");
		return (count($result) > 0) || ($this['creator_id'] == $user['id']);
	}

	public function previousVersion()
	{
		return self::find($this->original_version);
	}

	public function keywords()
	{
		return $this->belongsToMany('App\SearchableKeyword', 'document_keyword', 'document_id', 'keyword_id');
	}

	public function recordScanResult()
	{
		if (!$this->ocr_scanned) {
			// read file from s3
			$fileExt = pathinfo($this->file_name, PATHINFO_EXTENSION);
			$tempFilePath = S3DownloadHelper::toLocal($this->file_path);
			rename($tempFilePath, $tempFilePath . "." . $fileExt);
			$keywords = Ocr::tokenizedResult($tempFilePath . "." . $fileExt);
			unlink($tempFilePath . "." . $fileExt);

			// sync/associate document with keywords
			$keywordIndices = array_map(function($val) {
					return SearchableKeyword::firstOrCreate(['word' => $val])->id;
				}, $keywords);
			$this->keywords()->sync($keywordIndices);
		}
	}

	// $fileSource points to either field in 'request' object or indicate a path of local file
	public static function initialize(Request $request, $fileSource)
	{
		if (strpos($fileSource, DIRECTORY_SEPARATOR) !== false) {  // contain directory separator?
			// source is pointing to a local file
			$originalName = basename($fileSource);
			$fileSize = filesize($fileSource);
			$fileMime = Storage::mimeType($originalName);
			$hashValue = md5(date('YmdHis').$originalName);
			$realPath = $fileSource;
		} else {
			// source is pointing to a field in request
			$theFile = $request->file($fileSource);
			$originalName = $theFile->getClientOriginalName();
			$fileSize = $theFile->getSize();
			$fileMime = $theFile->getMimeType();
			$hashValue = md5(date('YmdHis').$originalName);
			$realPath = $theFile->getRealPath();
		}

		// copy to storage location
		Storage::disk('s3')->put($hashValue, file_get_contents($realPath), 'private');

		$document = self::create([
			'title' => $request->input('title'),
			'original_version' => 0,
			'version' => 1,
			'creator_id' => auth()->user()->id,
			'file_path' => $hashValue,
			'file_size' => $fileSize,
			'file_type' => $fileMime,
			'file_name' => $originalName,
			'valid' => 1,
			'notes' => $request->input('description'),
			'ocr_scanned' => 0,
		]);
		$document->update([
			'original_version' => $document->id,
		]);

		// update permission
		$permissions = $request->input('permission');
		// add creator into matrix even if not present
		$permissions['users'][auth()->user()->id] = [
				'read' => 1,
				'update' => 1,
				'delete' => 1,
			];
		$document->permission($permissions);

		event(new \App\Events\DocumentUpsertEvent($document));

		return $document;
	}

	public function synchronize(Request $request, $fileFieldName)
	{
		if ($theFile = $request->file($fileFieldName)) {
			$originalName = $theFile->getClientOriginalName();
			$fileSize = $theFile->getSize();
			$fileMime = $theFile->getMimeType();
			$hashValue = md5(date('YmdHis').$originalName);
			$realPath = $theFile->getRealPath();

			// copy to storage location
			//$theFile->move(Storage::getDriver()->getAdapter()->getPathPrefix(), $hashValue);
			Storage::disk('s3')->put($hashValue, file_get_contents($realPath), 'private');

			$newDocument = self::create([
				'title' => $request->input('title'),
				'original_version' => $this->original_version,
				'version' => $this->version+1,
				'creator_id' => auth()->user()->id,
				'file_path' => $hashValue,
				'file_size' => $fileSize,
				'file_type' => $fileMime,
				'file_name' => $originalName,
				'valid' => 1,
				'notes' => $request->input('description'),
				'ocr_scanned' => 0,
			]);
			$this->update([ 'valid' => 0 ]);
		} else {
			$this->update([
					'title' => $request->input('title'),
					'notes' => $request->input('description'),
				]);
		}

		// adjust permission
		$permissions = $request->input('permission');
		// add creator into matrix even if not present
		$permissions['users'][auth()->user()->id] = [
				'read' => 1,
				'update' => 1,
				'delete' => 1,
			];
		$this->permission($permissions);

		event(new \App\Events\DocumentUpsertEvent($newDocument ?? $this));

		return $newDocument ?? $this;
	}

	public function softDelete()
	{
		$this->update(['valid' => 0 ]);
		event(new \App\Events\DocumentUpsertEvent($this));

		return $this;
	}

}
