<?php
namespace App\Http\Requests;
use App\Rules\CheckAgeAccount;
use App\Rules\CheckStudentEmailSelf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UpdateStudentByAdmin extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(STUDENT_GUARD)->check() || Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        $rule = [
            'first_name' => 'required|min:2|max:40|bail',
            'last_name' => 'required|min:2|max:80|bail',
            'gender' => 'required|integer|between:1,2',
            'status' => 'required|integer',
            'dateOfBirth' => ['required', 'date_format:d/m/Y', new CheckAgeAccount]
        ];
        if (Auth::guard(ADMIN_GUARD)->check() && $this->get("email")) {
            array_merge($rule, ["student_id" => 'required|exists:students,id']);
            array_merge($rule, ["email" => ['required', 'email', new CheckStudentEmailSelf($this)]]);
        }
        return $rule;
    }
    public function messages()
    {
        return [
            'first_name' => 'The First Name must be between 2 and 40 characters',
            'last_name' => 'The Last Name must be between 2 and 80 characters'
        ];
    }
}
