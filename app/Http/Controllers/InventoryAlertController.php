<?php

namespace App\Http\Controllers;

use App\Helpers\HistoryHelper;
use App\InventoryAlertRule;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Session;
use Validator;

class InventoryAlertController extends Controller
{
	public function index(Request $request)
	{
		// load rule detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$rules = InventoryAlertRule::all()->toArray();
			$request->session()->flashInput([
				'line' => array_column($rules, 'id'),
				'unique_tradable' => array_column($rules, 'unique_tradable_id'),
				'location' => array_column($rules, 'location_id'),
				'lower_limit' => array_column($rules, 'min'),
				'upper_limit' => array_column($rules, 'max'),
				'valid' => array_column($rules, 'valid'),
			]);
		};

    return view()->first(generateTemplateCandidates("rule.inventory_alert"), []);
  }

  public function indexPost(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'unique_tradable.*' => 'required|numeric',
			'location.*' => 'required|numeric',
			'lower_limit.*' => 'required|numeric|min:0',
			'upper_limit.*' => 'required|numeric|min:1',
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
						$rule = InventoryAlertRule::find($id);
						$rule->unique_tradable_id = $request->input('unique_tradable')[$index];
						$rule->location_id = $request->input('location')[$index];
						$rule->min = $request->input('lower_limit')[$index];
						$rule->max = $request->input('upper_limit')[$index];
						$rule->valid = in_array($request->input('valid')[$index], ["1", "true"]);
						$rule->save();
					} else {
						InventoryAlertRule::create([
							'unique_tradable_id' => $request->input('unique_tradable')[$index],
							'location_id' => $request->input('location')[$index],
							'min' => $request->input('lower_limit')[$index],
							'max' => $request->input('upper_limit')[$index],
							'valid' => in_array($request->input('valid')[$index], ["1", "true"]),
						]);
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.Inventory alert updated successfully'));
  }
}
