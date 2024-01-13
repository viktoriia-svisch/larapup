<?php
namespace App\Http\Controllers\Coordinator\Auth;
use App\Http\Controllers\Controller;
use App\Rules\AccountStatus;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    use AuthenticatesUsers;
    protected $redirectTo = '/coordinator/dashboard';
    public function showLoginForm()
    {
        return view('coordinator.auth.login');
    }
    public function redirectTo()
    {
        return $this->redirectTo;
    }
    protected function loggedOut(Request $request)
    {
        self::flushAuth($request);
        return redirect(route('coordinator.login'));
    }
    public function flushAuth(Request $request)
    {
        Auth::guard(COORDINATOR_GUARD)->logout();
        $request->session()->invalidate();
    }
    protected function guard()
    {
        return Auth::guard(COORDINATOR_GUARD);
    }
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
    public function username()
    {
        return 'email';
    }
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'password' => [
                'required', 'min:6'
            ],
            'email' => [
                'required', 'email', new AccountStatus($request, COORDINATOR_GUARD)
            ]
        ], [
            'email.required' => __('auth.failed'),
            'password.required' => __('auth.failed'),
        ]);
    }
}
