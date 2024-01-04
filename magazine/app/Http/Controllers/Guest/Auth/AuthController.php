<?php
namespace App\Http\Controllers\Guest\Auth;
use App\Http\Controllers\Controller;
use App\Models\FacultySemester;
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
    protected $redirectTo = '/';
    public function showLoginForm()
    {
        return view('guest.auth.login');
    }
    public function redirectTo()
    {
        $semesterFaculty = FacultySemester::with('faculty')
            ->where("faculty_id", Auth::guard(GUEST_GUARD)->user()->faculty_id)->first();
        $this->redirectTo = route("shared.listPublishes", [$semesterFaculty->faculty_id, $semesterFaculty->semester_id]);
        return $this->redirectTo;
    }
    protected function loggedOut(Request $request)
    {
        self::flushAuth($request);
        return redirect(route('guest.login'));
    }
    public function flushAuth(Request $request)
    {
        Auth::guard(GUEST_GUARD)->logout();
        $request->session()->invalidate();
    }
    protected function guard()
    {
        return Auth::guard(GUEST_GUARD);
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
                'required', 'email', new AccountStatus($request, GUEST_GUARD)
            ]
        ], [
            'email.required' => __('auth.failed'),
            'password.required' => __('auth.failed'),
        ]);
    }
}
