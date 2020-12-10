<?php

namespace App\Http\Controllers;

use App;
use Auth;
use DB;
use App\User;
use App\Address;
use App\Document;
use App\Helpers\CountryHelper;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\S3DownloadHelper;
use App\Http\Requests;
use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Storage;
use Validator;

class HRController extends Controller
{
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$switch = [
			'staff-modal' => auth()->user()->can(['hr-edit', 'hr-view']),
			'staff-table' => auth()->user()->can(['hr-list']),
		];

		$level2Switch = [
			'staff-template' => $switch['staff-table'],
			'staff-window' => $switch['staff-table'],
		];

		return view()->first(generateTemplateCandidates('hr.list'), [
				'controlSwitch' => array_merge($switch, $level2Switch),
			]);
	}

	public function getDashboardStaffAjax(Request $request)
	{
		return response()->json([ 'success' => true, 'data' => UserResource::collection(User::select('users.*')->join('taxable_entities', 'taxable_entities.id', '=', 'users.entity_id')->where('type', 'employee')->get()) ]);
	}

	public function edit($id, Request $request)
	{
		$user = User::find($id);
		$address = $user->entity->defaultBillingAddress[0];
		$data = array(
			'name' => $user->name,
			'phone' => $user->phone,
			'email' => $user->email,
			'street' => $address->street,
			'unit' => $address->unit,
			'district' => $address->district,
			'city' => $address->city,
			'state' => $address->state,
			'country' => $address->country,
			'zipcode' => $address->zipcode,
		);

		return view()->first(generateTemplateCandidates('hr.staff'), [
											'readonly' => false,
											'path' => "/" . $request->path(),
											'user' => $data,
											'country' => CountryHelper::getAllCountryOptions()
										]);
	}

	public function editPost($id, Request $request)
	{
		// validate
		$rules = [
				'name' => "required",
				'email' => "required|email",
				'phone' => "required",
				'street' => "required",
				'city' => "required",
				'state' => "required",
				'country' => "required",
				'zipcode' => "required",
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
			DB::transaction(function() use ($id, $request) {
				// update information
				$user = User::find($id);
				$user->update([
						'name' => $request->input('name'),
						'phone' => $request->input('phone'),
						'email' => $request->input('email'),
					]);
				$address = $user->entity->defaultbillingAddress[0];
				$address->update([
						'street' => $request->input('street'),
						'unit' => $request->input('unit'),
						'district' => $request->input('district'),
						'city' => $request->input('city'),
						'state' => $request->input('state'),
						'country' => $request->input('country'),
						'zipcode' => $request->input('zipcode'),
					]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.Staff information updated successfully.'));
	}

	public function updatePostAjax(Request $request, $id)
	{
		// validate
		$rules = [
				'name' => "required",
				'email' => "required|email",
				'phone' => "required",
				'street' => "required",
				'city' => "required",
				'state' => "required",
				'country' => "required",
				'zipcode' => "required",
			];

		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$user = User::find($id);

		try {
			DB::transaction(function() use ($user, $request) {
				// update information

				$user->update([
						'name' => $request->input('name'),
						'phone' => $request->input('phone'),
						'email' => $request->input('email'),
					]);
				$address = $user->entity->defaultbillingAddress[0];
				$address->update([
						'street' => $request->input('street'),
						'unit' => $request->input('unit'),
						'district' => $request->input('district'),
						'city' => $request->input('city'),
						'state' => $request->input('state'),
						'country' => $request->input('country'),
						'zipcode' => $request->input('zipcode'),
					]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => [ 'staff' => new UserResource($user) ]]);
	}

	public function view($id, Request $request)
	{
		$user = User::find($id);
		$address = $user->entity->defaultBillingAddress[0];
		$data = array(
			'name' => $user->name,
			'phone' => $user->phone,
			'email' => $user->email,
			'street' => $address->street,
			'unit' => $address->unit,
			'district' => $address->district,
			'city' => $address->city,
			'state' => $address->state,
			'country' => $address->country,
			'zipcode' => $address->zipcode,
		);

		$files = $user->hrDocuments()->withPivot('creator_id')->get();

		return view()->first(generateTemplateCandidates('hr.staff'), [
											'readonly' => true,
											'path' => "/" . $request->path(),
											'user' => $data,
											'files' => $files,
											'country' => CountryHelper::getAllCountryOptions()
										]);
	}

	public function loadAjax(Request $request, $id)
	{
		$user = User::find($id);
		$address = $user->entity->defaultBillingAddress[0];

		$files = $user->hrDocuments()->with('creator')->get();

		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token(),
				'id' => $user->id,
				'name' => $user->name,
				'phone' => $user->phone,
				'email' => $user->email,
				'street' => $address->street,
				'unit' => $address->unit,
				'district' => $address->district,
				'city' => $address->city,
				'state' => $address->state,
				'country' => $address->country,
				'zipcode' => $address->zipcode,
				'file' => $files->pluck('id'),
				'file_date' => $files->pluck('created_at'),
				'file_date_display' => array_map(function($item) { return DateHelper::dbToGuiDate($item); }, $files->pluck('created_at')->toArray()),
				'file_title' => $files->pluck('title'),
				'file_creator' => $files->map(function($item) { return $item->creator->name; }),
				'file_path' => $files->pluck('file_path'),
			]
		]);
	}

	public function archive($id, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$staff = User::find($id);

		$files = $request->allFiles('files')['file'];

		foreach ($files as $index => $oneFile) {
			// process upload
			try {
				DB::transaction(function() use ($request, $staff, $index /*$oneFile*/) {
					$additionalData = [
						'title' => explode(".", $request->file('file.'.$index)->getClientOriginalName())[0],
						'description' => 'HR archive',
						'permission' => [ 'roles' => [], 'users' => [ auth()->user()->id => [ 'read' => true, 'update' => true]] ],
					];
					$request->merge($additionalData);
					// $document = Document::initialize($request, $oneFile);
					$document = Document::initialize($request, 'file.'.$index);

					// document / user relation
					$staff->hrDocuments()->sync([
						$document->id => [
							'creator_id' => Auth::user()->id,
						]
					], false);
				});
			} catch (\Exception $e) {
				$registration = recordAndReportProblem($e);
				return response()->json([ 'success' => false ], 500);
			}
		}

		return response()->json([ 'success' => true ], 200);
	}

	public function download($hash, Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		if (!auth()->user()->can('hr-view')) {
			return response()->json([ 'success' => false ], 500);
		}

		$document = Document::where([ 'file_path' => $hash ])->first();

		return response()->json([
			'success' => true,
			'content' => 'data:' . $document->file_type . ';base64, ' . base64_encode(file_get_contents(S3DownloadHelper::toLocal($document['file_path']))),
		]);
	}
}
