<?php

namespace App\Http\Controllers;

use App;
use App\Currency;
use App\Downloadable;
use App\Parameter;
use App\ExpenseHeader;
use App\ExpenseDetail;
use App\ExpenseHistory;
use App\RecurringExpense;
use App\TaxableEntity;
use App\UniqueTradable;
use App\User;
use App\Helpers\ChargeEntryView;
use App\Helpers\DateHelper;
use App\Helpers\HistoryHelper;
use App\Helpers\ParameterHelper;
use App\Http\Requests;
use App\Http\Resources\ExpenseHeader as ExpenseHeaderResource;
use App\Http\Resources\RecurringExpense as RecurringExpenseResource;
use Auth;
use DB;
use Illuminate\Http\Request;
use Session;
use Storage;
use Log;
use Validator;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class ChargeController extends Controller
{
	// validation rules for 'update', 'create' requires additional rules
	CONST VALIDATION_RULES = [
			"reference" => "required",
			"incurdate.*" => "required|date",
			"unitprice.*" => "required|numeric",
			"quantity.*" => "required|min:1|numeric",
		];

	public function create($sid, Request $request)
	{
		$entity = TaxableEntity::find($sid);
		// load charge detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors') && !Session::has('recurring')) {
			$request->session()->flashInput([
				'increment' => '?????',
				'entity' => $entity->id,
				'staff' => auth()->user()->id,
				'currency' => $entity->currency_id,
				'reference' => '',
				'notes' => '',
				'line' => [],
				'product' => [],
				'incurdate' => [],
				'unitprice' => [],
				'quantity' => [],
				'description' => [],
				'subtotal' => [],
				'filename' => [],
				'fileurl' => []
			]);
		}

		// flush out 'recurring' session value, whether exists or not
		$request->session()->pull('recurring');

		$optionArray = ChargeEntryView::generateOptionArrayForTemplate($sid, true);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
					// 'entity' => $sid,
					'title' => trans('finance.New charge'),
					// 'document' => '????',
					'currencyFormat' => $entity->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					'history' => null,
					'action' => trans('forms.Create')
				];

		return view()->first(generateTemplateCandidates('form.charge_entry'), $optionArray);
	}

	public function createPost($sid, Request $request)
	{
		// run the validation rules on the inputs from the form
		$inputLines = $request->input('line');
		$fileRules = [];
		array_walk($inputLines, function($val, $key) use(&$fileRules) {
			$fileRules['upload-selector.'.$key] = 'required|file';
		});
		$validator = Validator::make($request->all(), array_merge(self::VALIDATION_RULES, $fileRules));
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		$title = null;

		try {
			DB::transaction(function() use (&$title, $request) {
				$headerObj = ExpenseHeader::initialize($request);
				$title = $headerObj->title;
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$title, trans('finance.Charge ### created.')));
	}

	public function createPostAjax(Request $request, $sid)
	{
		// remove this visit from history, so redirect will not hit this URL
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		// run the validation rules on the inputs from the form
		$inputLines = $request->input('line');
		$fileRules = [];
		array_walk($inputLines, function($val, $key) use(&$fileRules) {
			$fileRules['upload-selector.'.$key] = 'required|file';
		});
		$validator = Validator::make($request->all(), array_merge(self::VALIDATION_RULES, $fileRules));
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$chargeHeaderObj = null;

		try {
			DB::transaction(function() use (&$chargeHeaderObj, $request) {
				$chargeHeaderObj = ExpenseHeader::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => [ 'charge' => new ExpenseHeaderResource($chargeHeaderObj) ]]);
	}

	public function update($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		// make sure
		//   - this person is not modifing other employee's expense.
		//   - this expense is not yet submitted
		if (!$expenseHeaderObj->canBeAccessedBy(auth()->user()->id) ||
			($expenseHeaderObj->status != 'un-submitted')) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be updated')));
		}

		// load product detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$oldInput = $expenseHeaderObj->generateArrayForOldInput();
			$request->session()->flashInput($oldInput);
		}

		$optionArray = ChargeEntryView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = false;
		$optionArray['source'] = [
					// 'entity' => $expenseHeaderObj->entity_id,  //$oldInput['entity'],
					'title' => trans('finance.Update charge'),
					// 'document' => $expenseHeaderObj->title,
					'currencyFormat' => $expenseHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					'history' => $expenseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.Update')
				];

		return view()->first(generateTemplateCandidates('form.charge_entry'), $optionArray);
	}

	public function updatePost($id, Request $request)
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

		// update database
		$header = ExpenseHeader::find($id);
		// make sure
		//   - this person is not modifing other employee's expense.
		//   - this expense is not yet submitted
		if (!$header->canBeAccessedBy(auth()->user()->id) ||
			($header->status != 'un-submitted')) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$header->title, trans('finance.Expense ### can not be updated')));
		}

		try {
			DB::transaction(function() use ($request, $header) {
				$header->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$header->title, trans('finance.Expense ### updated.')));
	}

	public function updatePostAjax(Request $request, $id)
	{
		// remove this visit from history, so redirect will not hit this URL
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), self::VALIDATION_RULES);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		// update database
		$header = ExpenseHeader::find($id);
		// make sure
		//   - this person is not modifing other employee's expense.
		//   - this expense is not yet submitted
		if (!$header->canBeAccessedBy(auth()->user()->id) ||
			($header->status != 'un-submitted')) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$header->title, trans('finance.Expense ### can not be updated')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $header) {
				$header->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => trans('messages.System failure') . ' #' . $registration ]]);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => [ 'charge' => new ExpenseHeaderResource($header) ]]);
	}

	public function view($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		if (!$expenseHeaderObj->canBeAccessedBy(Auth::user()->id)) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be viewed')));
		}

		// no need to check for error-redirect since this read only
		$oldInput = $expenseHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = ChargeEntryView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					// 'entity' => $oldInput['entity'],
					'title' => trans('finance.View charge'),
					// 'document' => $expenseHeaderObj->title,
					'currencyFormat' => $expenseHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					'history' => $expenseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => [] //trans('forms.View PDF')
				];

		return view()->first(generateTemplateCandidates('form.charge_entry'), $optionArray);
	}

	public function loadAjax(Request $request, $id)
	{
		// remove this visit from history, so redirect will not hit this URL
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		if ($id) {
			// retrieve appropriate charge
			$expenseHeaderObj = ExpenseHeader::find($id);
			$expenseDetails = $expenseHeaderObj->detail()->with('downloadable')->get();
			$monetaryFormat = "%0." . $expenseHeaderObj->currency->getFormat()['fdigit'] . "f";

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $expenseHeaderObj->id,
					'history' => array_map(function($elem) {
							$timeElem = explode(" ", $elem['updated_at']);
							$timeString = \App\Helpers\DateHelper::dbToGuiDate($timeElem[0]) . " " . date("g:iA", strtotime($elem['updated_at']));
							return sprintf(trans('messages.%1$s %2$s at %3$s'), $elem['staff']['name'], trans('action.'.$elem['process_status']), $timeString);
						}, $expenseHeaderObj->history()->with('staff')->orderBy('created_at', 'desc')->get()->toArray()),
					'csrf' => csrf_token(),
					'increment' => $expenseHeaderObj->title,
	        'entity' => $expenseHeaderObj->entity_id,
	        'staff' => $expenseHeaderObj->staff_id,
	        'currency' => $expenseHeaderObj->currency_id,
	        'reference' => $expenseHeaderObj->reference,
	        'notes' => $expenseHeaderObj->notes,
					'line' => $expenseDetails->pluck('id'),
					'product' => $expenseDetails->pluck('unique_tradable_id'),
					'incurdate' => array_map(function($elem) { return DateHelper::dbToGuiDate($elem); }, $expenseDetails->pluck('incur_date')->toArray()),
					'unitprice' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem); }, $expenseDetails->pluck('unit_price')->toArray()),
					'quantity' => array_map(function($elem) { return sprintf(env('APP_QUANTITY_FORMAT'), $elem); }, $expenseDetails->pluck('quantity')->toArray()),
					'description' => $expenseDetails->pluck('notes'),
					'subtotal' => array_map(function($elem) use ($monetaryFormat) { return sprintf($monetaryFormat, $elem); }, $expenseDetails->pluck('subtotal')->toArray()),
					'filename' => array_map(function($elem) { return $elem['downloadable']['original_name']; }, $expenseDetails->toArray()),
					'fileurl' => array_map(function($elem) { return '/file/download/' . $elem['downloadable']['hash']; }, $expenseDetails->toArray()),
				]
			]);
		}

		$monetaryFormat = "%0." . TaxableEntity::theCompany()->currency->getFormat()['fdigit'] . "f";

		// generate a stub for 'create'
		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'history' => [ ],
				'csrf' => csrf_token(),
				'increment' => '????',
				'entity' => 0,
				'staff' => auth()->user()->id,
				'currency' => TaxableEntity::theCompany()->currency->id,
				'reference' => '',
				'notes' => '',
				'line' => [ 0 ],
				'product' => [ \App\UniqueTradable::getActiveExpenditures('sku', 'asc')->first()->id ],
				'incurdate' => [ DateHelper::dbToGuiDate(date("Y-m-d")) ],
				'unitprice' => [ sprintf($monetaryFormat, 0) ],
				'quantity' => [ sprintf(env('APP_QUANTITY_FORMAT'), 0) ],
				'description' => [ '' ],
				'subtotal' => [ sprintf($monetaryFormat, 0) ],
				'filename' => [ trans('tool.Browse file') ],
				'fileurl' => [ '' ],
			]
		]);
	}

	public function retract($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		if (!$expenseHeaderObj->canBeAccessedBy(Auth::user()->id) || $expenseHeaderObj->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be retracted')));
		}

		// no need to check error-redirect since this is read only
		$oldInput = $expenseHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = ChargeEntryView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					// 'entity' => $oldInput['entity'],
					'title' => trans('finance.Retract charge'),
					// 'document' => $expenseHeaderObj->title,
					'currencyFormat' => $expenseHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					'history' => $expenseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.Retract')
				];

		return view()->first(generateTemplateCandidates('form.charge_entry'), $optionArray);
	}

	public function retractPost($id, Request $request)
	{
		$header = ExpenseHeader::find($id);
		if (!$header->canBeAccessedBy(Auth::user()->id) || $header->isNotOpen()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$header->title, trans('finance.Expense ### can not be retracted')));
		}

		try {
			DB::transaction(function() use ($request, $header) {
				$header->retract($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$header->title, trans('finance.Expense ### retracted.')));
	}

	public function retractPostAjax(Request $request, $id)
	{
		// remove this visit from history, so redirect will not hit this URL
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$header = ExpenseHeader::find($id);
		if (!$header->canBeAccessedBy(Auth::user()->id) || $header->isNotOpen()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$header->title, trans('finance.Expense ### can not be retracted')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $header) {
				$header->retract($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return response()->json([ 'success' => true, 'data'=> [ 'charge' => new ExpenseHeaderResource($header) ]]);
	}

	public function submit($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		if ($expenseHeaderObj->isSubmitted()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be submitted')));
		}

		// no need to check error-redirect since this is read only
		$oldInput = $expenseHeaderObj->generateArrayForOldInput();
		$request->session()->flashInput($oldInput);

		$optionArray = ChargeEntryView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					// 'entity' => $oldInput['entity'],
					'title' => trans('finance.Submit charge'),
					// 'document' => $expenseHeaderObj->title,
					'currencyFormat' => $expenseHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					'history' => $expenseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.Submit')
				];

		return view()->first(generateTemplateCandidates('form.charge_entry'), $optionArray);
	}

	public function submitPost($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		if ($expenseHeaderObj->isSubmitted()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be submitted')));
		}

		try {
			DB::transaction(function() use ($request, $expenseHeaderObj) {
				$expenseHeaderObj->submit($request);

				// email/contact all approvers for approval
				if ($expenseHeaderObj->requireApproval()) {
					$expenseHeaderObj->sendEmailRequestApproval();
				} else {
					$expenseHeaderObj->autoApprove($request);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### submitted.')));
	}

	public function submitPostAjax(Request $request, $id)
	{
		// remove this visit from history, so redirect will not hit this URL
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$expenseHeaderObj = ExpenseHeader::find($id);
		if ($expenseHeaderObj->isSubmitted()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be submitted')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $expenseHeaderObj) {
				$expenseHeaderObj->submit($request);

				// email/contact all approvers for approval
				if ($expenseHeaderObj->requireApproval()) {
					$expenseHeaderObj->sendEmailRequestApproval();
				} else {
					$expenseHeaderObj->autoApprove($request);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => [ 'charge' => new ExpenseHeaderResource($expenseHeaderObj) ]]);
	}

	public function approve($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		if (!$expenseHeaderObj->requireApproval(Auth::user()->id) || !$expenseHeaderObj->isUnderReview()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be approved')));
		}

		// no need to check error-redirect since this is read only
		$oldInput = $expenseHeaderObj->generateArrayForOldInput($id);
		$request->session()->flashInput($oldInput);

		$optionArray = ChargeEntryView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					// 'entity' => $oldInput['entity'],
					'title' => trans('finance.Approve charge'),
					// 'document' => $expenseHeaderObj->title,
					'currencyFormat' => $expenseHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					'history' => $expenseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => [
								'approve' => trans('forms.Approve'),
								'disapprove' => trans('forms.Disapprove'),
							]
				];

		return view()->first(generateTemplateCandidates('form.charge_entry'), $optionArray);
	}

	public function approvePost($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		if (!$expenseHeaderObj->requireApproval(Auth::user()->id) || !$expenseHeaderObj->isUnderReview()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be approved')));
		}

		try {
			DB::transaction(function() use ($request, $expenseHeaderObj) {
				switch ($request->input('submit')) {
				case 'approve':
					$expenseHeaderObj->approve(Auth::user()->id, $request->ip());
					if ($expenseHeaderObj->status == 'approved') {
						// when approved convert into payable
						$expenseHeaderObj->convertToPayable($request->ip());
					}
					break;
				case 'disapprove':
					$expenseHeaderObj->disapprove(Auth::user()->id, $request->ip());
					break;
				default:
					break;
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### processed.')));
	}

	public function approvePostAjax(Request $request, $id)
	{
		// remove this visit from history, so redirect will not hit this URL
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$expenseHeaderObj = ExpenseHeader::find($id);
		if (!$expenseHeaderObj->requireApproval(auth()->user()->id) || !$expenseHeaderObj->isUnderReview()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be approved')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $expenseHeaderObj) {
				switch ($request->input('submit')) {
				case 'approve':
					$expenseHeaderObj->approve(auth()->user()->id, $request->ip());
					if ($expenseHeaderObj->status == 'approved') {
						// when approved convert into payable
						$expenseHeaderObj->convertToPayable($request->ip());
					}
					break;
				case 'disapprove':
					$expenseHeaderObj->disapprove(auth()->user()->id, $request->ip());
					break;
				default:
					break;
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return response()->json([ 'success' => true, 'data' => [ 'charge' => new ExpenseHeaderResource($expenseHeaderObj) ]]);
	}

	public function process($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		if (!$expenseHeaderObj->canBeAccessedBy(Auth::user()->id) || !$expenseHeaderObj->isApproved()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be processed')));
		}

		// no need to check error-redirect since this is read only
		$oldInput = $this->generateArrayForOldInput($id);
		$request->session()->flashInput($oldInput);

		$optionArray = ChargeEntryView::generateOptionArrayForTemplate($id);
		$optionArray['readonly'] = true;
		$optionArray['source'] = [
					// 'entity' => $oldInput['entity'],
					'title' => trans('finance.Process charge'),
					// 'document' => $oldInput['increment'],
					'currencyFormat' => $expenseHeaderObj->currency->getFormat(true),
					'post_url' => '/' . $request->path(),
					'history' => $expenseHeaderObj->history()->orderBy('created_at', 'desc')->get(),
					'action' => trans('forms.Process')
				];

		return view()->first(generateTemplateCandidates('form.charge_entry'), $optionArray);
	}

	// this function is not being used.  reserved for future development
	public function processPost($id, Request $request)
	{
		$expenseHeaderObj = ExpenseHeader::find($id);
		if (!$expenseHeaderObj->canBeAccessedBy(Auth::user()->id) || !$expenseHeaderObj->isApproved()) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be processed')));
		}

		try {
			DB::transaction(function() use ($request, $expenseHeaderObj) {
				// process expense
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be processed')/*trans('finance.Expense ### processed.')*/));
	}

	public function processPostAjax(Request $request, $id)
	{
		// remove this visit from history, so redirect will not hit this URL
		// removal no longer needed since this controller is in web-ajax group
		// $this->removeFromHistory();

		$expenseHeaderObj = ExpenseHeader::find($id);
		if (!$expenseHeaderObj->canBeAccessedBy(Auth::user()->id) || !$expenseHeaderObj->isApproved()) {
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be processed')) ]]]);
		}

		try {
			DB::transaction(function() use ($request, $expenseHeaderObj) {
				// process expense
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return response()->json([ 'success' => false, 'errors' => [ 'general' => [ str_replace("###", "#".$expenseHeaderObj->title, trans('finance.Expense ### can not be processed') /*trans('finance.Expense ### processed.')*/ ) ]]]);
	}

	public function recurring($id, Request $request)
	{
		// remove this visit from history, so redirect will not hit this URL
		$this->removeFromHistory();

		$recurring = RecurringExpense::find($id);

		$entity = TaxableEntity::find($recurring->entity_id);

		$currencyFormat = $entity->currency->getFormat();

		$lastExpense = ExpenseDetail::join('expense_headers', 'expense_details.expense_header_id', '=', 'expense_headers.id')
							->join('taxable_entities', 'taxable_entities.id', '=', 'expense_headers.entity_id')
							->where('expense_headers.entity_id', $entity->id)
							->where('expense_details.unique_tradable_id', $recurring->unique_tradable_id)
							->orderBy('expense_details.incur_date', 'desc')
							->first();

		$request->session()->flashInput([
				'increment' => '?????',
				'entity' => $entity->id,
				'staff' => auth()->user()->id,
				'currency' => $entity->currency_id,
				'reference' => '',
				'notes' => '',
				'line' => [ 0 ],
				'product' => [ $recurring->unique_tradable_id ],
				'incurdate' => [ DateHelper::dbToGuiDate(date("Y-m-d")) ],
				'unitprice' => [ $lastExpense ? sprintf("%0.".$currencyFormat['fdigit']."f", $lastExpense->unit_price) : $currencyFormat['min'] ],
				'quantity' => [ $recurring->quantity ],
				'description' => [ $recurring->notes ],
				'subtotal' => [ sprintf("%0.".$currencyFormat['fdigit']."f", ($lastExpense ? $lastExpense->unit_price : $currencyFormat['min']) * $recurring->quantity) ],
				'filename' => [ trans('tool.Browse file') ],
				'fileurl' => [ "" ]
			]);

		$request->session()->put('recurring', 1);

		return redirect('/charge/create/' . $recurring->entity_id)->withInput($request->old());
	}

	public function loadRecurringAjax($id, Request $request)
	{

		$recurring = RecurringExpense::find($id);

		$entity = TaxableEntity::find($recurring->entity_id);

		$currencyFormat = $entity->currency->getFormat();

		$lastExpense = ExpenseDetail::select("expense_details.*")
							->join('expense_headers', 'expense_details.expense_header_id', '=', 'expense_headers.id')
							->join('taxable_entities', 'taxable_entities.id', '=', 'expense_headers.entity_id')
							->where('expense_headers.entity_id', $entity->id)
							->where('expense_details.unique_tradable_id', $recurring->unique_tradable_id)
							->orderBy('expense_details.incur_date', 'desc')
							->first();

		$monetaryFormat = "%0." . $currencyFormat['fdigit'] . "f";

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'history' => [ ],
				'csrf' => csrf_token(),
				'increment' => '????',
				'entity' => $entity->id,
				'staff' => auth()->user()->id,
				'currency' => $entity->currency_id,
				'reference' => '',
				'notes' => '',
				'line' => [ 0 ],
				'product' => [ $recurring->unique_tradable_id ],
				'incurdate' => [ DateHelper::dbToGuiDate(date("Y-m-d")) ],
				'unitprice' => [ $lastExpense ? sprintf("%0.".$currencyFormat['fdigit']."f", $lastExpense->unit_price) : $currencyFormat['min'] ],
				'quantity' => [ $recurring->quantity ],
				'description' => [ $lastExpense ? $lastExpense->notes : '' ],
				'subtotal' => [ sprintf("%0.".$currencyFormat['fdigit']."f", ($lastExpense ? $lastExpense->unit_price : $currencyFormat['min']) * $recurring->quantity) ],
				'filename' => [ trans('tool.Browse file') ],
				'fileurl' => [ '' ]
			]
		]);
	}

	public function manageRecurring(Request $request)
	{
		// load recurring detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$recurring = RecurringExpense::all()->toArray();
			$request->session()->flashInput([
				'line' => array_column($recurring, 'id'),
				'tradable' => array_column($recurring, 'unique_tradable_id'),
				'entity' => array_column($recurring, 'entity_id'),
				'valid' => array_column($recurring, 'valid'),
				'notes' => array_column($recurring, 'notes'),
				'numeral' => array_column($recurring, 'frequency_numeral'),
				'period' => array_column($recurring, 'frequency_unit'),
			]);
		}

		$entities = TaxableEntity::where('type', '!=', 'self')->orderBy('code', 'asc')->get();

		$tradables = UniqueTradable::where('expendable', 1)->orderBy('sku', 'asc')->get();

		return view()->first(generateTemplateCandidates('form.recurring_expense'), [
				'readonly' => false,
				'entities' => $entities,
				'tradables' => $tradables,
			]);
	}

	public function manageRecurringPost(Request $request)
	{
		//  validation, redirect back if failed.
		$validator = Validator::make($request->all(), [
			"tradable.*" => 'required|numeric',
			"entity.*" => "required|numeric",
			"numeral.*" => 'required|numeric',
			"period.*" => 'required|in:days,weeks,months,years',
		]);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the form
					->withInput($request->all()); // send back the input so we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request) {
				foreach ($request->input('line') as $index => $id) {
					if ($id) {
						$recurring = RecurringExpense::find($id);
						$recurring->entity_id = $request->input('entity')[$index];
						$recurring->unique_tradable_id = $request->input('tradable')[$index];
						$recurring->notes = $request->input('notes')[$index];
						$recurring->frequency_numeral = $request->input('numeral')[$index];
						$recurring->frequency_unit = $request->input('period')[$index];
						$recurring->valid = in_array($request->input('valid')[$index], ["1", "true"]);
						$recurring->save();
					} else {
						RecurringExpense::create([
							'entity_id' => $request->input('entity')[$index],
							'unique_tradable_id' => $request->input('tradable')[$index],
							'quantity' => 1,
							'notes' => $request->input('notes')[$index],
							'frequency_numeral' => $request->input('numeral')[$index],
							'frequency_unit' => $request->input('period')[$index],
							'valid' => in_array($request->input('valid')[$index], [ "1", "true"]),
						]);
					}
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(1))->with('alert-success', trans('messages.Recurring expense updated.'));
	}

	public function getDashboardExpenseAjax(Request $request, $type)
	{
		switch ($type) {
			case 'all':
				$expenses = ExpenseHeader::all();
				break;
			default:
				$entityType = explode("-", $type);
				$expenses = ExpenseHeader::select('expense_headers.*')
							->join('taxable_entities', 'taxable_entities.id', '=', 'expense_headers.entity_id')
							->whereIn('taxable_entities.type', $entityType)->get();
				break;
		}

		return response()->json([ 'success' => true, 'data' => ExpenseHeaderResource::collection($expenses) ]);
	}

	public function getDashboardRecurringExpenseAjax()
	{
		$recurrings = RecurringExpense::where('valid', 1)->get();

		return response()->json([ 'success' => true, 'data' => RecurringExpenseResource::collection($recurrings) ]);
	}

}
