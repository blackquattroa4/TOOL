<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App;
use App\UniqueTradable;
use App\UniqueTradableRestriction;
use App\TaxableEntity;
use Auth;
use App\Http\Requests;
use App\Helpers\HistoryHelper;
use DB;
use Session;
use Validator;

class UniqueTradableRestrictionController extends Controller
{
	public function index(Request $request)
	{
		// load restriction detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$restrictions = UniqueTradableRestriction::whereIn('unique_tradable_id',
					UniqueTradable::where('stockable', 1)->get()->pluck('id')->toArray())->get()->toArray();
			$request->session()->flashInput([
				'line' => array_column($restrictions, 'id'),
				'unique_tradable' => array_column($restrictions, 'unique_tradable_id'),
				'entity' => array_column($restrictions, 'associated_id'),
				'filter' => array_column($restrictions, 'action'),
				'valid' => array_column($restrictions, 'enforce'),
			]);
		}

		$tradables = UniqueTradable::where('stockable', 1)->get();

		$customers = TaxableEntity::getCustomers('name', 'asc');

		return view()->first(generateTemplateCandidates('rule.unique_tradable_restriction'),
						[
							'readonly' => false,
							'tradables' => $tradables,
							'customers' => $customers,
						]);
	}

	public function indexPost(Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [
			'unique_tradable.*' => 'required|numeric',
			'filter.*' => 'required|in:include,exclude',
			'entity.*' => 'required|numeric',
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
						$restriction = UniqueTradableRestriction::find($id);
						$restriction->unique_tradable_id = $request->input('unique_tradable')[$index];
						$restriction->action = $request->input('filter')[$index];
						$restriction->associated_id = $request->input('entity')[$index];
						$restriction->enforce = in_array($request->input('valid')[$index], ["1", "true"]);
						$restriction->save();
					} else {
						UniqueTradableRestriction::create([
								'unique_tradable_id' => $request->input('unique_tradable')[$index],
								'action' => $request->input('filter')[$index],
								'associated_attribute' => 'entity',
								'associated_id' => $request->input('entity')[$index],
								'enforce' => in_array($request->input('valid')[$index], ["1", "true"]),
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
