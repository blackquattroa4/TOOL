<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    // type of entity allowed
    protected $entityType = "employee";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validateEmail(Request $request)
    {
        $user = User::where([['email', $request->input('email')], ['active', 1]])->first();
        $requirement = 'required|email';
        if ($user->entity->type != $this->entityType) {
            $requirement .= '|in:zzz';  // intentionally fail validation.
        }
        $this->validate($request, ['email' => $requirement]);
    }
}
