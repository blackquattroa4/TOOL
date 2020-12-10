<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Auth;
use App\Parameter;
use App\User;
use App\Helpers\HistoryHelper;
use App\Http\Requests;
use App\Http\Resources\Parameter as ParameterResource;
use DB;
use Session;

class ParameterController extends Controller
{
	public function index()
	{
		// $parameters = Parameter::get();
		$switch = [
			'parameter-table' => Auth::user()->can('sy-list'),
			'create-parameter-button' => Auth::user()->can('sy-edit'),
			'parameter-template' => Auth::user()->can('sy-edit'),
			'parameter-modal' => Auth::user()->can('sy-edit')
		];

		$level2Switch = [
			'parameter-window' => $switch['parameter-table'] || $switch['parameter-tempalte']
		];

		return view()->first(generateTemplateCandidates('parameter.list'), [
						'controlSwitch' => array_merge($switch, $level2Switch),
					]);
	}

	public function getDashboardParametersAjax()
	{
		$parameters = Parameter::get();
		return response()->json([ 'success' => true, 'data' => ParameterResource::collection($parameters) ]);
	}

	public function loadParameterAjax(Request $request, $id)
	{
		if ($id) {
			$parameter = Parameter::find($id);
			$theValue = unserialize($parameter['value']);
			if (is_array($theValue)) {
				$theValue = "[" . implode(",", $theValue) . "]";
			}

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $parameter->id,
					'csrf' => csrf_token(),
					'key' => $parameter->key,
					'value' => $theValue
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'key' => '',
				'value' => ''
			]
		]);
	}

	public function create(Request $request)
	{
		return view()->first(generateTemplateCandidates('form.parameter'), [
							'readonly' => false,
							'postUrl' => "/" . $request->path(),
							'title' => trans('messages.Create parameter'),
							'action' => trans('forms.Update'),
						]);
	}

	public function createPost(Request $request)
	{
		$this->validate($request, [
			'param_key' => 'required',
			'param_value' => 'required',
		]);

		$theValue = $request->input('param_value');
		if (preg_match("/^\"(.)+\"$/i", $theValue)) {
			$theValue = trim((string)$theValue, "\"");
		} else if (preg_match("/^(\d)+$/i", $theValue)) {
			$theValue = (int)$theValue;
		} else if (preg_match("/^(\[)([^,]+)(,[^,]+)*(\])$/i", $theValue)) {
			$theValue = explode(",", rtrim(ltrim($theValue, "["), "]"));
		}

		try {
			DB::transaction(function() use ($request, $theValue) {
				Parameter::create([
						'key' => $request->input('param_key'),
						'value' => serialize($theValue),
					]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.Parameter created.'));
	}

	public function createPostAjax(Request $request)
	{
		$this->validate($request, [
			'param_key' => 'required',
			'param_value' => 'required',
		]);

		$theValue = $request->input('param_value');
		if (preg_match("/^\"(.)+\"$/i", $theValue)) {
			$theValue = trim((string)$theValue, "\"");
		} else if (preg_match("/^(\d)+$/i", $theValue)) {
			$theValue = (int)$theValue;
		} else if (preg_match("/^(\[)([^,]+)(,[^,]+)*(\])$/i", $theValue)) {
			$theValue = explode(",", rtrim(ltrim($theValue, "["), "]"));
		}

		$parameter = null;
		try {
			DB::transaction(function() use ($request, $theValue, &$parameter) {
				$parameter = Parameter::create([
						'key' => $request->input('param_key'),
						'value' => serialize($theValue),
					]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new ParameterResource($parameter) ]);
	}

	public function edit($id, Request $request)
	{
		$param = Parameter::find($id);
		$theValue = unserialize($param['value']);
		if (is_array($theValue)) {
			$theValue = "[" . implode(",", $theValue) . "]";
		}

		// load parameter detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
					'param_key' => $param['key'],
					'param_value' => $theValue,
				]);
		}

		return view()->first(generateTemplateCandidates('form.parameter'), [
							'readonly' => false,
							'postUrl' => "/" . $request->path(),
							'title' => trans('messages.Edit parameter'),
							'action' => trans('forms.Update'),
						]);
	}

	public function editPost($id, Request $request)
	{
		$this->validate($request, [
			'param_key' => 'required',
			'param_value' => 'required',
		]);

		$param = Parameter::find($id);

		$theValue = $request->input('param_value');
		if (preg_match("/^\"(.)+\"$/i", $theValue)) {
			$theValue = trim((string)$theValue, "\"");
		} else if (preg_match("/^(\d)+$/i", $theValue)) {
			$theValue = (int)$theValue;
		} else if (preg_match("/^(\[)([^,]+)(,[^,]+)*(\])$/i", $theValue)) {
			$theValue = explode(",", rtrim(ltrim($theValue, "["), "]"));
		}

		try {
			DB::transaction(function() use ($request, $param, $theValue) {
				$param->update([
						'key' => $request->input('param_key'),
						'value' => serialize($theValue),
					]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.Parameter updated.'));
	}

	public function updatePostAjax($id, Request $request)
	{
		$this->validate($request, [
			'param_key' => 'required',
			'param_value' => 'required',
		]);

		$param = Parameter::find($id);

		$theValue = $request->input('param_value');
		if (preg_match("/^\"(.)+\"$/i", $theValue)) {
			$theValue = trim((string)$theValue, "\"");
		} else if (preg_match("/^(\d)+$/i", $theValue)) {
			$theValue = (int)$theValue;
		} else if (preg_match("/^(\[)([^,]+)(,[^,]+)*(\])$/i", $theValue)) {
			$theValue = explode(",", rtrim(ltrim($theValue, "["), "]"));
		}

		try {
			DB::transaction(function() use ($request, $param, $theValue) {
				$param->update([
						'key' => $request->input('param_key'),
						'value' => serialize($theValue),
					]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new ParameterResource($param) ]);
	}
}
