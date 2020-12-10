<?php

namespace App\Http\Controllers;

use App\Helpers\HistoryHelper;
use App\InteractionUserRule;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Session;
use Validator;

class InteractionUserRuleController extends Controller
{
	public function index(Request $request)
	{
		// load rule detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$rules = InteractionUserRule::all()->toArray();
			$request->session()->flashInput([
				'line' => array_column($rules, 'id'),
				'originator' => array_column($rules, 'originator_id'),
				'participant' => array_column($rules, 'participant_id'),
				'role' => array_column($rules, 'role'),
				'valid' => array_column($rules, 'valid'),
			]);
		}

		$employees = User::getAllStaff('name', 'asc');

		return view()->first(generateTemplateCandidates('rule.interaction_user'),
						[
							'readonly' => false,
							'employees' => $employees,
						]);
	}

	public function indexPost(Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
			'originator.*' => 'required|numeric',
			'participant.*' => 'required|numeric',
			'role.*' => 'required|in:participant,requestor,requestee,assigner,assignee',
		]);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request) {
				foreach ($request->input('line') as $index => $id) {
					if ($id) {
						// update
						$rule = InteractionUserRule::find($id);
						$rule->originator_id = $request->input('originator')[$index];
						$rule->participant_id = $request->input('participant')[$index];
						$rule->role = $request->input('role')[$index];
						$rule->valid = in_array($request->input('valid')[$index], ["1", "true"]);
						$rule->save();
					} else {
						// create
						InteractionUserRule::create([
								'originator_id' => $request->input('originator')[$index],
								'participant_id' => $request->input('participant')[$index],
								'role' => $request->input('role')[$index],
								'valid' => in_array($request->input('valid')[$index], ["1", "true"]),
							]);
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(1))->with('alert-success', trans('messages.Rules updated.'));
	}
}
