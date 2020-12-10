<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App;
use App\ExpenseApprovalRule;
use App\TaxableEntity;
use App\User;
use Auth;
use App\Http\Requests;
use App\Helpers\HistoryHelper;
use DB;
use Session;
use Validator;

class ExpenseApprovalRuleController extends Controller
{
	public function index(Request $request)
	{
		// load rule detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$rules = ExpenseApprovalRule::all()->toArray();
			$request->session()->flashInput([
				'line' => array_column($rules, 'id'),
				'entity' => array_column($rules, 'src_entity_id'),
				'approver' => array_column($rules, 'approver_id'),
				'threshold' => array_column($rules, 'threshold'),
				'valid' => array_column($rules, 'valid'),
			]);
		}

		$entities = TaxableEntity::getNonSuppliers('name', 'asc');

		$employees = User::getAllStaff('name', 'asc');

		return view()->first(generateTemplateCandidates('rule.expense_approval'),
						[
							'readonly' => false,
							'entities' => $entities,
							'employees' => $employees,
							'currency' => TaxableEntity::theCompany()->currency->getFormat()
						]);
	}

	public function indexPost(Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
			'entity.*' => 'required|numeric',
			'approver.*' => 'required|numeric',
			'threshold.*' => 'required|numeric|min:0',
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
						$rule = ExpenseApprovalRule::find($id);
						$rule->src_entity_id = $request->input('entity')[$index];
						$rule->approver_id = $request->input('approver')[$index];
						$rule->threshold = $request->input('threshold')[$index];
						$rule->valid = in_array($request->input('valid')[$index], ["1", "true"]);
						$rule->save();
					} else {
						ExpenseApprovalRule::create([
								'approver_id' => $request->input('approver')[$index],
								'src_table' => 'taxable_entities',
								'src_entity_id' => $request->input('entity')[$index],
								'threshold' => $request->input('threshold')[$index],
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
