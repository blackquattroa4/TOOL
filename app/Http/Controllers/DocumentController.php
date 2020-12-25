<?php

namespace App\Http\Controllers;

use App;
use App\Document;
use App\User;
use App\Role;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\S3DownloadHelper;
use App\Http\Requests;
use App\Http\Resources\Document as DocumentResource;
use Auth;
use DB;
use Illuminate\Http\Request;
use Session;
use Storage;
use Validator;

class DocumentController extends Controller
{
	public function index()
	{
		$switch = [
			'document-table' => true,
			'create-document-button' => true,
			'document-modal' => true,
		];

		$level2Switch = [
			'document-window' => $switch['document-table'] || $switch['create-document-button'],
		];

		return view()->first(generateTemplateCandidates('document.list'), [
					'controlSwitch' => array_merge($switch, $level2Switch)
				]);
	}

	public function getDashboardDocumentAjax(Request $request)
	{
		// select document which is valid and accessible to user
		$documents = Document::where('valid', 1)->whereRaw("(select count(1)>0 from document_permission where original_document_id=documents.original_version and permission_read = 1 and ((accessor_type='users' and accessor_id=" . auth()->user()->id . ") or (accessor_type='roles' and accessor_id in (select role_id from role_user where user_id = " . auth()->user()->id . "))))")->get();

		return response()->json([ 'success' => true, 'data' => DocumentResource::collection($documents) ]);
	}

	public function loadDocumentAjax(Request $request, $id)
	{
		$roles = Role::orderBy('display_name', 'asc')->get()->mapWithKeys(function ($item) { return [ $item->id => [ 'id' => $item->id, 'display' => $item->display_name, 'read' => false, 'update' => false, 'delete' => false ]]; })->toArray();
		$users = User::select('users.*')->leftjoin('taxable_entities', 'taxable_entities.id', '=', 'users.entity_id')->where("users.active", 1)->whereRaw('taxable_entities.type', 'employee')->orderBy('name', 'asc')->get()->mapWithKeys(function ($item) { return [ $item->id => [ 'id' => $item->id, 'display' => $item->name, 'read' => false, 'update' => false, 'delete' => false ]]; })->toArray();

		if ($id) {
			$document = Document::find($id);

			// collect all previous version
			$versions = Document::where('original_version', $document->original_version)->orderBy('version', 'desc')->get();

			// collect all related permission
			foreach (DB::select("select * from document_permission where original_document_id = " . $document['original_version']) as $result) {
				foreach ([ 'read', 'update', 'delete' ] as $property) {
					${$result->accessor_type}[$result->accessor_id][$property] = ($result->{'permission_'.$property} == 1);
				}
			};

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $document->id,
					'csrf' => csrf_token(),
					'title' => $document->title,
					'version' => $document->version,
					'description' => $document->notes,
					'creator_id' => $document->creator_id,
					'file_name' => $document->file_name,
					'history' => DocumentResource::collection($versions),
					'permission' => [
						'roles' => $roles,
						'users' => $users,
					]
				]
			]);
		}

		// creator should have all privileges
		$users[auth()->user()->id]['read'] = true;
		$users[auth()->user()->id]['update'] = true;
		$users[auth()->user()->id]['delete'] = true;

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'title' => '',
				'version' => 1,
				'description' => '',
				'creator_id' => auth()->user()->id,
				'file_name' => '',
				'history' => [ ],
				'permission' => [
					'roles' => $roles,
					'users' => $users,
				]
			]
		]);
	}

	public function create(Request $request)
	{
		$roles = array_column(DB::select("select id, display_name from roles order by display_name"), "display_name", "id");

		$users = array_column(DB::select("select id, name from users where active = 1 order by name"), "name", "id");

		// load document detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
				'id' => 0,
				'reference' => '',
				'description' => '',
				'filename' => '',
				'permission' => [],
			]);
		}

		$request->session()->flashInput([
				'permission' => [
					'users' => [
						auth()->user()->id => [
							'read' => 1,
							'update' => 1,
							'delete' => 1,
						],
					],
				],
			]);

		return view()->first(generateTemplateCandidates('form.document'),
					array(
						'readonly' => false,
						'source' => [
							'title' => trans('document.New document'),
							'post_url' => '/' . $request->path(),
							'action' => [
								'create' => trans('forms.Create'),
							],
						],
						'roles' => $roles,
						'users' => $users,
						'creator_id' => auth()->user()->id,
						'history' => [],
					)
				);
	}

	public function createPost(Request $request)
	{
		$rules = [
			'title' => "required",
			'thefile' => "required|file",
		];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator)
					->withInput($request->all());
		}

		try {
			DB::transaction(function() use ($request) {
				Document::initialize($request, 'thefile');
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", $request->input('title'), trans("document.Document '###' created")));
	}

	public function createPostAjax(Request $request)
	{
		$rules = [
			'title' => "required",
			'thefile' => "required|file",
		];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$document = null;
		try {
			DB::transaction(function() use ($request, &$document) {
				$document = Document::initialize($request, 'thefile');
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'old' => null, 'new' => new DocumentResource($document) ]]);
	}

	public function update($id, Request $request)
	{
		$document = Document::find($id);
		if (!$document->canUpdate(auth()->user())) {
			return redirect(HistoryHelper::goBackPages(1))->with("alert-warning", trans("document.Document can not be updated"));
		}

		$roles = array_column(DB::select("select id, display_name from roles order by display_name"), "display_name", "id");

		$users = array_column(DB::select("select id, name from users where active = 1 order by name"), "name", "id");

		// load document detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
					'id' => $document['id'],
					'reference' => $document['title'],
					'description' => $document['notes'],
					'filename' => $document['file_name'],
					'permission' => $document->permission(),
				]);
		}

		return view()->first(generateTemplateCandidates('form.document'),
					array(
						'readonly' => false,
						'source' => [
							'title' => trans('document.Update document'),
							'post_url' => '/' . $request->path(),
							'action' => [
								'update' => trans('forms.Update'),
							],
						],
						'roles' => $roles,
						'users' => $users,
						'creator_id' => $document['creator_id'],
						'history' => [],
					)
				);
	}

	public function updatePost($id, Request $request)
	{
		$document = Document::find($id);
		if (!$document->canUpdate(auth()->user())) {
			return redirect(HistoryHelper::goBackPages(1))->with("alert-warning", trans("document.Document can not be updated"));
		}

		try {
			DB::transaction(function() use ($document, $request) {
				$document->synchronize($request, 'thefile');
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", $document['title'], trans("document.Document '###' updated")));
	}

	public function updatePostAjax($id, Request $request)
	{
		$document = Document::find($id);
		if (!$document->canUpdate(auth()->user())) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans("document.Document can not be updated") ]]]);
		}

		$newDocument = null;
		try {
			DB::transaction(function() use ($document, &$newDocument, $request) {
				$newDocument = $document->synchronize($request, 'thefile');
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'old' => new DocumentResource($document), 'new' => new DocumentResource($newDocument ?? $document) ]]);
	}

	public function deletePost($id, Request $request)
	{
		// do not register with session-history; prevent going back to this URL
		$this->removeFromHistory();

		$document = Document::find($id);
		if (!$document->canDelete(auth()->user())) {
			return redirect(HistoryHelper::goBackPages(1))->with("alert-warning", trans("document.Document can not be deleted"));
		}

		try {
			DB::transaction(function() use ($document) {
				$document->softDelete();
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(1))->with("alert-success", str_replace("###", $document['title'], trans("document.Document '###' deleted")));
	}

	public function deletePostAjax($id, Request $request)
	{
		// do not register with session-history; prevent going back to this URL
		// $this->removeFromHistory();

		$document = Document::find($id);
		if (!$document->canDelete(auth()->user())) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans("document.Document can not be deleted") ]]]);
		}

		try {
			DB::transaction(function() use ($document) {
				$document->softDelete();
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'old' => new DocumentResource($document), 'new' => null ]]);
	}

	public function viewDocument($id, Request $request)
	{
		$document = Document::find($id);
		if (!$document->canView(auth()->user())) {
			return redirect(HistoryHelper::goBackPages(1))->with("alert-warning", trans("document.Document can not be viewed"));
		}

		$roles = array_column(DB::select("select id, display_name from roles order by display_name"), "display_name", "id");

		$users = array_column(DB::select("select id, name from users where active = 1 order by name"), "name", "id");

		// no need to check error-redirect since this is read only
		$request->session()->flashInput([
				'id' => $document['id'],
				'reference' => $document['title'],
				'description' => $document['notes'],
				'filename' => $document['file_name'],
				'permission' => $document->permission(),
			]);

		$versions = Document::where('original_version', $document->original_version)->orderBy('version', 'desc')->get();

		return view()->first(generateTemplateCandidates('form.document'),
					array(
						'readonly' => true,
						'source' => [
							'title' => trans('document.View document'),
							'post_url' => '/' . $request->path(),
							'action' => [],
						],
						'roles' => $roles,
						'users' => $users,
						'creator_id' => $document['creator_id'],
						'history' => [],
						'pastVersions' => $versions,
					)
				);
	}

	public function download($id, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$document = Document::find($id);
		if (!$document->canView(auth()->user())) {
			return redirect(HistoryHelper::goBackPages(1))->with("alert-warning", trans("document.Document can not be viewed"));
		}

		//return response()->download(Storage::getDriver()->getAdapter()->getPathPrefix() . $document['file_path'], $document['file_name'], [ 'Content-Type: ' . $document['file_type'] ]);
		return response()->download(S3DownloadHelper::toLocal($document['file_path']), $document['file_name'], [ 'Content-Type: ' . $document['file_type'] ])->deleteFileAfterSend(true);
	}
}
