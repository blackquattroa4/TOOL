<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\S3DownloadHelper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    protected $entityTypes = [ 'employee' ];

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    /**
  	 * Get the needed authorization credentials from the request.
  	 *
  	 * @param  \Illuminate\Http\Request  $request
  	 * @return array
  	 */
  	protected function credentials(Request $request)
  	{
  		$credentials = $request->only('email', 'password');

      $user = User::where('email', $credentials['email'])->first();
      if ($user) {
    		$entity = $user->belongsTo('App\TaxableEntity', 'entity_id')->first();
    		if (!in_array($entity->type, $this->entityTypes)) {
    			// entity_id is never 0, this is to intentionally fail login-attempt
    			return array_merge($credentials, ['entity_id' => 0]);
    		}
      } else {
        return array_merge($credentials, ['entity_id' => 0]);
      }

  		return array_merge($credentials, ['active' => 1]);
  	}

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {

      // at this point, $request->session() is not the same as session()

      session([
        'session_language' => $user->language,
        'session_url_links' => [ $this->redirectTo ],
        // any other variables to stuff into session?
      ]);

      if ($request->get('url')) {
        return redirect($request->get('url'));
      }
    }

    public function logout(Request $request) {
      optional(Auth::user())->update([
      	'last_login' => date("Y-m-d H:i:s"),
      ]);
      Auth::logout();
      session()->flush();
      return redirect('/login');
    }
}
