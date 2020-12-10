<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\ChartAccount;
use App\Currency;
use App\Helpers\CountryHelper;
use App\Helpers\HistoryHelper;
use App\Http\Requests;
use App\Location;
use App\Parameter;
use App\PaymentTerm;
use App\PurchaseDetail;
use App\PurchaseHistory;
use App\PurchaseHeader;
use App\TaxableEntity;
use App\TradableTransaction;
use App\UniqueTradable;
use App\User;
use Auth;
use DB;
use NumberFormatter;
use Session;
use Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class SupplierController extends Controller
{
	public function createSupplier(Request $request)
	{
		return view()->first(generateTemplateCandidates('form.taxable_entity'),
					array(
						'source' => array(
							'title' => trans('vrm.New supplier'),
							'post_url' => '/' . $request->path(),
							'type' => 'supplier',
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

	public function createSupplierPost(Request $request)
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
					'type' => 'supplier',
				];
				$request->merge($additionalData);
				TaxableEntity::initialize($request);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard' member function of this controller
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('vrm.New supplier added.'));
	}

	public function updateSupplier($id, Request $request)
	{
		$taxableEntity = TaxableEntity::where('type', 'supplier')->where('id', $id)->first();
		if (!$taxableEntity) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#$id", trans('vrm.Supplier ### can not be updated')));
		}
		$contact = $taxableEntity->contact()->orderBy('id', 'desc')->first();
		$billingAddress = Address::where([['purpose', 'billing'], ['entity_id', $taxableEntity->id], ['is_default', 1]])->first();
		$shippingAddress = Address::where([['purpose', 'shipping'], ['entity_id', $taxableEntity->id], ['is_default', 1]])->first();

		// load supplier detail; errors imply redirect back, flashing input removes old value
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
							'title' => trans('vrm.Update supplier'),
							'post_url' => '/' . $request->path(),
							'type' => 'supplier',
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

	public function updateSupplierPost($id, Request $request)
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
			DB::transaction(function() use ($request, $id) {
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

		// show meesage of success, and give option to go back to 'dashboard' member function of this controller
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('vrm.Supplier updated.'));
	}

	public function viewSupplier($id, Request $request)
	{
		$taxableEntity = TaxableEntity::where([['type', 'supplier'], ['id', $id]])->first();
		if (!$taxableEntity) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', str_replace("###", "#$id", trans('vrm.Supplier ### can not be viewed')));
		}

		$suppliers = TaxableEntity::where('type', 'supplier')->get()->toArray();

		$products = UniqueTradable::getProductsOfSupplier($id, 'sku', 'asc');

		return view()->first(generateTemplateCandidates('vrm.viewsupplier'), [
							'suppliers' => $suppliers,
							'products' => $products,
						]);
	}

	public function supplierPerformanceAjax(Request $request)
	{
		// all Ajax controller does not register with session-history
		// $this->removeFromHistory();

		$supplierId = $request->input('supplier');
		$supplier = TaxableEntity::find($supplierId);
		$lookBack = $request->input('history');

		// prepare template dataset
		$labels = array_map(function ($n) { return date_format(date_sub(date_create(date("Y-m-01")), date_interval_create_from_date_string($n . " months")), "Y-m"); }, range($lookBack, 0, -1));

		return response()->json([
			'success' => true,
			'data' => [
				'labels' => $labels,
				'runrate' => TradableTransaction::getRunrate($labels, $supplierId)
			]
		]);
	}
}
