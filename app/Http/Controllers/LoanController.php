<?php

namespace App\Http\Controllers;

use App;
use App\ChartAccount;
use App\Currency;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\LoanHeader;
use App\Http\Resources\LoanHeader as LoanHeaderResource;
use App\TaxableEntity;
use Auth;
use DB;
use Illuminate\Http\Request;
use Storage;
use Validator;

class LoanController extends Controller
{
	CONST VALIDATION_RULES = [
		'title' => 'required',
		'role' => 'required',
		'entity' => 'required|numeric',
		'principal' => 'required|numeric|min:1',
		'apr' => 'required|numeric|min:0'
	];

	public function create(Request $request)
	{
		$entities = TaxableEntity::getActiveEntities('name', 'asc');

		$currencyFormat = TaxableEntity::theCompany()->currency->getFormat();

		return view()->first(generateTemplateCandidates('finance.loan'), [
				'is_create' => true,
				'readonly' => false,
				'postUrl' => '/' . $request->path(),
				'principal_min' => $currencyFormat['min'],
				'entities' => $entities,
				'action' => trans('forms.Submit'),
			]);
	}

	public function createPost(Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request) {
				$loan = LoanHeader::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('finance.Loan created.'));
	}

	public function createPostAjax(Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}
		$loan = null;

		try {
			DB::transaction(function() use ($request, &$loan) {
				$loan = LoanHeader::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => [ 'loan' => new LoanHeaderResource($loan) ]]);
	}

	public function update($id, Request $request)
	{
		$loan = LoanHeader::find($id);

		$entities = TaxableEntity::getActiveEntities('name', 'asc');

		$currencyFormat = $loan->currency->getFormat();

		$request->session()->flashInput([
			'title' => $loan->title,
			'role' => $loan->role,
			'entity' => $loan->entity_id,
			'principal' => sprintf("%.".$currencyFormat['fdigit']."f", $loan->principal),
			'apr' => $loan->annual_percent_rate,
			'currency' => $loan->currency_id,
			'notes' => $loan->notes,
		]);

		return view()->first(generateTemplateCandidates('finance.loan'), [
				'readonly' => false,
				'postUrl' => '/' . $request->path(),
				'principal_min' => $currencyFormat['min'],
				'history' => $loan->history()->orderBy('created_at', 'desc')->get(),
				'entities' => $entities,
				'action' => trans('forms.Update'),
				'transactions' => $loan->getTransactionsAsOf(),
			]);
	}

	public function updatePost($id, Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), array_diff_key(self::VALIDATION_RULES, [ 'role' => '', 'entity' => '', 'principal' => '']));
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}
		$loan = LoanHeader::find($id);

		try {
			DB::transaction(function() use ($request, $loan) {
				$loan->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('finance.Loan updated.'));
	}

	public function updatePostAjax($id, Request $request)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), array_diff_key(self::VALIDATION_RULES, [ 'role' => '', 'entity' => '', 'principal' => '']));
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}
		$loan = LoanHeader::find($id);

		try {
			DB::transaction(function() use ($request, $loan) {
				$loan->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => [ 'loan' => new LoanHeaderResource($loan) ]]);
	}

	public function view($id, Request $request)
	{
		$loan = LoanHeader::find($id);

		$entities = TaxableEntity::getActiveEntities('name', 'asc');

		$currencyFormat = $loan->currency->getFormat();

		$request->session()->flashInput([
			'title' => $loan->title,
			'role' => $loan->role,
			'entity' => $loan->entity_id,
			'principal' => sprintf("%.".$currencyFormat['fdigit']."f", $loan->principal),
			'apr' => $loan->annual_percent_rate,
			'currency' => $loan->currency_id,
			'notes' => $loan->notes,
		]);

		return view()->first(generateTemplateCandidates('finance.loan'), [
				'readonly' => true,
				'postUrl' => '/' . $request->path(),
				'principal_min' => $currencyFormat['min'],
				'history' => $loan->history()->orderBy('created_at', 'desc')->get(),
				'entities' => $entities,
				'transactions' => $loan->getTransactionsAsOf(),
			]);
	}

	public function loadAjax(Request $request, $id)
	{
		if ($id) {
			$loan = LoanHeader::find($id);
			$currencyFormat = $loan->currency->getFormat();

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $loan->id,
					'csrf' => csrf_token(),
					'history' => array_map(function($elem) {
							$timeElem = explode(" ", $elem['updated_at']);
							$timeString = DateHelper::dbToGuiDate($timeElem[0]) . " " . date("g:iA", strtotime($elem['updated_at']));
							return sprintf(trans('messages.%1$s %2$s at %3$s'), $elem['staff']['name'], trans('action.'.$elem['process_status']), $timeString);
						}, $loan->history()->with('staff')->orderBy('created_at', 'desc')->get()->toArray()),
					'transaction' => $loan->getTransactionsAsOf(),
					'title' => $loan->title,
					'role' => $loan->role,
					'entity' => $loan->entity_id,
					'principal' => sprintf("%0." . $currencyFormat['fdigit'] . "f", $loan->principal),
					'currency' => $loan->currency_id,
					'apr' => $loan->annual_percent_rate,
					'notes' => $loan->notes,
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'history' => [ ],
				'transaction' => [ ],
				'title' => '',
				'role' => '',
				'entity' => 0,
				'principal' => 0,
				'currency' => 0,
				'apr' => 0,
				'notes' => '',
			]
		]);
	}

	public function recordInterestAjax(Request $request, $id)
	{
		return response()->json([
			'success' => true,
			'data' => [
				'csrf' => csrf_token()
			]
		]);
	}

	public function recordInterestPostAjax(Request $request, $id)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$error = [];

		if (!$request->input('date')) {
			$error['date'] = [ trans('messages.Date not specified') ];
		}
		if (!$request->input('amount')) {
			$error['amount'] = [ trans('messages.Amount not specified') ];
		}
		$account = ChartAccount::find($request->input('account'));
		if (!$account) {
			$error['account'] = [ trans('messages.Account not specified') ];
		}
		// check from/to account has same currency
		if ($account['currency_id'] != LoanHeader::find($id)['currency_id']) {
			if (!isset($error['account'])) {
				$error['account'] = [ trans('messages.Currency mismatch') ];
			}
		}

		if (count($error)) {
			return response()->json([ 'success' => false, 'errors' => $error ]);
		}

		// record interest
		$loan = LoanHeader::find($id);
		try {
			DB::transaction(function() use ($request, $id, &$loan) {
				$loan->recordInterest(
						\App\Helpers\DateHelper::guiToDBDate($request->get("date")),
						$request->get("amount"),
						$request->get("account"),
						auth()->user()->id,
						$request->ip()
 				);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([ 'success' => true, 'data' => new LoanHeaderResource($loan) ]);
	}

	public function badDebt($id, Request $request)
	{
		$loan = LoanHeader::find($id);

		$entities = TaxableEntity::getActiveEntities('name', 'asc');

		$currencyFormat = $loan->currency->getFormat();

		$request->session()->flashInput([
			'loan_id' => $loan->id,
			'title' => $loan->title,
			'role' => $loan->role,
			'entity' => $loan->entity_id,
			'principal' => sprintf("%.".$currencyFormat['fdigit']."f", $loan->principal),
			'apr' => $loan->annual_percent_rate,
			'currency' => $loan->currency_id,
			'notes' => $loan->notes,
		]);

		return view()->first(generateTemplateCandidates('finance.loan'), [
				'readonly' => false,
				'postUrl' => '/' . $request->path(),
				'principal_min' => $currencyFormat['min'],
				'history' => $loan->history()->orderBy('created_at', 'desc')->get(),
				'entities' => $entities,
				'action' => [
					'baddebt' => trans('finance.Bad debt'),
				],
				'transactions' => $loan->getTransactionsAsOf(),
			]);
	}

	public function badDebtPost(Request $request, $id)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [ 'baddebt_date' => 'required', 'baddebt_account' => 'required' ]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		// write off as bad debt / forgiven loan
		try {
			DB::transaction(function() use ($request, $id) {
				$loan = LoanHeader::find($id)->becomeBadDebt(
						\App\Helpers\DateHelper::guiToDbDate($request->get("baddebt_date")),
						$request->get("baddebt_account"),
						auth()->user()->id,
						$request->ip()
				);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('finance.Loan updated.'));
	}

	public function forgivePostAjax(Request $request, $id)
	{
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), [ 'baddebt_date' => 'required', 'baddebt_account' => 'required' ]);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$loan = LoanHeader::find($id);

		// write off as bad debt / forgiven loan
		try {
			DB::transaction(function() use ($request, $loan) {
				$loan->becomeBadDebt(
						\App\Helpers\DateHelper::guiToDbDate($request->get("baddebt_date")),
						$request->get("baddebt_account"),
						auth()->user()->id,
						$request->ip()
				);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => trans('messages.System failure') . ' #' . $registration ]]);
		}

		// show meesage of success, and go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => [ 'loan' => new LoanHeaderResource($loan) ]]);
	}

	public function getDashboardLoanAjax()
	{
		$loans = LoanHeader::all();

		return response()->json([ 'success' => true, 'data' => LoanHeaderResource::collection($loans) ]);
	}

}
