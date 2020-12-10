<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\Helpers\CountryHelper;
use App\Helpers\HistoryHelper;
use App\Http\Requests;
use App\PaymentTerm;
use App\TaxableEntity;
use App\TradableTransaction;
use App\TransactableDetail;
use App\User;
use App\UniqueTradable;
use Auth;
use DB;
use Session;
use Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class CustomerController extends Controller
{
	public function createCustomer(Request $request)
	{
		return view()->first(generateTemplateCandidates('form.taxable_entity'),
					array(
						'source' => array(
							'title' => trans('crm.New customer'),
							'post_url' => '/' . $request->path(),
							'type' => 'customer',
							'action' => trans('forms.Create')
						),
						'country' => CountryHelper::getAllCountryOptions(),
						'currency' => Currency::getActiveCurrencies('symbol', 'asc'),
						'payment' => PaymentTerm::getActivePaymentTerms('grace_days', 'asc'),
						'read' => array(
							'code' => 0,
						),
					));
	}

	public function createCustomerPost(Request $request)
	{
		// validate first.
		// validate the info, create rules for the inputs
		$rules = array(
			'code' => 'required|unique:taxable_entities|max:10',
			'name' => 'required|max:100',
			'contact' => 'required|max:100',
			'email' => 'required|email|max:100',
			'phone' => 'required|max:20',
			'bstreet' => 'required|min:1',
			'bcity' => 'required|min:1',
			'bstate' => 'required|min:1',
			'bzipcode' => 'required|min:1',
			'sstreet' => 'required|min:1',
			'scity' => 'required|min:1',
			'sstate' => 'required|min:1',
			'szipcode' => 'required|min:1',
		);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request) {
				$additionalData = [
					'active' => empty($request->input('active')) ? 0 : 1,
					'type' => 'customer',
				];
				$request->merge($additionalData);
				TaxableEntity::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('crm.New customer added'));
	}

	public function updateCustomer($id, Request $request)
	{
		$taxableEntity = TaxableEntity::where([['type', 'customer'], ['id', $id]])->first();
		if (!$taxableEntity) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#$id", trans('crm.Customer ### can not be updated')));
		}
		$contact = $taxableEntity->contact()->orderBy('id', 'desc')->first();
		$billingAddress = Address::where([['purpose', 'billing'], ['entity_id', $taxableEntity->id], ['is_default', 1]])->first();
		$shippingAddress = Address::where([['purpose', 'shipping'], ['entity_id', $taxableEntity->id], ['is_default', 1]])->first();

		// load customer detail; errors imply redirect back, flashing input removes old value
		if (!Session::has('alert-danger') && !Session::has('alert-warning') && !Session::has('errors')) {
			$request->session()->flashInput([
				'code' => $taxableEntity->code,
				'name' => $taxableEntity->name,
				'active' => $taxableEntity->active,
				'contact' => $contact->name,
				'email' => $contact->email,
				'phone' => $contact->phone,
				'bstreet' => $billingAddress->street,
				'bunit' => $billingAddress->unit,
				'bdistrict' => $billingAddress->district,
				'bcity' => $billingAddress->city,
				'bstate' => $billingAddress->state,
				'bcountry' => $billingAddress->country,
				'bzipcode' => $billingAddress->zipcode,
				'sstreet' => $shippingAddress->street,
				'sunit' => $shippingAddress->unit,
				'sdistrict' => $shippingAddress->district,
				'scity' => $shippingAddress->city,
				'sstate' => $shippingAddress->state,
				'scountry' => $shippingAddress->country,
				'szipcode' => $shippingAddress->zipcode,
				'payment' => $taxableEntity->payment_term_id,
				'currency' => $taxableEntity->currency_id,
			]);
		}

		return view()->first(generateTemplateCandidates('form.taxable_entity'),
					array(
						'source' => array(
							'title' => trans('crm.Update customer'),
							'post_url' => '/' . $request->path(),
							'type' => 'customer',
							'action' => trans('forms.Update'),
						),
						'country' => CountryHelper::getAllCountryOptions(),
						'currency' => Currency::getActiveCurrencies('symbol', 'asc'),
						'payment' => PaymentTerm::getActivePaymentTerms('grace_days', 'asc'),
						'read' => array(
							'code' => 1,
						),
					));
	}

	public function updateCustomerPost($id, Request $request)
	{
		// validate first.
		// validate the info, create rules for the inputs
		$rules = array(
			'code' => 'required|max:10',
			'name' => 'required|max:100',
			'contact' => 'required|max:100',
			'email' => 'required|email|max:100',
			'phone' => 'required|max:20',
			'bstreet' => 'required|min:1',
			'bcity' => 'required|min:1',
			'bstate' => 'required|min:1',
			'bzipcode' => 'required|min:1',
			'sstreet' => 'required|min:1',
			'scity' => 'required|min:1',
			'sstate' => 'required|min:1',
			'szipcode' => 'required|min:1',
		);
		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);
		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/' . $request->path())
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($id, $request) {
				// edit database
				$taxableEntity = TaxableEntity::find($id);
				$additionalData = [
					'active' => empty($request->input('active')) ? 0 : 1,
				];
				$request->merge($additionalData);
				$taxableEntity->synchronize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('crm.Customer updated.'));
	}

	public function viewCustomer($id, Request $request)
	{
		$taxableEntity = TaxableEntity::where([['type', 'customer'], ['id', $id]])->first();
		if (!$taxableEntity) {
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', str_replace("###", "#$id", trans('crm.Customer ### can not be viewed')));
		}

		$customers = TaxableEntity::where('type', 'customer')->get()->toArray();

		$products = UniqueTradable::getProducts('sku', 'asc');

		return view()->first(generateTemplateCandidates('crm.viewcustomer'), [
							'customers' => $customers,
							'products' => $products,
						]);
	}

	public function customerPerformanceAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$customerId = $request->input('customer');
		$customer = TaxableEntity::find($customerId);
		$lookBack = $request->input('history');

		// prepare template dataset
		$labels = array_map(function ($n) { return date_format(date_sub(date_create(date("Y-m-01")), date_interval_create_from_date_string($n . " months")), "Y-m"); }, range($lookBack, 0, -1));

		$salesData = array_replace(
				array_fill_keys($labels, 0),
				array_column(DB::select("SELECT sum(if(debit_t_account_id = " . $customer['transaction_t_account_id'] . ", amount, -amount)) as amount, DATE_FORMAT(book_date,'%Y-%m') as period FROM taccount_transactions where (book_date between '" . $labels[0] . "-01 00:00:00' AND '" . date("Y-m-d") . " 23:59:59') AND (debit_t_account_id = " . $customer['transaction_t_account_id'] . " and credit_t_account_id = " . $customer['revenue_t_account_id'] . ")  or (debit_t_account_id = " . $customer['revenue_t_account_id'] . " and credit_t_account_id = " . $customer['transaction_t_account_id'] . ") group by period"), 'amount', 'period')
			);

		$expenseAccountIds = ChartAccount::where('type', 'expense')->pluck('id')->toArray();

		$expenseData = array_replace(
				array_fill_keys($labels, 0),
				array_column(DB::select("SELECT sum(if(credit_t_account_id = " . $customer['transaction_t_account_id'] . ", amount, -amount)) as amount, DATE_FORMAT(book_date,'%Y-%m') as period FROM taccount_transactions where (book_date between '" . $labels[0] . "-01 00:00:00' AND '" . date("Y-m-d") . " 23:59:59') AND (credit_t_account_id = " . $customer['transaction_t_account_id'] . " and debit_t_account_id in (" . implode(",", $expenseAccountIds) . "))  or (credit_t_account_id in (" . implode(",", $expenseAccountIds) . ") and debit_t_account_id = " . $customer['transaction_t_account_id'] . ") group by book_date"), 'amount', 'period')
			);

		$transactableDetails = TransactableDetail::select('transactable_details.*')->leftjoin('transactable_headers', 'transactable_details.transactable_header_id', '=', 'transactable_headers.id')->where('transactable_headers.entity_id', $customerId)->where('transactable_headers.status', '!=', 'valid')->pluck('transactable_details.id')->toArray();

		$profitData = array_replace(
				array_fill_keys($labels, 0),
				array_column(DB::select("SELECT sum(if(debit_t_account_id = " . $customer['transaction_t_account_id'] . ", amount, -amount)) as amount, DATE_FORMAT(book_date,'%Y-%m') as period FROM taccount_transactions where (book_date between '" . $labels[0] . "-01 00:00:00' AND '" . date("Y-m-d") . " 23:59:59') AND (debit_t_account_id = " . $customer['transaction_t_account_id'] . " and credit_t_account_id = " . $customer['revenue_t_account_id'] . ")  or (debit_t_account_id = " . $customer['revenue_t_account_id'] . " and credit_t_account_id = " . $customer['transaction_t_account_id'] . ") group by book_date"), 'amount', 'period')
			);

		return response()->json([
			'success' => true,
			'data' => [
				'labels' => $labels,
				'sales' => [
					'label' => trans('finance.Revenue'),
					'data' => array_values($salesData)
				],
				'expense' => [
					'label' => trans('finance.Expense'),
					'data' => array_values($expenseData)
				],
				'profit' => [
					'label' => trans('finance.Profit'),
					'data' => array_values($profitData)
				],
				'runrate' => TradableTransaction::getRunrate($labels, $customerId),
			]
		]);
	}
}
