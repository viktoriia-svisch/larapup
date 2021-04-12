<?php
namespace App\Http\Controllers\Student\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    use AuthenticatesUsers;
    protected $redirectTo = '/student/dashboard';
    public function showLoginForm()
    {
        return view('student.auth.login');
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
        return Auth::guard(STUDENT_GUARD);
    }
}
