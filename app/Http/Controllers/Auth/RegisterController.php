<?php

namespace App\Http\Controllers\Auth;

use App\Address;
use App\ChartAccount;
use App\TaxableEntity;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use DB;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
      $user = null;
      try {
        DB::transaction(function() use ($data, &$user) {
          $theCompany = TaxableEntity::theCompany();
          $additionalData = [
            'active' => 1,
            'type' => 'employee',
            'currency' => $theCompany->currency_id,
            'code' => 'XXXXXXX',
            'payment' => $theCompany->payment_term_id,
            'contact' => $data['name'],
            'bunit' => $data['unit'],
            'bstreet' => $data['street'],
            'bdistrict' => $data['district'],
            'bcity' => $data['city'],
            'bstate' => $data['state'],
            'bcountry' => $data['country'],
            'bzipcode' => $data['zipcode'],
            'sunit' => $data['unit'],
            'sstreet' => $data['street'],
            'sdistrict' => $data['district'],
            'scity' => $data['city'],
            'sstate' => $data['state'],
            'scountry' => $data['country'],
            'szipcode' => $data['zipcode'],
          ];
          $taxableEntity = TaxableEntity::initialize(array_merge($data, $additionalData));

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
  				$user = $taxableEntity->contact()->where('email', $data['email'])->first();
  				$user->update([
  					'password' => Hash::make($data['password']),
  				]);
        });
      } catch (\Exception $e) {
        $registration = recordAndReportProblem($e);
      }

  		if (User::count() == 2) {
  			// this newly created user is the first user of system.  Make it administrator
  			DB::table("role_user")->insert([
  					'user_id' => $user->id,
  					'role_id' => DB::table('roles')->first()->id,
  				]);
  		}

  		return $user;
    }
}
