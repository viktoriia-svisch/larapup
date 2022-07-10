<?php
namespace App\Http\Controllers\Coordinator\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    use AuthenticatesUsers;
    protected $redirectTo = '/coordinator/dashboard';
    public function showLoginForm()
    {
        return view('student.auth.admin');
    }
    public function redirectTo()
    {
        return $this->redirectTo;
    }
    protected function loggedOut(Request $request)
    {
        self::flushAuth($request);
        return redirect('/login');
    }
    public function flushAuth(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
    }
    protected function guard()
    {
        return Auth::guard(COORDINATOR_GUARD);
    }
    public function username()
    {
        return 'email';
    }
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'password' => [
                'required', 'min:6'
            ],
            'email' => [
                'required', 'email'
            ]
        ], [
            'email.required' => __('auth.failed'),
            'password.required' => __('auth.failed'),
        ]);
    }
}
