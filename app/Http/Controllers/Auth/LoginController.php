<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

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
    protected $redirectTo = RouteServiceProvider::DASHBOARD;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required',
        ]);
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username());
    }

    protected function attemptLogin(Request $request)
    {
        $user = User::where('no_ktp', $request->no_ktp)->first();
        if(is_null($user))
        {
            return $this->sendFailedLoginResponse($request);
        }

        if($user->status === 0){
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.not_active')],
            ]);
        }

        auth()->loginUsingId($user->id);
        $roles = [];
        foreach(auth()->user()->roles as $role){
            array_push($roles, $role->role_name);
        }
        Session::put('user_roles', $roles);
        return true;
    }

    public function username()
    {
        return 'no_ktp';
    }
}
