<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UpdateCoordinatorAccount extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(COORDINATOR_GUARD)->check() || Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        return [
            'new_password' =>['same:confirm_password','bail']
        ];
    }
    public function messages()
    {
        return [
            'new_password.same' =>  'Confirm Password must be coincided with New Password',
        ];
    }
}
