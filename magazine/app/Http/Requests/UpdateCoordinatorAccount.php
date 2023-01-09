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
            'new_password' =>['same:confirm_password','bail'],
            'first_name' => ['same:required|min:2|max:40|bail'],
            'last_name' => ['same:required|min:2|max:80|bail'],
            'gender' => 'required|integer',
            'dateOfBirth' => ['required', 'date_format:d/m/Y']
        ];
    }
    public function messages()
    {
        return [
            'new_password.same' =>  'Confirm Password must be coincided with New Password',
            'first_nam.same' => 'The length must longer than 2 character',
            'last_name.same' => 'The length must longer than 2 character',
        ];
    }
}
