<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;

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

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function attemptLogin(Request $request)
    {
        $attempt = $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );

        if ($attempt) {
            $user = $this->guard()->user();

            if ($user->status === 1) {
                return true;
            } else {
                $this->guard()->logout();

                $adminEmailsConfig = config('app.system_admin_emails');
                $firstAdminEmail = 'the administrator';

                if ($adminEmailsConfig) {
                    $adminEmails = array_map('trim', explode(';', $adminEmailsConfig));
                    if (!empty($adminEmails[0])) {
                        $firstAdminEmail = $adminEmails[0];
                    }
                }

                throw ValidationException::withMessages([
                    $this->username() => [trans('auth.inactive', ['email' => $firstAdminEmail])],
                ]);
            }
        }

        return false;
    }

    protected function credentials(Request $request)
    {
         return $request->only($this->username(), 'password');
    }
}