<?php
namespace App\Http\Requests;
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
        return [
            'email' => ['required', 'email', 'unique:guests,email', 'bail'],
            'status' => 'required|integer',
        ];
    }
}
