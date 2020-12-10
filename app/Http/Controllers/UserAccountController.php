<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\Role;
use App\ChartAccount;
use App\Address;
use App\TaxableEntity;
use App\User;
use App\Helpers\HistoryHelper;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Validator;

class UserAccountController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$data = User::orderBy('id','DESC')->paginate(10);
		$controlSwitch = [ 'user-modal' => Auth::user()->can([ 'user-create', 'user-view', 'user-edit' ]) ];
		return view()->first(generateTemplateCandidates('user.index'),compact('data', 'controlSwitch'))
			->with('i', ($request->input('page', 1) - 1) * 10);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$roles = Role::all()->pluck('display_name','id');
		return view()->first(generateTemplateCandidates('user.create'),compact('roles'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name' => 'required',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|same:confirm-password',
			'confirm-password' => 'required|same:password',
			'roles' => 'required'
		]);

		try {
			DB::transaction(function() use ($request) {
				$theCompany = TaxableEntity::theCompany();
				$billingAddress = $theCompany->defaultbillingAddress[0];
				$shippingAddress = $theCompany->defaultShippingAddress[0];
				$additionalData = [
					'active' => 1,
					'type' => 'employee',
					'currency' => $theCompany->currency_id,
					'code' => 'XXXXXXX',
					'payment' => $theCompany->payment_term_id,
					'contact' => $request->input('name'),
					'phone' => '',
					'bunit' => $billingAddress->unit,
					'bstreet' => $billingAddress->street,
					'bdistrict' => $billingAddress->district,
					'bcity' => $billingAddress->city,
					'bstate' => $billingAddress->state,
					'bcountry' => $billingAddress->country,
					'bzipcode' => $billingAddress->zipcode,
					'sunit' => $shippingAddress->unit,
					'sstreet' => $shippingAddress->street,
					'sdistrict' => $shippingAddress->district,
					'scity' => $shippingAddress->city,
					'sstate' => $shippingAddress->state,
					'scountry' => $shippingAddress->country,
					'szipcode' => $shippingAddress->zipcode,
				];
				$request->merge($additionalData);
				$taxableEntity = TaxableEntity::initialize($request);

				$taxableEntity->update([
						'code' => 'E' . sprintf('%06u', $taxableEntity->id),
						'tax_id' => sprintf('%010u', $taxableEntity->id),
					]);
				$taxableEntity->revenueChartAccount->update([
					'description' => 'revenue account of E' . sprintf('%06u', $taxableEntity->id),
				]);
				$taxableEntity->transactionChartAccount->update([
					'description' => 'transactable account of E' . sprintf('%06u', $taxableEntity->id),
				]);
				$user = $taxableEntity->contact()->where('email', $request->input('email'))->first();
				$user->update([
					'password' => Hash::make($request->input('password')),
				]);

				foreach ($request->input('roles') as $key => $value) {
					$user->attachRole($value);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.User created successfully'));
	}

	public function createPostAjax(Request $request)
	{
		$validator = Validator::make($request->input(), [
			'name' => 'required',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|same:confirm-password',
			'confirm-password' => 'required|same:password',
			'roles' => 'required'
		]);

		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$user = null;
		try {
			DB::transaction(function() use (&$user, $request) {
				$theCompany = TaxableEntity::theCompany();
				$billingAddress = $theCompany->defaultbillingAddress[0];
				$shippingAddress = $theCompany->defaultShippingAddress[0];
				$additionalData = [
					'active' => 1,
					'type' => 'employee',
					'currency' => $theCompany->currency_id,
					'code' => 'XXXXXXX',
					'payment' => $theCompany->payment_term_id,
					'contact' => $request->input('name'),
					'phone' => '',
					'bunit' => $billingAddress->unit,
					'bstreet' => $billingAddress->street,
					'bdistrict' => $billingAddress->district,
					'bcity' => $billingAddress->city,
					'bstate' => $billingAddress->state,
					'bcountry' => $billingAddress->country,
					'bzipcode' => $billingAddress->zipcode,
					'sunit' => $shippingAddress->unit,
					'sstreet' => $shippingAddress->street,
					'sdistrict' => $shippingAddress->district,
					'scity' => $shippingAddress->city,
					'sstate' => $shippingAddress->state,
					'scountry' => $shippingAddress->country,
					'szipcode' => $shippingAddress->zipcode,
				];
				$request->merge($additionalData);
				$taxableEntity = TaxableEntity::initialize($request);

				$taxableEntity->update([
						'code' => 'E' . sprintf('%06u', $taxableEntity->id),
						'tax_id' => sprintf('%010u', $taxableEntity->id),
					]);
				$taxableEntity->revenueChartAccount->update([
					'description' => 'revenue account of E' . sprintf('%06u', $taxableEntity->id),
				]);
				$taxableEntity->transactionChartAccount->update([
					'description' => 'transactable account of E' . sprintf('%06u', $taxableEntity->id),
				]);
				$user = $taxableEntity->contact()->where('email', $request->input('email'))->first();
				$user->update([
					'password' => Hash::make($request->input('password')),
				]);

				foreach ($request->input('roles') as $key => $value) {
					$user->attachRole($value);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'roles' => $user->roles->pluck('display_name'),
				'can_view' => Auth::user()->can('user-view'),
				'can_edit' => Auth::user()->can('user-edit')
			]
		]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		$user = User::find($id);
		return view()->first(generateTemplateCandidates('user.show'),compact('user'));
	}

	public function loadUserAjax($id)
	{
		if ($id) {
			$user = User::find($id);

			return response()->json([
				'success' => true,
				'data' => [
					'id' => $user->id,
					'csrf' => csrf_token(),
					'name' => $user->name,
					'email' => $user->email,
					'password' => '',
					'password_confirm' => '',
					'roles' => $user->roles->pluck('id'),
				]
			]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => 0,
				'csrf' => csrf_token(),
				'name' => '',
				'email' => '',
				'password' => '',
				'password_confirm' => '',
				'roles' => [ ],
			]
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$user = User::find($id);
		$roles = Role::all()->pluck('display_name','id');
		$userRole = $user->roles->pluck('id','id')->toArray();

		return view()->first(generateTemplateCandidates('user.edit'),compact('user','roles','userRole'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$this->validate($request, [
			'name' => 'required',
			'email' => 'required|email|unique:users,email,'.$id,
			'password' => 'same:confirm-password',
			'confirm-password' => 'same:password',
			'roles' => 'required'
		]);

		$input = $request->all();
		if(!empty($input['password'])){
			$input['password'] = Hash::make($input['password']);
		}else{
			$input = array_except($input,array('password'));
		}

		try {
			DB::transaction(function() use ($id, $input, $request) {
				$user = User::find($id);
				$user->update($input);
				DB::table('role_user')->where('user_id',$id)->delete();

				foreach ($request->input('roles') as $key => $value) {
					$user->attachRole($value);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return redirect(HistoryHelper::goBackPages(1))->with('alert-warning', trans('messages.System failure') . ' #' . $registration);
		}

		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.User updated successfully'));
	}

	public function updatePostAjax(Request $request, $id)
	{
		$validator = Validator::make($request->all(), [
				'name' => 'required',
				'email' => 'required|email|unique:users,email,'.$id,
				'password' => 'same:confirm-password',
				'confirm-password' => 'same:password',
				'roles' => 'required'
			]);

		if ($validator->fails()) {
			return response()->json([ 'success' => false, 'errors' => $validator->errors() ]);
		}

		$input = $request->all();
		if(!empty($input['password'])){
			$input['password'] = Hash::make($input['password']);
		}else{
			$input = array_except($input,array('password'));
		}

		$user = User::find($id);
		try {
			DB::transaction(function() use ($user, $input, $request) {
				$user->update($input);
				DB::table('role_user')->where('user_id',$user->id)->delete();

				foreach ($request->input('roles') as $key => $value) {
					$user->attachRole($value);
				}
			});
		} catch (\Exception $e) {
			$registration = recordAndReportProblem($e);
			return response()->json([ 'success' => false, 'errors' => [ 'general' => [ trans('messages.System failure') . ' #' . $registration ]]]);
		}

		return response()->json([
			'success' => true,
			'data' => [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'roles' => $user->roles->pluck('display_name'),
				'can_view' => Auth::user()->can('user-view'),
				'can_edit' => Auth::user()->can('user-edit')
			]
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		User::find($id)->delete();
		return redirect(HistoryHelper::goBackPages(2))->with('alert-success', trans('messages.User deleted successfully'));
	}
}

?>
