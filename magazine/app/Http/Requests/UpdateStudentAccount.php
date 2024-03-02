<?php
namespace App\Http\Requests;
use App\Rules\CheckAgeAccount;
use App\Rules\CheckStudentEmailSelf;
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
        $rule = [
            'new_password' => ['bail', 'same:confirm_password'],
            'first_name' => 'required|min:2|max:40|bail',
            'last_name' => 'required|min:2|max:80|bail',
            'gender' => 'required|integer|between:1,2',
            'dateOfBirth' => ['required', 'date_format:d/m/Y', new CheckAgeAccount]
        ];
        if ($this->get("old_password")) {
            array_merge($rule, ["old_password" => 'required']);
        }
        if (Auth::guard(ADMIN_GUARD)->check() && $this->get("email")) {
            array_merge($rule, ["student_id" => 'required|exists:students,id']);
            array_merge($rule, ["email" => ['required', 'email', new CheckStudentEmailSelf($this)]]);
        }
        return $rule;
    }
    public function messages()
    {
        return [
            'new_password.same' => 'Confirm Password must be coincided with New Password',
            'first_name.*' => 'The First Name must be between 2 and 40 characters',
            'last_name.*' => 'The Last Name must be between 2 and 80 characters'
        ];
    }
    public function attributes()
    {
        return [
            "new_password" => "new password",
            "confirm_password" => "re-enter password",
            "old_password" => "old password",
            "first_name" => "first name",
            "last_name" => "last name",
            "dateOfBirth" => "date of birth",
        ];
    }
}
