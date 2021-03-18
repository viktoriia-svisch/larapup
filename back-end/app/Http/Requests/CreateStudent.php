<?php
namespace App\Http\Requests;
use App\Rules\CheckStudentEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CreateStudent extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'email' => ['required', 'email', new CheckStudentEmail(), 'bail'],
            'password' => 'required|min:3|bail',
            'first_name' => 'required|min:2|bail',
            'last_name' => 'required|min:2|bail',
        ];
    }
    public function messages()
    {
        return [
            'email.required' =>  'Please input the email',
            'email.email' => 'Please input valid email',
            'password.password' => 'Please input the password',
            'password.min' => 'Password must contain at least 3 characters',
            'first_name.required' =>  'Please input first name',
            'first_name.min' => 'Name must contain at least 2 characters',
            'last_name.required' =>  'Please input last name',
            'last_name.min' => 'Name must contain at least 2 characters',
        ];
    }
}
