<?php

namespace App\Http\Controllers;

use App;
use App\Address;
use App\Helpers\CountryHelper;
use App\Helpers\HistoryHelper;
use App\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;

use App\Http\Requests;

class UserController extends Controller
{
	public function changePassword()
	{
		return view()->first(generateTemplateCandidates('user.updatePassword'));
	}

	public function changePasswordUpdate(Request $request)
	{
		$user = Auth::user();

		// validate the info, create rules for the inputs
		$rules = array(
			'password' => 'required|min:6|confirmed',
		);

		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/password/update')
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request, $user) {
				$user->update([
						'password' => bcrypt($request->input('password')),
					]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.Password updated successfully'));
	}

	public function update()
	{
		$user = Auth::user();
		$address = $user->entity->defaultBillingAddress[0];
		preg_match("/^([a-z.\-A-Z]+)\:([1-9][0-9]+)\/(imap|imap2|imap4|imap2bis|imap4rev1|pop3|nntp)\/(ssl|tls|notls)$/i", $user->imap_endpoint, $imapSetting);
		preg_match("/^([a-z.\-A-Z]+)\:([1-9][0-9]+)\/(smtp)\/(ssl|tls|notls)$/i", $user->smtp_endpoint, $smtpSetting);
		$data = array(
			'name' => $user->name,
			'phone' => $user->phone,
			'email' => $user->email,
			//'language' => $user->language,
			'street' => $address->street,
			'unit' => $address->unit,
			'district' => $address->district,
			'city' => $address->city,
			'state' => $address->state,
			'country' => $address->country,
			'zipcode' => $address->zipcode,
			'imap_server' => isset($imapSetting[1]) ? $imapSetting[1] : '',
			'imap_port' => isset($imapSetting[2]) ? $imapSetting[2] : '',
			'imap_protocol' => isset($imapSetting[3]) ? $imapSetting[3] : '',
			'imap_encryption' => isset($imapSetting[4]) ? $imapSetting[4] : '',
			'smtp_server' => isset($smtpSetting[1]) ? $smtpSetting[1] : '',
			'smtp_port' => isset($smtpSetting[2]) ? $smtpSetting[2] : '',
			'smtp_protocol' => isset($smtpSetting[3]) ? $smtpSetting[3] : '',
			'smtp_encryption' => isset($smtpSetting[4]) ? $smtpSetting[4] : '',
			'email_password' => $user->email_password,
		);
		return view()->first(generateTemplateCandidates('user.updateProfile'), ['user' => $data, 'country' => CountryHelper::getAllCountryOptions()]);
	}

	public function updatePost(Request $request)
	{
		$user = Auth::user();
		$location = $user->entity->defaultBillingAddress[0];

		// validate the info, create rules for the inputs
		$rules = array(
			'name' => 'required|max:255',
			'email' => 'required|email|max:255',
			'phone' => 'required|min:10|max:15',
		);

		// run the validation rules on the inputs from the form
		$validator = Validator::make($request->all(), $rules);

		// if the validator fails, redirect back to the form
		if ($validator->fails()) {
			return redirect('/profile/update')
					->with('alert-warning', trans('messages.Please correct all errors'))
					->withErrors($validator) // send back all errors to the login form
					->withInput($request->all()); // send back the input (not the password) so that we can repopulate the form
		}

		try {
			DB::transaction(function() use ($request, $user, $location) {
				$user->update([
					'name' => $request->input('name'),
					'email' => $request->input('email'),
					'phone' => $request->input('phone'),
					'imap_endpoint' => $request->input('imap_server').':'.$request->input('imap_port').'/'.$request->input('imap_protocol').'/'.$request->input('imap_encryption'),
					'smtp_endpoint' => $request->input('smtp_server').':'.$request->input('smtp_port').'/'.$request->input('smtp_protocol').'/'.$request->input('smtp_encryption'),
					'email_password' => $request->input('email_pswd'),
				]);

				$location->update([
					'street' => $request->input('street'),
					'unit' => $request->input('unit'),
					'district' => $request->input('district'),
					'city' => $request->input('city'),
					'state' => $request->input('state'),
					'country' => $request->input('country'),
					'zipcode' => $request->input('zipcode'),
				]);
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.Account updated successfully.'));
	}
}
