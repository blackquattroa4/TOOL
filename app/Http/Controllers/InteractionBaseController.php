<?php

namespace App\Http\Controllers;

use App\Downloadable;
use App\Interaction;
use App\InteractionLog;
use App\InteractionUserRule;
use App\Http\Controllers\Controller;
use App\Http\Resources\Interaction as InteractionResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use DB;
use Storage;

class InteractionBaseController extends Controller
{

	// This prefix is concat to view path
	protected $ViewNamespacePrefix = ".";

	public function index()
	{
		$requests = auth()->user()->interactions()->orderBy('id', 'desc')->get();

		return view()->first(generateTemplateCandidates($this->ViewNamespacePrefix . "interaction.list"), [
			'requests' => $requests,
		]);
  }

	public function getDashboardInteractionAjax(Request $request)
	{
		$requests = auth()->user()->interactions()->orderBy('id', 'desc')->get();

		return response()->json([ 'success' => true, 'data' => InteractionResource::collection($requests) ]);
	}

	public function view(Request $request, $id)
	{
		return view()->first(generateTemplateCandidates($this->ViewNamespacePrefix . "interaction.interaction"), [
			'request' => Interaction::find($id),
		]);
  }

	public function loadInteractionAjax(Request $request, $id)
	{
		if ($id) {
			$interaction = Interaction::find($id);

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $interaction->id,
					'csrf' => csrf_token(),
					'description' => $interaction->description,
					'status' => $interaction->status,
					'type' => $interaction->type,
					'responder_id' => $interaction->users(Interaction::responsibleRole($interaction->type))->get()->first()->id,
					'participants' => $interaction->users->pluck('name', 'id'),
					'groupLog' => $interaction->groupLogs(),
					'can_update' => in_array(auth()->user()->entity->type, [ 'self', 'employee' ])  // this means to be able to update property of an interaction, not adding new logs
				]
			]);

		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'description' => '',
				'status' => '',
				'responder_id' => 0,
				'participants' => [ ],
				'groupLog' => [ ],
				'can_update' => false   // this means to be able to update property of an interaction, not adding new logs
			]
		]);
	}

	public function uploadFileAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$downloadableIds = [];

		$files = $request->allFiles('files')['file'];

		foreach ($files as $oneFile) {
			// process upload
			try {
				DB::transaction(function() use ($oneFile, &$downloadableIds) {
					$originalName = $oneFile->getClientOriginalName();
					$fileSize = $oneFile->getSize();
					$fileMime = $oneFile->getMimeType();
					$hashValue = md5(date('YmdHis').$originalName);

					// copy to storage location
					//$theFile->move(Storage::getDriver()->getAdapter()->getPathPrefix(), $hashValue);
					Storage::disk('s3')->put($hashValue, file_get_contents($oneFile->getRealPath()), 'private');

					$downloadable = Downloadable::create([
						'uploader_id' => auth()->user()->id,
						'title' => 'request attachment',
						'description' => 'request attachment',
						'original_name' => $originalName,
						'file_size' => $fileSize,
						'mime_type' => $fileMime,
						'hash' => $hashValue,
						'valid' => 1,
					]);

					// since DOM stores integer as string, it's better to
					// send back result as array of string
					$downloadableIds[] = strval($downloadable->id);
				});
			} catch (\Exception $e) {
				$registration = recordAndReportProblem($e);
				return response()->json([ 'success' => false ], 500);
			}
		}

		return response()->json([ 'success' => true, 'ids' => $downloadableIds ], 200);
	}

	public function createRequestAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$interaction = null;

		try {
			DB::transaction(function() use ($request, &$interaction) {

				$interaction = Interaction::create([
					'type' => 'request',
					'description' => $request->get('title'),
					'status' => 'requested',
					'requestor_machine' => $request->ip(),
				]);

				// save participant
				$users[auth()->user()->id] = [
					'role' => 'requestor',
				];
				foreach (InteractionUserRule::getInitialParticipants(auth()->user()->id) as $participant) {
					$users[$participant->participant->id] = [
						'role' => $participant->role,
					];
				}

				$interaction->users()->sync($users);

				// save all entries (description and files)
				if (!empty($request->get('description'))) {
					InteractionLog::create([
						'interaction_id' => $interaction->id,
						'staff_id' => auth()->user()->id,
						'log' => $request->get('description'),
						'downloadable_id' => null,
					]);
				}

				if ($request->get('files')) {
					foreach (explode(",", $request->get('files')) as $fileId) {
						InteractionLog::create([
							'interaction_id' => $interaction->id,
							'staff_id' => auth()->user()->id,
							'log' => null,
							'downloadable_id' => $fileId,
						]);
					}
				}

			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return json_encode(['success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ] ] ]);
		}

		return response()->json([ 'success' => true, 'data' => new InteractionResource($interaction) ]);
	}

	public function addInfoToRequestAjax(Request $request, $id)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		try {
			DB::transaction(function() use ($request, $id) {

				// save all entries (description and files)
				InteractionLog::create([
					'interaction_id' => $id,
					'staff_id' => auth()->user()->id,
					'log' => $request->get('description'),
					'downloadable_id' => null,
				]);

				if ($request->get('files')) {
					foreach (explode(",", $request->get('files')) as $fileId) {
						InteractionLog::create([
							'interaction_id' => $id,
							'staff_id' => auth()->user()->id,
							'log' => null,
							'downloadable_id' => $fileId,
						]);
					}
				}

			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return json_encode(['success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		$interaction = Interaction::find($id);

		return response()->json([ 'success' => true, 'data' => [
				'groupLogs' => $interaction->groupLogs(),
				'interaction' => new InteractionResource($interaction)
			] ]);
	}
}

?>
