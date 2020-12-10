<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\S3DownloadHelper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords {
      sendResetResponse as sendResetResponseCore;
    }

    /**
     * Where to redirect users after resetting their password.
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

    //  Get the response for a successful password reset.
    protected function sendResetResponse(Request $request, $response)
    {
        session([
          'session_language' => auth()->user()->language,
          'session_url_links' => [ $this->redirectTo ],
          // any other variables to stuff into session?
        ]);

        return $this->sendResetResponseCore($request, $response);
    }

}
