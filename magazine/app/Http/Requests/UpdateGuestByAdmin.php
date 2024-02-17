<?php
namespace App\Http\Requests;
use App\Rules\CheckGuestEmailSelf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UpdateGuestByAdmin extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(GUEST_GUARD)->check() || Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        $rule = [
            'status' => 'required|integer|between:0,1',
            "guest_id" => 'required|exists:guests,id',
            "email" => ['required', 'email', new CheckGuestEmailSelf($this)]
        ];
        return $rule;
    }
}
