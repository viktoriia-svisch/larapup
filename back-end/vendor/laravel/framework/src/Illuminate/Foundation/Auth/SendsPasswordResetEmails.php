<?php
namespace Illuminate\Foundation\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
trait SendsPasswordResetEmails
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );
        return $response == Password::RESET_LINK_SENT
                    ? $this->sendResetLinkResponse($request, $response)
                    : $this->sendResetLinkFailedResponse($request, $response);
    }
    protected function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    }
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return back()->with('status', trans($response));
    }
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => trans($response)]);
    }
    public function broker()
    {
        return Password::broker();
    }
}
