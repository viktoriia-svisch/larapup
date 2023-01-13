<?php
namespace App\Http\Requests;
use App\Rules\CheckAgeAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UpdateStudentAccount extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(STUDENT_GUARD)->check() || Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        return [
            'new_password' =>['same:confirm_password','bail'],
            'first_name' => 'required|min:2|max:40|bail',
            'last_name' => 'required|min:2|max:80|bail',
            'gender' => 'required|integer',
            'dateOfBirth' => ['required', 'date_format:d/m/Y', new CheckAgeAccount]
        ];
    }
    public function messages()
    {
        return [
            'new_password.same' =>  'Confirm Password must be coincided with New Password',
        ];
    }
}
