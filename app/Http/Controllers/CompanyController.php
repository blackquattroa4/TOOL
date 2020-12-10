<?php

namespace App\Http\Controllers;

// use App;
use App\Address;
// use App\ChartAccount;
use App\Currency;
use App\Helpers\CountryHelper;
use App\Helpers\HistoryHelper;
// use App\Http\Requests;
use App\PaymentTerm;
use App\TaxableEntity;
// use App\TradableTransaction;
// use App\TransactableDetail;
use App\User;
// use App\UniqueTradable;
// use Auth;
// use Charts;
use DB;
use Session;
use Validator;
// use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// use Illuminate\Routing\Router;

class CompanyController extends Controller
{
	public function update(Request $request)
	{
		$taxableEntity = TaxableEntity::theCompany();
		if (!$taxableEntity) {
			return redirect(HistoryHelper::goBackPages(2))->with('alert-warning', trans('forms.Company information can not be updated'));
		}
		$contact = $taxableEntity->contact()->orderBy('id', 'desc')->first();
		$billingAddress = $taxableEntity->defaultBillingAddress[0];
		$shippingAddress = $taxableEntity->defaultShippingAddress[0];

		// load company detail; errors imply redirect back, flashing input removes old value
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
							'title' => trans('forms.Update company information'),
							'post_url' => '/' . $request->path(),
							'type' => 'self',
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

	public function updatePost(Request $request)
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
			DB::transaction(function() use ($request) {
				// edit database
				$newData = $request->input();
				$newData['active'] = isset($newData['active']);
				TaxableEntity::theCompany()->synchronize($newData);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		// show meesage of success, and give option to go back to 'dashboard'
		return redirect(HistoryHelper::goBackPages(1))->with('alert-success', trans('forms.Company information updated.'));
	}
}
